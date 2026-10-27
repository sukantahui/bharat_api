@php
    $isArray = is_array($data);
    $isAssoc = $isArray && \Illuminate\Support\Arr::isAssoc($data);
@endphp

@if($isArray)

<div>
    <span class="toggle">
        {{ $isAssoc ? '{' : '[' }}
    </span>

    <div class="content indent">
        @foreach($data as $key => $value)
            <div>
                @if($isAssoc)
                    <span class="key">"{{ $key }}"</span>:
                @endif

                @include('universal.partials.json-renderer', ['data' => $value])
            </div>
        @endforeach
    </div>

    <div>{{ $isAssoc ? '}' : ']' }}</div>
</div>

@elseif(is_string($data))
    <span class="string">"{{ $data }}"</span>

@elseif(is_numeric($data))
    <span class="number">{{ $data }}</span>

@elseif(is_bool($data))
    <span class="bool">{{ $data ? 'true' : 'false' }}</span>

@elseif(is_null($data))
    <span class="null">null</span>

@else
    <span class="string">"{{ (string)$data }}"</span>
@endif
