{{--
 | Reusable toggle switch for settings pages.
 |
 | Variables:
 |   $name        — form field name (hidden input)
 |   $state       — Alpine initial state: 'true' or 'false'
 |   $label       — visible label text
 |   $description — hint text below the label (optional)
--}}
<div x-data="{ on: {{ $state }} }" class="flex items-start gap-4">
    <input type="hidden" name="{{ $name }}" :value="on ? '1' : '0'">
    <button type="button" @click="on = !on"
            :class="on ? 'bg-ember-500' : 'bg-gray-200'"
            class="relative shrink-0 mt-0.5 inline-flex h-6 w-11 items-center rounded-full
                   transition-colors duration-200 focus:outline-none focus:ring-2
                   focus:ring-ember-500 focus:ring-offset-2">
        <span :class="on ? 'translate-x-5' : 'translate-x-0.5'"
              class="inline-block h-5 w-5 transform rounded-full bg-white shadow
                     transition-transform duration-200"></span>
    </button>
    <div @click="on = !on" class="cursor-pointer">
        <span class="text-sm font-medium text-gray-800">{{ $label }}</span>
        @if(!empty($description))
        <p class="text-xs text-gray-500 mt-0.5">{{ $description }}</p>
        @endif
    </div>
</div>
