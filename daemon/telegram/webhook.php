<?php

require_once './TelegramBot.php';
require_once '../api/DBApi.php';

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


$bot = new TelegramBot();
$bot->sendChatAction('typing', $chatID);

// add bot chat history to db
$dbApi = DBApi::getInstance();
//$dbApi->insertBotHistory($updateID, $fromID, $fromName, $chatID, $chatName, $date, $command, $type);
$subDomain = $dbApi->getSubDomainByBotID($chatID);

// check command list
$text = '';
$unregText = 'Hi, How are you?'."\r\n".'Unfortunately, this chat id is not registered on API Lotus site.'."\r\n".'Please register this one on it.'."\r\n\r\n".'You can confirm the CHAT ID with below command.';
$normalText = 'Hi, How are you?'."\r\n".'Please select the command for API Lotus.';

if ($command == '/check_this_chat_info' || $command == '/check_this_chat_info@apilotus_bot')
{
    $text = 'This Chat Information'."\r\n\r\n";
    $text .= 'Chat ID : '.$chatID."\r\n";
//    $text .= 'Chat Name : '.$chatName."\r\n";
//    $text .= 'Chat Type : '.$type."\r\n\r\n";
    if ($subDomain == '') {
        $text .= 'Sub Domain : Not Registered' . "\r\n\r\n";
        $text .= 'Please register your CHAT ID on site'."\r\n";
        $text .= 'and start the bot again with "/start"';
        $bot->sendPureMessageByID($text, $chatID);
        return;
    }
    else
        $text .= 'Sub Domain : '.$subDomain."\r\n";
}
else if ($command == '/dashboard_takerate' || $command == '/dashboard_takerate@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $userCrm = $dbApi->getUserCrmByBotID($chatID);
        $activeCrms = $userCrm['active_crm'];
        if ($activeCrms == array())
        {
            $text = "There are no active CRMs, please try to enable in account page.";
        }
        else
        {
            $ret = $dbApi->getCrmResultForAlert();

            if (count($ret) == 0) {
                $text = "There are no result, please try again after some minutes later.";
            }
            else {
                $activeCrmIds = array();
                foreach ($activeCrms as $item)
                {
                    $activeCrmIds[] = $item[0];
                }

                $filtered = array();
                foreach ($ret as $item)
                {
                    $crmId = $item['crm_id'];
                    if (in_array($crmId, $activeCrmIds))
                    {
                        $filtered[] = $item;
                    }
                }
                $ret = $filtered;

                $text = 'CRM Name    [TAKE RATE %]'."\r\n\r\n";
                $text .= 'Timestamp: '.$ret[0]['timestamp']."\r\n\r\n";

                for ($i = 0; $i < sizeof($ret); $i ++)
                    $text .= ($i + 1).'. '.$ret[$i]['crm_name'].'    ['.round($ret[$i]['takerate'], 2).'%]'."\r\n";
            }
            $text .= "\r\n\r\n".'Related commands:'."\r\n";
            $text .= '/dashboard_tablet'."\r\n";
            $text .= '/dashboard_goal'."\r\n";
        }

    }
}
else if ($command == '/dashboard_tablet' || $command == '/dashboard_tablet@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $userCrm = $dbApi->getUserCrmByBotID($chatID);
        $activeCrms = $userCrm['active_crm'];
        if ($activeCrms == array())
        {
            $text = "There are no active CRMs, please try to enable in account page.";
        }
        else
        {
            $ret = $dbApi->getCrmResultForAlert();

            if (count($ret) == 0) {
                $text = "There are no result, please try again after some minutes later.";
            }
            else {
                $activeCrmIds = array();
                foreach ($activeCrms as $item)
                {
                    $activeCrmIds[] = $item[0];
                }

                $filtered = array();
                foreach ($ret as $item)
                {
                    $crmId = $item['crm_id'];
                    if (in_array($crmId, $activeCrmIds))
                    {
                        $filtered[] = $item;
                    }
                }
                $ret = $filtered;

                $text = 'CRM Name    [TABLET %]' . "\r\n\r\n";
                $text .= 'Timestamp: '.$ret[0]['timestamp']."\r\n\r\n";
                for ($i = 0; $i < sizeof($ret); $i++)
                    $text .= ($i + 1) . '. ' . $ret[$i]['crm_name'] . '    [' . round($ret[$i]['tablet_takerate'], 2) . '%]' . "\r\n";
            }
            $text .= "\r\n\r\n" . 'Related commands:' . "\r\n";
            $text .= '/dashboard_takerate' . "\r\n";
            $text .= '/dashboard_goal' . "\r\n";
        }
    }
}
else if ($command == '/dashboard_goal' || $command == '/dashboard_goal@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $userCrm = $dbApi->getUserCrmByBotID($chatID);
        $activeCrms = $userCrm['active_crm'];
        if ($activeCrms == array())
        {
            $text = "There are no active CRMs, please try to enable in account page.";
        }
        else
        {
            $ret = $dbApi->getCrmResultForAlert();

            if (count($ret) == 0) {
                $text = "There are no result, please try again after some minutes later.";
            }
            else {
                $activeCrmIds = array();
                foreach ($activeCrms as $item)
                {
                    $activeCrmIds[] = $item[0];
                }

                $filtered = array();
                foreach ($ret as $item)
                {
                    $crmId = $item['crm_id'];
                    if (in_array($crmId, $activeCrmIds))
                    {
                        $filtered[] = $item;
                    }
                }
                $ret = $filtered;

                $text = 'CRM Name    [STEP1 / GOAL]'."\r\n\r\n";
                $text .= 'Timestamp: '.$ret[0]['timestamp']."\r\n\r\n";
                for ($i = 0; $i < sizeof($ret); $i ++)
                {
                    if ($ret[$i]['goal'] == '0')
                        $text .= ($i + 1).'. '.$ret[$i]['crm_name'].'    ['.$ret[$i]['step1'].' / '.$ret[$i]['goal'].'] (0%)'."\r\n";
                    else
                        $text .= ($i + 1).'. '.$ret[$i]['crm_name'].'    ['.$ret[$i]['step1'].' / '.$ret[$i]['goal'].'] ('.round($ret[$i]['step1'] * 100 / $ret[$i]['goal'], 2).'%)'."\r\n";
                }
            }
            $text .= "\r\n\r\n".'Related commands:'."\r\n";
            $text .= '/dashboard_takerate'."\r\n";
            $text .= '/dashboard_tablet'."\r\n";
        }

    }
}
else if ($command == '/alert_step1_rebill_report' || $command == '/alert_step1_rebill_report@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $ret = $dbApi->getAllLatestAlertReportByType(1);

        $text = 'CRM Name    [Value / Level] for Step1 Rebill Report Alert'."\r\n\r\n";
        for ($i = 0; $i < sizeof($ret); $i ++)
            $text .= ($i + 1).'. '.$ret[$i][11].'    ['.$ret[$i][3].' / '.$ret[$i][4].']'."\r\n";

        $text .= getRelatedCommands($command);
    }
}
else if ($command == '/alert_step2_rebill_report' || $command == '/alert_step2_rebill_report@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $ret = $dbApi->getAllLatestAlertReportByType(2);

        $text = 'CRM Name    [Value / Level] for Step2 Rebill Report Alert'."\r\n\r\n";
        for ($i = 0; $i < sizeof($ret); $i ++)
            $text .= ($i + 1).'. '.$ret[$i][11].'    ['.$ret[$i][3].' / '.$ret[$i][4].']'."\r\n";

        $text .= getRelatedCommands($command);
    }
}
else if ($command == '/alert_initial_approval_day' || $command == '/alert_initial_approval_day@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $ret = $dbApi->getAllLatestAlertReportByType(3);

        $text = 'CRM Name    [Value / Level] for Initial Approval Day Alert'."\r\n\r\n";
        for ($i = 0; $i < sizeof($ret); $i ++)
            $text .= ($i + 1).'. '.$ret[$i][11].'    ['.$ret[$i][3].' / '.$ret[$i][4].']'."\r\n";

        $text .= getRelatedCommands($command);
    }
}
else if ($command == '/alert_initial_approval_week' || $command == '/alert_initial_approval_week@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $ret = $dbApi->getAllLatestAlertReportByType(4);

        $text = 'CRM Name    [Value / Level] for Initial Approval Week Alert'."\r\n\r\n";
        for ($i = 0; $i < sizeof($ret); $i ++)
            $text .= ($i + 1).'. '.$ret[$i][11].'    ['.$ret[$i][3].' / '.$ret[$i][4].']'."\r\n";

        $text .= getRelatedCommands($command);
    }
}
else if ($command == '/alert_decline_percentage_day' || $command == '/alert_decline_percentage_day@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $ret = $dbApi->getAllLatestAlertReportByType(5);

        $text = 'CRM Name    [Value / Level] for Decline Percentage Day Alert'."\r\n\r\n";
        for ($i = 0; $i < sizeof($ret); $i ++)
            $text .= ($i + 1).'. '.$ret[$i][11].'    ['.$ret[$i][3].' / '.$ret[$i][4].']'."\r\n";

        $text .= getRelatedCommands($command);
    }
}
else if ($command == '/alert_decline_percentage_week' || $command == '/alert_decline_percentage_week@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $ret = $dbApi->getAllLatestAlertReportByType(6);

        $text = 'CRM Name    [Value / Level] for Decline Percentage Week Alert'."\r\n\r\n";
        for ($i = 0; $i < sizeof($ret); $i ++)
            $text .= ($i + 1).'. '.$ret[$i][11].'    ['.$ret[$i][3].' / '.$ret[$i][4].']'."\r\n";

        $text .= getRelatedCommands($command);
    }
}
else if ($command == '/alert_100step1_sales' || $command == '/alert_100step1_sales@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $ret = $dbApi->getAllLatestAlertReportByType(7);

        $text = 'CRM Name    [Value / Goal] for 100 Step1 Sales Away From Cap Alert'."\r\n\r\n";
        for ($i = 0; $i < sizeof($ret); $i ++)
            $text .= ($i + 1).'. '.$ret[$i][11].'    ['.$ret[$i][3].' / '.$ret[$i][12].']'."\r\n";

        $text .= getRelatedCommands($command);
    }
}
else if ($command == '/alert_30step1_sales' || $command == '/alert_30step1_sales@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $ret = $dbApi->getAllLatestAlertReportByType(8);

        $text = 'CRM Name    [Value / Goal] for 30 Step1 Sales Over Cap Alert'."\r\n\r\n";
        for ($i = 0; $i < sizeof($ret); $i ++)
            $text .= ($i + 1).'. '.$ret[$i][11].'    ['.$ret[$i][3].' / '.$ret[$i][12].']'."\r\n";

        $text .= getRelatedCommands($command);
    }
}
else if ($command == '/alert_take_rate' || $command == '/alert_take_rate@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $ret = $dbApi->getAllLatestAlertReportByType(9);

        $text = 'CRM Name    [Value / Level] for Take Rate Alert'."\r\n\r\n";
        for ($i = 0; $i < sizeof($ret); $i ++)
            $text .= ($i + 1).'. '.$ret[$i][11].'    ['.$ret[$i][3].' / '.$ret[$i][4].']'."\r\n";

        $text .= getRelatedCommands($command);
    }
}
else if ($command == '/alert_table_take_rate' || $command == '/alert_table_take_rate@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $ret = $dbApi->getAllLatestAlertReportByType(10);

        $text = 'CRM Name    [Value / Level] for Table Take Rate Alert'."\r\n\r\n";
        for ($i = 0; $i < sizeof($ret); $i ++)
            $text .= ($i + 1).'. '.$ret[$i][11].'    ['.$ret[$i][3].' / '.$ret[$i][4].']'."\r\n";

        $text .= getRelatedCommands($command);
    }
}
else if ($command == '/alert_step1_crm_capped' || $command == '/alert_step1_crm_capped@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $ret = $dbApi->getAllLatestAlertReportByType(11);

        $text = 'CRM Name    [Value / Level] for Step1 CRM Capped Alert'."\r\n\r\n";
        for ($i = 0; $i < sizeof($ret); $i ++)
            $text .= ($i + 1).'. '.$ret[$i][11].'    ['.$ret[$i][3].' / '.$ret[$i][4].']'."\r\n";

        $text .= getRelatedCommands($command);
    }
}
else if ($command == '/alert_password_validdays' || $command == '/alert_password_validdays@apilotus_bot')
{
    if ($subDomain == '')
        $text = $unregText;
    else
    {
        $dbApi->setSubDomain($subDomain);
        $ret = $dbApi->getAllCrm();

        $text = 'CRM Name    [Password Valid Days]'."\r\n\r\n";

        for ($i = 0; $i < sizeof($ret); $i ++)
        {
            if ($ret[$i][9] == '' || $ret[$i][9] == null || $ret[$i][9] == '0000-00-00')
                $text .= ($i + 1).'. '.$ret[$i][1].'    [No set password date]'."\r\n";
            else
                $text .= ($i + 1).'. '.$ret[$i][1].'    ['.(30 - date_diff(date_create($ret[$i][9]), date_create($ret[$i][10]))->days).' days]'."\r\n";
        }

        $text .= getRelatedCommands($command);
    }
}
else
{
    if ($subDomain == '')
        $text = $unregText;
    else
        $text = $normalText;
}

