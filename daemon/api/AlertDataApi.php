<?php
require_once 'RollingCurl.php';
require_once 'LLCrmHook.php';
require_once 'KKCrmHook.php';
class AlertDataApi {
    protected static $instance;
    private $subDomain = '';

    public static function getInstance() {

        if( is_null( static::$instance ) ) {

            static::$instance = new AlertDataApi();

        }

        return static::$instance;
    }
    public function __construct() {

    }

    private function __clone() {

    }

    private function __wakeup() {

    }
    public function setSubDomain($name){
        $this->subDomain = $name;
    }
    public function getSubDomain(){
        return $this->subDomain;
    }

    public function getPrespectAlertDataByCrm($crmID, $crmUrl, $userName, $password, $fromDate, $toDate)
    {
        $llcrmHook = new LLCrmHook();

        if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
        {
            $ret = $llcrmHook->getCrmSalesProgress($token, $fromDate, $toDate, $crmID);
            return $ret;
        }
        else
            return array();
    }
    public function getSTEP1ApprovalRateByCrm($crmId, $day)
    {
        $dbApi = DBApi::getInstance();

        $campaignIdsSTEP1 = $dbApi->getSTEP1CampaignIds($crmId);

        $approvalRateOfSTEP1 = 0;
        $netApproved = 0;
        $grossOrders = 0;



    }
    public function getDeclineAlertDataByCrm($crmID, $crmUrl, $userName, $password, $fromDate, $toDate, $cycle, $subDomain)
    {
        $cycle = 1;

        $llcrmHook = new LLCrmHook();
        $dbApi = DBApi::getInstance();
        $dbApi->setSubDomain($subDomain);
        $approvedStep1Rate = 0;
        $declineRate = 0;
        if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
        {
            $response = $llcrmHook->getRetentionReport($token, $fromDate, $toDate, $cycle);
            $result = $llcrmHook->parseRetentionByCampaign($response);

            // get campaign ids for STEP1
            $campaignIdsSTEP1 = $dbApi->getSTEP1CampaignIds($crmID);

            // get approval rate
            $netApproved = 0;
            $grossOrders = 0;

            foreach ($result['report'] as $data)
            {
                if(in_array($data[0] , $campaignIdsSTEP1))
                {
                    $grossOrders += $data[2];
                    $netApproved += $data[3];
                }
            }
            if($grossOrders == 0)
                $approvedStep1Rate = 0;
            else
                $approvedStep1Rate = ($netApproved / $grossOrders) * 100;

            // decline percentage
            $declined = 0;
            $grossOrders = 0;

            foreach ($result['report'] as $data)
            {
                if($data[0] != -1)
                {
                    $grossOrders += $data[2];
                    $declined += $data[9];
                }
            }

            if($grossOrders == 0)
                $declineRate = 0;
            else
                $declineRate = ($declined / $grossOrders) * 100;
        }
        return array($approvedStep1Rate, $declineRate);
    }

