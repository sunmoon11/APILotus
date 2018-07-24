<?php

$ipaddress = '';
if (!empty($_SERVER['HTTP_CLIENT_IP']) && getenv('HTTP_CLIENT_IP')) {
    $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && getenv('HTTP_X_FORWARDED_FOR')) {
    $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
} elseif (!empty($_SERVER['REMOTE_HOST']) && getenv('REMOTE_HOST')) {
    $ipaddress = $_SERVER['REMOTE_HOST'];
} elseif (!empty($_SERVER['REMOTE_ADDR']) && getenv('REMOTE_ADDR')) {
    $ipaddress = $_SERVER['REMOTE_ADDR'];
} else {
    $ipaddress = 'UNKNOWN';
}
if ($ipaddress != 'UNKNOWN') {
    $details = json_decode(file_get_contents("http://ipinfo.io/{$ipaddress}/json"));
    echo json_encode($details);
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    echo $userAgent;
    return;
}

echo json_encode(array('error', ));
