<?php

require_once 'AlertMethodApi.php';
require_once 'DBApi.php';
require_once '../telegram/TelegramBot.php';
require_once '../../lib/utils/TimeUtils.php';
class AlertManager {

    protected static $instance;
    private $subDomain = '';
    public static function getInstance()
    {

        if( is_null( static::$instance ) )
        {
            static::$instance = new AlertManager();
        }
        return static::$instance;
    }
    protected function __construct() {

    }

    private function __clone() {

    }
    public function __get($name) {
        return (isset($this->{$name})) ? $this->{$name} : null;
    }
    public function __set($name, $value){
        return $this->{$name} = $value;

    }
    private function __wakeup() {

    }
    private function getAlertDataByType($type)
    {
        $dbApi = DBApi::getInstance();
        $dbApi->setSubDomain($this->__get('subDomain'));
        $today = date('Y-m-d');
        $alertData = $dbApi->getAllAlertReportByType($today, $type);

        $report = array();
        $fromDate = '';
        $toDate = '';

        foreach ($alertData as $values)
        {
            $crmId = $values[1];
            $crmName = $values[11];
            $value = $values[3];
            $level = $values[4];
            $status = $values[5];

            if($values[2] == $type)
            {
                $report[] = array($crmName, $value, $level, $type, $status, $crmId);
                $fromDate = $values[9];
                $toDate = $values[10];
            }
        }

        if($report == array())
            return array();

        $data['status'] = $report;
        $data['fromDate'] = $fromDate;
        $data['toDate'] = $toDate;

        return $data;
    }
    private function getInitialApprovalAlertData($day)
    {
        $dbApi = DBApi::getInstance();
        $dbApi->setSubDomain($this->__get('subDomain'));
        $allCrmList = $dbApi->getAllActiveCrm();
        $level = 0;
        $type = ($day == 0) ? 4 : 3; // if day = 0 then week alert;
        $minGrossOrder = 15;

        $overLevelData = array();
        $belowLevelData = array();
        $belowLevelDataItem = array();

        $timeUtil = TimeUtils::getInstance();
        $fromDate = '';
        $toDate = '';
        if ($type == 3) {
            $fromDate = $timeUtil->getDateOfCurrentDay();
            $toDate = $timeUtil->getDateOfCurrentDay();
        } elseif ($type == 4) {
            $fromDate = $timeUtil->getDateOfCurrentWeek()[0];
            $toDate = $timeUtil->getDateOfCurrentWeek()[1];
        }

        foreach ($allCrmList as $crmInfo) {
            if ($crmInfo != null && $crmInfo[8] != 1) {
                $crmId = $crmInfo[0];
                $crmName = $crmInfo[1];
                // sum up
                $data = $dbApi->getSTEP1ApprovalRateForInitialAlertByCrm($crmId, $day);
                if($data[1] != '')
                    $fromDate = $data[1];
                if($data[2] != '')
                    $toDate = $data[2];

                $approvalRateOfSTEP1 = $data[0];
                $approvalRateOfSTEP1 = round($approvalRateOfSTEP1, 2);
                $alertSettings = $dbApi->getAlertLevelList($type);
                // get alert level
                foreach ($alertSettings as $alertInfo) {
                    if($alertInfo[1] == $crmId) {
                        $level = $alertInfo[4];
                        break;
                    }
                }
                // check status
                if ($approvalRateOfSTEP1 <= $level && $approvalRateOfSTEP1 > 0) {
                    $belowLevelDataItem['crm_id'] = $crmId;
                    $belowLevelDataItem['crm_name'] = $crmInfo[1];
                    $belowLevelDataItem['value'] = $approvalRateOfSTEP1;
                    $belowLevelDataItem['level'] = $level;

                    $campaignDetails = $dbApi->getCampaignDetailsForInitialAlertByCrm($crmId, $level, $day, $minGrossOrder);
                    $alertDetails = array();
                    $campaignValue = 0;
                    $affiliateValue = 0;

                    if($campaignDetails != array()) {
                        foreach ($campaignDetails as $campaignInfo) {
                            $campaignId = $campaignInfo[0];
                            $affiliateIds = array();
                            $campaignValue = $campaignInfo[2];

                            if($campaignInfo[1] == 1) {
                                $affiliateDetails = $dbApi->getAffiliateDetailsForInitialAlertByCampaignId($crmId, $campaignId, $level, $day, $minGrossOrder);
                                if ($affiliateDetails != array()) {
                                    foreach ($affiliateDetails as $affiliateInfo) {
                                        $affiliateId = $affiliateInfo[0];
                                        $subAffiliateIds = array();
                                        $affiliateValue = $affiliateInfo[2];
                                        if($affiliateInfo[1] == 1) {
                                            $subAffiliateIds = $dbApi->getSubAffiliateDetailsForInitialAlertByAffiliateId($crmId, $campaignId, $affiliateId, $level, $day, $minGrossOrder);
                                        }
                                        $affiliateIds[] = array($affiliateId, $affiliateValue, $subAffiliateIds);
                                    }
                                }
                            }
                            $alertDetails[] = array($campaignId, $campaignValue, $affiliateIds);
                        }
                    }

                    $belowLevelDataItem['detail'] = $alertDetails;
                    $belowLevelData[] = $belowLevelDataItem;

                } else if ($approvalRateOfSTEP1 > $level) {
                    $overLevelData[] = array('crm_name' =>$crmName, 'value' =>$approvalRateOfSTEP1, 'alert_level'=>$level, 'crm_id' => $crmId);
                }
            }
        }

        return array($belowLevelData, $overLevelData, $fromDate, $toDate);
    }
    public function sendDetailedInitialAlertByCrmPermissionOfAccount($methods, $belowData, $overData, $fromDate, $toDate, $day)
    {
        $sender = AlertMethodApi::getInstance();
        $telegramBot = new TelegramBot();
        $dbApi = DBApi::getInstance();
        $dbApi->setSubDomain($this->__get('subDomain'));
        $accounts = $dbApi->getAllUsers();
        $bot = $methods[2];
        $email = $methods[1];
        $sms = $methods[0];
        // filter alert data by user
        foreach ($accounts as $account) {
            $activeAccount = $account[5];
            if ($activeAccount){
                $crmPermission = $account[6];

                $newBelowData = $this->filterDetailedAlertByPermission($belowData, $crmPermission, false);
                $newOverData = $this->filterDetailedAlertByPermission($overData, $crmPermission, true);

                if ($newBelowData != array() || $newOverData != array()){

                    if ($bot){
                        $botId = $account[9];
                        $enableBot = $account[12];
                        if ($enableBot == 1 && $botId != null) {
                            $texts = $this->generateTextForSubAffiliateAlert($newBelowData, $newOverData, $fromDate, $toDate, $day);
                            if ($texts != array()){
                                foreach ($texts as $text){
                                    if ($text != "")
                                        $telegramBot->sendMessageByID($text, $botId);
                                }
                            }
                        }
                    }
                    if ($email){
                        $address = $account[8];
                        $enableEmail = $account[11];
                        if ($enableEmail == 1 && $address != null){
                            if($day == 1)
                                $subject = 'APILotus.com Initial Alert Day';
                            else
                                $subject = 'APILotus.com Initial Alert Week';
                            // body
                            $body = $this->generateHtmlForSubAffiliateAlert($newBelowData, $newOverData, $fromDate, $toDate, $day);
                            $ret = $sender->sendEmail('alert@apilotus.com', array($address), $subject, $body);
                            print_r($ret);
                        }
                    }
                    if ($sms) {
                        $phoneNumber = $account[7];
                        $enableSms = $account[10];
                        if ($enableSms == 1 && $phoneNumber != null){
                            $texts = $this->generateTextForSubAffiliateAlert($newBelowData, $newOverData, $fromDate, $toDate, $day);
                            foreach ($texts as $text){
                                if ($text != ""){
                                    $ret = $sender->sendSMS(array($phoneNumber), $text);
                                    print_r($ret);
                                }
                            }

                        }
                    }
                }
            }
        }
    }
    public function sendDetailedInitialAlert($methods, $belowData, $overData, $fromDate, $toDate, $day)
    {
        $sender = AlertMethodApi::getInstance();
        $bot = new TelegramBot();
        foreach ($methods as $value)
        {
            if($value == 2) {// telegram bot
                $texts = $this->generateTextForSubAffiliateAlert($belowData, $overData, $fromDate, $toDate, $day);

                foreach ($texts as $text) {
                    $bot->sendMessage($text);
                }

            } else if($value == 1) {// email

                // subject
                $subject = '';
                if($day == 1)
                    $subject = 'APILotus.com Initial Alert Day';
                else
                    $subject = 'APILotus.com Initial Alert Week';
                // body
                $body = $this->generateHtmlForSubAffiliateAlert($belowData, $overData, $fromDate, $toDate, $day);
                // address
                $dbApi = DBApi::getInstance();
                $address = $dbApi->getEnabledEmails();

                $ret = $sender->sendEmail('alert@apilotus.com', $address, $subject, $body);
                print_r($ret);
            } elseif ($value == 0) {// sms

                $text = $this->generateTextForSubAffiliateAlert($belowData, $overData, $fromDate, $toDate, $day);
                $dbApi = DBApi::getInstance();
                $numbers = $dbApi->getEnabledPhoneNumbers();
                $ret = $sender->sendSMS($numbers, $text);
                print_r($ret);
            }
        }
    }
    private function checkPermissionByCrmID($permissions, $crmId)
    {
        $arrayPermission = explode(',', $permissions);
        if(in_array($crmId, $arrayPermission))
            return true;
        else
            return false;


    }
    private function filterDetailedAlertByPermission($data, $permission, $below = false){
        $retData = array();
        if ($below){
            foreach ($data as $belowItem){
                if ($belowItem != null){
                    if ($this->checkPermissionByCrmID($permission, $belowItem['crm_id'])){
                        $retData[] = $belowItem;
                    }
                }
            }
            return $retData;

        }else{
            foreach ($data as $overItem){
                if ($overItem != null){
                    if ($this->checkPermissionByCrmID($permission, $overItem['crm_id'])){
                        $retData[] = $overItem;
                    }
                }
            }
            return $retData;
        }
    }

