<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/7/2018
 * Time: 9:16 PM
 */

require_once '../api/DBApi.php';


$crm_ids = explode(',', $_GET['crm_ids']);
$crm_goals = explode(',', $_GET['crm_goals']);

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

foreach ($crm_ids as $index=>$crm_id) {
    $dbApi->updateCrmGoal($crm_id, $crm_goals[$index]);
}
echo 'success';

?>