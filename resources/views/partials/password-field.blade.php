{{-- Input password reusable dengan tombol "intip" (show/hide).
    Props: $name, $label, $placeholder (ops), $required (bool), $autocomplete (ops), $hint (ops), $id (ops) --}}
@php
    $id = $id ?? $name.'-'.uniqid();
    $required = $required ?? true;
@endphp
<div>
    <label for="{{ $id }}" class="block text-sm font-medium mb-1">{{ $label }}</label>
    <div class="relative">
        <input type="password" id="{{ $id }}" name="{{ $name }}" {{ $required ? 'required' : '' }}
               {{ isset($autocomplete) ? 'autocomplete="'.$autocomplete.'"' : '' }}
               value="{{ $value ?? old($name) }}"
               placeholder="{{ $placeholder ?? '••••••••' }}"
               class="w-full rounded-xl border-gray-300 border px-4 py-2.5 pr-12 focus:ring-2 focus:ring-brand-500 focus:outline-none transition">
        <button type="button" class="pw-toggle" data-pw-toggle="{{ $id }}" aria-label="Lihat password" title="Intip password">👁️</button>
    </div>
    @if (!empty($hint))
        <p class="text-xs text-gray-400 mt-1">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
