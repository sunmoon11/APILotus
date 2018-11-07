<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/2/2018
 * Time: 4:52 AM
 */

require_once '../api/DBApi.php';


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$affiliates = $dbApi->getAllAffiliations();
$offers = $dbApi->getAllOffersWithCRMGoal();
$affiliates_goal = $dbApi->getAffiliationGoal();

$result = array();

foreach ($affiliates as $affiliate) {
    $sub_result = array();
    foreach ($offers as $offer) {
        foreach ($affiliates_goal as $affiliate_goal) {
            if ($affiliate[0] == $affiliate_goal[1] and $offer[0] == $affiliate_goal[2]) {
                $sub_result[] = array($affiliate_goal[0], $affiliate_goal[3], $offer[0], $offer[1], $offer[2] . '(' . $offer[3] . ')', $offer[7], $offer[5], $offer[8], $offer[9], $offer[10]);
                break;
            }
        }
    }
    $result[] = array($affiliate, $sub_result);
}

echo json_encode($result);
