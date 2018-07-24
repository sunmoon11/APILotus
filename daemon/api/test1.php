<?php
	require_once 'LLCrmApi.php';
	require_once 'DBApi.php';
	require_once 'AlertMethodApi.php';
	require_once 'SignupApi.php';

	
//    session_start();
//    echo session_cache_expire();
//    return;
//	 $signupApi = SignupApi::getInstance();
//	 $ret = $signupApi->sendVerifyMailTo("wangwang_a@outlook.com", "1234");
//	 if ($ret) {
//	 	echo "Sent";
//	 } else {
//	 	echo "Failed";
//	 }
//	 return;

	// $apiUrl = 'https://radcrm.limelightcrm.com/admin/';
	// $userName = 'dev2api';
	// $password = '6HBbZBVvRF4YKJ';
		
	// $apiClient = LLCrmApi::getInstanceWithCredentials($apiUrl, $userName, $password);
	// $ret = $apiClient->getCampaigns(33,array(), 1, 10);
	// print_r($ret);
	// return;
	/*
	$ret = $apiClient->validateAPICredentials();

	if( $ret === TRUE ) {

		echo 'valid';

	} else {

		echo 'invalid';

	}
	*/
	
	//$campaigns = array('600', '1');
	
	//$ret = $apiClient->getCampaigns(array(), 1, 10);
	//$ret = $apiClient->getLabelsOfCampaigns(array('382'));	
	//$ret = $apiClient->getAllCampaign();
	// print_r($ret);	


	// $id = $dbApi->checkIfSubdomainRegistered('test');
	// $id = $dbApi->checkIfUserIdRegistered('test');
	// $code = $dbApi->getVerifyCodeOfUser('test');
	// $verified = $dbApi->checkIfUserVerified('test');
//	$verified = $dbApi->checkIfEmailRegistered('a@c.com');
	// $info = $dbApi->getUserInfoByUserId('developer');
//	 $ret = $dbApi->addNewUser('kimch', 'kimch@mail.com', '111', '2222');
//	$ret = $dbApi->updateUserInfo('kimch', 'ch', 'kim', 'kimch', 'user', '1213', '23423');
	// print_r($info);
	// echo $verified ? 'verified':'not verified';
	//$ret = $dbApi->getAffiliateSumPerCrm(10, 18);
	// print_r($ret);
//    $user = $dbApi->signIn('user', 'user', 'primary');
//    print_r($user);
//    $dbApi->createTablesForSubdomain('ki');
$dbApi = new DBApi();
$dbApi->setSubDomain('primary');
$step1 = $dbApi->getSTEP1CampaignIds(75);
print_r($step1);
return;


//$dbApi->dropAllTablesOfSubdomain('demo');
$dbApi->createAllTablesForSubdomain('demo');
//$dbApi->setSubDomain('primary');
//$result = $dbApi->getMonitorStatusByUserId(21);
//print_r($result);
return;
$ret = $dbApi->updateAdminAccountPassword('admin', 'adminadmin');
//$ret = $dbApi->checkSubDomainBlock('primary');
echo $ret;
return;
$ret = $dbApi->updateAdminAccountBlock(1, 0);
return;
//$ret = $dbApi->updateProfilePassword('admin', 'adminadmin');
//$ret = $dbApi->getAllCustomers();

print_r($ret);
return;
$dbApi->dropAllTablesOfSubdomain('rijb');
return;
$domain = $dbApi->getSubDomainByBotID("-100107733665");
echo $domain;
return;
//$dbApi->setSubDomain("primary");
//$dbApi->updateDashboardShowColumns(1, "3,4");
$showItems = $dbApi->getDashboardShowColumns(1);
if ($showItems != '')
    echo 'success';
else
    echo 'empty';
return;
echo  $showItems;
//$ret = $dbApi->getTabletCampaign(33);

return;
//$ret = $dbApi->setBotByUserID(17, 1122, 'primary');
////$ret = $dbApi->checkUserID(0, 'primary');
//echo $ret == true ? 'yes':'no';
//return;

$dbApi->setSubDomain("primary");
$dbApi->insertBotHistory("11", "22", "33", "44", "55", "66", "77");
$dbApi->insertBotHistory("111", "222", "333", "44", "555", "666", "777");
$ret = $dbApi->getBotHistory();
//$ret = $dbApi->validateAdminPanelAccount("admin", "adminadmin");
print_r($ret);
return;
//    $dbApi->setSubDomain("primary");
//    $ret = $dbApi->getLabelsAndGoalsByCrmAndType(33, 3);
    // Initialize
    $breakDownStep1 = array();
    foreach ($ret as $item)
    {
        $id = $item[0];
        $breakDownStep1[$id] = 0;
    }
    print_r($breakDownStep1);
    return;
    $dbApi->updateLabelGoal(33, 3, 100);
    $dbApi->updateLabelGoal(33, 3, 200);
    $dbApi->updateLabelGoal(33, 4, 100);
    return;
