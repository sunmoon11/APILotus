<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/5/2018
 * Time: 6:35 AM
 */

require_once '../api/DBApi.php';

$affiliate_id = $_GET['affiliate_id'];
$offer_ids = explode(',', $_GET['offer_ids']);
$offer_goals = explode(',', $_GET['offer_goals']);
$from_date = $_GET['from_date'];
$to_date = $_GET['to_date'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->editAffiliationGoals($affiliate_id, $offer_ids, $offer_goals, $from_date, $to_date);
if ($ret)
    echo 'success';
else
    echo 'error';

?>