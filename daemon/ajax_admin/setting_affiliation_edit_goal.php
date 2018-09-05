<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/5/2018
 * Time: 6:35 AM
 */

require_once '../api/DBApi.php';

$affiliate_goal_id = $_GET['affiliate_goal_id'];
$goal = $_GET['goal'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->editAffiliationGoal($affiliate_goal_id, $goal);
if ($ret)
    echo 'success';
else
    echo 'error';

?>