<div class="overflow-auto">
    <table class="text-center">
        <colgroup>
            <col style="width: 150px;">
            <col>
            <col style="width: 150px;">
        </colgroup>
        <tr>
            <th colspan="3" class="text-white bg-blue">Dự đoán 3 càng lô đề siêu vip ngày hôm nay</th>
        </tr>
        @foreach($rows as $i)
            <tr>
                <td>{{ $i[0] }}</td>
                <td class="fw-7 text-blue">{{ $i[1] }}</td>
                <td>{!! $i[2] !!}</td>
            </tr>
        @endforeach
    </table>
</div>
