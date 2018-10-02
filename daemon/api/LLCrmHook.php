<?php
require_once 'DBApi.php';

class LLCrmHook
{
    const ERROR = 'error';

    public $crmID = -1;
    public $crmUrl = "";
    public $userName = "";
    public $password = "";

    public $crmToken = "";


    function __construct()
    {
    }

    function __destruct()
    {
    }

    public function printDebug($data)
    {
        $debugFile = '../../logs/debug.log';
        $handle = fopen($debugFile, "a");
        fwrite($handle, $data . "\n");
        fclose($handle);
    }

    public function getSecurityToken($token)
    {
        if ($this->crmUrl == '') return self::ERROR;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, $this->crmUrl.'/admin/login.php');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($token == null || $token == '') {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'user-agent:Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
                'upgrade-insecure-requests:1')
            );
        } else {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'cookie: '.$token,
                'user-agent:Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
                'upgrade-insecure-requests:1')
            );   
        }
        curl_setopt($curl, CURLOPT_HEADER, true);

        $response = curl_exec($curl);

        $token_pos = strpos($response, 'securityToken');
        if ($token_pos === false) return self::ERROR;

        $value_pos = strpos($response, "value", $token_pos);
        if ($value_pos === false) return self::ERROR;

        $first_pos = strpos($response, "\"", $value_pos);
        if ($first_pos === false) return self::ERROR;

        $second_pos = strpos($response, "\"", $first_pos + 1);
        if ($second_pos === false) return self::ERROR;

        $securityToken = substr($response, $first_pos + 1, $second_pos - $first_pos - 1);

        if ($token == null || $token == '')
        {
            // check cookie from response header
            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $data = $this->getHttpHeader2Array($header);
            $str = $data['Set-Cookie'];
            $pos = strpos($str, '; path=/');
            $cookie = substr($str, 0, $pos);

            $data['cookie'] = $cookie;
        }
        else
            $data['cookie'] = $token;
        
        $data['securityToken'] = $securityToken;

        curl_close($curl);

        return $data;
    }

    public function login($crmID, $crmUrl, $userName, $password)
    {
        $this->crmID = $crmID;
        $this->crmUrl = $crmUrl;
        $this->userName = $userName;
        $this->password = $password;

        $dbApi = DBApi::getInstance();
        $tokenInfo = $dbApi->getCrmToken($crmID);

        if ($tokenInfo == null)
        {
            $crmToken = '';
        }
        else
        {
            $token = $tokenInfo[0];
            $timestamp = $tokenInfo[1];

            if ((time() - $timestamp) > 1440)   // expired token
                $crmToken = $token;
            else        // valid token
            {
                $dbApi->addCrmToken($crmID, $token, time());
                return $token;
            }
        }

        $cookie = $this->getSecurityToken($crmToken);

        if (!isset($cookie) || !isset($cookie['cookie']) || !isset($cookie['securityToken']))
            return null;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $crmUrl.'/admin/login.php?'.$cookie['cookie'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'login_url=&securityToken='.$cookie['securityToken'].'&admin_name='.$userName.'&admin_pass='.urlencode($password),
            CURLOPT_HTTPHEADER => array(
                'content-type: application/x-www-form-urlencoded',
                'cookie: '.$cookie['cookie'],
                'origin: '.$crmUrl,
                'referer: '.$crmUrl.'/admin/login.php',
                'upgrade-insecure-requests: 1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'                
            )
        ));

        $response = curl_exec($curl);

        // check cookie from response header
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $data = $this->getHttpHeader2Array($header);
        $str = $data['Set-Cookie'];

        $token_pos = strpos($header, 'token=');
        if ($token_pos === false) return null;
        $end_pos = strpos($header, ";", $token_pos);
        if ($end_pos === false) return null;

        $token = substr($header, $token_pos, $end_pos - $token_pos);

        curl_close($curl);

        $this->crmToken = $token;

        $dbApi->addCrmToken($crmID, $token, time());
        
        return $token;
    }

    public function getProspectReport($token, $fromDate, $toDate)
    {
        if ($token == '' || $token == null) return self::ERROR;
        
        $this->crmToken = $token;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.'/admin/report/custom/index.php?r=7&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'cookie: p_cookie=1; o_cookie=1; c_cookie=1; b_cookie=1; '.$this->crmToken,
                'upgrade-insecure-requests:1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getSalesByProspectReport($token, $fromDate, $toDate, $campaignID = '')
    {
        if ($token == '' || $token == null) return self::ERROR;

        $this->crmToken = $token;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $url = '/admin/report/custom/index.php?r=7&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate;
        if (!empty($campaignID))
            $url = $url . '&campaign_id='.$campaignID;

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.$url,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'cookie: p_cookie=1; o_cookie=1; c_cookie=1; b_cookie=1; '.$this->crmToken,
                'upgrade-insecure-requests:1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getSalesReport($token, $fromDate, $toDate, $campaignID, $aff, $f, $sf)
    {
        if ($token == '' || $token == null) return self::ERROR;

        $this->crmToken = $token;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $url = '/admin/report/custom/index.php?r=7&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate;
        if (!empty($aff))       // affiliate, sub-affiliate
        {
            if (!empty($campaignID))
                $url .= '&affiliate_id='.$campaignID;

            $url .= '&aff='.$aff;

            if (!empty($f))
                $url .= '&f='.$f;

            if (!empty($sf))
                $url .= '&sf='.$sf;
        }
        else                    // campaign
        {
            if (!empty($campaignID))
                $url .= '&campaign_id='.$campaignID;
        }
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.$url,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'cookie: p_cookie=1; o_cookie=1; c_cookie=1; b_cookie=1; '.$this->crmToken,
                'upgrade-insecure-requests:1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getSalesByProspectReportByCampaign($token, $fromDate, $toDate, $campaignID)
    {
        if ($token == '' || $token == null) return self::ERROR;

        $this->crmToken = $token;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        if ($campaignID == 'Total')
            $url = '/admin/report/custom/index.php?r=7&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate.'&f=ALL';
        else
            $url = '/admin/report/custom/index.php?r=7&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate.'&f='.$campaignID;

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.$url,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'cookie: p_cookie=1; o_cookie=1; c_cookie=1; b_cookie=1; '.$this->crmToken,
                'upgrade-insecure-requests:1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
    public function getSalesByProspectReportByAffiliate($token, $fromDate, $toDate, $campaignID, $affiliateID)
    {
        if ($token == '' || $token == null) return self::ERROR;

        $this->crmToken = $token;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        if ($affiliateID == 'Total')
            $url = '/admin/report/custom/index.php?r=7&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate.'&f='.$campaignID.'&sf=ALL_SUB:ALL_SUB&aff=1';
        else
            $url = '/admin/report/custom/index.php?r=7&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate.'&f='.$campaignID.'&sf=AFFID:'.$affiliateID.'&aff=1';

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.$url,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'cookie: p_cookie=1; o_cookie=1; c_cookie=1; b_cookie=1; '.$this->crmToken,
                'upgrade-insecure-requests:1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
    public function getSalesByProspectReportBySubAffiliate($token, $fromDate, $toDate, $campaignID, $depth, $subAffiliateID)
    {
        if ($token == '' || $token == null) return self::ERROR;

        $this->crmToken = $token;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        if ($subAffiliateID == 'Total')
        {
            $depth ++;
            $url = '/admin/report/custom/index.php?r=7&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate.'&f='.$campaignID."&sf=ALL_SUB{$depth}:ALL_SUB{$depth}&aff=1";
        }
        else
            $url = '/admin/report/custom/index.php?r=7&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate.'&f='.$campaignID."&sf=C{$depth}:".$subAffiliateID.'&aff=1';

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.$url,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'cookie: p_cookie=1; o_cookie=1; c_cookie=1; b_cookie=1; '.$this->crmToken,
                'upgrade-insecure-requests:1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function parseSalesReport($response)
    {
        $data = $response;

        $finder = 'col10pct';
        $type = 'campaign';
        $start = strpos($response, '<tr class="list_header">', 0);
        $start = strpos($response, 'title="">', $start);
        $start += 9;
        $end = strpos($response, '</span>', $start);
        $sub = substr($response, $start, $end - $start);
        if ($sub == 'Affiliate ID')
            $type = 'affiliate';
        if ($sub == 'Sub-Affiliate ID')
            $type = 'sub_affiliate';

        $prospects = array();
        while (true)
        {
            $response = strstr($response, $finder);
            if ($response === FALSE)
                break;
            $prospect = array();
            // get type id
            $start = strpos($response, 'style=', 0);
            if ($type == 'campaign')
            {
                $start = strpos($response, '(', $start);
                $end = strpos($response, ')', $start);
                $campaignId = substr($response, $start + 1, $end - $start -1);
                $val = $campaignId;
            }
            else
            {
                $start += 9; // style=\"\">
                $end = strpos($response, '<', $start);
                $val = substr($response, $start, $end - $start);
            }

            $prospect[] = $val;

            // get prospects
            $response = substr($response, $end);
            $start = strpos($response, 'style=');
            $start += 9; // style="">
            $end = strpos($response, '<', $start);
            $pros = substr($response, $start, $end - $start);
            $val = intval(str_replace(',', '', $pros));

            $prospect[] = $val;

            // get initial customer
            $response = substr($response, $end);
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $initialCustomer = substr($response, $start, $end - $start);
            $val = intval(str_replace(',', '', $initialCustomer));

            $prospect[] = $val;

            // get conversion rate
            $response = substr($response, $end);
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $conversionRate = substr($response, $start, $end - $start);
            $val = str_replace('%', '', $conversionRate);

            $prospect[] = $val;

            // get gross revenue
            $response = substr($response, $end);
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $gross = substr($response, $start, $end - $start);
            $val = str_replace('$', '', $gross);
            $val = str_replace(',', '', $val);

            $prospect[] = $val;

            // get average revenue
            $response = substr($response, $end);
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $average = substr($response, $start, $end - $start);
            $val = str_replace('$', '', $average);
            $val = str_replace(',', '', $val);

            $prospect[] = $val;

            // get breakdown
            $response = substr($response, $end);
            $start = strpos($response, 'style=');
            $end = strpos($response, $finder);
            if ($end < 0)
                $end = strpos($response, 'Total:');
            $sub = substr($response, $start, $end - $start);

            if ($search_start = strpos($sub, 'SearchAffiliate('))
            {
                $search_end = strpos($sub, ')',$search_start);
                $str = substr($sub, $search_start, $search_end - $search_start);
                $affs = explode(',', $str);
                $prospect[] = str_replace("'", "", $affs[1]);
                $prospect[] = str_replace("'", "", $affs[2]);
                if ($type == 'campaign')
                    $prospect[] = 'Affiliate';
                else
                    $prospect[] = 'Sub-Affiliate';
            }
            else
            {
                $prospect[] = "";
                $prospect[] = "";
                $prospect[] = "";
            }
            $prospect[] = "";
            $prospects[] = $prospect;

            $response = substr($response, $start);
        }

        $prospect = array();
        // find Total
        $prospect = array();
        $response = strstr($data, 'Total:');
        if ($response)
        {
            $prospect[] = 'Total';

            // get prospects
            $start = strpos($response, 'style=');
            $start += 9; // style=\"\">
            $end = strpos($response, '<', $start);
            $pros = substr($response, $start, $end - $start);
            $val = intval(str_replace(',', '', $pros));

            $prospect[] = $val;

            // get initial customer
            $response = substr($response, $end);
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $initialCustomer = substr($response, $start, $end - $start);
            $val = intval(str_replace(',', '', $initialCustomer));

            $prospect[] = $val;

            // get conversion rate
            $response = substr($response, $end);
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $conversionRate = substr($response, $start, $end - $start);
            $val = str_replace('%', '', $conversionRate);

            $prospect[] = $val;

            // get gross revenue
            $response = substr($response, $end);
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $gross = substr($response, $start, $end - $start);
            $val = str_replace('$', '', $gross);
            $val = str_replace(',', '', $val);

            $prospect[] = $val;

            // get average revenue
            $response = substr($response, $end);
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $average = substr($response, $start, $end - $start);
            $val = str_replace('$', '', $average);
            $val = str_replace(',', '', $val);

            $prospect[] = $val;

            // get affiliate breakdown
            $response = substr($response, $end);
            if ($search_start = strpos($response, 'SearchAffiliate('))
            {
                $search_end = strpos($response, ')',$search_start);
                $str = substr($response, $search_start, $search_end - $search_start);
                $affs = explode(',', $str);
                $prospect[] = str_replace("'", "", $affs[1]);
                $prospect[] = str_replace("'", "", $affs[2]);
                if ($type == 'campaign')
                    $prospect[] = 'Affiliate';
                else
                    $prospect[] = 'Sub-Affiliate';
            }
            else
            {
                $prospect[] = "";
                $prospect[] = "";
                $prospect[] = "";
            }
            $prospects[] = $prospect;
        }

        return $prospects;
    }

    public function getAffiliateReportByCampaign($token, $fromDate, $toDate, $campaignID)
    {
        if ($token == '' || $token == null) return self::ERROR;
        
        $this->crmToken = $token;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.'/admin/report/custom/index.php?r=7&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate.'&f='.$campaignID.'&aff=1',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'cookie: p_cookie=1; o_cookie=1; c_cookie=1; b_cookie=1; '.$this->crmToken,
                'referer:'.$this->crmUrl.'/admin/report/custom/index.php?r=7',
                'upgrade-insecure-requests:1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        return $this->parse4InitialCustomerByAffiliate($response);
    }

    public function getRetentionReport($token, $fromDate, $toDate, $cycle)
    {
        if ($token == '' || $token == null) return self::ERROR;
        
        $this->crmToken = $token;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.'/admin/report/custom/index.php?r=8&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate.'&rebill_depth='.$cycle.'&aff=0',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'cookie: p_cookie=1; o_cookie=1; c_cookie=1; b_cookie=1; '.$this->crmToken,
                'referer:'.$this->crmUrl.'/admin/report/custom/index.php?r=8',
                'upgrade-insecure-requests:1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        return $response;
    }

    public function getRetentionReportByCampaign($token, $fromDate, $toDate, $cycle, $campaignID)
    {
        if ($token == '' || $token == null) return self::ERROR;
        
        $this->crmToken = $token;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.'/admin/report/custom/index.php?r=8&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate.'&rebill_depth='.$cycle.'&f='.$campaignID.'&aff=1',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'cookie: p_cookie=1; o_cookie=1; c_cookie=1; b_cookie=1; '.$this->crmToken,
                'referer:'.$this->crmUrl.'/admin/report/custom/index.php?r=8',
                'upgrade-insecure-requests:1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        return $response;
    }

    public function getRetentionReportByAffiliate($token, $fromDate, $toDate, $cycle, $campaignID, $affiliateID)
    {
        if ($token == '' || $token == null) return self::ERROR;
        
        $this->crmToken = $token;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.'/admin/report/custom/index.php?r=8&test_flag=0&from_date='.$fromDate.'&to_date='.$toDate.'&rebill_depth='.$cycle.'&f='.$campaignID.'&sf='.urlencode('AFFID:'.$affiliateID).'&aff=1',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'cookie: p_cookie=1; o_cookie=1; c_cookie=1; b_cookie=1; '.$this->crmToken,
                'referer:'.$this->crmUrl.'/admin/report/custom/index.php?r=8&f='.$campaignID.'&aff=1',
                'upgrade-insecure-requests:1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        return $response;
    }

    public function getAffiliateReport($token, $fromDate, $toDate, $userToken, $delete, $crmID)
    {
        $dbApi = DBApi::getInstance();

        if ($delete == '1')
            $dbApi->deleteAffiliateProgress($userToken);
        else
            $dbApi->deleteAffiliateProgressByCrmID($userToken, $crmID);

        // get prospect page
        $prospectPage = $this->getProspectReport($token, $fromDate, $toDate);
        $initialCustomers = $this->parse4InitialCustomerByCampaign($prospectPage);

        return $initialCustomers;
    }
    private function getAffiliateInfo($affiliateId, $affiliateTable)
    {
        foreach ($affiliateTable as $row) 
        {
            if($row[0] == $affiliateId)
            {
                return array($row[1], $row[2]); // label, sales_goal
            }    
        }
        return null;
    }
    public function getAffiliateList($token, $fromDate, $toDate, $userToken, $camId)
    {
        // get affiliate prospect page
        $data = array();

        $affiliates = $this->getAffiliateReportByCampaign($token, $fromDate, $toDate, $camId);
        // get affiliaet labeling from db
        $dbApi = DBApi::getInstance();
        $affiliateTable = $dbApi->getAffiliatesByCrmId($this->crmID);

        foreach ($affiliates as $value) 
        {
            $affiliateInfo = $this->getAffiliateInfo($value['affiliate_id'], $affiliateTable);
            if($affiliateInfo != null) 
            {
                $item['crm_id'] = $this->crmID;
                $item['campaign_id'] = $camId;
                $item['affiliate_id'] = $value['affiliate_id'];
                $item['initial_customer'] = $value['initial_customer'];
                $item['user_token'] = $userToken;
                $item['label'] = $affiliateInfo['0'];
                $data[] = $item;    
            }            
        }        
        // store db and get sum of affiliate`s initial customers
        if ($dbApi->addAffiliateProgress($data))
            return true;

        return false;
    }
    /*
     * @description
     * Get CRM sales with new breakdown by labels
     *
     */
    public function getCrmSalesBreakDown($token, $fromDate, $toDate, $crmID)
    {
        $prospectPage = $this->getProspectReport($token, $fromDate, $toDate);

        $response = $this->getRetentionReport($token, $fromDate, $toDate, 1);

        $retention = $this->parseRetentionByCampaign($response);


        $dbApi = DBApi::getInstance();
        // get campaign for STEP1
        $campaignSTEP1 = $dbApi->getSTEP1Campaign($crmID);
        // get campaign for STEP2
        $campaignSTEP2 = $dbApi->getSTEP2Campaign($crmID);
        // get campaign for TABLET S2
        $campaignTABLET = $dbApi->getTabletCampaign($crmID);
        // get campaign for STEP2 Non Prepaids
        $campaignSTEP2NonPP = $dbApi->getSTEP2NonPPCampaign($crmID);
        // get campaign for STEP1 Non Prepaids
        $campaignSTEP1NonPP = $dbApi->getSTEP1NonPPCampaign($crmID);
        // get campaign for PrePaids
        $campaignPrepaids = $dbApi->getPrepaidCampaign($crmID);
        // get labels (only visible verticals and custom labels per crm ) details
        $labelInfo = $dbApi->getLabelsAndGoalsByCrmAndType($crmID, 3);

        $initialCustomers = $this->parse4InitialCustomerByCampaign($prospectPage);


        $valueSTEP1 = 0;
        $valueSTEP2 = 0;
        $valueTABLET = 0;
        $valueSTEP2NonPP = 0;
        $valueSTEP1NonPP = 0;
        $valueOrderPage = 0;
        $valuePrepaid = 0;
        $orderCount = 0;

        // Initialize
        $breakDown = array();
        foreach ($labelInfo as $item)
        {
            // id, label, goal, step1, step2, tablet, prepaids, step2 non pp, step1 non pp, order page, order count, initial decline, gross order
            $breakDown[] = array($item[0], $item[1], $item[3], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        }
        $ret = array();

        foreach ($initialCustomers as $value) {
            $cId = $value['campaign_id'];

            foreach ($campaignSTEP1 as $item)
            {
                if ($item[0] == $cId)
                {
                    $valueSTEP1 += $value['initial_customer'];
                    $orderCount ++;
                    $valueOrderPage += $value['conversion_rate'];
                    foreach ($labelInfo as $label)
                    {
                        $id = $label[0];
                        for ($i = 0; $i < count($breakDown); $i ++)
                        {
                            $breakItem = $breakDown[$i];
                            if ($breakItem[0] == $id)
                            {
                                if (strstr($item[1], ','.$id.',') !== FALSE)
                                {
                                    $breakItem[3] += $value['initial_customer'];
                                    $breakItem[9] += $value['conversion_rate'];
                                    $breakItem[10] ++;
                                }
                            }
                            $breakDown[$i] = $breakItem;
                        }
                    }
                }
            }
            foreach ($campaignSTEP2 as $item)
            {
                if ($item[0] == $cId)
                {
                    $valueSTEP2 += $value['initial_customer'];
                    foreach ($labelInfo as $label)
                    {
                        $id = $label[0];
                        for ($i = 0; $i < count($breakDown); $i ++)
                        {
                            $breakItem = $breakDown[$i];
                            if ($breakItem[0] == $id)
                            {
                                if (strstr($item[1], ','.$id.',') !== FALSE)
                                {
                                    $breakItem[4] += $value['initial_customer'];
                                }
                            }
                            $breakDown[$i] = $breakItem;
                        }
                    }
                }
            }
            foreach ($campaignTABLET as $item)
            {
                if ($item[0] == $cId)
                {
                    $valueTABLET += $value['initial_customer'];
                    foreach ($labelInfo as $label)
                    {
                        $id = $label[0];
                        for ($i = 0; $i < count($breakDown); $i ++)
                        {
                            $breakItem = $breakDown[$i];
                            if ($breakItem[0] == $id)
                            {
                                if (strstr($item[1], ','.$id.',') !== FALSE)
                                {
                                    $breakItem[5] += $value['initial_customer'];
                                }
                            }
                            $breakDown[$i] = $breakItem;
                        }
                    }
                }
            }
            foreach ($campaignPrepaids as $item)
            {
                if ($item[0] == $cId)
                {
                    $valuePrepaid += $value['initial_customer'];
                    foreach ($labelInfo as $label)
                    {
                        $id = $label[0];
                        for ($i = 0; $i < count($breakDown); $i++)
                        {
                            $breakItem = $breakDown[$i];
                            if ($breakItem[0] == $id)
                            {
                                if (strstr($item[1], ','.$id.',') !== FALSE)
                                {
                                    $breakItem[6] += $value['initial_customer'];
                                }
                            }
                            $breakDown[$i] = $breakItem;
                        }
                    }
                }
            }
            foreach ($campaignSTEP2NonPP as $item)
            {
                if ($item[0] == $cId)
                {
                    $valueSTEP2NonPP += $value['initial_customer'];
                    foreach ($labelInfo as $label)
                    {
                        $id = $label[0];
                        for ($i = 0; $i < count($breakDown); $i ++)
                        {
                            $breakItem = $breakDown[$i];
                            if ($breakItem[0] == $id)
                            {
                                if (strstr($item[1], ','.$id.',') !== FALSE)
                                {
                                    $breakItem[7] += $value['initial_customer'];
                                }
                            }
                        }
                    }
                }
            }
            foreach ($campaignSTEP1NonPP as $item)
            {
                if ($item[0] == $cId)
                {
                    $valueSTEP1NonPP += $value['initial_customer'];
                    foreach ($labelInfo as $label)
                    {
                        $id = $label[0];
                        for ($i = 0; $i < count($breakDown); $i ++)
                        {
                            $breakItem = $breakDown[$i];
                            if ($breakItem[0] == $id)
                            {
                                if (strstr($item[1], ','.$id.',') !== FALSE)
                                {
                                    $breakItem[8] += $value['initial_customer'];
                                }
                            }
                            $breakDown[$i] = $breakItem;
                        }
                    }

                }
            }
        }
        $valueDecline = 0;
        $valueGrossOrder = 0;
        $retention = $retention['report'];
        foreach ($retention as $retentionItem)
        {
            $campaignId = $retentionItem[0];
            foreach ($campaignSTEP1 as $step1Item)
            {
                if ($step1Item[0] == $campaignId)
                {
                    $valueDecline += $retentionItem[9];
                    $valueGrossOrder += $retentionItem[2];
                    foreach ($labelInfo as $labelItem)
                    {
                        $id = $labelItem[0];
                        for ($i = 0; $i < count($breakDown); $i++)
                        {
                            $breakItem = $breakDown[$i];
                            if ($breakItem[0] == $id)
                            {
                                if (strstr($step1Item[1], ','.$id.',') !== FALSE)
                                {
                                    $breakItem[11] += $retentionItem[9];
                                    $breakItem[12] += $retentionItem[2];
                                }
                            }
                            $breakDown[$i] = $breakItem;
                        }
                    }
                }
            }
        }
        $ret[] = array(0, '', '', $valueSTEP1, $valueSTEP2, $valueTABLET, $valuePrepaid, $valueSTEP1NonPP, $valueSTEP2NonPP, $valueOrderPage, $orderCount, $valueDecline, $valueGrossOrder);
        foreach ($breakDown as $item)
        {
            $ret[] = $item;
        }
        return $ret;
//        return array($valueSTEP1, $valueSTEP2, $valueTABLET, $valueSTEP2NonPP, $valueSTEP1NonPP, $valueOrderPage);
    }
    /*
     * Old api
     */
    public function getCrmSalesProgress($token, $fromDate, $toDate, $crmID)
    {
        $prospectPage = $this->getProspectReport($token, $fromDate, $toDate);

        $dbApi = DBApi::getInstance();
        // get campaign ids for STEP1
        $campaignIdsSTEP1 = $dbApi->getSTEP1CampaignIds($crmID);        
        // get campaign ids for STEP2
        $campaignIdsSTEP2 = $dbApi->getSTEP2CampaignIds($crmID);
        // get campaign ids for TABLET S2
        $campaignIdsTABLET = $dbApi->getTabletCampaignIds($crmID);
        // get campaign ids for STEP2 Non Prepaids
        $campaignIdsSTEP2NonPP = $dbApi->getSTEP2NonPPCampaignIds($crmID);
        // get campaign ids for STEP1 Non Prepaids
        $campaignIdsSTEP1NonPP = $dbApi->getSTEP1NonPPCampaignIds($crmID);

        $initialCustomers = $this->parse4InitialCustomerByCampaign($prospectPage);
        
        $valueSTEP1 = 0;
        $valueSTEP2 = 0;
        $valueTABLET = 0;
        $valueSTEP2NonPP = 0;
        $valueSTEP1NonPP = 0;
        $valueOrderPage = 0;

        $orderCount = 0;
        foreach ($initialCustomers as $value) {
            $cId = $value['campaign_id'];
            
            if(in_array($cId, $campaignIdsSTEP1)) {            
                $valueSTEP1 += $value['initial_customer'];
                $orderCount ++;
                $valueOrderPage += $value['conversion_rate'];
            }
            if(in_array($cId, $campaignIdsSTEP2)) {                
                $valueSTEP2 += $value['initial_customer'];
            }
            if(in_array($cId, $campaignIdsTABLET)) {                
                $valueTABLET += $value['initial_customer'];
            }
            if(in_array($cId, $campaignIdsSTEP2NonPP)) {                
                $valueSTEP2NonPP += $value['initial_customer'];
            }
            if(in_array($cId, $campaignIdsSTEP1NonPP)) {
                $valueSTEP1NonPP += $value['initial_customer'];
            }
        }
        if($orderCount == 0)
            $valueOrderPage = 0;
        else {
            $valueOrderPage = $valueOrderPage / $orderCount;
            $valueOrderPage = round($valueOrderPage, 2);
        }

        return array($valueSTEP1, $valueSTEP2, $valueTABLET, $valueSTEP2NonPP, $valueSTEP1NonPP, $valueOrderPage);
    }

    private function getHttpHeader2Array($rawheader)
    {
        $header_array = array();
        $header_rows = explode("\n", $rawheader);
        for ($i = 0; $i < count($header_rows); $i++) {
            $fields = explode(":", $header_rows[$i]);

            if ($i != 0 && !isset($fields[1])) {
                if (substr($fields[0], 0, 1) == "\t") {
                    end($header_array);
                    $header_array[key($header_array)] .= "\r\n\t" . trim($fields[0]);
                } else {
                    end($header_array);
                    $header_array[key($header_array)] .= trim($fields[0]);
                }
            } else {
                $field_title = trim($fields[0]);
                if (!isset($header_array[$field_title])) {
                    if (!empty($fields[1])) {
                        $header_array[$field_title] = trim($fields[1]);
                    }
                } else if (is_array($header_array[$field_title])) {
                    $header_array[$field_title] = array_merge($header_array[$fields[0]], array(trim($fields[1])));
                } else {
                    $header_array[$field_title] = array_merge(array($header_array[$fields[0]]), array(trim($fields[1])));
                }
            }
        }
        return $header_array;
    }
    public function parse4InitialCustomerByCampaign($response)
    {

        $prospects = array();
        while (true) {
            $response = strstr($response, 'col10pct');
            if($response === FALSE) {
                break;
            } else {
                $start = strpos($response, 'style=', 0);
                $start = strpos($response, '(', $start);
                // get campaign id
                $end = strpos($response, ')', $start);
                $campaignId = substr($response, $start + 1, $end - $start -1);
                $val = intval(str_replace(',', '', $campaignId));

                $dbApi = DBApi::getInstance();
                $hasLabel = $dbApi->checkLabelingOfCampaign($val, $this->crmID);
                if($hasLabel)
                    $prospect['campaign_id'] = $val;

                $response = substr($response, $end + 1);                
                // get initial customer
                $start = strpos($response, 'style=');
                $start += 9; // style=\"\">
                $start = strpos($response, 'style=', $start);
                $start += 9;
                $end = strpos($response, '<', $start);
                $initialCustomer = substr($response, $start, $end - $start);
                $val = intval(str_replace(',', '', $initialCustomer));
                if($hasLabel)
                    $prospect['initial_customer'] = $val;
                $response = substr($response, $end);
                // get conversion rate
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $conversionRate = substr($response, $start, $end - $start);
                $val1 = str_replace('%', '', $conversionRate);
                if($hasLabel)
                    $prospect['conversion_rate'] = $val1;
                $response = substr($response, $end);

                if($hasLabel)
                    $prospects[] = $prospect;

            }
        }
        return $prospects;
    }

    public function parse4InitialCustomerByAffiliate($response)
    {
        $prospects = array();
        while (true) {
            $response = strstr($response, 'col10pct');
            if($response === FALSE) {
                break;
            } else {
                $start = strpos($response, 'style=', 0);                
                // get affiliate id
                $start += 9;
                $end = strpos($response, '<', $start);
                $affiliateId = substr($response, $start, $end - $start);                
                $val = str_replace(',', '', $affiliateId); // remove ',' in affiliat ID string.
                

                $prospect['affiliate_id'] = $val;
                $response = substr($response, $end + 1);                
                // get initial customer
                $start = strpos($response, 'style=');
                $start += 9; // style=\"\">
                $start = strpos($response, 'style=', $start);
                $start += 9;
                $end = strpos($response, '<', $start);
                $initialCustomer = substr($response, $start, $end - $start);
                $val = intval(str_replace(',', '', $initialCustomer));
                $prospect['initial_customer'] = $val;
                $response = substr($response, $end);

                $prospects[] = $prospect;

            }
        }
        return $prospects;
    }
    public function parseRetentionByCampaign($response)
    {
        $start = strpos($response, 'list_header list_category');
        $end = strpos($response, '/tr', $start);
        $res = substr($response, $start, $end - $start);
        $cycleCount = substr_count($res, 'Cycle'); 

        $report = array();

        while (true) 
        {
            if(strstr($response, 'No results exist at this time'))
            {
                $ret['report'] = array();
                $ret['cycle'] = 0;

                return $ret;
            }
                
            $faplus = strstr($response, 'fa fa-plus');
            if(!$faplus)
                break;
            $response = $faplus;

            // get campaign id
            $start = strpos($response, '(', 0);
            $end = strpos($response, ')', $start);

            $val = substr($response, $start + 1, $end - $start - 1);
            $campaignId = intval(str_replace(',', '', $val));
            $response = substr($response, $end + 1);

            // get campaign name
            $end = strpos($response, '<');
            $campaignName = substr($response, 0, $end);
            $response = substr($response, $end + 1);

            // get Gross Orders
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $grossOrders = substr($response, $start, $end - $start);
            $grossOrders = $this->valueFilterInRetention($grossOrders);
            $response = substr($response, $end + 1);

            // get Net Approved
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $netApproved = substr($response, $start, $end - $start);
            $netApproved = $this->valueFilterInRetention($netApproved);
            $response = substr($response, $end + 1);

            // get Subscriptions Approved
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $subscriptions = substr($response, $start, $end - $start);
            $subscriptions = $this->valueFilterInRetention($subscriptions);
            $response = substr($response, $end + 1);

            // get Declined
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $declined = substr($response, $start, $end - $start);
            $declined = $this->valueFilterInRetention($declined);
            $response = substr($response, $end + 1);

            // get Void/Full Refund
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $voidFull = substr($response, $start, $end - $start);
            $voidFull = $this->valueFilterInRetention($voidFull);
            $response = substr($response, $end + 1);

            // get Partial Refund
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $partial = substr($response, $start, $end - $start);
            $partial = $this->valueFilterInRetention($partial);
            $response = substr($response, $end + 1);

            // get Void/Refund
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $voidRefund = substr($response, $start, $end - $start);
            $voidRefund = $this->valueFilterInRetention($voidRefund);
            $response = substr($response, $end + 1);

            // get Canceled
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $cancel = substr($response, $start, $end - $start);
            $cancel = $this->valueFilterInRetention($cancel);
            $response = substr($response, $end + 1);

            // get Hold
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $hold = substr($response, $start, $end - $start);
            $hold = $this->valueFilterInRetention($hold);
            $response = substr($response, $end + 1);

            // get Pending
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $pending = substr($response, $start, $end - $start);
            $pending = $this->valueFilterInRetention($pending);
            $response = substr($response, $end + 1);

            // get Approval Rate
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $approvalRate = substr($response, $start, $end - $start);
            $approvalRate = $this->valueFilterInRetention($approvalRate);
            $response = substr($response, $end + 1);

            // get Net Revenue
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $netRevenue = substr($response, $start, $end - $start);
            $netRevenue = $this->valueFilterInRetention($netRevenue);
            $response = substr($response, $end + 1);                        

            $start = strpos($response, '<td');
            $end = strpos($response, '/td', $start);
            $sub = substr($response, $start, $end - $start);

            if($cycleCount == 1)//strstr($sub, 'inline-block'))
            {
                // cycle = 1
             
                if(strpos($sub, 'Affiliate ID'))
                    $report[] = array($campaignId, $campaignName, $grossOrders, $netApproved, $voidFull, $partial, $voidRefund, $approvalRate, 'yes', $declined);
                else
                    $report[] = array($campaignId, $campaignName, $grossOrders, $netApproved, $voidFull, $partial, $voidRefund, $approvalRate, 'no', $declined);                
                
            }   
            else if($cycleCount == 2)
            {
                // cycle = 2;
             
                // get gross orders in cycle 1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $grossOrders1 = substr($response, $start, $end - $start);
                $grossOrders1 = $this->valueFilterInRetention($grossOrders1);
                $response = substr($response, $end + 1);

                // get Net Approvded in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $netApproved1 = substr($response, $start, $end - $start);
                $netApproved1 = $this->valueFilterInRetention($netApproved1);
                $response = substr($response, $end + 1);

                // get Declined in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $declined1 = substr($response, $start, $end - $start);
                $declined1 = $this->valueFilterInRetention($declined1);
                $response = substr($response, $end + 1);

                // get Void/Full Refund in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $voidFull1 = substr($response, $start, $end - $start);
                $voidFull1 = $this->valueFilterInRetention($voidFull1);
                $response = substr($response, $end + 1);

                // get Partial Refund in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $partial1 = substr($response, $start, $end - $start);
                $partial1 = $this->valueFilterInRetention($partial1);
                $response = substr($response, $end + 1);

                // get Void/Refund Refund in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $voidRefund1 = substr($response, $start, $end - $start);
                $voidRefund1 = $this->valueFilterInRetention($voidRefund1);
                $response = substr($response, $end + 1);

                // get Canceled Refund in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $canceled1 = substr($response, $start, $end - $start);
                $canceled1 = $this->valueFilterInRetention($canceled1);
                $response = substr($response, $end + 1);

                // get Hold in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $hold1 = substr($response, $start, $end - $start);
                $hold1 = $this->valueFilterInRetention($hold1);
                $response = substr($response, $end + 1);

                // get Pending in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $pending1 = substr($response, $start, $end - $start);
                $pending1 = $this->valueFilterInRetention($pending1);
                $response = substr($response, $end + 1);

                // get Conversion in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $conversion1 = substr($response, $start, $end - $start);
                $conversion1 = $this->valueFilterInRetention($conversion1);
                $response = substr($response, $end + 1);

                // get Net Revenue in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $netRevenue1 = substr($response, $start, $end - $start);
                $netRevenue1 = $this->valueFilterInRetention($netRevenue1);
                $response = substr($response, $end + 1);

                $start = strpos($response, '<td');
                $end = strpos($response, '<\/td', $start);
                $sub = substr($response, $start, $end - $start);

                if(strpos($sub, 'Affiliate ID'))
                    $report[] = array($campaignId, $campaignName, $grossOrders, $netApproved, $voidFull, $partial, $voidRefund, $approvalRate, $grossOrders1, $netApproved1,$voidFull1, $partial1, $voidRefund1, $conversion1, 'yes');
                else
                    $report[] = array($campaignId, $campaignName, $grossOrders, $netApproved, $voidFull, $partial, $voidRefund, $approvalRate, $grossOrders1, $netApproved1,$voidFull1, $partial1, $voidRefund1, $conversion1, 'no');   
                
            }
            
        }

        // get total
        $response = strstr($response, 'Total:');
        if(!$response)
        {
            $ret['report'] = array();
            $ret['cycle'] = 0;
            return $ret;
        }
            
        $campaignId = -1;
        $campaignName = 'Total:';
        // get Gross Orders
        $start = strpos($response, 'style=');
        $start += 9;
        $end = strpos($response, '<', $start);
        $grossOrders = substr($response, $start, $end - $start);
        $grossOrders = $this->valueFilterInRetention($grossOrders);
        $response = substr($response, $end + 1);

        // get Net Approved
        $start = strpos($response, 'style=');
        $start += 9;
        $end = strpos($response, '<', $start);
        $netApproved = substr($response, $start, $end - $start);
        $netApproved = $this->valueFilterInRetention($netApproved);
        $response = substr($response, $end + 1);

        // get Subscriptions Approved
        $start = strpos($response, 'style=');
        $start += 9;
        $end = strpos($response, '<', $start);
        $subscriptions = substr($response, $start, $end - $start);
        $response = substr($response, $end + 1);

        // get Declined
        $start = strpos($response, 'style=');
        $start += 9;
        $end = strpos($response, '<', $start);
        $declined = substr($response, $start, $end - $start);
        $declined = $this->valueFilterInRetention($declined);
        $response = substr($response, $end + 1);

        // get Void/Full Refund
        $start = strpos($response, 'style=');
        $start += 9;
        $end = strpos($response, '<', $start);
        $voidFull = substr($response, $start, $end - $start);
        $voidFull = $this->valueFilterInRetention($voidFull);
        $response = substr($response, $end + 1);

        // get Partial Refund
        $start = strpos($response, 'style=');
        $start += 9;
        $end = strpos($response, '<', $start);
        $partial = substr($response, $start, $end - $start);
        $partial = $this->valueFilterInRetention($partial);
        $response = substr($response, $end + 1);

        // get Void/Refund
        $start = strpos($response, 'style=');
        $start += 9;
        $end = strpos($response, '<', $start);
        $voidRefund = substr($response, $start, $end - $start);
        $voidRefund = $this->valueFilterInRetention($voidRefund);
        $response = substr($response, $end + 1);

        // get Canceled
        $start = strpos($response, 'style=');
        $start += 9;
        $end = strpos($response, '<', $start);
        $cancel = substr($response, $start, $end - $start);
        $cancel = $this->valueFilterInRetention($cancel);
        $response = substr($response, $end + 1);

        // get Hold
        $start = strpos($response, 'style=');
        $start += 9;
        $end = strpos($response, '<', $start);
        $hold = substr($response, $start, $end - $start);
        $hold = $this->valueFilterInRetention($hold);
        $response = substr($response, $end + 1);

        // get Pending
        $start = strpos($response, 'style=');
        $start += 9;
        $end = strpos($response, '<', $start);
        $pending = substr($response, $start, $end - $start);
        $pending = $this->valueFilterInRetention($pending);
        $response = substr($response, $end + 1);

        // get Approval Rate
        $start = strpos($response, 'style=');
        $start += 9;
        $end = strpos($response, '<', $start);
        $approvalRate = substr($response, $start, $end - $start);
        $approvalRate = $this->valueFilterInRetention($approvalRate);
        $response = substr($response, $end + 1);

        // get Net Revenue
        $start = strpos($response, 'style=');
        $start += 9;
        $end = strpos($response, '<', $start);
        $netRevenue = substr($response, $start, $end - $start);
        $netRevenue = $this->valueFilterInRetention($netRevenue);
        $response = substr($response, $end + 1);
        
        if($cycleCount == 1)//strstr($sub, 'inline-block'))
        {
            // cycle = 1           

            $report[] = array($campaignId, $campaignName, $grossOrders, $netApproved, $voidFull, $partial, $voidRefund, $approvalRate);

        }   
        else if($cycleCount == 2)
        {
            // cycle = 2            

            // get gross orders in cycle 1
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $grossOrders1 = substr($response, $start, $end - $start);
            $grossOrders1 = $this->valueFilterInRetention($grossOrders1);
            $response = substr($response, $end + 1);

            // get Net Approvded in cycle1
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $netApproved1 = substr($response, $start, $end - $start);
            $netApproved1 = $this->valueFilterInRetention($netApproved1);
            $response = substr($response, $end + 1);

            // get Declined in cycle1
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $declined1 = substr($response, $start, $end - $start);
            $declined1 = $this->valueFilterInRetention($declined1);
            $response = substr($response, $end + 1);

            // get Void/Full Refund in cycle1
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $voidFull1 = substr($response, $start, $end - $start);
            $voidFull1 = $this->valueFilterInRetention($voidFull1);
            $response = substr($response, $end + 1);

            // get Partial Refund in cycle1
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $partial1 = substr($response, $start, $end - $start);
            $partial1 = $this->valueFilterInRetention($partial1);
            $response = substr($response, $end + 1);

            // get Void/Refund Refund in cycle1
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $voidRefund1 = substr($response, $start, $end - $start);
            $voidRefund1 = $this->valueFilterInRetention($voidRefund1);
            $response = substr($response, $end + 1);

            // get Canceled Refund in cycle1
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $canceled1 = substr($response, $start, $end - $start);
            $canceled1 = $this->valueFilterInRetention($canceled1);
            $response = substr($response, $end + 1);

            // get Hold in cycle1
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $hold1 = substr($response, $start, $end - $start);
            $hold1 = $this->valueFilterInRetention($hold1);
            $response = substr($response, $end + 1);

            // get Pending in cycle1
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $pending1 = substr($response, $start, $end - $start);
            $pending1 = $this->valueFilterInRetention($pending1);
            $response = substr($response, $end + 1);

            // get Conversion in cycle1
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $conversion1 = substr($response, $start, $end - $start);
            $conversion1 = $this->valueFilterInRetention($conversion1);
            $response = substr($response, $end + 1);

            // get Net Revenue in cycle1
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $netRevenue1 = substr($response, $start, $end - $start);
            $netRevenue1 = $this->valueFilterInRetention($netRevenue1);
            $response = substr($response, $end + 1);

            $report[] = array($campaignId, $campaignName, $grossOrders, $netApproved, $voidFull, $partial, $voidRefund, $approvalRate, $grossOrders1, $netApproved1,$voidFull1, $partial1, $voidRefund1, $conversion1);
        }

        $ret['report'] = $report;
        $ret['cycle'] = $cycleCount;
        
        return $ret;
    }

    public function parseInitialReport($response, $html) {
        if(strstr($response, 'No results exist at this time'))
        {
            $ret['report'] = array();

            return $ret;
        }

        $report = array();

        $trs = array_slice($html->find('#report_8', 0)->find('tr'), 2);
        $total = end($trs);
        foreach ($trs as $tr) {
            $td = $tr->find('td');

            // get campaign id, name
            $campaign = $td[0]->plaintext;
            $start = strpos($campaign, '(', 0);
            $end = strpos($campaign, ')', $start);
            $val = substr($campaign, $start + 1, $end - $start - 1);
            $campaignId = intval(str_replace(',', '', $val));
            $campaignName = substr($campaign, $end + 2);

            // get Gross Orders
            $grossOrders = $this->valueFilterInRetention($td[1]->plaintext);
            // get Net Approved
            $netApproved = $this->valueFilterInRetention($td[2]->plaintext);
            // get Subscriptions Approved
            $subscriptions = $this->valueFilterInRetention($td[3]->plaintext);
            // get Declined
            $declined = $this->valueFilterInRetention($td[4]->plaintext);
            // get Void/Full Refund
            $voidFull = $this->valueFilterInRetention($td[5]->plaintext);
            // get Partial Refund
            $partial = $this->valueFilterInRetention($td[6]->plaintext);
            // get Void/Refund
            $voidRefund = $this->valueFilterInRetention($td[7]->plaintext);
            // get Canceled
            $cancel = $this->valueFilterInRetention($td[8]->plaintext);
            // get Hold
            $hold = $this->valueFilterInRetention($td[9]->plaintext);
            // get Pending
            $pending = $this->valueFilterInRetention($td[10]->plaintext);
            // get Approval Rate
            $approvalRate = $this->valueFilterInRetention($td[11]->plaintext);
            // get Net Revenue
            $netRevenue = $this->valueFilterInRetention($td[12]->plaintext);

            if ($td[13]->plaintext)
                $report[] = array($campaignId, $campaignName, $netApproved, $declined, $approvalRate, 'yes');
            else
                $report[] = array($campaignId, $campaignName, $netApproved, $declined, $approvalRate, 'no');
        }

        $ret['report'] = $report;

        return $ret;
    }

    public function getAffiliateLabel($allLabels, $affiliateId)
    {
        if($allLabels == null)
            return '';

        foreach ($allLabels as $value) {
            if($value[0] == $affiliateId)
                return $value[1];
        }

        return '';
    }
    public function parseRetentionByAffiliate($response, $level = '')
    {
        $start = strpos($response, 'list_header list_category');
        $end = strpos($response, '/tr', $start);
        $res = substr($response, $start, $end - $start);
        $cycleCount = substr_count($res, 'Cycle');

        $report = array();
        if ($level == '')
        {
            $dbApi = DBApi::getInstance();
            $allAffiliateLabels = $dbApi->getAllAffiliateLabels();
        }


        while (true) 
        {
            if(strstr($response, 'No results exist at this time'))
            {
                $ret['report'] = array();
                $ret['cycle'] = 0;

                return $ret;
            }   
            $tr = strstr($response, '<tr style=');
            if(!$tr)
                break;
            else
            {
                $end = strpos($tr, '/tr');
                $sub = substr($tr, 0, $end);                                
                if(strstr($sub, 'Total'))
                {                    
                    break;                    
                }
            }           
            $response = substr($tr, 13);
            // get affiliate id
            $start = strpos($response, 'style=');            
            $start += 9;
            $end = strpos($response, '<', $start);

            $val = substr($response, $start, $end - $start);
            $affiliateId = $val;
            // get affiliate label
            if ($level == '')
                $affiliateLabel = $this->getAffiliateLabel($allAffiliateLabels, $affiliateId);
            else
                $affiliateLabel = '';

            $response = substr($response, $end + 1);

            // get Gross Orders
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $grossOrders = substr($response, $start, $end - $start);
            $grossOrders = $this->valueFilterInRetention($grossOrders);
            $response = substr($response, $end + 1);

            // get Net Approved
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $netApproved = substr($response, $start, $end - $start);
            $netApproved = $this->valueFilterInRetention($netApproved);
            $response = substr($response, $end + 1);

            // get Subscriptions Approved
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $subscriptions = substr($response, $start, $end - $start);
            $subscriptions = $this->valueFilterInRetention($subscriptions);
            $response = substr($response, $end + 1);

            // get Declined
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $declined = substr($response, $start, $end - $start);
            $declined = $this->valueFilterInRetention($declined);
            $response = substr($response, $end + 1);

            // get Void/Full Refund
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $voidFull = substr($response, $start, $end - $start);
            $voidFull = $this->valueFilterInRetention($voidFull);
            $response = substr($response, $end + 1);

            // get Partial Refund
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $partial = substr($response, $start, $end - $start);
            $partial = $this->valueFilterInRetention($partial);
            $response = substr($response, $end + 1);

            // get Void/Refund
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $voidRefund = substr($response, $start, $end - $start);
            $voidRefund = $this->valueFilterInRetention($voidRefund);
            $response = substr($response, $end + 1);

            // get Canceled
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $cancel = substr($response, $start, $end - $start);
            $cancel = $this->valueFilterInRetention($cancel);
            $response = substr($response, $end + 1);

            // get Hold
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $hold = substr($response, $start, $end - $start);
            $hold = $this->valueFilterInRetention($hold);
            $response = substr($response, $end + 1);

            // get Pending
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $pending = substr($response, $start, $end - $start);
            $pending = $this->valueFilterInRetention($pending);
            $response = substr($response, $end + 1);

            // get Approval Rate
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $approvalRate = substr($response, $start, $end - $start);
            $approvalRate = $this->valueFilterInRetention($approvalRate);
            $response = substr($response, $end + 1);

            // get Net Revenue
            $start = strpos($response, 'style=');
            $start += 9;
            $end = strpos($response, '<', $start);
            $netRevenue = substr($response, $start, $end - $start);
            $netRevenue = $this->valueFilterInRetention($netRevenue);
            $response = substr($response, $end + 1);
            
            $start = strpos($response, '<td');
            $end = strpos($response, '/td', $start);
            $sub = substr($response, $start, $end - $start);
 
            if($cycleCount == 1)
            {
                // cycle = 1                
                if(strpos($sub, 'Sub-Affiliate ID'))
                    $report[] = array($affiliateId, $affiliateLabel, $grossOrders, $netApproved, $voidFull, $partial, $voidRefund, $approvalRate, 'yes');
                else
                    $report[] = array($affiliateId, $affiliateLabel, $grossOrders, $netApproved, $voidFull, $partial, $voidRefund, $approvalRate, 'no');                
                
            }   
            else if($cycleCount == 2)
            {
                // cycle = 2;                

                // get gross orders in cycle 1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $grossOrders1 = substr($response, $start, $end - $start);
                $grossOrders1 = $this->valueFilterInRetention($grossOrders1);
                $response = substr($response, $end + 1);

                // get Net Approvded in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $netApproved1 = substr($response, $start, $end - $start);
                $netApproved1 = $this->valueFilterInRetention($netApproved1);
                $response = substr($response, $end + 1);

                // get Declined in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $declined1 = substr($response, $start, $end - $start);
                $declined1 = $this->valueFilterInRetention($declined1);
                $response = substr($response, $end + 1);

                // get Void/Full Refund in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $voidFull1 = substr($response, $start, $end - $start);
                $voidFull1 = $this->valueFilterInRetention($voidFull1);
                $response = substr($response, $end + 1);

                // get Partial Refund in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $partial1 = substr($response, $start, $end - $start);
                $partial1 = $this->valueFilterInRetention($partial1);
                $response = substr($response, $end + 1);

                // get Void/Refund Refund in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $voidRefund1 = substr($response, $start, $end - $start);
                $voidRefund1 = $this->valueFilterInRetention($voidRefund1);
                $response = substr($response, $end + 1);

                // get Canceled Refund in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $canceled1 = substr($response, $start, $end - $start);
                $canceled1 = $this->valueFilterInRetention($canceled1);
                $response = substr($response, $end + 1);

                // get Hold in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $hold1 = substr($response, $start, $end - $start);
                $hold1 = $this->valueFilterInRetention($hold1);
                $response = substr($response, $end + 1);

                // get Pending in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $pending1 = substr($response, $start, $end - $start);
                $pending1 = $this->valueFilterInRetention($pending1);
                $response = substr($response, $end + 1);

                // get Conversion in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $conversion1 = substr($response, $start, $end - $start);
                $conversion1 = $this->valueFilterInRetention($conversion1);
                $response = substr($response, $end + 1);

                // get Net Revenue in cycle1
                $start = strpos($response, 'style=');
                $start += 9;
                $end = strpos($response, '<', $start);
                $netRevenue1 = substr($response, $start, $end - $start);
                $netRevenue1 = $this->valueFilterInRetention($netRevenue1);
                $response = substr($response, $end + 1);

                $start = strpos($response, '<td');
                $end = strpos($response, '/td', $start);
                $sub = substr($response, $start, $end - $start);

                if(strpos($sub, 'Sub-Affiliate ID'))
                    $report[] = array($affiliateId, $affiliateLabel,$grossOrders, $netApproved, $voidFull, $partial, $voidRefund, $approvalRate, $grossOrders1, $netApproved1,$voidFull1, $partial1, $voidRefund1, $conversion1, 'yes');
                else
                    $report[] = array($affiliateId, $affiliateLabel, $grossOrders, $netApproved, $voidFull, $partial, $voidRefund, $approvalRate, $grossOrders1, $netApproved1,$voidFull1, $partial1, $voidRefund1, $conversion1, 'no');   
                
            }
            
        }      
        $report = array_reverse($report);
        $ret['report'] = $report;
        $ret['cycle'] = $cycleCount;
        
        return $ret;
    }
    public function parseRetentionBySubAffiliate($response)
    {
        return $this->parseRetentionByAffiliate($response, 'sub-affiliate');
    }
    public function valueFilterInRetention($value)
    {
        $value = str_replace('$', '', $value);
        $value = str_replace('%', '', $value);
        $value = str_replace(',', '', $value);
        return $value;
    }

    public function writeQuickRetentionByCrm($userToken, $crmID, $response, $delete, $cycle)
    {
        $dbApi = DBApi::getInstance();
        if($delete == 1)
            $dbApi->deleteRetentionQuickByCrmId($userToken, $crmID);

        $crmName = $dbApi->getCrmName($crmID);
        $campaignLabeling = $dbApi->getCampaignLabelingByCrmId($crmID);

        
        if($response['cycle'] == 0)
            return ;

        foreach ($response['report'] as $campaignInfo)
        {            
            if($campaignInfo[0] != -1)
            {
                $campaignId = $campaignInfo[0];
                $campaignName = $campaignInfo[1];

                $campaignLabelIds = "";
                foreach ($campaignLabeling as $labeling) 
                {
                    if($labeling[0] == $campaignId)
                    {
                        $campaignLabelIds = $labeling[1];
                        break;
                    }    

                }
                $campaignLabel = "";
                if($campaignLabelIds != "")
                    $campaignLabel = $dbApi->getCampaignLabelingFromIds($campaignLabelIds);

                                
                if($cycle == 1)
                {   
                     $dbApi->writeRetentionQuickExport($crmID, $crmName, $campaignId, $campaignName, $campaignLabel, '', '', '', '',$campaignInfo[2], $campaignInfo[3], $campaignInfo[4], $campaignInfo[5], $campaignInfo[6], $campaignInfo[7], '', '', '', '', '', '', $userToken);
                }
                else 
                {
                   $dbApi->writeRetentionQuickExport($crmID, $crmName, $campaignId, $campaignName, $campaignLabel, '', '', '', '', $campaignInfo[2], $campaignInfo[3], $campaignInfo[4], $campaignInfo[5], $campaignInfo[6], $campaignInfo[7], $campaignInfo[8], $campaignInfo[9], $campaignInfo[10], $campaignInfo[11], $campaignInfo[12], $campaignInfo[13], $userToken);    
                }

            }
        }        
    }
    /*  class: export retention apis
    *   actions: pull retention page by crm id, store campaign report data to db
    *   return : campaign info (campaign id, campaign name, has affiliate)
    */
    public function exportRetentionCampaign($token, $crmID, $fromDate, $toDate, $cycle, $userToken, $delete)
    {
        $dbApi = DBApi::getInstance();

        if($delete == 1)
            $dbApi->deleteRetentionExportByCrmId($userToken, $crmID);

        $response = $this->getRetentionReport($token, $fromDate, $toDate, $cycle);
        $result = $this->parseRetentionByCampaign($response);

        $crmName = $dbApi->getCrmName($crmID);

        $campaignLabeling = $dbApi->getCampaignLabelingByCrmId($crmID);        
        $ret = array();
        if($result['cycle'] == 0)
            return $ret;

        foreach ($result['report'] as $campaignInfo)
        {            
            if($campaignInfo[0] != -1)
            {
                $campaignId = $campaignInfo[0];
                $campaignName = $campaignInfo[1];

                $campaignLabelIds = "";
                foreach ($campaignLabeling as $labeling) 
                {
                    if($labeling[0] == $campaignId)
                    {
                        $campaignLabelIds = $labeling[1];
                        break;
                    }    

                }
                $campaignLabel = "";
                if($campaignLabelIds != "")
                    $campaignLabel = $dbApi->getCampaignLabelingFromIds($campaignLabelIds);

                                
                if($cycle == 1)
                {
                    $ret[] = array($campaignId, $campaignName, $campaignInfo[8]);
                    
                     $dbApi->writeRetentionExport($crmID, $crmName, $campaignId, $campaignName, $campaignLabel, '', '', '', '',$campaignInfo[2], $campaignInfo[3], $campaignInfo[4], $campaignInfo[5], $campaignInfo[6], $campaignInfo[7], '', '', '', '', '', '', $userToken);
                }
                else 
                {                    
                    $ret[] = array($campaignId, $campaignName, $campaignInfo[14]);                    
                     $dbApi->writeRetentionExport($crmID, $crmName, $campaignId, $campaignName, $campaignLabel, '', '', '', '', $campaignInfo[2], $campaignInfo[3], $campaignInfo[4], $campaignInfo[5], $campaignInfo[6], $campaignInfo[7], $campaignInfo[8], $campaignInfo[9], $campaignInfo[10], $campaignInfo[11], $campaignInfo[12], $campaignInfo[13], $userToken);    
                }

            }
        }
        return $ret;
    }
    public function writeRetentionQuickByCampaign($crmID, $campaignID, $response, $cycle, $userToken, $delete)
    {
        $dbApi = DBApi::getInstance();

        if($delete == 1)
            $dbApi->deleteRetentionQuickByCampaignId($userToken, $crmID, $campaignID);       
        
        
        if($response['cycle'] == 0)
            return;
        foreach ($response['report'] as $affiliateInfo)
        {            
            
            $affiliateId = $affiliateInfo[0];
            $affiliateLabel = $affiliateInfo[1];
                            
            if($cycle == 1)
            {   
                 $dbApi->writeRetentionQuickExport($crmID, '', $campaignID,'', '', $affiliateId, $affiliateLabel, '', '',  $affiliateInfo[2], $affiliateInfo[3], $affiliateInfo[4], $affiliateInfo[5], $affiliateInfo[6], $affiliateInfo[7], '', '', '', '', '', '', $userToken);
            }
            else 
            {   
                 $dbApi->writeRetentionQuickExport($crmID, '', $campaignID, '', '', $affiliateId, $affiliateLabel, '', '', $affiliateInfo[2], $affiliateInfo[3], $affiliateInfo[4], $affiliateInfo[5], $affiliateInfo[6], $affiliateInfo[7], $affiliateInfo[8], $affiliateInfo[9], $affiliateInfo[10], $affiliateInfo[11], $affiliateInfo[12], $affiliateInfo[13], $userToken);    
            }            
        }
        return ;
        

    }
    /*  class: export retention apis
    *   actions: pull affiliate page by campaign id , store affiliate report data to db
    *   return : 
    */
    public function exportRetentionAffiliate($token, $crmID, $campaignID, $fromDate, $toDate, $cycle, $userToken, $delete)
    {
        $dbApi = DBApi::getInstance();

        if($delete == 1)
            $dbApi->deleteRetentionExportByCampaignId($userToken, $crmID, $campaignID);

        $response = $this->getRetentionReportByCampaign($token, $fromDate, $toDate , $cycle, $campaignID);               
        $result = $this->parseRetentionByAffiliate($response);
        
        $ret = array();
        if($result['cycle'] == 0)
            return $ret;
        foreach ($result['report'] as $affiliateInfo)
        {            
            
            $affiliateId = $affiliateInfo[0];
            $affiliateLabel = $affiliateInfo[1];
                            
            if($cycle == 1)
            {
                $ret[] = array($affiliateId, $affiliateLabel, $affiliateInfo[8]);
                
                 $dbApi->writeRetentionExport($crmID, '', $campaignID,'', '', $affiliateId, $affiliateLabel, '', '',  $affiliateInfo[2], $affiliateInfo[3], $affiliateInfo[4], $affiliateInfo[5], $affiliateInfo[6], $affiliateInfo[7], '', '', '', '', '', '', $userToken);
            }
            else 
            {                    
                $ret[] = array($affiliateId, $affiliateLabel, $affiliateInfo[14]);                    
                 $dbApi->writeRetentionExport($crmID, '', $campaignID, '', '', $affiliateId, $affiliateLabel, '', '', $affiliateInfo[2], $affiliateInfo[3], $affiliateInfo[4], $affiliateInfo[5], $affiliateInfo[6], $affiliateInfo[7], $affiliateInfo[8], $affiliateInfo[9], $affiliateInfo[10], $affiliateInfo[11], $affiliateInfo[12], $affiliateInfo[13], $userToken);    
            }
            
        }
        return $ret;
        

    }
    public function writeRetentionQuickByAffiliate($crmID, $campaignID, $affiliateID, $response, $cycle, $userToken, $delete)
    {
        $dbApi = DBApi::getInstance();

        if($delete == 1)
            $dbApi->deleteRetentionQuickByAffiliateId($userToken, $crmID, $campaignID, $affiliateID);

        if($response['cycle'] == 0)
            return ;
        foreach ($response['report'] as $subAffiliateInfo)
        {            
            
            $subAffiliateId = $subAffiliateInfo[0];
            $subAffiliateLabel = $subAffiliateInfo[1];
                            
            if($cycle == 1)
            {
                 $dbApi->writeRetentionQuickExport($crmID,'', $campaignID,'', '', $affiliateID, '',  $subAffiliateId, $subAffiliateLabel, $subAffiliateInfo[2], $subAffiliateInfo[3], $subAffiliateInfo[4], $subAffiliateInfo[5], $subAffiliateInfo[6], $subAffiliateInfo[7], '', '', '', '', '', '', $userToken);
            }
            else 
            {
                 $dbApi->writeRetentionQuickExport($crmID, '',$campaignID, '', '', $affiliateID, '',$subAffiliateId, $subAffiliateLabel, $subAffiliateInfo[2], $subAffiliateInfo[3], $subAffiliateInfo[4], $subAffiliateInfo[5], $subAffiliateInfo[6], $subAffiliateInfo[7], $subAffiliateInfo[8], $subAffiliateInfo[9], $subAffiliateInfo[10], $subAffiliateInfo[11], $subAffiliateInfo[12], $subAffiliateInfo[13], $userToken);    
            }
            
        }
    }
    /*  class: export retention apis
    *   actions: pull sub affiliate page by affiliate id , store sub affiliate report data to db
    *   return : 
    */
    public function exportRetentionSubAffiliate($token, $crmID, $campaignID, $affiliateID, $fromDate, $toDate, $cycle, $userToken, $delete)
    {
        $dbApi = DBApi::getInstance();

        if($delete == 1)
            $dbApi->deleteRetentionExportByAffiliateId($userToken, $crmID, $campaignID, $affiliateID);

        $response = $this->getRetentionReportByAffiliate($token, $fromDate, $toDate , $cycle, $campaignID, $affiliateID);      
        $result = $this->parseRetentionBySubAffiliate($response);

        $ret = array();
        if($result['cycle'] == 0)
            return $ret;
        foreach ($result['report'] as $subAffiliateInfo)
        {            
            
            $subAffiliateId = $subAffiliateInfo[0];
            $subAffiliateLabel = $subAffiliateInfo[1];
                            
            if($cycle == 1)
            {
                $ret[] = array($subAffiliateId, $subAffiliateLabel, $subAffiliateInfo[8]);
                
                 $dbApi->writeRetentionExport($crmID,'', $campaignID,'', '', $affiliateID, '',  $subAffiliateId, $subAffiliateLabel, $subAffiliateInfo[2], $subAffiliateInfo[3], $subAffiliateInfo[4], $subAffiliateInfo[5], $subAffiliateInfo[6], $subAffiliateInfo[7], '', '', '', '', '', '', $userToken);
            }
            else 
            {                    
                $ret[] = array($subAffiliateId, $subAffiliateLabel, $subAffiliateInfo[14]);

                 $dbApi->writeRetentionExport($crmID, '',$campaignID, '', '', $affiliateID, '',$subAffiliateId, $subAffiliateLabel, $subAffiliateInfo[2], $subAffiliateInfo[3], $subAffiliateInfo[4], $subAffiliateInfo[5], $subAffiliateInfo[6], $subAffiliateInfo[7], $subAffiliateInfo[8], $subAffiliateInfo[9], $subAffiliateInfo[10], $subAffiliateInfo[11], $subAffiliateInfo[12], $subAffiliateInfo[13], $userToken);    
            }
            
        }
        return $ret;
        

    }
    
}

?>