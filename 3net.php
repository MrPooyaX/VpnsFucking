<?php
header('Content-type: application/json');
function httpGet($url)
{
    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $headers = array();

    //$headers[] = "Accept-Encoding: gzip";
    $headers[] = "User-Agent: okhttp/3.14.9";
    $headers[] = "Connection: Keep-Alive";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


    curl_setopt($ch,CURLOPT_HEADER, 0);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
   // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $output=curl_exec($ch);
    curl_close($ch);
    return $output;
}

function convertVlessJsonToUrl($vlessJson)
{
    $data = json_decode($vlessJson, true);

    $uuid = $data['outbounds'][0]['settings']['vnext'][0]['users'][0]['id'];
    $address = $data['outbounds'][0]['settings']['vnext'][0]['address'];
    $port = $data['outbounds'][0]['settings']['vnext'][0]['port'];
    $security = $data['outbounds'][0]['streamSettings']['security'];
    $tlsSni = $data['outbounds'][0]['streamSettings']['tlsSettings']['serverName'];
    $type = $data['outbounds'][0]['streamSettings']['network'];
    $host = $data['outbounds'][0]['streamSettings']['wsSettings']['headers']['Host'];
    $path = $data['outbounds'][0]['streamSettings']['wsSettings']['path'];

    $url = "vless://{$uuid}@{$address}:{$port}?encryption=none&security={$security}&sni={$tlsSni}&type={$type}&host={$host}&path={$path}#new server";

    return $url;
}
function decryptAES($sValue, $sSecretKey,$IV) {
    $sValue = hex2bin($sValue);
    $method = "AES-256-CTR";
    return  openssl_decrypt($sValue, $method, $sSecretKey, OPENSSL_RAW_DATA, $IV);
}
function generateRandomAndroidDeviceId() {
    $characters = '0123456789abcdef';
    $deviceId = '';

    for ($i = 0; $i < 16; $i++) {
        $deviceId .= $characters[random_int(0, 15)];
    }

    return $deviceId;
}


$res = httpGet("https://mci-api.googleadservices.info/app-configuration?deviceId=".generateRandomAndroidDeviceId()."&deviceBrand=OnePlus&deviceModel=ONEPLUS%20A5000&deviceOs=28");

$ex = explode(':',$res);
$iv = $ex[0];

$data = $ex[1];

$data = decryptAES($data,"D9mG1BEqGdHSwdvly3q7ol9qp2OB8pC2",hex2bin($iv));

$jsonlist = json_decode($data,true)["servers"];

$configs = "";
for ($i = 0; $i < count($jsonlist);$i++)
{
    $vpn = $jsonlist[$i]["configuration"];
    
    
    if (isset($_GET["convert"]) && $_GET["convert"] == TRUE){ 
        $configs .= convertVlessJsonToUrl($vpn) . "\n";
    }else
    {
        $configs .= str_replace(" ","",str_replace("\r\n","",$vpn)) . "\n";
    }
}



echo $configs . "\n";

