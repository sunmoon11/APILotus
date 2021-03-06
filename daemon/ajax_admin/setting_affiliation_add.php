<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/5/2018
 * Time: 6:34 AM
 */

require_once '../api/DBApi.php';

$name = $_GET['name'];
$afid = $_GET['afid'];
$offer_ids = array_filter(explode(',', $_GET['offer_ids']));
$offer_goals = explode(',', $_GET['offer_goals']);
$s1_payouts = explode(',', $_GET['s1_payouts']);
$s2_ids = array_filter(explode(',', $_GET['s2_ids']));
$s2_payouts = explode(',', $_GET['s2_payouts']);

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->addAffiliation($name, $afid, $offer_ids, $offer_goals, $s1_payouts, $s2_ids, $s2_payouts);
if ($ret)
    echo 'success';
else
    echo 'error';

?>