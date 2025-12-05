<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

// 生成唯一编号
function createNO($table, $field, $prefix = '', $number = 6) 
{
    $billno = $prefix . random($number);
    while( 1 ) 
    {
        $count = DB::table("$table")->where($field,$billno)->count();
        if( $count <= 0 ) 
        {
            break;
        }
        $billno = $prefix . random($number);
    }
    return $billno;
}

// 生成随机数字
function random($length) {
    return rand(pow(10,($length-1)), pow(10,$length)-1);
}

// 生成随机字符串
function createStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

// xml转数组
function xmlToArray($xml)
{
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    $val = json_decode(json_encode($xmlstring), true);
    $val = array_map('trim',$val);
    return $val;
}

// 将表情进行转义
function emojiEncode($str){
    $strEncode = '';
    $length = mb_strlen($str,'utf-8');
    for ($i=0; $i < $length; $i++) {
        $_tmpStr = mb_substr($str,$i,1,'utf-8');
        if(strlen($_tmpStr) >= 4){
            $strEncode .= '[[EMOJI:'.rawurlencode($_tmpStr).']]';
        }else{
            $strEncode .= $_tmpStr;
        }
    }
    return $strEncode;
}

// 将表情进行反转义
function emojiDecode($str){
    $strDecode = preg_replace_callback('|\[\[EMOJI:(.*?)\]\]|', function($matches){
            return rawurldecode($matches[1]);
        }, $str);
    return $strDecode;
}

// 过滤掉emoji表情
function removeEmoji($str)
{
    $str = emoji_decode($str);
    $str = preg_replace_callback('/./u',function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },$str);
    return $str;
}

/**
* 参数说明：
* $originalData: 未加密字符串
* $certPath: 公钥证书路径
* $split: 内容分段字节长度
* $keyType: 加密秘钥类型：public公钥 private私钥
*/
function opensslEncrypt($originalData, $certPath, $split, $keyType = 'public')
{
    if($keyType == 'public'){
        $publicKey = openssl_pkey_get_public(file_get_contents($certPath));
        $publicKey or die('公钥不可用');
        $crypto = '';
        foreach (str_split($originalData, $split) as $chunk) {
            $encryptData = '';
            if(openssl_public_encrypt($chunk, $encryptData, $publicKey)){
                $crypto .= $encryptData;
            }else{
                die('加密失败');
            }
        }
        $encryptData = base64_encode($crypto);
        return $encryptData;
    }else{
        $privateKey = openssl_pkey_get_private(file_get_contents($certPath));
        $privateKey or die('私钥不可用');
        $crypto = '';
        foreach (str_split($originalData, $split) as $chunk) {
            $encryptData = '';
            if(openssl_private_encrypt($chunk, $encryptData, $privateKey)){
                $crypto .= $encryptData;
            }else{
                die('加密失败');
            }
        }
        $encryptData = base64_encode($crypto);
        return $encryptData;
    }
}

/**
* 参数说明：
* $encryptData: 解密密文
* $certPath: 公钥证书路径
* $split: 内容分段字节长度
* $keyType: 加密秘钥类型：public公钥 private私钥
*/
function opensslDecrypt($encryptData, $certPath, $split, $keyType = 'private')
{
    if($keyType == 'private'){
        $privateKey = openssl_pkey_get_private(file_get_contents($certPath));
        $privateKey or die('私钥不可用');
        $decrypt = '';
        foreach (str_split(base64_decode($encryptData), $split) as $chunk) {
            $decryptData = '';
            if(openssl_private_decrypt($chunk, $decryptData, $privateKey)){
                $decrypt .= $decryptData;
            }else{
                die('解密失败');
            }

        }
        return $decrypt;
    }else{
        $publicKey = openssl_pkey_get_public(file_get_contents($certPath));
        $publicKey or die('公钥不可用');
        $decrypt = '';
        foreach (str_split(base64_decode($encryptData), $split) as $chunk) {
            $decryptData = '';
            if(openssl_public_decrypt($chunk, $decryptData, $publicKey)){
                $decrypt .= $decryptData;
            }else{
                die('解密失败');
            }

        }
        return $decrypt;
    }
}

//计算范围，可以做搜索用户
function getRange($lat,$lon,$raidus){
    //计算纬度
    $degree = (24901 * 1609) / 360.0;
    $dpmLat = 1 / $degree;
    $radiusLat = $dpmLat * $raidus;
    $minLat = $lat - $radiusLat; //得到最小纬度
    $maxLat = $lat + $radiusLat; //得到最大纬度
    //计算经度
    $mpdLng = $degree * cos($lat * (PI / 180));
    $dpmLng = 1 / $mpdLng;
    $radiusLng = $dpmLng * $raidus;
    $minLng = $lon - $radiusLng; //得到最小经度
    $maxLng = $lon + $radiusLng; //得到最大经度
    //范围
    $range = array(
        'minLat' => $minLat,
        'maxLat' => $maxLat,
        'minLon' => $minLng,
        'maxLon' => $maxLng
    );
    return $range;
}

/**
 * 计算两点之间的距离
 * @param $lng1 经度1
 * @param $lat1 纬度1
 * @param $lng2 经度2
 * @param $lat2 纬度2
 * @param int $unit m，km
 * @param int $decimal 位数
 * @return float
 */
