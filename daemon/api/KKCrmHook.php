<?php

require_once 'DBApi.php';


class KKCrmHook
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
        $debugFile = '../../logs/kk_debug.log';
        $handle = fopen($debugFile, "a");
        fwrite($handle, $data . "\n");
        fclose($handle);
    }

    public function getSecurityToken()
    {
        if ($this->crmUrl == '') return self::ERROR;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, $this->crmUrl.'/');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'user-agent:Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
            'upgrade-insecure-requests:1')
        );
        curl_setopt($curl, CURLOPT_HEADER, true);

        $response = curl_exec($curl);

        // check cookie from response header
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $data = $this->getHttpHeader2Array($header);

        $token_pos = strpos($response, 'crmid=');
        if ($token_pos === false) return self::ERROR;
        $end_pos = strpos($response, ";", $token_pos);
        if ($end_pos === false) return self::ERROR;

        $token = substr($response, $token_pos, $end_pos - $token_pos);

        curl_close($curl);

        return $token;
    }

    public function login($crmID, $crmUrl, $userName, $password)
    {
        $this->crmID = $crmID;
        $this->crmUrl = $crmUrl;
        $this->userName = $userName;
        $this->password = $password;

        $dbApi = DBApi::getInstance();
        $tokenInfo = $dbApi->getKKCrmToken($crmID);

        if ($tokenInfo == null)
        {
            $this->crmToken = '';
        }
        else
        {
            $token = $tokenInfo[0];
            $timestamp = $tokenInfo[1];

            $this->crmToken = $token;

            if ((time() - $timestamp) <= 840)   // valid token
            {
                $dbApi->addKKCrmToken($crmID, $token, time());
                return $token;
            }
        }

        $token = $this->getSecurityToken();

        if (!isset($token))
            return self::ERROR;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $crmUrl.'/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'userName='.$userName.'&password='.urlencode($password).'&processLogin=1',
            CURLOPT_HTTPHEADER => array(
                'content-type: application/x-www-form-urlencoded',
                'cookie: '.$token,
                'origin: '.$crmUrl,
                'referer: '.$crmUrl.'/',
                'upgrade-insecure-requests: 1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'                
            )
        ));

        $response = curl_exec($curl);

        // check cookie from response header
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $data = $this->getHttpHeader2Array($header);

        $token_pos = strpos($response, 'crmid=');
        if ($token_pos === false) return self::ERROR;
        $end_pos = strpos($response, ";", $token_pos);
        if ($end_pos === false) return self::ERROR;

        $newToken = substr($response, $token_pos, $end_pos - $token_pos);

        curl_close($curl);

        $this->crmToken = $newToken;
        $dbApi->addKKCrmToken($crmID, $newToken, time());
        
        return $newToken;
    }

    public function getOrderSummaryReport($token, $fromDate, $toDate, $productID, $affiliateID)
    {
        if ($token == '' || $token == null) return self::ERROR;
        
        $this->crmToken = $token;

        $curl = curl_init();

        // get Order Summary Report Page
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.'/reports/order-summary/',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'cookie: '.$this->crmToken,
                'referer: '.$this->crmUrl.'/reports/',
                'upgrade-insecure-requests: 1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);


        // parsing for csrfToken and companyId
        // {'csrfToken':'1b80cfd3bb93767fd287bf153ba4274b5758e6a0', 'currentCompanyId':'705', 'sessionTimeoutMinutes':'60', 'sessionTimeoutWarningMinutesLeft':'15'}
        $pos1 = strpos($response, 'csrfToken');
        if ($pos1 === false) return self::ERROR;
        $pos2 = strpos($response, ',', $pos1);
        if ($pos2 === false) return self::ERROR;
        $csrfToken = substr($response, $pos1 + 12, $pos2 - $pos1 - 13);

        $pos1 = strpos($response, 'currentCompanyId');
        if ($pos1 === false) return self::ERROR;
        $pos2 = strpos($response, ',', $pos1);
        if ($pos2 === false) return self::ERROR;
        $companyId = substr($response, $pos1 + 19, $pos2 - $pos1 - 19);

        return $this->ajaxOrderSummaryReport($csrfToken, $companyId, $fromDate, $toDate, $productID, $affiliateID);
    }

    private function ajaxOrderSummaryReport($csrfToken, $companyId, $fromDate, $toDate, $productID, $affiliateID)
    {
        if ($csrfToken == '' || $csrfToken == null || $companyId == null) 
            return self::ERROR;

        $productParam = ($productID == 0) ? '' : '&productId='.$productID;
        $affiliateParam = ($affiliateID == 0) ? '' : '&affiliateId='.$affiliateID;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.'/reports/order-summary/getTable.ajax.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'query=1&page=1&sortDir=0&dateRangeType=dateCreated&reportType=campaign&startDateTime='.$fromDate.'&endDateTime='.$toDate.$productParam.$affiliateParam,
            CURLOPT_HTTPHEADER => array(
                'content-type: application/x-www-form-urlencoded',
                'cookie: '.$this->crmToken,
                'origin: '.$this->crmUrl,
                'referer: '.$this->crmUrl.'/reports/order-summary/',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
                'X-COMPANY-ID: '.$companyId,
                'X-CSRF-Token: '.$csrfToken
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function getRetentionReport($token, $fromDate, $toDate, $cycles, $productID, $affiliateID)
    {
        if ($token == '' || $token == null) return self::ERROR;
        
        $this->crmToken = $token;

        $curl = curl_init();

        // get Retention Report Page
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.'/reports/retention/',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'cookie: '.$this->crmToken,
                'referer: '.$this->crmUrl.'/reports/',
                'upgrade-insecure-requests: 1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);


        // parsing for csrfToken and companyId
        // {'csrfToken':'1b80cfd3bb93767fd287bf153ba4274b5758e6a0', 'currentCompanyId':'705', 'sessionTimeoutMinutes':'60', 'sessionTimeoutWarningMinutesLeft':'15'}
        $pos1 = strpos($response, 'csrfToken');
        if ($pos1 === false) return self::ERROR;
        $pos2 = strpos($response, ',', $pos1);
        if ($pos2 === false) return self::ERROR;
        $csrfToken = substr($response, $pos1 + 12, $pos2 - $pos1 - 13);

        $pos1 = strpos($response, 'currentCompanyId');
        if ($pos1 === false) return self::ERROR;
        $pos2 = strpos($response, ',', $pos1);
        if ($pos2 === false) return self::ERROR;
        $companyId = substr($response, $pos1 + 19, $pos2 - $pos1 - 19);

        return $this->ajaxRetentionReport($csrfToken, $companyId, $fromDate, $toDate, $cycles, $productID, $affiliateID);
    }

    private function ajaxRetentionReport($csrfToken, $companyId, $fromDate, $toDate, $cycles, $productID, $affiliateID)
    {
        if ($csrfToken == '' || $csrfToken == null || $companyId == null) 
            return self::ERROR;

        $productParam = ($productID == 0) ? '' : '&productId='.$productID;
        $affiliateParam = ($affiliateID == 0) ? '' : '&affiliateId='.$affiliateID;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.'/reports/retention/getTable.ajax.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'query=1&page=1&resultsPerPage=25&dateRangeType=dateCreated&reportType=campaign&startDateTime='.$fromDate.'&endDateTime='.$toDate.$productParam.$affiliateParam.'&maxCycles='.$cycles,
            CURLOPT_HTTPHEADER => array(
                'content-type: application/x-www-form-urlencoded',
                'cookie: '.$this->crmToken,
                'origin: '.$this->crmUrl,
                'referer: '.$this->crmUrl.'/reports/retention/',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
                'X-COMPANY-ID: '.$companyId,
                'X-CSRF-Token: '.$csrfToken
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
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

    private function filterValue($value, $cols)
    {
        $needle = '';
        if (strstr($value, '<span'))
        {
            $needle = '<span';

        } else if (strstr($value, '<b'))
        {
            $needle = '<br';
        }
        if ($needle != '')
        {
            $start_pos = strpos($value, $needle);
            $start_pos = strpos($value, '>', $start_pos) + 1;
            $end_pos = strpos($value, '<', $start_pos);
            $value = substr($value, $start_pos, $end_pos - $start_pos);
        }
        if ($cols != 0)
        {
            if (strstr($value, '%'))
                $value = str_replace('%', '', $value);
            if (strstr($value, ','))
                $value = str_replace(',', '', $value);
            if (strstr($value, '$'))
                $value = str_replace('$', '', $value);
        }

        return $value;
    }

    function parseOrderSummaryReport($response, $campaignIDs, $campaignNames)
    {
        if (strstr($response, 'No results found.'))
            return 'No results found.';

        $campaignNames = explode('::', $campaignNames);
        $error_msg = array('parsing error');
        /*
         *  parse table header
        */
        $cols = 0;
        $headers = array();
        $needle = '<\/thead>';
        $header_end_pos = strpos($response, $needle);
        if (!$header_end_pos)
        {
            return $error_msg;
        }

        while (true)
        {
            $needle = 'ajaxSubmit();\">'; // this needle only appears in table header part.
            $start_pos = strpos($response, $needle);
            if (!$start_pos)
            {
                break;
            }
            $start_pos = $start_pos + strlen($needle) + 2;
            $needle = '<\/thead>';
            $end_pos = strpos($response, $needle, $start_pos);
            if (!$end_pos)
                break;
            $needle = '<\/th>';
            $end_pos = strpos($response, $needle, $start_pos);
            if (!$end_pos)
                break;
            $header = substr($response, $start_pos, $end_pos - $start_pos);
            $headers[$cols] = $header;
            $cols ++;
            $response = substr($response, $end_pos + strlen($needle));
        }
        /*
         * parse rows
         */
        $table = array();
        $rows = 0;

        while (true)
        {
            $needle = '<tr';
            $start_pos = strpos($response, $needle);
            if (!$start_pos)
            {
                break;
            }
            $needle = '<\/tr>';
            $end_pos = strpos($response, $needle, $start_pos);
            if (!$end_pos)
                break;
            $tr = substr($response, $start_pos, $end_pos - $start_pos);
            $row = array();
            $valid_row = false;
            for ($i = 0; $i < count($headers); $i++) {
                $needle = '<td';
                $td_start_pos = strpos($tr, $needle);
                if (!$td_start_pos)
                    break;
                $td_start_pos = strpos($tr, '>', $td_start_pos + strlen($needle)) + 1;
                $needle = '<\/td>';
                $td_end_pos = strpos($tr, $needle, $td_start_pos);
                if (!$td_end_pos)
                    break;
                $td = substr($tr, $td_start_pos, $td_end_pos - $td_start_pos);

                $td = self::filterValue($td, $i);
                if ($i == 0) {
                    if (strstr($td, 'Total'))
                        break;
                    if (count($campaignNames) > 1 &&!in_array($td, $campaignNames))
                        break;
                }
                $valid_row = true;
                $row[] = $td;
                $tr = substr($tr, $td_end_pos + strlen($needle));
            }
            if ($valid_row){
                $table[$rows] = $row;
                $rows ++;
            }

            $needle = '<\/tr>';
            $response = substr($response, $end_pos + strlen($needle) - 1);
        }

        return $table;
    }

    function parseRetentionReport($response, $campaignIDs, $campaignNames)
    {
        $campaignIDs = explode(',', $campaignIDs);

        if (strstr($response, 'No results found.'))
            return 'No results found.';
        
        $error_msg = array('parsing error');

        /*
         *  parse cycle count from table header
        */
        $cycle = 0;

        $needle = '<thead';
        $start_pos = strpos($response, $needle);
        $needle = '<tr';
        $start_pos = strpos($response, $needle, $start_pos);
        $needle = '<\/tr>';
        $end_pos = strpos($response, $needle, $start_pos);
        while (true)
        {
            $needle = '<\/td>';
            $start_pos = strpos($response, $needle, $start_pos);
            if (!$start_pos)
                break;
            if ($start_pos > $end_pos)
                break;
            $cycle ++;
            $start_pos += strlen($needle);
        }

        $cycle -= 2; // due to first blank and last total

        /*
         * parse columns from table header
         */
        $headers = array();

        $needle = '<td';
        $start_pos = strpos($response, $needle, $end_pos);
        $needle = '<\/tr>';
        $tr_end_pos = strpos($response, $needle, $start_pos);
        while (true)
        {
            $needle = '>';
            $start_pos = strpos($response, $needle, $start_pos) + strlen($needle);
            if (!$start_pos)
                break;
            $needle = '<\/td>';
            $end_pos = strpos($response, $needle, $start_pos);
            if ($end_pos > $tr_end_pos)
                break;
            $td = substr($response, $start_pos, $end_pos - $start_pos);
            $headers[] = self::filterValue1($td);
            $needle = '<td';
            $start_pos = strpos($response, $needle, $end_pos);
        }
        /*
         * parse rows from table body
         */
        $table = array();

        $tr_start_pos = $tr_end_pos;
        while (true)
        {
            if ($tr_start_pos == -1)
                break;

            $needle = '<tr class=';
            $tr_start_pos = strpos($response, $needle, $tr_start_pos);
            if (!$tr_start_pos)
                break;
            $tr_end_pos = strpos($response, $needle, $tr_start_pos + strlen($needle));
            if (!$tr_end_pos)
            {
                $tr = substr($response, $tr_start_pos);
                $tr_end_pos = -1;
            }
            else
                $tr = substr($response, $tr_start_pos, $tr_end_pos - $tr_start_pos);

            $rows = array();
            $start_pos = 0;
            $i = 0;
            $valid_row = false;
            while (true)
            {
                if ($start_pos == -1)
                    break;
                $needle = '<td ';
                $start_pos = strpos($tr, $needle, $start_pos);
                if (!$start_pos)
                    break;
                $end_pos = strpos($tr, $needle, $start_pos + strlen($needle));
                if (!$end_pos)
                {
                    $td = substr($tr, $start_pos);
                    $end_pos = -1;
                } else
                    $td = substr($tr, $start_pos, $end_pos - $start_pos);
                if ($i == 0)
                {
                    $needle = 'showCampSubRow1(this,'.$cycle.',';
                    $campaign_start_pos = strpos($td, $needle);
                    if ($campaign_start_pos)
                    {
                        $campaign_start_pos += strlen($needle);
                        $needle = ')';
                        $campaign_end_pos = strpos($td, $needle, $campaign_start_pos);
                        $campaign_id = substr($td, $campaign_start_pos, $campaign_end_pos - $campaign_start_pos);
                        if (count($campaignIDs) > 1 && !in_array($campaign_id, $campaignIDs)) {
                            break;
                        }
                        $valid_row = true;
                        $rows[] = $campaign_id;
                    }
                    else
                    {
//                        $needle = 'Total';
//
//                        if (strstr($td, $needle))
//                        {
//                            $rows[] = '-1';
//                        }
                    }
                }
                $rows[] = self::filterValue1($td, $i);
                $i ++;
                $start_pos = $end_pos;
            }
            if ($valid_row)
                $table[] = $rows;

            $tr_start_pos = $tr_end_pos;
        }
        return array("cycle" =>$cycle, "report" =>$table);
    }
    private function filterValue1($value, $i = -1)
    {
        while (true) {
            $pos = strpos($value, "'>");
            if (!$pos)
                break;
            $value = substr($value, $pos + strlen("'>"));
        }
        if ($i == 0) {
            $value = str_replace("<\/div>", '', $value);
            $value = str_replace("<b>", '', $value);
        }
        if ($i != -1) {
            $pos = strpos($value, '<\/');
            $value = substr($value, 0, $pos);
        }
        if ($i > 0) {
            if (strstr($value, '%'))
                $value = str_replace('%', '', $value);
            if (strstr($value, ','))
                $value = str_replace(',', '', $value);
            if (strstr($value, '$'))
                $value = str_replace('$', '', $value);
        }
        $value = ltrim($value);
        $value = rtrim($value);

        return $value;
    }

    public function getProductAffiliateList($token)
    {
        if ($token == '' || $token == null) return self::ERROR;
        
        $this->crmToken = $token;

        $curl = curl_init();

        // get Order Summary Report Page
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->crmUrl.'/reports/order-summary/',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'cookie: '.$this->crmToken,
                'referer: '.$this->crmUrl.'/reports/',
                'upgrade-insecure-requests: 1',
                'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36'
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        
        // parsing for products
        // <select name="productId" id="productId" class="form-control select2-hidden-accessible" tabindex="-1" aria-hidden="true"></select>
        $pos1 = strpos($response, "<select name='productId' id='productId'");
        if ($pos1 === false) return self::ERROR;
        $pos2 = strpos($response, "</select>", $pos1);
        if ($pos2 === false) return self::ERROR;
        $products = substr($response, $pos1, $pos2 - $pos1 + 9);

        $productList = $this->parseProducts($products);


        // parsing for affiliates
        // <select name="affiliateId" id="affiliateId" class='form-control' onchange=\"showPubInput()\"></select>
        $pos1 = strpos($response, "<select name='affiliateId' id='affiliateId'");
        if ($pos1 === false) return self::ERROR;
        $pos2 = strpos($response, "</select>", $pos1);
        if ($pos2 === false) return self::ERROR;
        $affiliates = substr($response, $pos1, $pos2 - $pos1 + 9);

        $affiliateList = $this->parseAffiliates($affiliates);

        $allList = array($productList, $affiliateList);

        return $allList;
    }


    public function parseProducts($response)
    {
        $products = array();
        $start_pos = 0;
        while (true)
        {
            $needle = "<option value='";
            $start_pos = strpos($response, $needle, $start_pos);
            if (!$start_pos)
                break;
            $start_pos += strlen($needle);
            $needle = "'>";
            $end_pos = strpos($response, $needle, $start_pos);
            if (!$end_pos)
                break;
            $id = substr($response, $start_pos, $end_pos - $start_pos);
            if ($id != '')
            {
                $end_pos += strlen($needle);
                $start_pos = $end_pos;
                $needle = "</option>";
                $end_pos = strpos($response, $needle,$start_pos);
                $name = substr($response, $start_pos, $end_pos - $start_pos);
                $name = self::filterValue2($name);
                $products[] = array($id, $name);
            }
            $start_pos = $end_pos;
        }

        return $products;
    }
    public function parseAffiliates($response)
    {
        $affiliates = array();
        $start_pos = 0;
        while (true)
        {
            $needle = "<option value='";
            $start_pos = strpos($response, $needle, $start_pos);
            if (!$start_pos)
                break;
            $start_pos += strlen($needle);
            $needle = "'>";
            $end_pos = strpos($response, $needle, $start_pos);
            if (!$end_pos)
                break;
            $id = substr($response, $start_pos, $end_pos - $start_pos);
            if ($id != '')
            {
                $end_pos += strlen($needle);
                $start_pos = $end_pos;
                $needle = "</option>";
                $end_pos = strpos($response, $needle,$start_pos);
                $name = substr($response, $start_pos, $end_pos - $start_pos);
                $name = self::filterValue2($name);
                $affiliates[] = array($id, $name);
            }
            $start_pos = $end_pos;
        }

        return $affiliates;
    }
    private function filterValue2($name)
    {
        $needle = ') ';
        $start_pos = strpos($name, $needle);
        if ($start_pos)
        {
            $start_pos += strlen($needle);
            $name = substr($name, $start_pos);
        }

        ltrim($name);
        rtrim($name);

        return $name;
    }
    public function getCrmSalesBreakDown($token, $fromDate, $toDate, $crmID)
    {
        $response = $this->getOrderSummaryReport($token, $fromDate, $toDate, 0, 0);
        $response = $this->parseOrderSummaryReport($response, '', '');

        $step1 = 0;
        $step2 = 0;
        $decline = 0;
        $takeRate = 0;
        $tablet = 0;
        $tablet_percent = 0;
        $prepaid = 0;
        $sales_percent = '0.00';
        $decline_percent = '0.00';

        $dbApi = DBApi::getInstance();
        $campaignList = $dbApi->getKKCrmCampaignCategoryList($crmID);
        $regularCampaigns = isset($campaignList[0]) ? $campaignList[0][3] : null;
        $regularCampaigns = explode('::', $regularCampaigns);
        $prepaidCampaigns = isset($campaignList[1]) ? $campaignList[1][3] : null;
        $prepaidCampaigns = explode('::', $prepaidCampaigns);
        $tabletCampaigns = isset($campaignList[2]) ? $campaignList[2][3] : null;
        $tabletCampaigns = explode('::', $tabletCampaigns);

        $ret = array($step1, $step2, $takeRate, $tablet, $tablet_percent, $prepaid, $sales_percent, $decline_percent);

        foreach ($response as $row) {
            if (in_array($row[0], $regularCampaigns)) {
                $step1 += isset($row[5])? $row[5] : 0;
                $step2 += isset($row[8])? $row[8] : 0;
                $decline += isset($row[3])? $row[3] : 0;
            } elseif (in_array($row[0], $tabletCampaigns)) {
                $tablet += isset($row[5])? $row[5] : 0;
            } elseif (in_array($row[0], $prepaidCampaigns)) {
                $prepaid += (isset($row[5])? $row[5] : 0) + (isset($row[8])? $row[8] : 0);
            }
        }

        $sales_percent = ($decline + $step1) == 0 ? '0.00' : number_format(($step1 / (float)($decline + $step1) * 100), 2, '.', '');
        $decline_percent = ($decline + $step1) == 0 ? '0.00' : number_format(($decline / (float)($decline + $step1) * 100), 2, '.', '');
        $takeRate = $step1 == 0 ? '0.00' : number_format(($step2 / (float)$step1 * 100.0), 2, '.', '');
        $tablet_percent = ($step2 + $tablet) == 0 ? '0.00' : number_format(($tablet / ($tablet + $step2) * 100), 2, '.', '');

        $ret[0] = $step1;
        $ret[1] = $step2;
        $ret[2] = $takeRate;
        $ret[3] = $tablet;
        $ret[4] = $tablet_percent;
        $ret[5] = $prepaid;
        $ret[6] = $sales_percent;
        $ret[7] = $decline_percent;

        return $ret;

    }
}