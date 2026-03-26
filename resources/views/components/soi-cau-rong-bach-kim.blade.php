@php
$titles = [
    'Bạch thủ lô :',
    'Song thủ lô :',
    'Lô xiên 2 đẹp :',
    'Lô kép đẹp :',
    'Đặc biệt chạm :',
    'Dàn 3 càng :',
]
@endphp
<div class="overflow-auto">
    <table class="text-center">
        <tr>
            <th colspan="2" class="bg-blue text-white">Soi Cầu Rồng Bạch Kim Hôm Nay</th>
        </tr>
        @foreach($titles as $k => $title)
            <tr>
                <td class="text-start">{{ $title }}</td>
                <td class="fw-7 text-red">{{ $data[$k] }}</td>
            </tr>
        @endforeach
    </table>
</div>
