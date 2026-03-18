<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('getListChildByParentID')) {
    function getListChildByParentID($parentId = 0)
    {
        $_this =& get_instance();
        $_this->load->model('category_model');
        $categoryModel = new Category_model();
        $list = $categoryModel->getListChild($categoryModel->_all_category(), $parentId);
        return $list;
    }
}

if (!function_exists('getUserById')) {
    function getUserById($id)
    {
        $_this =& get_instance();
        $_this->load->model('users_model');
        $dataModel = new Users_model();
        $data = $dataModel->getById($id, '', 'vi');
        return $data;
    }
}

if (!function_exists('getListNewsByCateId')) {
    function getListNewsByCateId($id, $limit = 10)
    {
        $_this =& get_instance();
        $keyCache = "getListNewsByCateId_{$id}_{$limit}";
        $data = $_this->getCache($keyCache);
        if(empty($data)){
            $_this->load->model(['post_model', 'category_model']);
            $categoryModel = new Category_model();
            $postModel = new Post_model();
            $_all_category = $categoryModel->_all_category();
            $categoryModel->_recursive_child_id($_all_category, $id);
            $params = array(
                'is_status' => 1,
                'category_id' => $categoryModel->_list_category_child_id,
                'limit' => $limit,
                'order' => ['created_time' => 'DESC'],
                'until' => date('Y-m-d')
            );
            $data = $postModel->getData($params);
            $_this->setCache($keyCache,$data,60*50);
        }
        return $data;
    }
}

if (!function_exists('getNewsByTagCode')) {
    function getNewsByTagCode($layout, $code, $limit = 10, $date = '')
    {
        if ($date == '') $date = date('Y-m-d H:i:s');
        else $date = date('Y-m-d H:i:s', strtotime($date));
        $_this =& get_instance();

        $_this->load->model(['post_model']);
        $postModel = new Post_model();
        return $postModel->getNewsByTagCode($code, $date, $limit);
    }
}

if (!function_exists('getTableResult')) {
    function getTableResult($data_result)
    {
        return json_decode($data_result,true);
    }
}

if (!function_exists('getNewByTag')) {
    function getNewByTag($code,$day,$limit=5) {
        $_this =& get_instance();
        $_this->load->model(['post_model']);
        $postModel = new Post_model();
        $data['data'] = $postModel->getNewsByTagCode($code,$day,$limit);
        return $data;
    }
}

if (!function_exists('getReward')) {
    function getReward($category){
        if ($category == 1){
            $reward = [
                0 => '',
                1 => 'ĐB',
                2 => 'G1',
                3 => 'G2',
                4 => 'G3',
                5 => 'G4',
                6 => 'G5',
                7 => 'G6',
                8 => 'G7'
            ];
        }else{
            $reward = [
                0 => 'G8',
                1 => 'G7',
                2 => 'G6',
                3 => 'G5',
                4 => 'G4',
                5 => 'G3',
                6 => 'G2',
                7 => 'G1',
                8 => 'ĐB'
            ];
        }
        return $reward;
    }
}

if (!function_exists('getAllCateByType')) {
    function getAllCateByType($type ='result')
    {
        $_this =&get_instance();
        $_this->load->model('category_model');
        $categoryModel = new Category_model();
        $listProvince = $categoryModel->_all_category($type);
        return $listProvince;
    }
}

if (!function_exists('getCateByApiId')) {
    function getCateByApiId($api_id){
        $listProvince = getAllCateByType();
        foreach ($listProvince as $k=>$i){
            if ($i->api_id == $api_id) return $i;
        }
    }
}

if (!function_exists('getProvinceByDOB')) {
    function getProvinceByDOB($parent_id, $dob){
        $_this = &get_instance();
        $_this->load->model("category_model");
        $categoryModel = new Category_model();
        $allProvince = $categoryModel->_all_category('result');
        $list = [];
        if(!empty($allProvince)) foreach ($allProvince as $item){
            if(in_array($dob,json_decode($item->day_prize,true)) && $item->parent_id == $parent_id){
                $list[] = $item;
            }
        }
        return $list;
    }
}

