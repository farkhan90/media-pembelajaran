@props(['class' => 'w-6 h-6'])

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    {{-- Kita akan memberi warna yang berbeda untuk setiap bagian --}}
    <path d="M9 13.0899C9 13.0899 9.387 13.5 10 13.5C10.613 13.5 11 13.0899 11 13.0899"
        class="stroke-current text-pink-500" {{-- Warna pink untuk senyum --}} stroke-width="1.5" stroke-linecap="round"
        stroke-linejoin="round" />
    <path d="M14 11H15" class="stroke-current text-blue-500" {{-- Warna biru --}} stroke-width="1.5"
        stroke-linecap="round" stroke-linejoin="round" />
    <path d="M11 7L13 9" class="stroke-current text-yellow-500" {{-- Warna kuning --}} stroke-width="1.5"
        stroke-linecap="round" stroke-linejoin="round" />
    <path d="M4 17.5L8.5 13L13 17.5L17.5 13L20 15.5" class="stroke-current text-green-500" {{-- Warna hijau untuk badan popper --}}
        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    <path d="M10 5L11 2" class="stroke-current text-red-500" {{-- Warna merah --}} stroke-width="1.5"
        stroke-linecap="round" stroke-linejoin="round" />
    <path d="M16 5.5L18 3.5" class="stroke-current text-purple-500" {{-- Warna ungu --}} stroke-width="1.5"
        stroke-linecap="round" stroke-linejoin="round" />
    <path d="M7 4L5 2" class="stroke-current text-indigo-500" {{-- Warna indigo --}} stroke-width="1.5"
        stroke-linecap="round" stroke-linejoin="round" />
</svg>
