<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/2/2018
 * Time: 4:52 AM
 */

require_once '../api/DBApi.php';


$fromDate = $_GET['from_date'];
$toDate = $_GET['to_date'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$affiliates = $dbApi->getAllAffiliations();
$offers = $dbApi->getAllOffersWithCRMGoal();
$all_offers = $dbApi->getAllOffersOfAffiliates();
$affiliates_goal = $dbApi->getAffiliationGoal($fromDate, $toDate);

$result = array();

foreach ($affiliates as $affiliate) {
    $sub_result = array();
    foreach ($offers as $offer) {
        foreach ($all_offers as $offer_of_affiliate) {
            if ($affiliate[0] == $offer_of_affiliate[1] and $offer[0] == $offer_of_affiliate[2]) {
                $goal = 0;
                $affiliate_goal_id = 0;
                foreach ($affiliates_goal as $affiliate_goal) {
                    if ($affiliate[0] == $affiliate_goal[1] and $offer[0] == $affiliate_goal[2]) {
                        $goal = $affiliate_goal[3];
                        $affiliate_goal_id = $affiliate_goal[0];
                        break;
                    }
                }
                $sub_result[] = array($affiliate_goal_id, $goal, $offer[0], $offer[1], $offer[2] . '(' . $offer[3] . ')');
            }
        }
    }
    $result[] = array($affiliate, $sub_result);
}

echo json_encode($result);

?>