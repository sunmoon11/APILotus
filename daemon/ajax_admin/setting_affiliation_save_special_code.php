<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 2018-11-28
 * Time: 12:31 PM
 */

require_once '../api/DBApi.php';
require_once '../telegram/TelegramBot.php';

$affiliate_id = $_GET['affiliate_id'];
$special_code = $_GET['special_code'];

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$ret = $dbApi->editSpecialCode($affiliate_id, $special_code);
if (false == $ret) {
    echo 'error';
}
else {
    if ($ret != 'empty') {
        $bot = new TelegramBot(false);
        $bot->sendChatAction('typing', $ret);
        $bot->sendAffiliateRegisterMessage('Hi, How are you?'."\r\n".'This group has deactivated.', $ret);
    }
    echo 'success';
}
