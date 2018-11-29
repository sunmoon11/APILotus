<?php

require_once '../api/DBApi.php';

 
class TelegramBot
{
    private $baseUrl = 'https://api.telegram.org/bot';

    private $chatIDList = array();


    /**
     * @brief
     *
     * @param bool $bot_method
     */
    public function __construct($bot_method=true)
    {
        if ($bot_method)
            $tokenKey = '675022460:AAHB6q5tqZdPd0cyXxVzE-XBm_IolohXYm0';
        else
            $tokenKey = '796563407:AAENWxTWobpEy-2bpkCub_kASYmf6AQVmeo';
        $this->baseUrl = $this->baseUrl.$tokenKey;
    } 


    /**
     * @brief
     *
     **/
    private function GetCurl($url, $data=array()) 
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }
      

    /**
     * @brief   
     *
     **/
    public function getData($offsetId) 
    {
        $url =  $this->baseUrl.'/getUpdates?limit=100&offset='.$offsetId; //?limit=1
        $return = $this->GetCurl($url);
 
        $returnData = array();
        foreach($return['result'] as $key=>$value) 
        {
            //$Room_id[] = $value['message']['chat']['id'];
            $returnData[$key]['updateId']             = $value['update_id'];
            $returnData[$key]['messageText']          = $value['message']['text'];
            $returnData[$key]['messageDate']          = $value['message']['date'];
            $returnData[$key]['messageFromId']        = $value['message']['from']['id'];
            $returnData[$key]['messageFromUsername']  = $value['message']['from']['username'];
            $returnData[$key]['messageChatId']        = $value['message']['chat']['id'];
            $returnData[$key]['messageChatUsername']  = $value['message']['chat']['username'];
             
            // if chat room
            if( empty($returnData[$key]['messageChatUsername']) == true) {
                $returnData[$key]['messageChatUsername'] = $value['message']['chat']['title'];
            }
        }
 
        return $returnData;
    }
     

    /**
     * @brief   
     *
     **/
    public function sendMessage($msg='') 
    {
        $chatIDList = $this->getChatIDList();

        for ($i = 0; $i < sizeof($chatIDList); $i ++)
        {
            if (empty($msg) == false && empty($chatIDList[$i]) == false) 
            {
                $urlSum = '';
                $urlSum .= $this->baseUrl.'/sendMessage?chat_id=';
                $urlSum .= $chatIDList[$i];
                $urlSum .= '&text='.urlencode($msg);

                $sendStatus = $this->GetCurl($urlSum);
            }
        }
    }

    public function sendPureMessageByID($msg='', $userID)
    {
        $url = $this->baseUrl.'/sendMessage?chat_id=';
        $url .= $userID;
        $url .= '&text='.urlencode($msg);

        $sendStatus = $this->GetCurl($url);
    }

    public function sendChatAction($action='', $userID)
    {
        $url = $this->baseUrl.'/sendChatAction?chat_id=';
        $url .= $userID;
        $url .= '&action='.$action;

        $sendStatus = $this->GetCurl($url);
    }

    public function sendRegisterMessage($msg='', $userID)
    {
        $url = $this->baseUrl.'/sendMessage?chat_id=';
        $url .= $userID;
        $url .= '&text='.urlencode($msg);

        $keyboard = array(array('/check_this_chat_info'));
        $resp = array('keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true);
        $reply = json_encode($resp);

        $url .= '&reply_markup='.$reply;

        $sendStatus = $this->GetCurl($url);
    }

    public function sendNormalMessage($msg='', $userID)
    {
        $url = $this->baseUrl.'/sendMessage?chat_id=';
        $url .= $userID;
        $url .= '&text='.urlencode($msg);

        $keyboard = array(
            array('/dashboard_takerate', '/dashboard_tablet', '/dashboard_goal'),
            array('/alert_step1_rebill_report', '/alert_step2_rebill_report'),
            array('/alert_initial_approval_day', '/alert_initial_approval_week'),
            array('/alert_decline_percentage_day', '/alert_decline_percentage_week'),
            array('/alert_100step1_sales', '/alert_30step1_sales', '/alert_take_rate', '/alert_table_take_rate'),
            array('/alert_step1_crm_capped', '/alert_password_validdays'),
        );
        $resp = array('keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true);
        $reply = json_encode($resp);

        $url .= '&reply_markup='.$reply;

        $sendStatus = $this->GetCurl($url);
    }

    public function sendAffiliateRegisterMessage($msg='', $userID)
    {
        $url = $this->baseUrl.'/sendMessage?chat_id=';
        $url .= $userID;
        $url .= '&text='.urlencode($msg);

        $keyboard = array(array('/activate'));
        $resp = array('keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true);
        $reply = json_encode($resp);

        $url .= '&reply_markup='.$reply;

        $sendStatus = $this->GetCurl($url);
    }

    public function sendAffiliateNormalMessage($msg='', $userID)
    {
        $url = $this->baseUrl.'/sendMessage?chat_id=';
        $url .= $userID;
        $url .= '&text='.urlencode($msg);

        $keyboard = array(
            array('/capupdate', '/capped', '/capupdateid', '/deactivate'),
        );
        $resp = array('keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true);
        $reply = json_encode($resp);

        $url .= '&reply_markup='.$reply;

        $sendStatus = $this->GetCurl($url);
    }

    public function sendMessageByID($msg='', $userID)
    {
        $url = $this->baseUrl.'/sendMessage?chat_id=';
        $url .= $userID;
        $url .= '&text='.urlencode($msg);

        // add custom keyboard
        /*
        $keyboard = array(array('A', 'B', 'C'));
        $resp = array('keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true);
        $reply = json_encode($resp);
        */

        // remove custom keyboard
        /*
        $resp = array('remove_keyboard' => true);
        $reply = json_encode($resp);

        $url .= '&reply_markup='.$reply;
        */

        $sendStatus = $this->GetCurl($url);
    }

    public function sendInlineMessageByID($msg='', $userID)
    {
        $url = $this->baseUrl.'/sendMessage?chat_id=';
        $url .= $userID;
        $url .= '&text='.urlencode($msg);

        /*
        $keyboard = array(array('text' => 'A', 'callback_data' => 'test'));
        $resp = array('inline_keyboard' => $keyboard);
        $reply = json_encode($resp, true);
        */

        $keyboard = [
            'inline_keyboard' => [['text' =>  'test', 'callback_data' => 'test_callback']],
        ];
        $reply = json_encode($keyboard, true);
       

        $url .= '&reply_markup='.$reply;

        $sendStatus = $this->GetCurl($url);
    }

    public function getChatIDList()
    {
        $dbApi = DBApi::getInstance();
        return $dbApi->getTelegramChatIDList();
    }
}

/*
$tg = new telegram();
$returnData = $tg->getData($offsetId);
 
foreach($returnData as $key=>$value) {
        //echo "V offset ID         : ".$offsetId.Chr(10);
        //echo "V updateId      : ".$value['updateId'].Chr(10);
        //echo "V message Text: ".$value['messageText'].Chr(10);
        //echo "V message Date       : ".$value['messageDate'].Chr(10);
        //echo "V message From Id      : ".$value['messageFromId'].Chr(10);
        //echo "V message From Username     : ".$value['messageFromUsername'].Chr(10);
        //echo "V message Chat Id      : ".$value['messageChatId'].Chr(10);
        //echo "V message Chat Username      : ".$value['messageChatUsername'].Chr(10);
}

$tg = new telegram();
$returnBool = $tg->setData($value['sendText'], chatID);
*/