<?php

namespace App\Services\Ghl;

use App\Models\Enquiry;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * GoHighLevel API v2 (services.leadconnectorhq.com) using a Private Integration Token.
 * OFF unless GHL_API_TOKEN and GHL_LOCATION_ID are both set. The token is only ever sent as a header.
 */
class GhlService
{
    public function enabled(): bool
    {
        return filled(config('services.ghl.token')) && filled(config('services.ghl.location_id'));
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(config('services.ghl.base_url'))
            ->withToken(config('services.ghl.token'))
            ->withHeaders(['Version' => config('services.ghl.version')])
            ->acceptJson()->asJson()->timeout(20);
    }

    private function loc(): string
    {
        return (string) config('services.ghl.location_id');
    }

    /** Check the token + location work. Returns the location name. */
    public function checkLocation(): string
    {
        $res = $this->http()->get('/locations/'.$this->loc())->throw();

        return (string) ($res->json('location.name') ?? $res->json('name') ?? 'unknown');
    }

    /** @return array<string,string> existing contact custom fields, name => id */
    public function existingFields(): array
    {
        $res = $this->http()->get('/locations/'.$this->loc().'/customFields', ['model' => 'contact'])->throw();

        return collect($res->json('customFields', []))->pluck('id', 'name')->all();
    }

    /** Field name => id map, cached for an hour. */
    public function fieldIds(bool $fresh = false): array
    {
        if ($fresh) {
            Cache::forget('ghl.field_ids');
        }

        return Cache::remember('ghl.field_ids', 3600, fn () => $this->existingFields());
    }

    /** Create any missing custom fields. Safe to re-run. @return array{created:array,existing:array} */
    public function setupFields(): array
    {
        $existing = $this->existingFields();
        $created = [];
        foreach (GhlFields::all() as $name) {
            if (isset($existing[$name])) {
                continue;
            }
            $dataType = in_array($name, [GhlFields::core('special_request'), GhlFields::core('quiz_answers')], true) ? 'LARGE_TEXT' : 'TEXT';
            $this->http()->post('/locations/'.$this->loc().'/customFields', [
                'name' => $name, 'dataType' => $dataType, 'model' => 'contact',
            ])->throw();
            $created[] = $name;
        }
        Cache::forget('ghl.field_ids');

        return ['created' => $created, 'existing' => array_values(array_diff(GhlFields::all(), $created))];
    }

    /** Field name => value pairs for one enquiry (first-touch included only when $withFirst). */
    public function fieldValues(Enquiry $e, bool $withFirst = true): array
    {
        $v = [
            GhlFields::core('travel_date') => $e->travel_date,
            GhlFields::core('second_quote') => $e->second_quote,
            GhlFields::core('special_request') => $e->special_request,
            GhlFields::core('package') => $e->package_name,
            GhlFields::core('form_name') => config('gnt.form_names.'.$e->form_name, $e->form_name),
            GhlFields::core('consent_given') => $e->consent_given ? 'Yes' : 'No',
            GhlFields::core('consent_at') => $e->consented_at?->toIso8601String(),
            GhlFields::core('consent_version') => $e->terms_version,
        ];

        $answers = $e->quiz_answers ?? [];
        if ($answers) {
            $v[GhlFields::core('quiz_answers')] = collect($answers)->map(fn ($val, $k) => ucfirst(str_replace('_', ' ', $k)).': '.$val)->implode("\n");
            foreach ($answers as $k => $val) {
                if ($k === 'travel_date') {
                    continue; // already sent as "Preferred Travel Date"
                }
                $v[GhlFields::quiz($k)] = $val;
            }
        }

        $last = $e->tracking_last ?? [];
        $first = $e->tracking_first ?? [];
        foreach (array_keys(GhlFields::TRACKING) as $k) {
            $v[GhlFields::tracking($k, 'last')] = $last[$k] ?? ($k === 'landing_page' ? ($e->page_url ?? null) : null);
            if ($withFirst) {
                $v[GhlFields::tracking($k, 'first')] = $first[$k] ?? null;
            }
        }

        return array_filter($v, fn ($x) => $x !== null && $x !== '');
    }

    public function tags(Enquiry $e): array
    {
        return array_values(array_filter([
            setting('brand_name', 'GoNetherlandTours'),
            data_get($e->tracking_first, 'utm_source') ? 'Source: '.data_get($e->tracking_first, 'utm_source') : 'Source: direct',
            $e->package_name ? 'Package: '.$e->package_name : null,
            config('gnt.form_names.'.$e->form_name, $e->form_name),
        ]));
    }

    /** Upsert the contact by email. Keeps first-touch fields already stored on an existing contact. */
    public function upsert(Enquiry $e): string
    {
        $ids = $this->fieldIds();
        $existingId = $this->findContactId($e->email);
        $withFirst = true;

        if ($existingId) {
            $current = $this->contactFieldValues($existingId);
            $firstField = GhlFields::tracking('utm_source', 'first');
            $firstLanding = GhlFields::tracking('landing_page', 'first');
            // Any first-touch value already stored means "do not overwrite first-touch".
            $withFirst = empty($current[$ids[$firstField] ?? ''] ?? null) && empty($current[$ids[$firstLanding] ?? ''] ?? null);
        }

        $values = $this->fieldValues($e, $withFirst);
        if (array_diff_key($values, $ids)) {
            // Some fields are not in the cached map: refresh once in case they were just created in GHL.
            $ids = $this->fieldIds(true);
        }
        $custom = [];
        foreach ($values as $name => $value) {
            if (isset($ids[$name])) {
                $custom[] = ['id' => $ids[$name], 'field_value' => (string) $value];
            }
        }

        $res = $this->http()->post('/contacts/upsert', [
            'locationId' => $this->loc(),
            'firstName' => $e->first_name,
            'lastName' => $e->last_name,
            'email' => $e->email,
            'phone' => $e->phone_e164,
            'tags' => $this->tags($e),
            'source' => 'Website: '.setting('brand_name', 'GoNetherlandTours'),
            'customFields' => $custom,
        ])->throw();

        $id = $res->json('contact.id');
        if (! $id) {
            throw new RuntimeException('GHL upsert returned no contact id.');
        }

        return $id;
    }

    private function findContactId(string $email): ?string
    {
        $res = $this->http()->get('/contacts/search/duplicate', ['locationId' => $this->loc(), 'email' => $email]);

        return $res->successful() ? $res->json('contact.id') : null;
    }

    /** @return array<string,mixed> field id => value for an existing contact */
    private function contactFieldValues(string $contactId): array
    {
        $res = $this->http()->get('/contacts/'.$contactId);

        return $res->successful()
            ? collect($res->json('contact.customFields', []))->pluck('value', 'id')->all()
            : [];
    }

    public function deleteContact(string $id): bool
    {
        return $this->http()->delete('/contacts/'.$id)->successful();
    }
}
