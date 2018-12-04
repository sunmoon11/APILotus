<?php
/**
 * Created by PhpStorm.
 * User: zaza3
 * Date: 2018-11-28
 * Time: 10:41 AM
 */

require_once './TelegramBot.php';
require_once '../api/DBApi.php';
require_once '../../lib/utils/TimeUtils.php';

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}


$data = json_decode(file_get_contents('php://input'), true);

/*
Array
(
    [update_id] => 143704935
    [message] => Array
        (
            [message_id] => 61
            [from] => Array
                (
                    [id] => 11111
                    [first_name] =>
                    [username] =>
                )

            [chat] => Array
                (
                    [id] => -11111111
                    [title] => chatting room title
                    [type] => group
                )

            [date] => 1454424972
            [text] => /command
        )

)
*/

$updateID = $data['update_id'];
$fromID = $data['message']['from']['id'];
$fromName = $data['message']['from']['username'];
$chatID = $data['message']['chat']['id'];
$chatName = $data['message']['chat']['username'];
$type = $data['message']['chat']['type'];
$date = $data['message']['date'];
$command = $data['message']['text'];

// if chat room
if (empty($chatName) == true) {
    $chatName = $data['message']['chat']['title'];
}

$timeUtil = TimeUtils::getInstance();

$bot = new TelegramBot(false);
$bot->sendChatAction('typing', $chatID);
//$bot->sendPureMessageByID(json_encode($data), $chatID);

// add bot chat history to db
$dbApi = DBApi::getInstance();
//$dbApi->insertBotHistory($updateID, $fromID, $fromName, $chatID, $chatName, $date, $command, $type);
$activated = $dbApi->checkAffiliateBotID($chatID);

// check command list
$text = '';
$unregText = 'Hi, How are you?'."\r\n".'Unfortunately, this group is not activated.';
$normalText = 'Hi, How are you?'."\r\n".'Please select one of the commands below.';

