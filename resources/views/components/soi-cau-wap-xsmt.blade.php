@php
$td1 = [
    'Đặc biệt:',
    'Cầu Loto VIP:',
    'Loto Xiên:',
    'Loto về nhiều:',
    'Loto lâu không về:',
]
@endphp
<div class="overflow-auto">
    <table class="text-center">
        <template>
            <th style="width: 150px;"></th>
            <th></th>
        </template>
        @foreach($data as $i)
            <tr>
                <th colspan="2" class="text-white bg-blue">Dự đoán xổ số {{ $i['name'] }}</th>
            </tr>
            @foreach($td1 as $k => $title)
                <tr>
                    <td>{{ $title }}</td>
                    <td class="fw-7 text-red">{{ $i['data'][$k] }}</td>
                </tr>
            @endforeach
        @endforeach
    </table>
</div>
