@php
$td1 = [
    'Bạch thủ lô:',
    'Song thủ lô:',
    'Lô Xiên 2 đẹp:',
    'Lô Xiên 3 đẹp:',
    'Lô kép đẹp:',
    'Đặc biệt chạm:',
    'Dàn 3 càng lô đẹp:',
]
@endphp
<div class="overflow-auto">
    <table class="text-center">
        <colgroup>
            <col style="width: 150px;">
            <col>
        </colgroup>
        <tr>
            <th colspan="2" class="text-white bg-blue">Dự đoán xổ số Miền Bắc</th>
        </tr>
        @foreach($td1 as $k => $title)
            <tr>
                <td>{{ $title }}</td>
                <td class="fw-7 text-red">{{ $data[$k] }}</td>
            </tr>
        @endforeach
    </table>
</div>
