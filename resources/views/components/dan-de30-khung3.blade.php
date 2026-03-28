<div class="overflow-auto">
    <table class="text-center">
        <tr class="text-blue">
            <th style="width: 150px">Ngày</th>
            <th>Dàn đề 30 số nuôi khung 3 ngày</th>
            <th style="width: 150px">Kết quả</th>
        </tr>
        @foreach($rows as $i)
            <tr>
                <td>{{ $i[0] }}</td>
                <td class="fw-7 text-red">{{ $i[1] }}</td>
                <td>{!! $i[2] !!}</td>
            </tr>
        @endforeach
    </table>
</div>
