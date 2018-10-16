<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 8/19/2018
 * Time: 3:57 PM
 */

require_once '../api/DBApi.php';


$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo json_encode(array('no_cookie'));
    return;
}

$affiliates_goal = $dbApi->getCapUpdate();

echo json_encode($affiliates_goal);

?>