<?php

require_once '../api/DBApi.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo 'no_cookie';
    return;
}

$user_id = $_SESSION['user_id'];
session_write_close();

$siteName = $_GET['site_name'];
$siteUrl = $_GET['site_url'];
$siteName = explode(',', $siteName);
$siteUrl = explode(',', $siteUrl);

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}
$ret = 'success';
$existing = 0;
for ($i = 0; $i < count($siteName); $i++)
{
    if($siteName[$i] != "" && $siteUrl[$i] != "")
    {
        $result = $dbApi->checkUrl($user_id, $siteName[$i], $siteUrl[$i]);
        if ($result === false)
        {
            $result = $dbApi->addMonitorSite($user_id, $siteName[$i], $siteUrl[$i]);
            if (!$result)
            {
                $ret = 'error';
                break;
            }
        } else if ($result === true){
            $existing ++;
        }
    } else
    {
        $existing ++;
    }
}
if ($existing == count($siteName))
    $ret = 'exist';

echo $ret;