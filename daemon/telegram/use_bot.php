<?php

require_once './TelegramBot.php';

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

$bot = new TelegramBot();
//$bot->sendMessage('Alert Message');
$bot->sendMessageByID('Alert Message', '558667560');

