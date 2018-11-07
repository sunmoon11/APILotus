<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/5/2018
 * Time: 6:34 AM
 */

require_once '../api/DBApi.php';

$affiliate_id = $_GET['affiliate_id'];
$name = $_GET['name'];
$afid = $_GET['afid'];
$offer_ids = array_filter(explode(',', $_GET['offer_ids']));
$offer_goals = explode(',', $_GET['offer_goals']);

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->editAffiliation($affiliate_id, $name, $afid, $offer_ids, $offer_goals);
if ($ret)
    echo 'success';
else
    echo 'error';

?>