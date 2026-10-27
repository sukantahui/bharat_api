@php
    use Illuminate\Support\Arr;

    $isArray = is_array($data);
    $isAssoc = $isArray && Arr::isAssoc($data);
@endphp

@if($isArray)

    {{-- LIST OF RECORDS (ARRAY OF ARRAYS ONLY) --}}
    @if(!$isAssoc && isset($data[0]) && is_array($data[0]))

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        @foreach(array_keys($data[0]) as $key)
                            <th>{{ ucfirst(str_replace('_',' ',$key)) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td>
                                    @include('universal.partials.renderer', ['data' => $cell])
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    {{-- KEY–VALUE ARRAY --}}
    @else
        <table class="table table-bordered table-sm">
            @foreach($data as $key => $value)
                <tr>
                    <th class="bg-light w-25">
                        {{ ucfirst(str_replace('_',' ',$key)) }}
                    </th>
                    <td>
                        @include('universal.partials.renderer', ['data' => $value])
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

@elseif(is_bool($data))
    <span class="badge {{ $data ? 'bg-success' : 'bg-danger' }}">
        {{ $data ? 'TRUE' : 'FALSE' }}
    </span>

@elseif(is_null($data))
    <span class="text-muted fst-italic">NULL</span>

@else
    <span>{{ (string) $data }}</span>
@endif