function getDistance($lng1, $lat1, $lng2, $lat2, $unit = 2, $decimal = 2)
{

    $EARTH_RADIUS = 6370.996; // 地球半径系数
    $PI           = 3.1415926535898;

    $radLat1 = $lat1 * $PI / 180.0;
    $radLat2 = $lat2 * $PI / 180.0;

    $radLng1 = $lng1 * $PI / 180.0;
    $radLng2 = $lng2 * $PI / 180.0;

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $distance = $distance * $EARTH_RADIUS * 1000;

    if ($unit === 2) {
        $distance /= 1000;
    }

    return round($distance, $decimal);
}

// 二进制转图片
function imageUri($contents, $mime)
{
    $base64   = base64_encode($contents);
    return ('data:' . $mime . ';base64,' . $base64);
}

// 存储网络图片
function storageImage($image,$filePath,$fileName){
   if(!empty($image)){
        preg_match('/data:image\/(\w+?);base64,(.+)$/si',$image,$result);
        //生成图片名称
        $fileName = $fileName.'.'.$result[1];
        //项目路径
        $rootPath = './';
        //返回储存路径    保存路径 拼接 图片
        $merge_path = $filePath.$fileName;
        //没有文件夹创建文件夹
        if(!is_dir($rootPath.$filePath))
        {
            is_file($rootPath.$filePath) or mkdir($rootPath.$filePath, 0777 , true);
        }
        $res = file_put_contents($rootPath.$merge_path,base64_decode($result[2]));
        if($res){
            return $merge_path;
        }
    }
}

// 位置拆分
function handleAddress($address = '广东省深圳市龙华新区大浪街道同胜科技大厦'){
    preg_match("/(.*?(省|自治区|北京市|天津市))/", $address, $matches);
    if(count($matches)>1){
        $province = $matches[count($matches) - 2];
        $address = str_replace($province, '', $address);
    }
    preg_match("/(.*?(市|自治州|地区|区划|县))/", $address, $matches);
    if(count($matches)>1){
        $city = $matches[count($matches) - 2];
        $address = str_replace($city, '', $address);
    }
    preg_match("/(.*?(市|区|县|镇|乡|街道))/", $address, $matches);
    if (count($matches) > 1) {
        $area = $matches[count($matches) - 2];
        $address = str_replace($area, '', $address);
    }
    return [
        'province'=>isset($province) ? $province : '',
        'city'=>isset($city) ? $city : '',
        'area'=>isset($area) ? $area : '',
        'street'=>$address
    ];
}

/**
 * 字节转换
 * @Author   Chen
 * @DateTime 2022-11-24
 */
function getFileSize($num) {
        $p = 0;
        $format = 'bytes';
        if( $num > 0 && $num < 1024 ) {
          $p = 0;
          return number_format($num) . ' ' . $format;
        }
        if( $num >= 1024 && $num < pow(1024, 2) ){
          $p = 1;
          $format = 'KB';
       }
       if ( $num >= pow(1024, 2) && $num < pow(1024, 3) ) {
         $p = 2;
         $format = 'MB';
       }
       if ( $num >= pow(1024, 3) && $num < pow(1024, 4) ) {
         $p = 3;
         $format = 'GB';
       }
       if ( $num >= pow(1024, 4) && $num < pow(1024, 5) ) {
         $p = 3;
         $format = 'TB';
       }
       $num /= pow(1024, $p);
       return number_format($num, 2) . ' ' . $format;
}

/**
 * 检测并创建目录
 * @Author   Chen
 * @return   [type]     [description]
 */
function createPath($path)
{
    if (!is_dir($path)) {
        // 尝试创建目录
        if (!mkdir($path, 0755, true)) {
            return false;
        }
    }
}

/**
 * curl请求指定url (post)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curlPost($url, $data = [], $header = [])
{
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if(!$data){
        return 'data is null';
    }
    if(is_array($data))
    {
        $data = json_encode($data);
    }
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER,array_merge(array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
    ),$header));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($curl);
    $errorno = curl_errno($curl);
    if ($errorno) {
        return $errorno;
    }
    curl_close($curl);
    return $res;
}

/**
 * curl请求指定url (get)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curlGet($url,$data = [],$timeout = 60){
    if($url == "" || $timeout <= 0){
        return false;
    }
    $url = $url.'?'.http_build_query($data);
    $curl = curl_init((string)$url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl, CURLOPT_TIMEOUT, (int)$timeout);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

function getEthGas(){
    return '0x'.dechex(90000);
}

function getEthGasPrice($url){
    return '0x12a05f200';
}

/**
 * @param $url
 * @param null $data
 * @return bool|mixed
 * 请求接口方法
 */
function curl($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    if ($output === FALSE) {
        return false;
    }
    curl_close($curl);
    return $output;
}

function sctonum($num, $double = 10){
    if(stripos($num, "e") !== false){
        $a   = explode('e', strtolower($num));
        $str =  bcmul($a[0], bcpow(10, $a[1], $double), $double);
        $num =  rtrim(rtrim($str, '0'), '.');   //去除小数后多余的0
    }
    return $num;
}