    public function getRebillAlertDataByCrm($crmID, $crmUrl, $userName, $password, $fromDate, $toDate, $cycle, $subDomain)
    {
        $cycle = 2;

        $llcrmHook = new LLCrmHook();
        $dbApi = DBApi::getInstance();
        $dbApi->setSubDomain($subDomain);
        $conversionStep1 = 0;
        $conversionStep2 = 0;
        if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
        {
            $response = $llcrmHook->getRetentionReport($token, $fromDate, $toDate, $cycle);
            $result = $llcrmHook->parseRetentionByCampaign($response);

            // get campaign ids for STEP1
            $campaignIdsSTEP1 = $dbApi->getSTEP1CampaignIds($crmID);
            // get campaign ids for STEP2
            $campaignIdsSTEP2 = $dbApi->getSTEP2CampaignIds($crmID);

            $netApproved1Step1 = 0;
            $netApproved0Step1 = 0;
            $netApproved1Step2 = 0;
            $netApproved0Step2 = 0;


            foreach ($result['report'] as $data)
            {
                if(count($data) < 10) // if cycle = 0
                    break;

                if(in_array($data[0] , $campaignIdsSTEP1))
                {
                    $netApproved0Step1 += $data[3];
                    $netApproved1Step1 += $data[9];
                }
                else if(in_array($data[0], $campaignIdsSTEP2))
                {

                    $netApproved0Step2 += $data[3];
                    $netApproved1Step2 += $data[9];
                }
            }
            if($netApproved0Step1 != 0)
                $conversionStep1 =  ($netApproved1Step1 / $netApproved0Step1) * 100;
            if($netApproved0Step2 != 0)
                $conversionStep2 =  ($netApproved1Step2 / $netApproved0Step2) * 100;

        }

        return array($conversionStep1, $conversionStep2);
    }
    function request_callback($response, $info, $type, $crmId, $crmName, $campaignId, $affiliateId, $day, $fromDate, $toDate)
    {
        $llcrmHook = new LLCrmHook();
        $dbApi = DBApi::getInstance();
        $dbApi->setSubDomain($this->getSubDomain());

        $fromDate = date('Y-m-d', strtotime($fromDate));
        $toDate = date('Y-m-d', strtotime($toDate));
        $timestamp = date('Y-m-d H:i:s');
        if ($type == 0) { // by crm
            $result = $llcrmHook->parseRetentionByCampaign($response);
            $dbApi->deleteRetentionForInitialAlertByCrmID($crmId, $day);
            foreach ($result['report'] as $data) {
                if(($data != array() || $data != null) && $data[0] != -1) {
                    $has_child = ($data[8] == 'yes' ? 1 : 0);
                    $dbApi->writeRetentionForInitialAlert($crmId, $crmName, $data[0], '', '',$data[2], $data[3], $data[7], $day, $has_child, $fromDate, $toDate, $timestamp);
                }
            }
        } elseif ($type == 1) { // by campaign
            $result = $llcrmHook->parseRetentionByAffiliate($response);
            foreach ($result['report'] as $data) {
                if(($data != array() || $data != null) && $data[0] != -1) {
                    $has_child = ($data[8] == 'yes' ? 1 : 0);
                    $dbApi->writeRetentionForInitialAlert($crmId, '', $campaignId, $data[0], '',$data[2], $data[3], $data[7], $day, $has_child, $fromDate, $toDate, $timestamp);
                }
            }
        } elseif ($type == 2) { // by affiliate
            $result = $llcrmHook->parseRetentionByAffiliate($response);
            foreach ($result['report'] as $data) {
                if(($data != array() || $data != null) && $data[0] != -1) {
                    $has_child = ($data[8] == 'yes' ? 1 : 0);
                    $dbApi->writeRetentionForInitialAlert($crmId, '', $campaignId, $affiliateId, $data[0], $data[2], $data[3], $data[7], $day, $has_child, $fromDate, $toDate, $timestamp);
                }
            }
        }
    }
    public function getInitialAlertDataByCrm($fromDate, $toDate, $crmList, $day)
    {
        $llcrmHook = new LLCrmHook();
        $rc = new RollingCurl(array($this,'request_callback'));
        $rc->window_size = 20;

        foreach ($crmList as $crmInfo) {
            $crmID = $crmInfo[0];
            $crmName = $crmInfo[1];
            $crmUrl = $crmInfo[2];
            $userName = $crmInfo[3]; // crm account name
            $password = $crmInfo[4]; // crm account password
            $cycle = 1;
            if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null) {

                $url = $crmUrl.'/admin/report/custom/index.php?r=8&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate.'&rebill_depth='.$cycle.'&aff=0';

                $request = new RollingCurlRequest($url);

                $request->options = array(CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 300,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array('cookie: p_cookie=1; o_cookie=1; c_cookie=1; b_cookie=1; '.$token,
                        'referer:'.$crmUrl.'/admin/report/custom/index.php?r=8',
                        'upgrade-insecure-requests:1',
                        'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'));
                $request->__set('type', 0);
                $request->__set('crmId', $crmID);
                $request->__set('crmName', $crmName);
                $request->__set('day', $day);
                $request->__set('from_date', $fromDate);
                $request->__set('to_date', $toDate);
                $rc->add($request);
            }
        }
        $reqCount = count($rc->__get('requests'));
        echo $this->subDomain."  by CRM = ".$reqCount."\n";

