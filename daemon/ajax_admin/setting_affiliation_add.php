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

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->addAffiliation($name, $afid);
if ($ret)
    echo 'success';
else
    echo 'error';

?>