if (!function_exists('array_group_by')) {
    function array_group_by(array $arr, callable $key_selector)
    {
        $result = array();
        foreach ($arr as $i) {
            $key = call_user_func($key_selector, $i);
            $result[$key][] = $i;
        }
        return $result;
    }
}

if (!function_exists('getRandomDanDe')) {
    function getRandomDanDe($count, $delimiter = ' - ') {
        $arr_number = array_merge(['00','01','02','03','04','05','06','07','08','09'], range(10, 99));
        shuffle($arr_number);
        $arr_number = array_slice($arr_number, 0, $count);
        sort($arr_number);
        return implode($delimiter, $arr_number);
    }
}
function getRandomDauSo($arr, $delimiter = ' - ', $return = '') {
    $arr = (array) $arr;
    $arr_number = [];
    foreach ($arr as $item){
        for ($i=0; $i<=9; $i++)
            $arr_number[] = $item.$i;
    }
    if ($return == 'array'){
        return $arr_number;
    }
    return implode($delimiter, $arr_number);
}

if (!function_exists('getRandomLokep')) {
    function getRandomLokep($arr_reject = [])
    {
        $number = rand(0, 9);
        if($arr_reject) while (in_array($number, $arr_reject)) {
            $number = rand(0, 9);
        }
        return $number.$number;
    }
}

if (!function_exists('getRandomSTL')) {
    function getRandomSTL($reject = '', $delimiter = ' - ')
    {
        loop:
        $number_1 = rand(0, 9);
        $number_2 = rand(0, 9);
        while ($number_2 == $number_1) {
            $number_2 = rand(0, 9);
        }
        $number = $number_1.$number_2.$delimiter.$number_2.$number_1;
        if ($number == $reject) goto loop;
        return $number;
    }
}

if (!function_exists('getRandomNumber')) {
    function getRandomNumber($number_length = 2, $count = 2, $delimiter = ' - ', $return = '', $start = '', $end = '') {
        if (empty($start) || empty($end)) {
            $start = 0;
            $end = pow(10, $number_length) - 1;
        }
        $numbers = [];
        for ($i = 0; $i < $count; $i++) {
            do {
                $number = sprintf("%0".$number_length."d", rand($start, $end));
            } while (in_array($number, $numbers));
            $numbers[] = $number;
        }
        if ($return == 'array') return $numbers;
        return implode($delimiter, $numbers);
    }
}

if (!function_exists('getRandomNumberLokep')) {
    function getRandomNumberLokep($count = 2, $delimiter = ' - ') {
        $numbers = [];
        for ($i = 0; $i < $count; $i++) {
            do {
                $number = rand(0, 9);
            } while (in_array($number.$number, $numbers));
            $numbers[] = $number.$number;
        }
        return implode($delimiter, $numbers);
    }
}

if (!function_exists('getRandomNumberSTL')) {
    function getRandomNumberSTL($count = 2, $delimiter = ' - ') {
        $numbers = '';
        for ($i = 0; $i < $count; $i++) {
            do {
                $number = getRandomSTL('', ',');
            } while (
                strpos($numbers, substr($number, 0, 2)) !== false || strpos($numbers, strrev(substr($number, 0, 2))) !== false
            );
            $numbers .= $delimiter.$number;
        }
        return substr($numbers, strlen($delimiter));
    }
}

if (!function_exists('listNewsMain')) {
    function listNewsMain($data, $showDate = true, $heading = '') {
        $_this = &get_instance();
        $_this->load->view($_this->template_path.'news/_list-news-main',['data' => $data, 'showDate' => $showDate, 'heading' => $heading]);
    }
}

if (!function_exists('listNews1Thumb')) {
    function listNews1Thumb($data, $titleBox = '', $arrInsert = []) {
        $_this = &get_instance();
        $_this->load->view($_this->template_path.'news/_list-news-1-thumb',['data' => $data, 'titleBox' => $titleBox, 'arrInsert' => $arrInsert]);
    }
}

if (!function_exists('listNewsSidebar')) {
    function listNewsSidebar($data) {
        $_this = &get_instance();
        $_this->load->view($_this->template_path.'news/_list-news-sidebar',['data' => $data]);
    }
}