        if ($reqCount > 0)
            $rc->execute();
    }
    public function getInitialAlertDataBySTEP1Campaign($fromDate, $toDate, $crmList, $day)
    {
        $dbApi = DBApi::getInstance();
        $dbApi->setSubDomain($this->getSubDomain());
        $llcrmHook = new LLCrmHook();
        $rc = new RollingCurl(array($this,'request_callback'));
        $rc->window_size = 20;

        foreach ($crmList as $data) {
            $crmInfo = $data['crm_info'];
            $level = $data['alert_level'];

            $campaignIdsSTEP1 = $dbApi->getSTEP1CampaignsForInitialAlertByCrm($crmInfo[0], $level, $day);
            if($campaignIdsSTEP1 != array()) {
                foreach ($campaignIdsSTEP1 as $campaignId) {
                    $crmID = $crmInfo[0];
                    $crmName = $crmInfo[1];
                    $crmUrl = $crmInfo[2];
                    $userName = $crmInfo[3]; // crm account name
                    $password = $crmInfo[4]; // crm account password
                    $cycle = 1;

                    if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null) {

                        $url = $crmUrl.'/admin/report/custom/index.php?r=8&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate.'&rebill_depth='.$cycle.'&f='.$campaignId.'&aff=1';

                        $request = new RollingCurlRequest($url);
                        $request->options = array(CURLOPT_SSL_VERIFYHOST => 0,
                            CURLOPT_SSL_VERIFYPEER => 0,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 300,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                            CURLOPT_HTTPHEADER => array('cookie: p_cookie=1; o_cookie=1; c_cookie=1; b_cookie=1; '.$token,
                                'referer:'.$crmUrl.'/admin/report/custom/index.php?r=8',
                                'upgrade-insecure-requests:1',
                                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'));
                        $request->__set('type', 1);
                        $request->__set('crmId', $crmID);
                        $request->__set('campaignId', $campaignId);
                        $request->__set('day', $day);
                        $request->__set('from_date', $fromDate);
                        $request->__set('to_date', $toDate);
                        $rc->add($request);
                    }
                }
            }
        }
        $reqCount = count($rc->__get('requests'));
        echo $this->subDomain."  by Campaign = ".$reqCount."\n";

        if ($reqCount > 0)
            $rc->execute();
    }
    public function getInitialAlertDataByAffiliate($fromDate, $toDate, $crmList, $day)
    {
        $dbApi = DBApi::getInstance();
        $dbApi->setSubDomain($this->getSubDomain());
        $llcrmHook = new LLCrmHook();
        $rc = new RollingCurl(array($this,'request_callback'));
        $rc->window_size = 20;
        foreach ($crmList as $data) {
            $crmInfo = $data['crm_info'];
            $level = $data['alert_level'];

            $affiliateIdList = $dbApi->getAffiliateIdsForInitialAlertBySTEP1CampaignId($crmInfo[0], $level, $day);
            if($affiliateIdList != array()) {
                foreach ($affiliateIdList as $affiliateInfo) {
                    $crmID = $crmInfo[0];
                    $crmName = $crmInfo[1];
                    $crmUrl = $crmInfo[2];
                    $userName = $crmInfo[3]; // crm account name
                    $password = $crmInfo[4]; // crm account password
                    $cycle = 1;

                    $campaignId = $affiliateInfo[0];
                    $affiliateId = $affiliateInfo[1];
                    if (($token = $llcrmHook->login($crmID, $crmUrl, $userName, $password)) != null) {

                        $url = $crmUrl.'/admin/report/custom/index.php?r=8&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate.'&rebill_depth='.$cycle.'&f='.$campaignId.'&sf='.urlencode('AFFID:'.$affiliateId).'&aff=1';

                        $request = new RollingCurlRequest($url);
                        $request->options = array(CURLOPT_SSL_VERIFYHOST => 0,
                            CURLOPT_SSL_VERIFYPEER => 0,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 300,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                            CURLOPT_HTTPHEADER => array('cookie: p_cookie=1; o_cookie=1; c_cookie=1; b_cookie=1; '.$token,
                                'referer:'.$crmUrl.'/admin/report/custom/index.php?r=8&f='.$campaignId.'&aff=1',
                                'upgrade-insecure-requests:1',
                                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'));
                        $request->__set('type', 2);
                        $request->__set('crmId', $crmID);
                        $request->__set('campaignId', $campaignId);
                        $request->__set('affiliateId', $affiliateId);
                        $request->__set('day', $day);
                        $request->__set('from_date', $fromDate);
                        $request->__set('to_date', $toDate);
                        $rc->add($request);
                    }
                }
            }
        }
        $reqCount = count($rc->__get('requests'));
        echo $this->subDomain."  by Affiliate = ".$reqCount."\n";

        if ($reqCount > 0)
            $rc->execute();
    }
    public function getKKCrmSalesProgress($crmID, $crmUrl, $userName, $password, $fromDate, $toDate)
    {
        $kkcrmHook = new KKCrmHook();
        if (($token = $kkcrmHook->login($crmID, $crmUrl, $userName, $password)) != null)
        {
            $response = $kkcrmHook->getCrmSalesBreakDown($token, $fromDate, $toDate, $crmID);
            return $response;

        } else
        {
            echo "Login failed in konnektive sales progress alert..";
            return array();
        }
    }
}
?>