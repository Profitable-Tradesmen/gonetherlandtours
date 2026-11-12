<?php

namespace App\Http\Requests;

use App\Services\EnquiryService;
use App\Services\RecaptchaService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validation shared by EVERY enquiry form (quiz, modal, package, contact). */
class EnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Sanitise: strip tags and trim every text input; the honeypot is left untouched.
        $clean = [];
        foreach (['first_name', 'last_name', 'email', 'phone', 'travel_date', 'second_quote', 'special_request', 'season', 'group_size', 'nights', 'destination', 'travel_style', 'budget', 'package_name'] as $k) {
            if (is_string($this->input($k))) {
                $clean[$k] = trim(strip_tags($this->input($k)));
            }
        }
        if (is_array($this->input('quiz'))) {
            $clean['quiz'] = array_map(fn ($v) => is_string($v) ? trim(strip_tags($v)) : '', $this->input('quiz'));
        }
        $this->merge($clean);
    }

    public function rules(): array
    {
        return [
            'form_name' => ['required', 'string', 'in:'.implode(',', array_keys(config('gnt.form_names')))],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'country_code' => ['required', 'string', 'in:'.implode(',', array_column(config('gnt.countries'), 2))],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s().]{5,30}$/'],
            'travel_date' => ['nullable', 'date'],   // calendar picker sends YYYY-MM-DD
            'quiz.travel_date' => ['nullable', 'date'],
            // Rule::in (not the "in:" string form) because the option labels contain commas.
            'second_quote' => ['nullable', 'string', Rule::in(array_keys(config('gnt.second_quote_options')))],
            'special_request' => ['nullable', 'string', 'max:2000'],
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'season' => ['nullable', 'string', 'max:20'],
            'group_size' => ['nullable', 'string', 'max:20'],
            'nights' => ['nullable', 'string', 'max:20'],
            'destination' => ['nullable', 'string', 'max:120'],
            'travel_style' => ['nullable', 'string', 'max:120'],
            // The pop-up form requires an estimated budget; the other forms do not ask for it directly.
            'budget' => [Rule::requiredIf(fn () => $this->input('form_name') === 'header-modal'), 'nullable', 'string', 'max:60'],
            'quiz' => ['nullable', 'array', 'max:30'],
            'quiz.*' => ['nullable', 'string', 'max:120'],
            'page_url' => ['nullable', 'string', 'max:500'],
            'consent' => ['accepted'],
            'company_website' => ['nullable', 'max:0'],   // honeypot
            'g-recaptcha-response' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent.accepted' => 'Please tick the box to agree before sending your enquiry.',
            'g-recaptcha-response.required' => 'Please tick "I\'m not a robot".',
            'phone.regex' => 'Please enter a valid phone number.',
            'company_website.max' => 'Invalid submission.',
        ];
    }

    /** After the field rules pass: check the phone and verify reCAPTCHA on the server (fail closed). */
    public function after(): array
    {
        return [function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            if (! app(EnquiryService::class)->e164($this->input('country_code'), $this->input('phone'))) {
                $v->errors()->add('phone', 'Please enter a valid phone number.');

                return;
            }
            if (! app(RecaptchaService::class)->verify($this->input('g-recaptcha-response'), $this->ip())) {
                $v->errors()->add('g-recaptcha-response', 'We could not verify you are human. Please tick the box and try again.');
            }
        }];
    }
}