if ($subDomain == '')
    $bot->sendRegisterMessage($text, $chatID);
else
    $bot->sendNormalMessage($text, $chatID);


function getRelatedCommands($cmd)
{
    $cmds = "\r\n\r\n".'Related commands:'."\r\n";

    if ($cmd != '/alert_step1_rebill_report')
        $cmds .= '/alert_step1_rebill_report'."\r\n";
    if ($cmd != '/alert_step2_rebill_report')
        $cmds .= '/alert_step2_rebill_report'."\r\n";
    if ($cmd != '/alert_initial_approval_day')
        $cmds .= '/alert_initial_approval_day'."\r\n";
    if ($cmd != '/alert_initial_approval_week')
        $cmds .= '/alert_initial_approval_week'."\r\n";
    if ($cmd != '/alert_decline_percentage_day')
        $cmds .= '/alert_decline_percentage_day'."\r\n";
    if ($cmd != '/alert_decline_percentage_week')
        $cmds .= '/alert_decline_percentage_week'."\r\n";
    if ($cmd != '/alert_100step1_sales')
        $cmds .= '/alert_100step1_sales'."\r\n";
    if ($cmd != '/alert_30step1_sales')
        $cmds .= '/alert_30step1_sales'."\r\n";
    if ($cmd != '/alert_take_rate')
        $cmds .= '/alert_take_rate'."\r\n";
    if ($cmd != '/alert_table_take_rate')
        $cmds .= '/alert_table_take_rate'."\r\n";
    if ($cmd != '/alert_step1_crm_capped')
        $cmds .= '/alert_step1_crm_capped'."\r\n";
    if ($cmd != '/alert_password_validdays')
        $cmds .= '/alert_password_validdays'."\r\n";

    return $cmds;
}