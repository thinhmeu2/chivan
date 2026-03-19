@php
$titles = [
    'Bạch thủ lô:',
    'Song thủ lô:',
    'Lô xiên 2:',
    'Lô xiên 3:',
    'Lô kép đẹp:',
    'Dàn lô 4 số:',
    'Dàn lô 6 số:',
    'Dàn lô 10 số:',
]
@endphp
<table>
    @foreach($titles as $k => $title)
        <tr>
            <td>{{ $title }}</td>
            <td class="fw-7 text-red text-center">{{ implode(' - ', is_array($data[$k][0]) ? $data[$k][0] : $data[$k]) }}</td>
        </tr>
    @endforeach
</table>
