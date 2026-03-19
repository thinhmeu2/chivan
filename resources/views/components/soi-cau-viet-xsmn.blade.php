@foreach(['xsmn'] as $code)
    @foreach(${$code} as $i)
        <h3>Dự đoán xổ số {{ $i['name'] }}</h3>
        Đầu đuôi giải tám: <b class="text-red">{{ implode(' - ', $i['data'][0]) }}</b>
        <br>
        Đầu đuôi đặc biệt: <b class="text-red">{{ implode(' - ', $i['data'][1]) }}</b>
        <br>
        Bao lô 2 số: <b class="text-red">{{ implode(' - ', $i['data'][2]) }}</b>
        <br>
        Loto 3 số đẹp: <b class="text-red">{{ implode(' - ', $i['data'][3]) }}</b>
    @endforeach
@endforeach