function listNewsBoxDuDoan($data, $titleBox = '',$showDate = true) {
    $_this = &get_instance();
    $_this->load->view($_this->template_path_default.'news/_list-news-du-doan',['dataPost' => $data, 'titleBox' => $titleBox, 'showDate' => $showDate]);
}

function shuffle_assoc(&$array) {
    $keys = array_keys($array);

    shuffle($keys);

    foreach($keys as $key) {
        $new[$key] = $array[$key];
    }

    $array = $new;

    return true;
}

function postApi($urlAPI, $postField = [], $dataType = '')
{
    $params = json_encode($postField);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => URL_API_RESULT . "$urlAPI",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $params,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($params)
        )
    ));

    $data = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        log_message('error', "cURL Error #:" . $err);
        return false;
    } else {
        if ($dataType != 'json') $data = json_decode($data, true);
    }
    return $data;
}

function getApi($urlAPI, $postField = [], $jsonDecode = true)
{
    $postField = (array) $postField;
    $request =  http_build_query($postField);
    $url = URL_API_RESULT."$urlAPI?$request";
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_USERAGENT => 'Send request api new'
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        log_message('error', "getApi: " . $err);
        return null;
    } else {
        if ($jsonDecode)
            return json_decode($response,true);
        else
            return $response;
    }
}

function getLoLon($data){
    $loto = getLoto($data, 'loto');
    $lo_lon = [];
    foreach ($loto as $number) {
        if ($number % 11 != 0 && in_array(strrev($number), $loto) && !in_array(strrev($number), $lo_lon)) $lo_lon[] = $number;
    }
    return $lo_lon;
}
function convertStaticKey($key){
    $key = '['.$key.']';

    $_this = &get_instance();
    $_this->load->model('key_model');
    $model = new Key_model();
    return $model->convertStaticKey($key);
}
function convertDynamicKey($key){
    $key = '['.$key.']';

    $_this = &get_instance();
    $_this->load->model('key_model');
    $model = new Key_model();
    return $model->convertDynamicKey($key);
}
function getKeyStatic($key, $save_db = 0, $date = ''){
    $_this = &get_instance();
    $_this->load->model('key_model');
    $model = new Key_model();
    return $model->getKeyStatic($key, $save_db, $date);
}

if (!function_exists('generateNhacai')) {
    function generateNhacai()
    {
        $_this = &get_instance();
        $_this->load->model('Nhacai_model');
        $model = new Nhacai_model();
        $post_nha_cai = $model->getAll();
        $_this->load->view($_this->template_path.'_block/nhacai',['post_nha_cai' => $post_nha_cai]);
    }
}
function viewLoxien($data){    if (empty($data))
        return '<p class="text-center">Dữ liệu đang cập nhật!</p>';

    $_this = &get_instance();
    return $_this->load->view($_this->template_path . "statistic/block/_lo-xien", ['data' => $data], TRUE);
}

function viewLogan($data){
    if (empty($data))
    return '<p class="text-center">Dữ liệu đang cập nhật!</p>';

    $_this = &get_instance();
    return $_this->load->view($_this->template_path . "statistic/block/_lo-gan", ['data' => $data], TRUE);
}

function viewLoKep($data){    if (empty($data))
    return '<p class="text-center">Dữ liệu đang cập nhật!</p>';

    $_this = &get_instance();
    return $_this->load->view($_this->template_path . "statistic/block/_lo-kep", ['data' => $data], TRUE);
}

function getLoTopRBK()
{
    $_this =& get_instance();
    $keyCache = "getLoTopRBK";
    $data = $_this->getCache($keyCache);
    if (empty($data)){
        $data = getApi("Page/lo_top");
        if (!empty($data))
            $data = $data['lo_top'];
        $_this->setCache($keyCache, $data, 60*5);
    }
    return $data;
}

