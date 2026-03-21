@foreach($todayCategories as $name)
    <div class="fw-7 mb-1">Dự đoán xổ số {{ $name }}</div>
    Giải đặc biệt đầu đuôi: <b class="text-red">{{ implode(' - ', \App\Helpers\RandomHelper::getRandomNumber(2,2)) }}</b>
    <br>
    Lô giải 8: <b class="text-red">{{ implode(' - ', \App\Helpers\RandomHelper::getRandomNumber(2,2)) }}</b>
    <br>
    Bao lô 2 số: <b class="text-red">{{ implode(' - ', \App\Helpers\RandomHelper::getRandomNumber(2,2)) }}</b>
    <br>
    Xỉu chủ số đẹp: <b class="text-red">{{ implode(' - ', \App\Helpers\RandomHelper::getRandomNumber(3,2)) }}</b>
    <div class="mb-4"></div>
@endforeach
