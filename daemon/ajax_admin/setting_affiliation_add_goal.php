<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/5/2018
 * Time: 6:34 AM
 */

require_once '../api/DBApi.php';

$affiliate_id = $_GET['affiliate_id'];
$offer_id = $_GET['offer_id'];
$goal = $_GET['goal'];
$from_date = $_GET['from_date'];
$to_date = $_GET['to_date'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->addAffiliationGoal($affiliate_id, $offer_id, $goal, $from_date, $to_date);
if ($ret)
    echo 'success';
else
    echo 'error';

?>