function getDataDauDuoiLauXuatHien($code, $limit) {
    $_this =& get_instance();
    $keyCache = "getDataDauDuoiLauXuatHien_{$code}_{$limit}";
    $data = $_this->getCache($keyCache);
    if (empty($data)) {
        $date_end = date('Y-m-d', strtotime("-1 days"));
        $tanSuatLoTo = getTanSuatLoTo($code, $date_end, $limit)['data'];
        $inum = 0;
        $imax = 0;
        $itail = 0;
        $sort_head = [];
        $sort_tail = [];
        foreach ($tanSuatLoTo as $k => $v):
            if ($inum > 0 && $inum % 10 == 0 || $inum == 99):
                if ($inum == 99) $imax = $imax + $v['sum'];
                array_push($sort_tail, ['tail' => $itail, 'sum' => $imax]);
                $imax = $v['sum'];
                $itail = $itail + 1;
            else:
                $imax = $imax + $v['sum'];
            endif;

            if ($inum < 10) {
                array_push($sort_head, ['head' => $inum, 'sum' => $v['sum']]);
            } else {
                $in = $inum % 10;
                $sort_head[$in]['sum'] = $sort_head[$in]['sum'] + $v['sum'];
            };

            $inum = $inum + 1;
        endforeach;
        usort($sort_head, function ($a, $b) {
            return $a['sum'] < $b['sum'] ? 1 : -1;
        });
        usort($sort_tail, function ($a, $b) {
            return $a['sum'] < $b['sum'] ? 1 : -1;
        });
        $data['head'] = $sort_head;
        $data['tail'] = $sort_tail;
        $_this->setCache($keyCache,$data);
    }
    return $data;
}

function isSpinned($data_result) {
    return strpos($data_result, '""') === false;
}

function calculatePascalNumber($numCheck) {
    $length = count($numCheck);

    if ($length === 1) {
        return $numCheck;
    }

    $num = array();
    for ($i = 0; $i < $length - 1; $i++) {
        $sum = $numCheck[$i] + $numCheck[$i + 1];
        array_push($num, $sum % 10);
    }

    $data = calculatePascalNumber($num);
    array_unshift($data, $numCheck);

    return $data;
}

function getPascalSoiCauByDay($cate, $date = 'now') {
    $code = strtolower($cate->code);
    if (in_array($code, ['xsmt', 'xsmn']))
        return;

    $ci = &get_instance();
    $ci->load->model('result_model');
    $ci->result_model = new Result_model();
    $data = $ci->result_model->getDataNearestByDay($cate->id, $date);

    if (empty($data))
        return;

    $data = $data['data']['data'][0]['data_result'];
    $data = getLoto($data, 'loto', 'full');

    if ($code == 'xsmb') {
        $data = array_slice($data, 0, 2);
        $db = $data[0];
        $nhat = $data[1];
    } else {
        $data = array_slice($data, -2);
        $db = $data[1];
        $nhat = $data[0];
    }

    $data = "$db$nhat";
    $data = array_map('intval', str_split($data));
    $data = calculatePascalNumber($data);

    unset($data[key(array_slice($data, 0, 1, 1))]);
    unset($data[key(array_slice($data, -1, 1, 1))]);

    $db = str_split($db);
    $nhat = str_split($nhat);
    $cau = implode('', array_splice($data, -1)[0]);

    return [
        'db' => $db,
        'nhat' => $nhat,
        'data' => $data,
        'cau' => $cau,
    ];
}

