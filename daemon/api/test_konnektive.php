<?php
/**
 * Created by PhpStorm.
 * User: Victory
 * Date: 10/16/2017
 * Time: 4:39 PM
 */

require_once 'KKCrmApi.php';

$kkcrmApi = new KKCrmApi();
$loginId = 'lotusa20';
$password = 'Knnek1298%@';

$kkcrmApi->credentials($loginId, $password);
$ret = $kkcrmApi->getAllCampaigns('CampaignId');
if ($ret == $kkcrmApi::ERROR)
    echo 'error';
else
    print_r($ret);








