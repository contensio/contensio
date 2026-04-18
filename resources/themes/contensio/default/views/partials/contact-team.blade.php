{{--
 | Team Cards partial
 | Variables: $members (array), $locale
--}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
    @foreach($members as $member)
    @php
        $name  = $member['name']  ?? '';
        $photo = $member['photo'] ?? '';
        $email = $member['email'] ?? '';
        $phone = $member['phone'] ?? '';
        $role  = $member['role'][$locale]  ?? $member['role']['en']  ?? '';
        $bio   = $member['bio'][$locale]   ?? $member['bio']['en']   ?? '';
    @endphp
    @if($name)
    <div class="text-center">
        @if($photo)
        <img src="{{ $photo }}" alt="{{ $name }}"
             class="w-24 h-24 rounded-full object-cover mx-auto mb-4 border-2 border-gray-100 shadow-sm">
        @else
        <div class="w-24 h-24 rounded-full bg-gray-100 mx-auto mb-4 flex items-center justify-center">
            <i class="bi bi-person text-3xl text-gray-300"></i>
        </div>
        @endif

        <h3 class="font-bold text-gray-900">{{ $name }}</h3>
        @if($role)
        <p class="text-sm text-gray-500 mt-0.5">{{ $role }}</p>
        @endif
        @if($bio)
        <p class="text-sm text-gray-500 mt-2 leading-relaxed">{{ $bio }}</p>
        @endif

        @if($email || $phone)
        <div class="flex flex-col items-center gap-1.5 mt-3">
            @if($email)
            <a href="mailto:{{ $email }}"
               class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors">
                <i class="bi bi-envelope"></i>{{ $email }}
            </a>
            @endif
            @if($phone)
            <a href="tel:{{ $phone }}"
               class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors">
                <i class="bi bi-telephone"></i>{{ $phone }}
            </a>
            @endif
        </div>
        @endif
    </div>
    @endif
    @endforeach
</div>
