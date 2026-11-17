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
        return ghl_enabled();
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(config('services.ghl.base_url'))
            ->withToken((string) ghl_token())
            ->withHeaders(['Version' => config('services.ghl.version')])
            ->acceptJson()->asJson()->timeout(20);
    }

    private function loc(): string
    {
        return (string) ghl_location_id();
    }

    /**
     * Read-only health check for Admin > GoHighLevel. Tries each read scope on its own so a missing scope is named
     * instead of failing the whole test. Creates nothing and sends no contact.
     *
     * @return array{checks:array<int,array{scope:string,label:string,required:bool,ok:bool,error:?string}>,location:?string,total:int,missing:array<int,string>}
     */
    public function diagnose(): array
    {
        $all = GhlFields::all();
        $location = null;
        $missing = [];

        $probe = function (string $scope, string $label, bool $required, callable $call) {
            try {
                $call();

                return ['scope' => $scope, 'label' => $label, 'required' => $required, 'ok' => true, 'error' => null];
            } catch (\Throwable $e) {
                $msg = str_replace((string) ghl_token(), '[token]', $e->getMessage());

                return ['scope' => $scope, 'label' => $label, 'required' => $required, 'ok' => false, 'error' => \Illuminate\Support\Str::limit($msg, 200)];
            }
        };

        $checks = [
            $probe('locations.readonly', 'Read the sub-account name (optional)', false, function () use (&$location) {
                $location = $this->checkLocation();
            }),
            $probe('locations/customFields.readonly', 'Read custom fields', true, function () use (&$missing, $all) {
                $existing = $this->existingFields();
                $missing = array_values(array_filter($all, fn ($n) => ! isset($existing[$n])));
            }),
            $probe('contacts.readonly', 'Read contacts (finds existing contacts)', true, function () {
                $this->http()->get('/contacts/', ['locationId' => $this->loc(), 'limit' => 1])->throw();
            }),
        ];

        return ['checks' => $checks, 'location' => $location, 'total' => count($all), 'missing' => $missing];
    }

    /** Check the token + location work. Returns the location name. */
    public function checkLocation(): string
    {
        $res = $this->http()->get('/locations/'.$this->loc())->throw();

        return (string) ($res->json('location.name') ?? $res->json('name') ?? 'unknown');
    }

    /**
     * Existing contact custom fields, name => id. GoHighLevel derives a field's key from its name ("Special Request" becomes
     * contact.special_request) and refuses a second field with the same key. So a field that exists under a slightly
     * different name (e.g. "special request") but the same key is also returned under OUR name, and is reused instead of re-created.
     *
     * @return array<string,string>
     */
    public function existingFields(): array
    {
        $res = $this->http()->get('/locations/'.$this->loc().'/customFields', ['model' => 'contact'])->throw();
        $fields = collect($res->json('customFields', []));
        $map = $fields->pluck('id', 'name')->all();

        $byKey = [];
        foreach ($fields as $f) {
            if (! empty($f['fieldKey'])) {
                $byKey[GhlFields::normalizeKey((string) $f['fieldKey'])] = $f['id'];
            }
        }
        foreach (GhlFields::all() as $name) {
            if (! isset($map[$name]) && isset($byKey[GhlFields::keyFor($name)])) {
                $map[$name] = $byKey[GhlFields::keyFor($name)];
            }
        }

        return $map;
    }

    /** Field name => id map, cached for an hour. */
    public function fieldIds(bool $fresh = false): array
    {
        if ($fresh) {
            Cache::forget('ghl.field_ids');
        }

        return Cache::remember('ghl.field_ids', 3600, fn () => $this->existingFields());
    }

    /** Create any missing custom fields. Safe to re-run. @return array{created:array,existing:array,skipped:array} */
    public function setupFields(): array
    {
        $existing = $this->existingFields();
        $created = [];
        $skipped = [];
        foreach (GhlFields::all() as $name) {
            if (isset($existing[$name])) {
                continue;
            }
            $dataType = in_array($name, [GhlFields::core('special_request'), GhlFields::core('quiz_answers')], true) ? 'LARGE_TEXT' : 'TEXT';
            $res = $this->http()->post('/locations/'.$this->loc().'/customFields', [
                'name' => $name, 'dataType' => $dataType, 'model' => 'contact',
            ]);
            if ($res->status() === 400 && str_contains((string) $res->body(), 'already exists')) {
                $skipped[] = $name; // key taken by a field we cannot see by name: report it instead of failing
                continue;
            }
            $res->throw();
            $created[] = $name;
        }
        Cache::forget('ghl.field_ids');

        return ['created' => $created, 'skipped' => $skipped, 'existing' => array_values(array_diff(GhlFields::all(), $created, $skipped))];
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
        $existingId = $this->findContactId($e->email, $e->phone_e164);
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

    /** GHL treats a matching email OR phone as the same contact, so look up both (email first). */
    private function findContactId(string $email, ?string $phone = null): ?string
    {
        foreach (array_filter(['email' => $email, 'number' => $phone]) as $param => $value) {
            $res = $this->http()->get('/contacts/search/duplicate', ['locationId' => $this->loc(), $param => $value]);
            if ($res->successful() && $res->json('contact.id')) {
                return $res->json('contact.id');
            }
        }

        return null;
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