//    $dbApi->dropAllTablesOfSubdomain('rijb');
//    $dbApi->setUserVerified('admin');
//    $dbApi->addCardInfo('test', '2432', '23', '2245', '24542');
//    $dbApi->updateCardInfo('test', '2431', '22', '2244', '24541');
    $dbApi = DBApi::getInstance();
    function myFunc() {
        global $dbApi;
        $dbApi->setSubDomain("primary");
        //
        $ret = $dbApi->getPrepaidCampaignIds(33);
        print_r($ret);
        return;

        $dbApi->setSubDomain('wang');
        $dbApi->changeAlertSchedule(1,"1", "1", 1, 1, 1);
//        $setting = $dbApi->getAlertTypeByType(12);
//        if ($setting[10] == null)
//            echo "10 is NULL";
//        print_r($setting);

//        $a = array(1);
//        foreach ($a as $item){
//            echo $item.': array';
//        }
//        $a = null;
//        foreach ($a as $item){
//            echo $item.': null';
//        }
    }

    myFunc();
    return;
	//$campaigns = $dbApi->getCampaignsByLabelName('Step1', '1');
	//print_r($campaigns);
	//$ret = $dbApi->getLabelNameByCampaignId('385', '1');
	//$ret = $dbApi->getLabelIdByName('Step3');
	//$ret = $dbApi->getCampaignsByLabelName('Step1', '1');
	//print_r($ret);
	
	// $ret = $dbApi->addCrm('a', 'a','a','a','a','cc','2000', 1);
	//$ret = $dbApi->updateCrm('19','a', 'a','a','a','a','cc','2000');
	//$ret = $dbApi->updateLabelsOfCampaignsInLabeling(33, '385,384,383', '1,1');
	//$ret = $dbApi->updateUser(10,'aa', '123', 'aa', 1, 'admin');
	// $ret = $dbApi->getSTEP1CampaignIds(33);
	// $ret = $dbApi->getAffiliate(1, 2);
	// for ($i = 4;$i <= 100; $i++)
	// {
	// 	$ret = $dbApi->addAffiliate($i, '', 0);	
	// }
	
	// $ret = $dbApi->updateAffiliates(array(20), '', 0);
	// echo $ret;
	// print_r($ret);
	//$ret = $dbApi->addAffiliate(2, 'BG Media Inc', array(33,37), array(100,200));	
	//$ret = $dbApi->getAffiliateSumPerCrm(3, 'BLANK');
	// $ret = $dbApi->getPermissionList(14);
	// $ret = $dbApi->getAllCrmByAccountId(1);
	// $ret = $dbApi->setPermissionList(1, '20, 1');
	// $ret = $dbApi->getAffiliatesByCrmId(33);
	// $ret = $dbApi->getAllSTEP2CampaignIds();
	// $ret = $dbApi->addBlockedIp("192.168.1.111", "Test1");
	// $ret = $dbApi->addBlockedIp("192.168.1.112", "Test2");
	// $ret = $dbApi->editBlockedIp(1, "192.168.0.111", "TEST3");
	// $ret = $dbApi->deleteBlockedIp(2);
	// $ret = $dbApi->getBlockedIpList();
	// $ret = $dbApi->checkClientIp();
	// $ret = $dbApi->changeAlertLevel(9, 100, 40, "Rebill Report alert (Step3)", "BBB", "CCC", 34, 2);
	// $ret = $dbApi->getAlertLevelList(33);
	// $ret = $dbApi->getAlertHistory(33, '03/29/2017', '03/30/2017', 1, 10);
	// $ret = $dbApi->addAlertReceiver(0, "jalin@outlook.com", 0);
	// $ret = $dbApi->deleteAlertReceiver(5);
	// $ret = $dbApi->changeAlertReceiver(2, 1, "jialin1982@outlook.com", 1);
	// $ret = $dbApi->getAlertReceiverList();
	// echo $ret;
	// print_r($ret) ;

	$alertMethodApi = AlertMethodApi::getInstance();
	$dbApi = DBApi::getInstance();
	// $emails = array('wangwei1029k@hotmail.com', 'jialin1982@outlook.com');
	// $ret = $alertMethodApi->sendEmail('wangdawei1029k@hotmail.com', $emails, 'Test Email', 'Aws ses test. \r\n Aws ses test');
	$message = 'abc 0x0d0x0a cba';
	$receivers = $dbApi->getAlertReceiverList();
	$emails = array();
	$phones = array();
	foreach ($receivers as $value) 
	{
		if($value[1] == 1 && $value[3] == 1)
		{
			// enabled email address
			$emails[] = $value[2];
		}
		if($value[1] == 0 && $value[3] == 1)
		{
			$phones[] = $value[2];
		}
	}
	if($phones != array())
	{
		print_r($phones);
		$ret = $alertMethodApi->sendSMS(array('8618744397720'), $message);
		print_r($ret);
	}	
?>