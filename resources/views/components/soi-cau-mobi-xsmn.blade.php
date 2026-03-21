@php
    $titles = [
        '',
        'Bạch thủ giải 8',
        'Bạch thủ đặc biệt',
        'Đánh lô 2',
        'Cặp xiên 3',
        'Cặp số lô gan lâu về nhất',
        'Cặp số lô tô về nhiều nhất',
    ];
@endphp

<table class="text-center text-red">
    @foreach ($titles as $index => $title)
        <tr>
            {{-- Cột tiêu đề --}}
            <td class="text-start text-000">{{ $title }}</td>

            {{-- Dữ liệu theo cột --}}
            @foreach ($data as $row)
                <td @class(['text-000' => !$index])>
                    @php $cell = $row[$index] ?? null; @endphp

                    @if (is_array($cell))
                        {{ implode(' - ', $cell) }}
                    @else
                        {{ $cell }}
                    @endif
                </td>
            @endforeach
        </tr>
    @endforeach
</table>
