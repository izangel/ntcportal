@props(['text' => null])

@php
    $lines = array_values(array_filter(
        array_map('trim', preg_split('/\r\n|\r|\n/', (string) $text) ?: []),
        fn ($line) => $line !== ''
    ));
@endphp

@if(count($lines) > 0)
    <ul class="bulleted-list list-disc ps-5 m-0">
        @foreach($lines as $line)
            <li>{{ $line }}</li>
        @endforeach
    </ul>
@endif