    private function filterAlertDataByPermission($data, $permission, $alertType){
        $retData = array();

        if ($alertType == 12 || $alertType == 13){
            foreach ($data as $crm){
                if ($this->checkPermissionByCrmID($permission, $crm[0])){
                    $retData[] = $crm;
                }
            }
            return $retData;
        }

        $statusData = $data['status'];
        foreach ($statusData as $sData){
            if ($this->checkPermissionByCrmID($permission, $sData[5])){
                $retData[] = $sData;
            }
        }
        $data['status'] = $retData;
        return $data;

    }

    public function sendAlertsByCrmPermissionOfAccount($sms, $email, $bot, $data, $type, $subDomain) {
        $sender = AlertMethodApi::getInstance();
        $telegramBot = new TelegramBot();
        $dbApi = DBApi::getInstance();

        $dbApi->setSubDomain($subDomain);
        $accounts = $dbApi->getAllUsers();
        // filter alert data by user and send
        foreach ($accounts as $account) {
            $activeAccount = $account[5];
            if (!$activeAccount)
                continue;
            $crmPermission = $account[6];
            if ($crmPermission == null)
                continue;
            $newData = $this->filterAlertDataByPermission($data, $crmPermission, $type);
            if ($newData == array())
                continue;

            if ($bot){
                $botId = $account[9];
                $enableBot = $account[12];
                if ($enableBot == 1 && $botId != null) {
                    $texts = $this->dataToText($newData, $type);
                    foreach ($texts as $text){
                        if ($text != "")
                            $telegramBot->sendMessageByID($text, $botId);
                    }
                }
            }
            if ($email){
                $address = $account[8];
                $enableEmail = $account[11];
                if ($enableEmail == 1 && $address != null){
                    // subject
                    $subject = '';
                    if($type == 1 || $type == 2)
                        $subject = 'APILotus.com Rebill Report Alert';
                    if($type == 3 || $type == 4)
                        $subject = 'APILotus.com Initial Approvals Alert';
                    if($type == 5 || $type == 6)
                        $subject = 'APILotus.com Decline Percentage Alert';
                    if($type == 7 || $type == 8)
                        $subject = 'APILotus.com Step1 Sales Away From Cap Alert';
                    if($type == 9)
                        $subject = 'APILotus.com Take Rate Alert';
                    if($type == 10)
                        $subject = 'APILotus.com Tablet Take Rate Alert';
                    if($type == 11)
                        $subject = 'APILotus.com Over Step1 CRM Cap Alert';
                    if($type == 12)
                        $subject = 'APILotus.com CRM Password Update Alert';
                    if($type == 13)
                        $subject = 'APILotus.com CRM Goal Status Alert';
                    // body
                    $body = $this->dataToHtml($newData, $type);
                    if ($body != ""){
                        $ret = $sender->sendEmail('alert@apilotus.com', array($address), $subject, $body);
                        print_r($ret);
                    }
                }
            }
            if ($sms) {
                $phoneNumber = $account[7];
                $enableSms = $account[10];
                if ($enableSms == 1 && $phoneNumber != null){
                    $texts = $this->dataToText($newData, $type);
                    foreach ($texts as $text) {
                        if ($text != ""){
                            $ret = $sender->sendSMS(array($phoneNumber), $text);
                            print_r($ret);
                        }

                    }
//                    if ($text != ""){
//                        $ret = $sender->sendSMS(array($phoneNumber), $text);
//                        print_r($ret);
//                    }

                }
            }
        }
    }
    public function sendKKCrmAlerts($sms, $email, $bot, $data, $type, $subDomain) {
        $sender = AlertMethodApi::getInstance();
        $telegramBot = new TelegramBot();
        $dbApi = DBApi::getInstance();

        $dbApi->setSubDomain($subDomain);
        $accounts = $dbApi->getAllUsers();
        // filter alert data by user and send
        foreach ($accounts as $account) {
            $activeAccount = $account[5];
            if (!$activeAccount)
                continue;
            $crmPermission = $account[6];
            if ($crmPermission == null)
                continue;
//            $newData = $this->filterAlertDataByPermission($data, $crmPermission, $type);
            $newData = $data;
//            if ($newData == array())
//                continue;

            if ($bot){
                $botId = $account[9];
                $enableBot = $account[12];
                if ($enableBot == 1 && $botId != null) {
                    $texts = $this->dataToText($newData, $type);
                    foreach ($texts as $text){
                        if ($text != "")
                            $telegramBot->sendMessageByID($text, $botId);
                    }
                }
            }
            if ($email){
                $address = $account[8];
                $enableEmail = $account[11];
                if ($enableEmail == 1 && $address != null){
                    // subject
                    $subject = '';
                    if($type == 1 || $type == 2)
                        $subject = 'APILotus.com Rebill Report Alert';
                    if($type == 3 || $type == 4)
                        $subject = 'APILotus.com Initial Approvals Alert';
                    if($type == 5 || $type == 6)
                        $subject = 'APILotus.com Decline Percentage Alert';
                    if($type == 7 || $type == 8)
                        $subject = 'APILotus.com Step1 Sales Away From Cap Alert';
                    if($type == 9)
                        $subject = 'APILotus.com Take Rate Alert';
                    if($type == 10)
                        $subject = 'APILotus.com Tablet Take Rate Alert';
                    if($type == 11)
                        $subject = 'APILotus.com Over Step1 CRM Cap Alert';
                    if($type == 12)
                        $subject = 'APILotus.com CRM Password Update Alert';
                    if($type == 13)
                        $subject = 'APILotus.com CRM Goal Status Alert';
                    // body
                    $body = $this->dataToHtml($newData, $type);
                    if ($body != ""){
                        $ret = $sender->sendEmail('alert@apilotus.com', array($address), $subject, $body);
                        print_r($ret);
                    }
                }
            }
            if ($sms) {
                $phoneNumber = $account[7];
                $enableSms = $account[10];
                if ($enableSms == 1 && $phoneNumber != null){
                    $texts = $this->dataToText($newData, $type);
                    foreach ($texts as $text) {
                        if ($text != ""){
                            $ret = $sender->sendSMS(array($phoneNumber), $text);
                            print_r($ret);
                        }

                    }
//                    if ($text != ""){
//                        $ret = $sender->sendSMS(array($phoneNumber), $text);
//                        print_r($ret);
//                    }

                }
            }
        }
    }
    public function sendAlerts($methods, $data, $type)
    {
        $sender = AlertMethodApi::getInstance();
        $bot = new TelegramBot();

        foreach ($methods as $value)
        {
            if($value == 2)
            {
                // telegram bot
                $text = $this->dataToText($data, $type);
                $bot->sendMessage($text);

            } else if($value == 1)
            {
                // email

                // subject
                $subject = '';
                if($type == 1 || $type == 2)
                    $subject = 'APILotus.com Rebill Report Alert';
                if($type == 3 || $type == 4)
                    $subject = 'APILotus.com Initial Approvals Alert';
                if($type == 5 || $type == 6)
                    $subject = 'APILotus.com Decline Percentage Alert';
                if($type == 7 || $type == 8)
                    $subject = 'APILotus.com Step1 Sales Away From Cap Alert';
                if($type == 9)
                    $subject = 'APILotus.com Take Rate Alert';
                if($type == 10)
                    $subject = 'APILotus.com Tablet Take Rate Alert';
                if($type == 11)
                    $subject = 'APILotus.com Over Step1 CRM Cap Alert';
                if($type == 12)
                    $subject = 'APILotus.com CRM Password Update Alert';
                if($type == 13)
                    $subject = 'APILotus.com CRM Goal Status Alert';

                // body
                $body = $this->dataToHtml($data, $type);
                // address
                $dbApi = DBApi::getInstance();
                $address = $dbApi->getEnabledEmails();

                $ret = $sender->sendEmail('alert@apilotus.com', $address, $subject, $body);
                print_r($ret);
            }
            else
            {
                // sms
                $text = $this->dataToText($data, $type);
                $dbApi = DBApi::getInstance();
                $numbers = $dbApi->getEnabledPhoneNumbers();
                $ret = $sender->sendSMS($numbers, $text);
                print_r($ret);
            }
        }
    }
    private function generateTextForSubAffiliateAlert($belowData, $overData, $fromDate, $toDate, $day)
    {
        $title = '';
        $dateText = '';
        $belowText = '';
        $overText = '';

        // title
        if($day == 1)
            $title = 'Initial Approval Day';
        else
            $title = 'Initial Approval Week';
        // date
        if($fromDate == '' || $toDate == '')
            return array();
        else {
            if ($day == 1) {
                $dateText ='Date Range : '.$fromDate."\r\n\r\n";
            } else {
                $dateText ='Date Range : '.$fromDate.' ~ '.$toDate."\r\n\r\n";
            }
        }

        // body
        foreach ($belowData as $data) {
            if ($data == null)
                break;
            $crmName = $data['crm_name'];
            $level = $data['level'];
            $crmDetails = $data['detail'];
            $crmValue = $data['value'];

            $belowText = $belowText.$crmName."\r\n";
            $belowText = $belowText.'Current Value :'.$crmValue.' <== (Level:'.$level.')'."\r\n\r\n";

            foreach ($crmDetails as $crmDetail) {
                $campaignId = $crmDetail[0];
                $campaignValue = $crmDetail[1];
                $campaignDetails = $crmDetail[2];

                if($campaignDetails != array()) {
                    foreach ($campaignDetails as $affiliateDetail) {
                        $affiliateId = $affiliateDetail[0];
                        $affiliateValue = $affiliateDetail[1];
                        $affiliateDetails = $affiliateDetail[2];
                        if($affiliateDetails != array()) {
                            foreach ($affiliateDetails as $subAffiliateDetail) {
                                $subAffiliateId = $subAffiliateDetail[0];
                                $subAffiliateValue = $subAffiliateDetail[1];

                                $belowText = $belowText.'AFID '.$affiliateId.' SID '.$subAffiliateId."\r\n";
                                $belowText = $belowText.'CampaignID '.$campaignId."\r\n";
                                $belowText = $belowText.'Current Value :'.$subAffiliateValue.' <== (Level:'.$level.')'."\r\n\r\n";
                            }
                        }
                        $belowText = $belowText.'AFID '.$affiliateId."\r\n";
                        $belowText = $belowText.'CampaignID '.$campaignId."\r\n";
                        $belowText = $belowText.'Current Value :'.$affiliateValue.' <== (Level:'.$level.')'."\r\n\r\n";
                    }
                }
                $belowText = $belowText.'CampaignID '.$campaignId."\r\n";
                $belowText = $belowText.'Current Value :'.$campaignValue.' <== (Level:'.$level.')'."\r\n\r\n";
            }

        }
        foreach ($overData as $data)
        {
            if ($data == null)
                break;
            $crmName = $data['crm_name'];
            $level = $data['alert_level'];
            $crmValue = $data['value'];

            $overText = $overText.$crmName."\r\n";
            $overText = $overText.'Current Value :'.$crmValue.' > (Level:'.$level.')'."\r\n\r\n";
        }

        $text = $title."\r\n\r\n".$dateText;
        $text = $text.$belowText.$overText;
        $botTextLimit = 4096;
        $texts = str_split($text, $botTextLimit);
        return $texts;
    }
    private function dataToText($content, $type)
    {
        $title = '';
        $alertText = '';
        $statusText = '';

        // title
        if($type == 1 || $type == 2)
            $title = 'Rebill Report Alert';
        if($type == 3 || $type == 4)
            $title = 'Initial Approvals Alert';
        if($type == 5 || $type == 6)
            $title = 'Decline Percentage Alert';
        if($type == 7 || $type == 8)
            $title = 'Step1 Sales Away From Cap Alert';
        if($type == 9)
            $title = 'Take Rate Alert';
        if($type == 10)
            $title = 'Tablet Take Rate Alert';
        if($type == 11)
            $title = '*OVER CAP ALERT*'."\r\n".'Step1 CRM Capped';
        if($type == 12)
            $title = '*CRM Password Update*';
        if($type == 13)
            $title = '*CRM Goal Progress*';

        if($type == 12)
        {
            $i = 1;
            foreach ($content as $crm){
                $alertText = $alertText.$i.$crm[1]."\r\n";
                $i ++;
            }
            $text = $title."\r\n\r\n".$alertText;
            $botTextLimit = 4096;
            $texts = str_split($text, $botTextLimit);
            return $texts;
        }
        if($type == 13)
        {
            $i = 1;
            foreach ($content as $crm)
            {
                $crmName = $crm[1];
                $step1 = $crm[2];
                $goal = $crm[7];
                if($goal == 0)
                    $rate = 0;
                else
                    $rate = ($step1 / $goal) * 100;
                $rate = floor($rate);
                $alertText = $alertText.$i.'. '.$crmName.' ['.$step1.' / '.$goal.'] ['.$rate.'%]'."\r\n";
                $i++;
            }
            $text = $title."\r\n\r\n".$alertText;
            $botTextLimit = 4096;
            $texts = str_split($text, $botTextLimit);
            return $texts;
        }

        // date
        if($content['fromDate'] == '' || $content['toDate'] == '')
            return '';
        else
            $dateText ='Date Range : '.$content['fromDate'].' ~ '.$content['toDate']."\r\n\r\n";

        if($type == 3 || $type == 5)
            $dateText = 'Today Alert'."\r\n".$dateText;
        if($type == 4 || $type == 6)
            $dateText = 'Week to Date Alert'."\r\n".$dateText;

        // body
        foreach ($content['status'] as $data)
        {
            if($data[4] == 1)
            {
                if($data[3] == 1)
                    $alertText = $alertText.'['.$data[0].'] Step1 :'.$data[1].'% ( <= '.$data[2].')'."\r\n";
                if($data[3] == 2)
                    $alertText = $alertText.'['.$data[0].'] Step2 :'.$data[1].'% ( <= '.$data[2].')'."\r\n";
                if($data[3] == 3 || $data[3] == 4)
                    $alertText = $alertText.'['.$data[0].'] '.$data[1].'% ( <= '.$data[2].')'."\r\n";
                if($data[3] == 5 || $data[3] == 6)
                    $alertText = $alertText.'['.$data[0].'] '.$data[1].'% ( >= '.$data[2].')'."\r\n";
                if($data[3] == 7 || $data[3] == 8)
                    $alertText = $alertText.'['.$data[0].'] '.$data[1].' ( >= '.$data[2].' Away )'."\r\n";
                if($data[3] == 9)
                    $alertText = $alertText.'['.$data[0].'] '.$data[1].'% ( >= '.$data[2].')'."\r\n";
                if($data[3] == 10)
                    $alertText = $alertText.'['.$data[0].'] '.$data[1].'% ( <= '.$data[2].')'."\r\n";
                if($data[3] == 11)
                    $alertText = $alertText.'['.$data[0].'] '.$data[1].' ( >= '.$data[2].')'."\r\n";
            }
            else
            {
                if($data[3] == 1)
                    $statusText = $statusText.'['.$data[0].'] Step1 :'.$data[1].'% ( > '.$data[2].')'."\r\n";
                if($data[3] == 2)
                    $statusText = $statusText.'['.$data[0].'] Step2 :'.$data[1].'% ( > '.$data[2].')'."\r\n";
                if($data[3] == 3 || $data[3] == 4)
                    $statusText = $statusText.'['.$data[0].'] '.$data[1].'% ( > '.$data[2].')'."\r\n";
                if($data[3] == 5 || $data[3] == 6)
                    $statusText = $statusText.'['.$data[0].'] '.$data[1].'% ( < '.$data[2].')'."\r\n";
                // if($data[3] == 7 || $data[3] == 8)
                //   $statusText = $statusText.'['.$data[0].'] '.$data[1].' ( < '.$data[2].' Away )'."\r\n";
                if($data[3] == 9)
                    $statusText = $statusText.'['.$data[0].'] '.$data[1].'% ( < '.$data[2].')'."\r\n";
                if($data[3] == 10)
                    $statusText = $statusText.'['.$data[0].'] '.$data[1].'% ( > '.$data[2].')'."\r\n";
            }

        }
        $text = $title."\r\n\r\n".$dateText;
        if($alertText != '')
            $text = $text.$alertText;
        if($statusText != '')
            $text = $text."\r\n\r\n".$statusText;

        $botTextLimit = 4096;
        $texts = str_split($text, $botTextLimit);
        return $texts;
    }
    private function generateHtmlForSubAffiliateAlert($belowData, $overData, $fromDate, $toDate, $day)
    {

        $dateText = '';
        $belowText = '';
        $overText = '';

        if($fromDate == '' || $toDate == '')
            return '';
        else {
            if ($day == 1) {
                $dateText ='Date Range : '.$fromDate."<br/><br/>";
            } else {
                $dateText ='Date Range : '.$fromDate.' ~ '.$toDate."<br/><br/>";
            }
        }

        // body
        foreach ($belowData as $data) {
            $crmName = $data['crm_name'];
            $level = $data['level'];
            $crmDetails = $data['detail'];
            $crmValue = $data['value'];

            $belowText = $belowText.$crmName."<br/>";
            $belowText = $belowText.'Current Value :'.$crmValue.' <== (Level:'.$level.')'."<br/><br/>";

            foreach ($crmDetails as $crmDetail) {
                $campaignId = $crmDetail[0];
                $campaignValue = $crmDetail[1];
                $campaignDetails = $crmDetail[2];
                if($campaignDetails != array()) {
                    foreach ($campaignDetails as $affiliateDetail) {
                        $affiliateId = $affiliateDetail[0];
                        $affiliateValue = $affiliateDetail[1];
                        $affiliateDetails = $affiliateDetail[2];
                        if($affiliateDetails != array()) {
                            foreach ($affiliateDetails as $subAffiliateDetail) {
                                $subAffiliateId = $subAffiliateDetail[0];
                                $subAffiliateValue = $subAffiliateDetail[1];

                                $belowText = $belowText.'AFID '.$affiliateId.' SID '.$subAffiliateId."<br/>";
                                $belowText = $belowText.'CampaignID '.$campaignId."<br/><br/>";
                                $belowText = $belowText.'Current Value :'.$subAffiliateValue.' <== (Level:'.$level.')'."<br/><br/>";
                            }
                        }
                        $belowText = $belowText.'AFID '.$affiliateId."<br/>";
                        $belowText = $belowText.'CampaignID '.$campaignId."<br/>";
                        $belowText = $belowText.'Current Value :'.$affiliateValue.' <== (Level:'.$level.')'."<br/><br/>";
                    }
                }
                $belowText = $belowText.'CampaignID '.$campaignId."<br/>";
                $belowText = $belowText.'Current Value :'.$campaignValue.' <== (Level:'.$level.')'."<br/><br/>";
            }

        }
        foreach ($overData as $data)
        {
            $crmName = $data['crm_name'];
            $level = $data['alert_level'];
            $crmValue = $data['value'];

            $overText = $overText.$crmName."<br/>";
            $overText = $overText.'Current Value :'.$crmValue.' > (Level:'.$level.')'."<br/><br/>";
        }

        $text = $dateText;
        $text = "<html><body>".$text.$belowText.$overText."</body></html>";

        return $text;
    }
    private function dataToHtml($content, $type)
    {
        $alertText = '';
        $statusText = '';
        if($type == 12)
        {
            $i = 1;
            foreach ($content as $crm)
            {
                $alertText = $alertText.$i.$crm[1]."<br />";
                $i ++;
            }
            return "<html><body>".$alertText."</body></html>";
        }
        if($type == 13)
        {
            $i = 1;
            foreach ($content as $crm)
            {
                $crmName = $crm[1];
                $step1 = $crm[2];
                $goal = $crm[7];

                if($goal == 0)
                    $rate = 0;
                else
                    $rate = ($step1 / $goal) * 100;

                $rate = floor($rate);
                $alertText = $alertText.$i.'. '.$crmName.' ['.$step1.' / '.$goal.'] ['.$rate.'%]'."<br />";
                $i++;
            }
            $text = "<html><body>".$alertText."</body></html>";
            return $text;
        }

        // date
        if($content['fromDate'] == '' || $content['toDate'] == '')
            return '';
        else
            $dateText ='Date Range : '.$content['fromDate'].' ~ '.$content['toDate']."<br /><br />";

        if($type == 3 || $type == 5)
            $dateText = 'Today Alert'."<br />".$dateText;
        if($type == 4 || $type == 6)
            $dateText = 'Week to Date Alert'."<br />".$dateText;

        // body
        foreach ($content['status'] as $data)
        {
            if($data[4] == 1)
            {
                if($data[3] == 1)
                    $alertText = $alertText.'['.$data[0].'] Step1 :'.$data[1].'% ( <= '.$data[2].')'."<br />";
                if($data[3] == 2)
                    $alertText = $alertText.'['.$data[0].'] Step2 :'.$data[1].'% ( <= '.$data[2].')'."<br />";
                if($data[3] == 3 || $data[3] == 4)
                    $alertText = $alertText.'['.$data[0].'] '.$data[1].'% ( <= '.$data[2].')'."<br />";
                if($data[3] == 5 || $data[3] == 6)
                    $alertText = $alertText.'['.$data[0].'] '.$data[1].'% ( >= '.$data[2].')'."<br />";
                if($data[3] == 7 || $data[3] == 8)
                    $alertText = $alertText.'['.$data[0].'] '.$data[1].' ( >= '.$data[2].' Away )'."<br />";
                if($data[3] == 9)
                    $alertText = $alertText.'['.$data[0].'] '.$data[1].'% ( >= '.$data[2].')'."<br />";
                if($data[3] == 10)
                    $alertText = $alertText.'['.$data[0].'] '.$data[1].'% ( <= '.$data[2].')'."<br />";
                if($data[3] == 11)
                    $alertText = $alertText.'['.$data[0].'] '.$data[1].' ( >= '.$data[2].')'."<br />";
            }
            else
            {
                if($data[3] == 1)
                    $statusText = $statusText.'['.$data[0].'] Step1 :'.$data[1].'% ( > '.$data[2].')'."<br />";
                if($data[3] == 2)
                    $statusText = $statusText.'['.$data[0].'] Step2 :'.$data[1].'% ( > '.$data[2].')'."<br />";
                if($data[3] == 3 || $data[3] == 4)
                    $statusText = $statusText.'['.$data[0].'] '.$data[1].'% ( > '.$data[2].')'."<br />";
                if($data[3] == 5 || $data[3] == 6)
                    $statusText = $statusText.'['.$data[0].'] '.$data[1].'% ( < '.$data[2].')'."<br />";
                // if($data[3] == 7 || $data[3] == 8)
                //   $statusText = $statusText.'['.$data[0].'] '.$data[1].' ( < '.$data[2].' Away )'."<br />";
                if($data[3] == 9)
                    $statusText = $statusText.'['.$data[0].'] '.$data[1].'% ( < '.$data[2].')'."<br />";
                if($data[3] == 10)
                    $statusText = $statusText.'['.$data[0].'] '.$data[1].'% ( > '.$data[2].')'."<br />";
            }

        }
        $text = $dateText;
        if($alertText != '')
            $text = $text.$alertText;
        if($statusText != '')
            $text = $text."<br /><br />".$statusText;

        return "<html><body>".$text."</body></html>";
    }
    public function checkRebillReportStep1($methods)
    {
        $alertData = $this->getAlertDataByType(1);
        if($alertData != array())
            $this->sendAlertsByCrmPermissionOfAccount($methods[0], $methods[1], $methods[2], $alertData, 1, $this->__get('subDomain'));
//            $this->sendAlerts($methods, $alertData, 1);
        // print_r($alertData);
    }
    public function checkRebillReportStep2($methods)
    {
        $alertData = $this->getAlertDataByType(2);
        if($alertData != array())
            $this->sendAlertsByCrmPermissionOfAccount($methods[0], $methods[1], $methods[2], $alertData, 2, $this->__get('subDomain'));
//            $this->sendAlerts($methods, $alertData, 2);
        // print_r($alertData);
    }
    public function checkInitialApprovalToday($methods)
    {
        $alertData = $this->getInitialApprovalAlertData(1);
        if($alertData != array()) {
            // print_r($alertData);
            $belowData = $alertData[0];
            $overData = $alertData[1];
            $fromDate = $alertData[2];
            $toDate = $alertData[3];

//            $this->sendDetailedInitialAlert($methods, $belowData, $overData, $fromDate, $toDate, 1);
            $this->sendDetailedInitialAlertByCrmPermissionOfAccount($methods, $belowData, $overData, $fromDate, $toDate, 1);
        }
    }
    public function checkInitialApprovalWeek($methods)
    {
        $alertData = $this->getInitialApprovalAlertData(0);
        if($alertData != array()) {
            $belowData = $alertData[0];
            $overData = $alertData[1];
            $fromDate = $alertData[2];
            $toDate = $alertData[3];
//            $this->sendDetailedInitialAlert($methods, $belowData, $overData, $fromDate, $toDate, 0);
            $this->sendDetailedInitialAlertByCrmPermissionOfAccount($methods, $belowData, $overData, $fromDate, $toDate, 1);
        }
    }
    public function checkDeclinePercentageToday($methods)
    {
        $alertData = $this->getAlertDataByType(5);
        if($alertData != array())
            $this->sendAlertsByCrmPermissionOfAccount($methods[0], $methods[1], $methods[2], $alertData, 5, $this->__get('subDomain'));
//            $this->sendAlerts($methods, $alertData, 5);
        // print_r($alertData);
    }
    public function checkDeclinePercentageWeek($methods)
    {
        $alertData = $this->getAlertDataByType(6);
        if($alertData != array())
            $this->sendAlertsByCrmPermissionOfAccount($methods[0], $methods[1], $methods[2], $alertData, 6, $this->__get('subDomain'));
//            $this->sendAlerts($methods, $alertData, 6);
        // print_r($alertData);
    }
    public function check100Step1SalesAway($methods)
    {
        $alertData = $this->getAlertDataByType(7);
        if($alertData != array())
            $this->sendAlertsByCrmPermissionOfAccount($methods[0], $methods[1], $methods[2], $alertData, 7, $this->__get('subDomain'));
//            $this->sendAlerts($methods, $alertData, 7);
        // print_r($alertData);
    }
    public function check100Step1SalesAwayWithData($alertData, $enableSms, $enableEmail, $enableTelegramBot, $subDomain, $crmType = 'limelight')
    {
        print_r(array($alertData, $subDomain, $enableSms, $enableEmail, $enableTelegramBot));
//      return;
        if($alertData != array())
        {
            if ($crmType == 'konnektive')
                $this->sendKKCrmAlerts($enableSms, $enableEmail, $enableTelegramBot, $alertData, 7, $subDomain);
            else
                $this->sendAlertsByCrmPermissionOfAccount($enableSms, $enableEmail, $enableTelegramBot, $alertData, 7, $subDomain);
        }
    }
    public function check30Step1SalesAway($methods)
    {
        $alertData = $this->getAlertDataByType(8);
        if($alertData != array())
            $this->sendAlertsByCrmPermissionOfAccount($methods[0], $methods[1], $methods[2], $alertData, 8, $this->__get('subDomain'));
//            $this->sendAlerts($methods, $alertData, 8);
        // print_r($alertData);
    }
    public function check30Step1SalesAwayWithData($alertData, $enableSms, $enableEmail, $enableTelegramBot, $subDomain, $crmType = 'limelight')
    {
        print_r(array($alertData, $subDomain, $enableSms, $enableEmail, $enableTelegramBot));
//      return;
        if($alertData != array())
        {
            if ($crmType == 'konnektive')
                $this->sendKKCrmAlerts($enableSms, $enableEmail, $enableTelegramBot, $alertData, 8, $subDomain);
            else
                $this->sendAlertsByCrmPermissionOfAccount($enableSms, $enableEmail, $enableTelegramBot, $alertData, 8, $subDomain);
        }


    }
    public function checkStep1SalesGoalOverWithData($alertData, $enableSms, $enableEmail, $enableTelegramBot, $subDomain, $crmType = 'limelight')
    {
        print_r(array($alertData, $subDomain, $enableSms, $enableEmail, $enableTelegramBot));
//      return;
        if($alertData != array())
        {
            if ($crmType == 'konnektive')
                $this->sendKKCrmAlerts($enableSms, $enableEmail, $enableTelegramBot, $alertData, 11, $subDomain);
            else
                $this->sendAlertsByCrmPermissionOfAccount($enableSms, $enableEmail, $enableTelegramBot, $alertData, 11, $subDomain);

        }

    }
    public function checkStep1SalesGoalOver($methods)
    {
        $alertData = $this->getAlertDataByType(11);
        if($alertData != array())
            $this->sendAlertsByCrmPermissionOfAccount($methods[0], $methods[1], $methods[2], $alertData, 11, $this->__get('subDomain'));
//            $this->sendAlerts($methods, $alertData, 11);
        // print_r($alertData);
    }
    public function checkTakeRate($methods)
    {
        $alertData = $this->getAlertDataByType(9);
        if($alertData != array())
            $this->sendAlertsByCrmPermissionOfAccount($methods[0], $methods[1], $methods[2], $alertData, 9, $this->__get('subDomain'));
//            $this->sendAlerts($methods, $alertData, 9);
        // print_r($alertData);
    }
    public function checkTabletTakeRate($methods)
    {
        $alertData = $this->getAlertDataByType(10);
        if($alertData != array())
            $this->sendAlertsByCrmPermissionOfAccount($methods[0], $methods[1], $methods[2], $alertData, 10, $this->__get('subDomain'));
//            $this->sendAlerts($methods, $alertData, 10);
        // print_r($alertData);
    }
    public function checkStep1Goal($methods)
    {
        $dbApi = DBApi::getInstance();
        $dbApi->setSubDomain($this->__get('subDomain'));
        $alertData = $dbApi->getDashboardData();
        // update alert_status
        $from = date('Y-m-d');
        $to = date('Y-m-d');
        $timestamp = date('Y-m-d H:i:s');
        foreach ($alertData as $data) {
            $crmId = $data[0];
            $value = $data[2];
            $goal = $data[7];
            $status = ($value >= $goal) ? 1:0;
            $dbApi->updateAlertStatus($crmId, 13, $value, $goal, $status, $from, $to, $timestamp);
        }

        if($alertData != array())
            $this->sendAlertsByCrmPermissionOfAccount($methods[0], $methods[1], $methods[2], $alertData, 13, $this->__get('subDomain'));
//            $this->sendAlerts($methods, $alertData, 13);
    }
    public function checkAlertsForAllSubDomain($day, $hour)
    {
        $dbApi = DBApi::getInstance();
        $subDomains = $dbApi->getAllSubDomain();
        foreach ($subDomains as $item) {
            $name = $item[1];
            $dbApi->setSubDomain($name);
            $this->__set('subDomain', $name);
            $types = array(1, 2, 3, 4, 5, 6, 9, 10, 13);

            foreach ($types as $type)
            {
                $setting = $dbApi->getAlertTypeByType($type);
                $days = $setting[5];
                $hours = $setting[6];

                $days = explode(',', $days);
                $hours = explode(',', $hours);

                if(in_array($day, $days))
                {
                    if(in_array($hour, $hours))
                    {
                        $methods = array();
                        if($setting[7] == 1)
                            $methods[] = 1;
                        else
                            $methods[] = 0;
                        if($setting[8] == 1)
                            $methods[] = 1;
                        else
                            $methods[] = 0;
                        if($setting[9] == 1)
                            $methods[] = 1;
                        else
                            $methods[] = 0;

                        if($type == 1)
                            $this->checkRebillReportStep1($methods);
                        if($type == 2)
                            $this->checkRebillReportStep2($methods);
                        if($type == 3)
                            $this->checkInitialApprovalToday($methods);
                        if($type == 4)
                            $this->checkInitialApprovalWeek($methods);
                        if($type == 5)
                            $this->checkDeclinePercentageToday($methods);
                        if($type == 6)
                            $this->checkDeclinePercentageWeek($methods);
                        if($type == 9)
                            $this->checkTakeRate($methods);
                        if($type == 10)
                            $this->checkTabletTakeRate($methods);
                        if($type == 13)
                            $this->checkStep1Goal($methods);
                    }
                }
            }
        }


    }
    public function checkAlerts($day, $hour)
    {
        $dbApi = DBApi::getInstance();
        $types = array(1, 2, 3, 4, 5, 6, 9, 10, 13);

        foreach ($types as $type)
        {
            $setting = $dbApi->getAlertTypeByType($type);
            $days = $setting[5];
            $hours = $setting[6];

            $days = explode(',', $days);
            $hours = explode(',', $hours);

            if(in_array($day, $days))
            {
                if(in_array($hour, $hours))
                {
                    $methods = array();
                    if($setting[9] == 1)
                        $methods[] = 2;
                    if($setting[8] == 1)
                        $methods[1] = 1;
                    if($setting[7] == 1)
                        $methods[] = 0;

                    if($type == 1)
                        $this->checkRebillReportStep1($methods);
                    if($type == 2)
                        $this->checkRebillReportStep2($methods);
                    if($type == 3)
                        $this->checkInitialApprovalToday($methods);
                    if($type == 4)
                        $this->checkInitialApprovalWeek($methods);
                    if($type == 5)
                        $this->checkDeclinePercentageToday($methods);
                    if($type == 6)
                        $this->checkDeclinePercentageWeek($methods);
                    if($type == 9)
                        $this->checkTakeRate($methods);
                    if($type == 10)
                        $this->checkTabletTakeRate($methods);
                    if($type == 13)
                        $this->checkStep1Goal($methods);
                }
            }
        }


    }
}

?>