function thongkeKqxs($string){
    if (is_array($string))
        return;

    $loto = getLoto($string, 'loto');
    $lotoUnique = array_unique($loto);
    $db = $loto[0];

    $countLoto = array_count_values($loto);
    $nhieuNhay = array_filter($countLoto, function ($a){
        return $a > 1;
    });

    $lokep = array_filter($lotoUnique, function ($a){
        return $a % 11 == 0;
    });

    $arrCacap = [];
    foreach ($lotoUnique as $num){
        $numRev = strrev($num);
        if (in_array($numRev, $lotoUnique) && !in_array($numRev, $arrCacap) && $num % 11 != 0)
            $arrCacap[] = $num;
    }

    $tkChamDauDuoi = $arrDau = $arrDuoi = $arrTong = array_fill(0, 10, 0);
    foreach ($loto as $num){
        $head = substr($num, 0, 1);
        $tail = substr($num, 1, 1);
        $sum = ($head + $tail) % 10;
        $arrDau[$head]++;
        $arrDuoi[$tail]++;
        $tkChamDauDuoi[$head]++;
        $tkChamDauDuoi[$tail]++;
        $arrTong[$sum]++;
    }

    $maxDau = max($arrDau);
    $dauCam = array_filter($arrDau, function ($a){
        return $a == 0;
    });
    $dauNhieu = array_filter($arrDau, function ($a) use ($maxDau){
        return $a == $maxDau;
    });

    $maxDuoi = max($arrDuoi);
    $duoiCam = array_filter($arrDuoi, function ($a){
        return $a == 0;
    });
    $duoiNhieu = array_filter($arrDuoi, function ($a) use ($maxDuoi){
        return $a == $maxDuoi;
    });

    $maxTong = max($arrTong);
    $tongCam = array_filter($arrTong, function ($a){
        return $a == 0;
    });
    $tongNhieu = array_filter($arrTong, function ($a) use ($maxTong){
        return $a == $maxTong;
    });

    $dataReturn = [
        'db' => $db,
        'nhieuNhay' => $nhieuNhay,
        'loKep' => $lokep,
        'caCap' => $arrCacap,
        'dauCam' => $dauCam,
        'dauNhieu' => $dauNhieu,
        'duoiCam' => $duoiCam,
        'duoiNhieu' => $duoiNhieu,
        'tongCam' => $tongCam,
        'tongNhieu' => $tongNhieu,
        'tkChamDauDuoi' => $tkChamDauDuoi,
        'tkCountDau' => $arrDau,
        'tkCountDuoi' => $arrDuoi,
        'tkCountTong' => $arrTong
    ];

    return $dataReturn;
}

function getRandomResult($code, $empty = 0){
    $code = strtoupper($code);
    if ($code == 'XSMB'){
        if ($empty){
            $result = [
                ['', '', ''],
                [''],
                [''],
                ['', ''],
                ['', '', '', '', '', ''],
                ['', '', '', ''],
                ['', '', '', '', '', ''],
                ['', '', ''],
                ['', '', '', ''],
            ];
        } else {
            $result = [
                ['', '', ''],
                getRandomNumber(5, 1, '', 'array'),
                getRandomNumber(5, 1, '', 'array'),
                getRandomNumber(5, 2, '', 'array'),
                getRandomNumber(5, 6, '', 'array'),
                getRandomNumber(4, 4, '', 'array'),
                getRandomNumber(4, 6, '', 'array'),
                getRandomNumber(3, 3, '', 'array'),
                getRandomNumber(2, 4, '', 'array')
            ];
        }
    } else {
        if ($empty){
            $result = [
                [''],
                [''],
                ['', '', ''],
                [''],
                ['', '', '', '', '', '', ''],
                ['', ''],
                [''],
                [''],
                [''],
            ];
        } else {
            $result = [
                getRandomNumber(2, 1, '', 'array'),
                getRandomNumber(3, 1, '', 'array'),
                getRandomNumber(4, 3, '', 'array'),
                getRandomNumber(4, 1, '', 'array'),
                getRandomNumber(5, 7, '', 'array'),
                getRandomNumber(5, 2, '', 'array'),
                getRandomNumber(5, 1, '', 'array'),
                getRandomNumber(5, 1, '', 'array'),
                getRandomNumber(6, 1, '', 'array'),
            ];
        }
    }
    return $result;
}

function getEmptyResult($oneCate, $date = 'now') {
    $date = date('Y-m-d', strtotime($date));
    if (in_array($oneCate->code, ['XSMT', 'XSMN'])) {
        $dateN = date('N', strtotime($date));
        $provinces = getCatChildByDOW($oneCate->id, $dateN);
        $result = [];
        foreach ($provinces as $province) {
            $result[] = [
                'data_result' => json_encode(getRandomResult($province->code, 1)),
                'category_id' => $province->id,
                'displayed_time' => $date,
            ];
        }
    } else {
        $result[] = [
            'data_result' => json_encode(getRandomResult($oneCate->code, 1)),
            'category_id' => $oneCate->id,
            'displayed_time' => $date,
        ];
    }

    return $result;
}