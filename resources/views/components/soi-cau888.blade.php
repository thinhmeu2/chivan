@php
$td1 = [
    'Bạch thủ lô VIP',
    'Song Thủ Lô',
    '3 càng lô đẹp',
    'Lô xiên 2',
    'Xiên 3',
    'Lô kép hôm nay',
    'Đề Chạm',
]
@endphp
<div class="overflow-auto">
    <table class="text-center">
        <colgroup>
            <col style="width: 150px;">
            <col>
        </colgroup>
        <tr>
            <th colspan="2" class="text-white bg-blue">Soi cầu 888 XSMB hôm nay</th>
        </tr>
        @foreach($td1 as $k => $title)
            <tr>
                <td>{{ $title }}</td>
                <td class="fw-7 text-red">{{ $data[$k] }}</td>
            </tr>
        @endforeach
    </table>
</div>