if ($command == '/start') {
    $bot->sendChatAction('typing', $chatID);
    if ($activated)
        $text = $normalText;
    else
        $text = $unregText;
}
else if (startsWith($command, '/activate')) {
    $bot->sendChatAction('typing', $chatID);
//    if ($fromID == '558667560') {
        if (27 == strlen($command)) {
            $valid = $dbApi->activateAffiliateBotID(substr($command, 10, -1), $chatID);
            if ($valid) {
                $text = "Congratulation!"."\r\n"."This group activated successfully.";
                $activated = true;
            }
            else {
                $text = "Sorry."."\r\n"."Security key is not correct. Please check again.";
            }
        }
        else {
            $text = "Sorry."."\r\n"."Please input the security key with correct format."."\r\n"."/activate[16 length security key]";
        }
//    }
//    else {
//        $text = 'You have no activate permission.';
//    }
}
else if ($command == '/deactivate') {
    $bot->sendChatAction('typing', $chatID);
//    if ($fromID == '558667560') {
        $valid = $dbApi->deactivateAffiliateBotID($chatID);
        $text = 'Hi, How are you?'."\r\n".'This group has deactivated.';
        $activated = false;
//    }
//    else
//        $text = 'You have no deactivate permission';
}
else if ($command == '/capupdate') {
    $bot->sendChatAction('typing', $chatID);
    $affiliate = $dbApi->getAffiliationByGroupChatID($chatID);
    $offers = $dbApi->getCapUpdateByAffiliateID($affiliate['id']);

    $fromDate = $timeUtil->getDateOfCurrentWeek()[0];
    $toDate = $timeUtil->getDateOfCurrentWeek()[1];

    $text = '* '.$affiliate['name'].' Progress *'."\r\n\r\n";

    foreach ($offers as $idx=>$offer) {
        $result_by_crm = $dbApi->getCapUpdateResult($offer['crm_id'], $fromDate, $toDate);
        if (false == $result_by_crm || null == $result_by_crm)  continue;
        $result = json_decode(str_replace("'", '"', $result_by_crm[0]));

        $count = 0;
        $afids = explode(',', $offer['afid']);
        $campaign_ids = explode(',', $offer['campaign_ids']);
        foreach ($result as $campaign_prospects) {
            foreach ($campaign_ids as $campaign_id) {
                if ("step1" === explode('_', $campaign_id)[0]) {
                    if (explode('_', $campaign_id)[1] == $campaign_prospects[0]) {
                        foreach ($campaign_prospects[1] as $campaign_prospect) {
                            foreach ($afids as $afid) {
                                if ($campaign_prospect[0] == $afid) {
                                    $count += $campaign_prospect[2];
                                }
                            }
                        }
                    }
                }
            }
        }
        $text .= ($idx+1).'. '.$offer['offer_name'].' ['.$count.' / '.$offer['goal'].']'."\r\n";
    }
}
else if ($command == '/capped') {
    $bot->sendChatAction('typing', $chatID);
    $affiliate = $dbApi->getAffiliationByGroupChatID($chatID);
    $offers = $dbApi->getCapUpdateByAffiliateID($affiliate['id']);

    $fromDate = $timeUtil->getDateOfCurrentWeek()[0];
    $toDate = $timeUtil->getDateOfCurrentWeek()[1];

    $text = '* '.$affiliate['name'].' Capped Progress *'."\r\n\r\n";

    $capped_count = 0;
    foreach ($offers as $idx=>$offer) {
        $result_by_crm = $dbApi->getCapUpdateResult($offer['crm_id'], $fromDate, $toDate);
        if (false == $result_by_crm || null == $result_by_crm)  continue;
        $result = json_decode(str_replace("'", '"', $result_by_crm[0]));

        $count = 0;
        $afids = explode(',', $offer['afid']);
        $campaign_ids = explode(',', $offer['campaign_ids']);
        foreach ($result as $campaign_prospects) {
            foreach ($campaign_ids as $campaign_id) {
                if ("step1" === explode('_', $campaign_id)[0]) {
                    if (explode('_', $campaign_id)[1] == $campaign_prospects[0]) {
                        foreach ($campaign_prospects[1] as $campaign_prospect) {
                            foreach ($afids as $afid) {
                                if ($campaign_prospect[0] == $afid) {
                                    $count += $campaign_prospect[2];
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($count >= $offer['goal']) {
            $capped_count += 1;
            $text .= ($idx + 1) . '. ' . $offer['offer_name'] . ' [' . $count . ' / ' . $offer['goal'] . ']' . "\r\n";
        }
    }
    if (0 == $capped_count)
        $text .= 'There is no capped offer yet.';
}
else if (startsWith($command, '/capupdateid')) {
    $bot->sendChatAction('typing', $chatID);
    if (!endsWith($command, ']') or ($command[12] != '[') or !is_numeric(substr($command, 13, -1)))
        $text = "Sorry."."\r\n"."Please input the affiliate ID with correct format."."\r\n"."/capupdateid[affiliate id]";
    else {
        $affiliate = $dbApi->getAffiliationByGroupChatID($chatID);
        $afids = explode(',', $affiliate['afid']);

        $afid = substr($command, 13, -1);

        if (!in_array($afid, $afids))
            $text = "Sorry."."\r\n"."Affiliate ID is not correct.";
        else {
            $offers = $dbApi->getCapUpdateByAffiliateID($affiliate['id']);

            $fromDate = $timeUtil->getDateOfCurrentWeek()[0];
            $toDate = $timeUtil->getDateOfCurrentWeek()[1];

            $text = '* '.$affiliate['name'].' Progress *'."\r\n\r\n";

            foreach ($offers as $idx=>$offer) {
                $result_by_crm = $dbApi->getCapUpdateResult($offer['crm_id'], $fromDate, $toDate);
                if (false == $result_by_crm || null == $result_by_crm)  continue;
                $result = json_decode(str_replace("'", '"', $result_by_crm[0]));

                $count = 0;
                $campaign_ids = explode(',', $offer['campaign_ids']);
                foreach ($result as $campaign_prospects) {
                    foreach ($campaign_ids as $campaign_id) {
                        if ("step1" === explode('_', $campaign_id)[0]) {
                            if (explode('_', $campaign_id)[1] == $campaign_prospects[0]) {
                                foreach ($campaign_prospects[1] as $campaign_prospect) {
                                    if ($campaign_prospect[0] == $afid) {
                                        $count += $campaign_prospect[2];
                                    }
                                }
                            }
                        }
                    }
                }
                $text .= ($idx+1).'. '.$offer['offer_name'].' ['.$count.' / '.$offer['goal'].']'."\r\n";
            }
        }
    }
}
else {
    return;
}

if ($activated)
    $bot->sendAffiliateNormalMessage($text, $chatID);
else
    $bot->sendAffiliateRegisterMessage($text, $chatID);
