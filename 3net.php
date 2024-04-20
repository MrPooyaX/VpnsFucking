<?php
header('Content-type: application/json');
function httpGet($url)
{
    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $headers = array();
    $headers[] = "User-Agent: okhttp/3.14.9";
    $headers[] = "Connection: Keep-Alive";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch,CURLOPT_HEADER, 0);
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
    //$alpn = $data['outbounds'][0]['streamSettings']['tlsSettings']['alpn'];
    $alpn = 'h2%2Chttp%2F1.1';
    $fingerprint = $data['outbounds'][0]['streamSettings']['tlsSettings']['fingerprint'];
    $type = $data['outbounds'][0]['streamSettings']['network'];
    if($data['outbounds'][0]['streamSettings']['network'] == "grpc")
    {
        $serviceName = $data['outbounds'][0]['streamSettings']['grpcSettings']['serviceName'];
        $multiMode = $data['outbounds'][0]['streamSettings']['grpcSettings']['multiMode'];
        if ($multiMode == false)
        {
            $mode = "gun";
        }else
        {
            $mode = "multi";
        }
        $url = "vless://{$uuid}@{$address}:{$port}?encryption=none&security={$security}&sni={$tlsSni}&type={$type}&serviceName={$serviceName}&mode={$mode}&alpn={$alpn}&fp={$fingerprint}#new server";
    }else
    {
        $host = $data['outbounds'][0]['streamSettings']['wsSettings']['headers']['Host'];
        $path = $data['outbounds'][0]['streamSettings']['wsSettings']['path'];
        
        $url = "vless://{$uuid}@{$address}:{$port}?encryption=none&security={$security}&sni={$tlsSni}&type={$type}&host={$host}&path={$path}&alpn={$alpn}&fp={$fingerprint}#new server";
    }

    return $url;
}
function decryptAES($data, $sSecretKey) {
	$ex = explode(':',$data);
	$iv = hex2bin($ex[0]);
	$data = $ex[1];

    $data = hex2bin($data);
    $method = "AES-256-CTR";
    return  openssl_decrypt($data, $method, $sSecretKey, OPENSSL_RAW_DATA, $iv);
}
function generateRandomAndroidDeviceId() {
    $characters = '0123456789abcdef';
    $deviceId = '';

    for ($i = 0; $i < 16; $i++) {
        $deviceId .= $characters[random_int(0, 15)];
    }

    return $deviceId;
}
$res = httpGet("https://gist.github.com/TonDevv/9d4313f10b2906d9c98190d0fa3cefa4/raw");
$data = decryptAES($res,"D9mG1BEqGdHSwdvly3q7ol9qp2OB8pC2");
$address = json_decode($data,true)["iran-wifi"];

$list = ["Xiaomi:POCO%20AX3:29","Xiaomi:POCO%20AX3:29","Xiaomi:Redmi%20A8:29","Xiaomi:Redmi%20A9:29","Samsung:Samsung%20AGalaxy%20AA10:28","Samsung:Samsung%20AGalaxy%20AA30:28","Samsung:Samsung%20AGalaxy%20AS21:30","Samsung:Samsung%20AGalaxy%20AS21%20AUltra:30"];
$device = explode(':',$list[array_rand($list, 1)]);
$brand = $device[0];
$model = urlencode($device[1]);
$api = $device[2];



$res = httpGet($address."/app-configuration?deviceId=".generateRandomAndroidDeviceId()."&deviceBrand=$brand&deviceModel=$model&deviceOs=$api");



$data = decryptAES($res,"D9mG1BEqGdHSwdvly3q7ol9qp2OB8pC2");

$jsonlist = json_decode($data,true)["servers"];

$configs = "";
for ($i = 0; $i < count($jsonlist);$i++)
{
    $vpn = $jsonlist[$i]["configuration"];
    
    if (is_object(json_decode($vpn))) 
    {
		if (isset($_GET["convert"]) && $_GET["convert"] == TRUE){ 
			$configs .= convertVlessJsonToUrl($vpn) . "\n";
		}else
		{
			$configs .= str_replace(" ","",str_replace("\r\n","",$vpn)) . "\n";
		}
	}else
	{
		$configs .= str_replace(" ","",str_replace("\r\n","",$vpn)) . "\n";
	}
}



echo $configs . "\n";

