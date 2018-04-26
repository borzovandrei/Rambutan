<?php
// Создаем поток
//echo '123';


//$auth = base64_encode('sockduser:proxytelegram');
//$aContext = array(
//    'http' => array(
//        'proxy' => 'tcp://94.130.187.212:7777',
//        'request_fulluri' => true,
//        'header' => "Proxy-Authorization: Basic $auth"
//    ),
//);
//$context = stream_context_create($aContext);
//
//$file = file_get_contents('http://api.telegram.org');
//$file = file_get_contents('http://api.ipify.org?format=json', false, $context);
//$file = file_get_contents('http://google.com', false, $context);
//$file = file_get_contents('http://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/getUpdates', false, $context);

//echo $file;
function dec(){
    $ch = curl_init();
//    curl_setopt($ch, CURLOPT_URL, 'http://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/getUpdates');
    curl_setopt($ch, CURLOPT_URL, 'http://api.ipify.org?format=json');
    curl_setopt($ch, CURLOPT_PROXY, '188.40.141.216:3128');
//curl_setopt($ch, CURLOPT_PROXYUSERPWD, 'sockduser:proxytelegram');
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    $result = curl_exec($ch);
//    echo $result;
    curl_close($ch);
    return $result;
}

//$ch = curl_init();
//curl_setopt($ch, CURLOPT_URL, 'http://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/getUpdates?offset=120917622');
//curl_setopt($ch, CURLOPT_PROXY, '206.189.39.170:3128');
////curl_setopt($ch, CURLOPT_PROXYUSERPWD, 'sockduser:proxytelegram');
//curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//curl_setopt($ch, CURLOPT_FAILONERROR, true);
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
//$result = curl_exec($ch);
//echo $result;
//curl_close($ch);


$data = json_decode(dec(), TRUE);
echo dec();
//var_dump($data['result']);
?>