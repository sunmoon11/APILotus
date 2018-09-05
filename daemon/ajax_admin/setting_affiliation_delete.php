<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 9/5/2018
 * Time: 6:34 AM
 */

require_once '../api/DBApi.php';


$affiliate_id = $_GET['affiliate_id'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->deleteAffiliation($affiliate_id);
if ($ret)
    echo 'success';
else
    echo 'error';

?>