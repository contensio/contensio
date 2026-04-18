{{--
 | Contact form partial — rendered by contact.blade.php
 | Variables expected: $fields, $lang, $settings, $gdpr, $fileCfg,
 |   $antispam, $recaptcha, $turnstile, $locale,
 |   $inputSizeClass, $labelSizeClass, $btnSizeClass
--}}

@php
    $contactUrl = request()->url();
    $mathEnabled = ($antispam['math_question']['enabled'] ?? false);
    $honeypot    = $antispam['honeypot'] ?? true;
    $timeCheck   = $antispam['time_check'] ?? true;
@endphp

{{-- Success message --}}
@if(session('contact_success'))
<div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-xl px-6 py-5 flex items-start gap-3">
    <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p class="text-sm font-medium">{{ session('contact_success') }}</p>
</div>
@endif

{{-- General antispam error --}}
@if($errors->has('_antispam'))
<div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl px-6 py-4 text-sm">
    {{ $errors->first('_antispam') }}
</div>
@endif

<form method="POST" action="{{ $contactUrl }}"
      enctype="{{ ($fileCfg['enabled'] ?? false) ? 'multipart/form-data' : 'application/x-www-form-urlencoded' }}"
      novalidate>
    @csrf

    {{-- Honeypot --}}
    @if($honeypot)
    <div aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
        <label for="_hp_website">Website</label>
        <input type="text" name="_hp_website" id="_hp_website" tabindex="-1" autocomplete="off" value="">
    </div>
    @endif

    {{-- Time check --}}
    @if($timeCheck)
    <input type="hidden" name="_form_time" value="{{ time() }}">
    @endif

    <div class="grid grid-cols-12 gap-5">

        @foreach($fields->sortBy('sort_order') as $field)
        @php
            $translation = $lang ? $field->translationFor($lang->id) : null;
            $label       = $translation?->label ?: ucfirst(str_replace('_', ' ', $field->key));
            $placeholder = $translation?->placeholder ?? '';
            $helpText    = $translation?->help_text ?? '';
            $inputName   = $field->is_default ? $field->key : "extra_{$field->key}";
            $oldValue    = old($inputName);
            $hasError    = $errors->has($inputName);
            $errorMsg    = $errors->first($inputName);

            $baseInput = "w-full rounded-lg border transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent {$inputSizeClass} "
                . ($hasError ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white');

            $colSpan = match($field->width ?? 'full') {
                'half' => 'col-span-12 sm:col-span-6',
                '1/3'  => 'col-span-12 sm:col-span-4',
                '1/4'  => 'col-span-12 sm:col-span-3',
                default => 'col-span-12',
            };

            // Conditional logic
            $conditional = $field->conditional;
            $condAttr    = '';
            if ($conditional && !empty($conditional['field'])) {
                $condJson = json_encode($conditional);
                $condAttr = 'x-show="contactFieldVisible(' . htmlspecialchars($condJson, ENT_QUOTES) . ')"';
            }
        @endphp

        <div class="{{ $colSpan }}" {{ $condAttr ? $condAttr : '' }}
             @if($conditional && !empty($conditional['field'])) x-cloak @endif>

            <label for="cf_{{ $inputName }}" class="{{ $labelSizeClass }} font-medium text-gray-700 block mb-1.5">
                {{ $label }}
                @if($field->required) <span class="text-red-500 ml-0.5">*</span> @endif
            </label>

            @if($field->type === 'textarea')
                <textarea id="cf_{{ $inputName }}"
                          name="{{ $inputName }}"
                          rows="5"
                          placeholder="{{ $placeholder }}"
                          {{ $field->required ? 'required' : '' }}
                          class="{{ $baseInput }} resize-y">{{ $oldValue }}</textarea>

            @elseif($field->type === 'select')
                <select id="cf_{{ $inputName }}"
                        name="{{ $inputName }}"
                        {{ $field->required ? 'required' : '' }}
                        class="{{ $baseInput }} cursor-pointer">
                    <option value="">{{ $placeholder ?: __('contensio::frontend.contact.select_option') }}</option>
                    @foreach($field->options['choices'] ?? [] as $choice)
                    <option value="{{ $choice }}" @selected($oldValue === $choice)>{{ $choice }}</option>
                    @endforeach
                </select>

            @elseif($field->type === 'multiselect')
                <select id="cf_{{ $inputName }}"
                        name="{{ $inputName }}[]"
                        multiple
                        {{ $field->required ? 'required' : '' }}
                        class="{{ $baseInput }} cursor-pointer h-32">
                    @foreach($field->options['choices'] ?? [] as $choice)
                    <option value="{{ $choice }}" @selected(in_array($choice, (array)($oldValue ?? [])))>{{ $choice }}</option>
                    @endforeach
                </select>

            @elseif($field->type === 'checkbox')
                <div class="flex items-start gap-2 mt-1">
                    <input type="checkbox"
                           id="cf_{{ $inputName }}"
                           name="{{ $inputName }}"
                           value="1"
                           @checked($oldValue)
                           {{ $field->required ? 'required' : '' }}
                           class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    @if($placeholder)
                    <label for="cf_{{ $inputName }}" class="{{ $labelSizeClass }} text-gray-600 cursor-pointer leading-relaxed">
                        {{ $placeholder }}
                    </label>
                    @endif
                </div>

            @elseif($field->type === 'rating')
                <div class="flex items-center gap-1 mt-1" x-data="{ rating: {{ (int)($oldValue ?? 0) }} }">
                    <input type="hidden" name="{{ $inputName }}" :value="rating">
                    @for($star = 1; $star <= 5; $star++)
                    <button type="button"
                            @click="rating = {{ $star }}"
                            :class="rating >= {{ $star }} ? 'text-amber-400' : 'text-gray-300'"
                            class="text-3xl leading-none transition-colors hover:text-amber-400">★</button>
                    @endfor
                </div>

            @elseif($field->type === 'file')
                <input type="file"
                       id="cf_{{ $inputName }}"
                       name="{{ $inputName }}"
                       {{ $field->required ? 'required' : '' }}
                       class="{{ $labelSizeClass }} block w-full text-gray-700 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">

            @else
                {{-- text, email, phone, date, url --}}
                @php
                    $inputType = match($field->type) {
                        'email' => 'email',
                        'phone' => 'tel',
                        'date'  => 'date',
                        'url'   => 'url',
                        default => 'text',
                    };
                    $maxLen = $field->options['max_length'] ?? null;
                    $minLen = $field->options['min_length'] ?? null;
                @endphp
                <input type="{{ $inputType }}"
                       id="cf_{{ $inputName }}"
                       name="{{ $inputName }}"
                       value="{{ $oldValue }}"
                       placeholder="{{ $placeholder }}"
                       {{ $field->required ? 'required' : '' }}
                       @if($maxLen) maxlength="{{ $maxLen }}" @endif
                       @if($minLen) minlength="{{ $minLen }}" @endif
                       class="{{ $baseInput }}">
            @endif

            @if($helpText)
            <p class="mt-1 text-xs text-gray-500">{{ $helpText }}</p>
            @endif

            @if($hasError)
            <p class="mt-1 text-xs text-red-600">{{ $errorMsg }}</p>
            @endif
        </div>
        @endforeach

        {{-- File uploads --}}
        @if(($fileCfg['enabled'] ?? false))
        @php $allowedTypes = implode(',', array_map(fn($t) => '.' . $t, (array)($fileCfg['allowed_types'] ?? ['jpg','png','pdf']))); @endphp
        <div class="col-span-12">
            <label class="{{ $labelSizeClass }} font-medium text-gray-700 block mb-1.5">
                {{ __('contensio::frontend.contact.attachments') }}
                <span class="font-normal text-gray-400 ml-1">({{ __('contensio::frontend.contact.optional') }})</span>
            </label>
            <input type="file" name="attachments[]" multiple accept="{{ $allowedTypes }}"
                   class="{{ $labelSizeClass }} block w-full text-gray-700 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
            <p class="mt-1 text-xs text-gray-400">
                {{ __('contensio::frontend.contact.max_files', ['n' => $fileCfg['max_files'] ?? 3]) }}
                &nbsp;·&nbsp;{{ __('contensio::frontend.contact.max_size', ['size' => ($fileCfg['max_size_mb'] ?? 5) . ' MB']) }}
                &nbsp;·&nbsp;{{ implode(', ', array_map('strtoupper', (array)($fileCfg['allowed_types'] ?? ['jpg','png','pdf']))) }}
            </p>
            @error('attachments')   <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @error('attachments.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        @endif

        {{-- GDPR consent --}}
        @if(($gdpr['enabled'] ?? false))
        @php
            $gdprText = $gdpr['text'][$locale] ?? $gdpr['text']['en'] ?? __('contensio::frontend.contact.gdpr_default');
            $required = $gdpr['required'] ?? true;
        @endphp
        <div class="col-span-12">
            <div class="flex items-start gap-2.5">
                <input type="checkbox" id="gdpr_consent" name="gdpr_consent" value="1"
                       @checked(old('gdpr_consent')) {{ $required ? 'required' : '' }}
                       class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 shrink-0">
                <label for="gdpr_consent" class="{{ $labelSizeClass }} text-gray-600 leading-relaxed cursor-pointer">
                    {!! $gdprText !!}
                    @if($required) <span class="text-red-500 ml-0.5">*</span> @endif
                </label>
            </div>
            @error('gdpr_consent')
            <p class="mt-1 text-xs text-red-600">{{ __('contensio::frontend.contact.gdpr_required') }}</p>
            @enderror
        </div>
        @endif

        {{-- Math question --}}
        @if($mathEnabled)
        @php
            $a = session('contact_math_a', rand(1, 9));
            $b = session('contact_math_b', rand(1, 9));
            session(['contact_math_a' => $a, 'contact_math_b' => $b]);
        @endphp
        <div class="col-span-12 sm:col-span-6">
            <label for="math_answer" class="{{ $labelSizeClass }} font-medium text-gray-700 block mb-1.5">
                {{ $a }} + {{ $b }} = ? <span class="text-red-500">*</span>
            </label>
            <input type="text" id="math_answer" name="math_answer" value="{{ old('math_answer') }}"
                   autocomplete="off" required
                   class="w-28 rounded-lg border border-gray-300 {{ $inputSizeClass }} focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            @error('math_answer')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        @endif

        {{-- reCAPTCHA --}}
        @if(($recaptcha['enabled'] ?? false) && !empty($recaptcha['site_key']))
        <div class="col-span-12">
            <div class="g-recaptcha" data-sitekey="{{ $recaptcha['site_key'] }}"></div>
            @error('g-recaptcha-response')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        @endif

        {{-- Cloudflare Turnstile --}}
        @if(($turnstile['enabled'] ?? false) && !empty($turnstile['site_key']))
        <div class="col-span-12">
            <div class="cf-turnstile" data-sitekey="{{ $turnstile['site_key'] }}"></div>
        </div>
        @endif

        {{-- Submit --}}
        <div class="col-span-12">
            <button type="submit"
                    class="{{ $btnSizeClass }} font-semibold rounded-lg transition-colors theme-btn-primary">
                {{ __('contensio::frontend.contact.send_message') }}
            </button>
        </div>

    </div>
</form>

@if($fields->contains('conditional', '!=', null))
{{-- Conditional fields Alpine helper --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('contactForm', () => ({
        fields: {},
        fieldVisible(conditional) {
            if (!conditional || !conditional.field) return true;
            const val = this.fields[conditional.field] ?? '';
            const cmp = conditional.value ?? '';
            const op  = conditional.operator ?? 'equals';
            if (op === 'not_equals') return val != cmp;
            if (op === 'contains')   return String(val).includes(cmp);
            return val == cmp;
        },
    }));
});

function contactFieldVisible(conditional) {
    if (!conditional || !conditional.field) return true;
    const el  = document.querySelector('[name="' + conditional.field + '"], [name="extra_' + conditional.field + '"]');
    const val = el ? el.value : '';
    const cmp = conditional.value ?? '';
    const op  = conditional.operator ?? 'equals';
    if (op === 'not_equals') return val != cmp;
    if (op === 'contains')   return String(val).includes(cmp);
    return val == cmp;
}

// Re-evaluate on any input change
document.querySelectorAll('input, select, textarea').forEach(el => {
    el.addEventListener('change', () => {
        document.querySelectorAll('[x-show]').forEach(node => {
            if (node._x_bindings || node.__x) {
                // Let Alpine handle it — it will re-evaluate automatically
            }
        });
    });
});
</script>
@endif
