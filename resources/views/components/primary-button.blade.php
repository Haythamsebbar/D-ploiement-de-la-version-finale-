@props(['disabled' => false])

<button {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#157e74] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#279078] focus:bg-[#279078] active:bg-[#279078] focus:outline-none focus:ring-2 focus:ring-[#3aa17d] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button> 