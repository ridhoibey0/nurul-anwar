@props(['balik' => false])

{{--
    Pembatas antar-section berbentuk gumpalan awan.
    Warna diambil dari class text-* yang dipasang, mis.
    <x-awan class="text-krem" /> membuat awan berwarna krem.
--}}

<div {{ $attributes->merge(['class' => 'pointer-events-none w-full'.($balik ? ' rotate-180' : '')]) }} aria-hidden="true">
    <svg class="block h-14 w-full sm:h-20" viewBox="0 0 1440 140" preserveAspectRatio="none" fill="currentColor">
        <path d="M0,140 L0,118
            a60,60 0 0 1 120,0
            a85,85 0 0 1 170,0
            a45,45 0 0 1 90,0
            a100,100 0 0 1 200,0
            a65,65 0 0 1 130,0
            a90,90 0 0 1 180,0
            a50,50 0 0 1 100,0
            a110,110 0 0 1 220,0
            a55,55 0 0 1 110,0
            a60,60 0 0 1 120,0
            L1440,140 Z" />
    </svg>
</div>
