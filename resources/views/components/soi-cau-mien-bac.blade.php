@php
$titles = [
    'Đặc biệt đầu, đuôi',
    'Lô tô 2 số, xiên 2, xiên 3',
    'Cầu VIP 4 số',
    'Nhận định lô kép đẹp',
    'Loto 3 số đẹp hôm nay',
    'Dàn đặc biệt 10 số ',
    'Dàn đề 20 số miễn phí',
    'Dàn đề 36 số bất bại',
]
@endphp
<div class="overflow-auto">
    <table class="text-center">
        <tr>
            <th colspan="2" class="bg-blue text-white">Soi Cầu Miền Bắc Ngày Hôm Nay</th>
        </tr>
        @foreach($titles as $k => $title)
            <tr>
                <td class="text-start">{{ $title }}</td>
                <td class="fw-7 text-red">{{ $data[$k] }}</td>
            </tr>
        @endforeach
    </table>
</div>
