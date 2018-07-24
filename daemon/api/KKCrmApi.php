<?php

require_once 'DBApi.php';
require_once dirname(dirname(dirname(__FILE__))).'/lib/konnektive/vendor/autoload.php';

use Illuminate\Validation\ValidationException;
use Konnektive\Dispatcher;
use Konnektive\Request\Report\QueryCampaignRequest;

class KKCrmApi {

	protected static $instance;

	public $apiUserName;
	public $apiPassword;

    const ERROR = 'error';

	public static function getInstance()
    {
		
		if( is_null( static::$instance ) )
		{
			static::$instance = new KKCrmApi();
		}
		return static::$instance;
	}
	public function credentials($apiUserName, $apiPassword)
    {
        $this->apiUserName = $apiUserName;
		$this->apiPassword = $apiPassword;
	}

	public function __construct() {

	}

	private function __clone() {

	}
    public function getAllCampaigns($sort = 'CampaignName')
    {
        $ret = $this->__queryCampaign();
        if ($ret != self::ERROR)
        {
            $campaigns = array();
            $totalResults = $ret['totalResults'];
            $resultsPerPage = $ret['resultsPerPage'];
            $page = $ret['page'];

            foreach ($ret['data'] as $data)
            {
                $campaignType = isset($data['campaignType']) ? $data['campaignType'] : '';
                $currency = isset($data['currency']) ? $data['currency'] : '';
                $requireQA = isset($data['requireQA']) ? $data['requireQA'] : '';

                if ($sort == 'CampaignName')
                {
                    $campaigns[] = array($data['campaignName'], array($data['campaignId'], $data['campaignName'], $campaignType, $currency, $requireQA));
                    $sortData[] = $data['campaignName'];
                } elseif ($sort == 'CampaignId')
                {
                    $campaigns[] = array($data['campaignId'], array($data['campaignId'], $data['campaignName'], $campaignType, $currency, $requireQA));
                    $sortData[] = $data['campaignId'];
                }
            }
            sort($sortData);
            $sortedCampaign = array();
            foreach ($sortData as $key)
            {
                foreach ($campaigns as $campaign)
                {
                    if ($key == $campaign[0])
                    {
                        $sortedCampaign[] = $campaign[1];
                        break;
                    }
                }
            }

//            $totalPages = $totalResults / $resultsPerPage;
//            if (($totalResults % $resultsPerPage) != 0)
//                $totalPages ++;
//
//            $page ++;
//
//            while ($page <= $totalPages)
//            {
//                $ret = $this->__queryCampaign($page);
////                $totalResults = $ret['totalResults'];
////                $resultsPerPage = $ret['resultsPerPage'];
////                $page = $ret['page'];
//                foreach ($ret['data'] as $data)
//                {
//                    $campaignType = isset($data['campaignType']) ? $data['campaignType'] : '';
//                    $currency = isset($data['currency']) ? $data['currency'] : '';
//                    $requireQA = isset($data['requireQA']) ? $data['requireQA'] : '';
//
//                    $campaigns[] = array($data['campaignId'], $data['campaignName'], $campaignType, $currency, $requireQA);
//                }
//                $page ++;
//            }
            //sort($campaigns);
            return array($totalResults, $sortedCampaign);
        }

        return $ret;

    }
    private function __queryCampaign($page = -1)
    {
        $dispatch = new Dispatcher();
        $request = new QueryCampaignRequest();
        $request->loginId = $this->apiUserName;
        $request->password = $this->apiPassword;
        $request->resultsPerPage = 200;
        //$request->showAllCampaigns = 0;
        $request->visible = 1;
//        if ($page != -1)
//            $request->page = $page;

        try {
            $response = $dispatch->handle($request);
            if ($response->isSuccessful())
            {
                return $response->message;
            }
        } catch (ValidationException $exception)
        {
            return self::ERROR;
        }
    }
	
}

?>