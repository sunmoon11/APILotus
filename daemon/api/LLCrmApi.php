<?php

require_once 'DBApi.php';

class LLCrmApi {

	protected static $instance;

	public $apiBaseUrl;
	public $apiEndPoint;
	public $apiUserName;
	public $apiPassword;

	public static function getInstance() {
		
		if( is_null( static::$instance ) ) {

			static::$instance = new LLCrmApi();

		}

		return static::$instance;
	}
	
	public static function getInstanceWithCredentials($apiUrl, $apiUserName, $apiPassword) {

		if( is_null(static::$instance) ) {
		
			static::$instance = new LLCrmApi();
		}

		static::$instance->credentials($apiUrl, $apiUserName, $apiPassword);

		return static::$instance;
	}
 
	private function credentials($apiBaseUrl, $apiUserName, $apiPassword) {
		
		$this->apiBaseUrl = $apiBaseUrl;
		$this->apiUserName = $apiUserName;
		$this->apiPassword = $apiPassword;		
		
	}

	public function getAPIEndPoint($apiType) {
		
		if ( $apiType === 'membership' ) {

			$this->apiEndPoint = $this->apiBaseUrl.'membership.php';

		} elseif ($apiType === 'transaction') {

			$this->apiEndPoint = $this->apiBaseUrl.'transact.php';

		} else {

			exit();

		}

		return $this->apiEndPoint;
		
	}

	public function getResponse($apiMethod, $apiType, $parameters) {
		
		$parameters['username'] = $this->apiUserName;
		$parameters['password'] = $this->apiPassword;
		$parameters['method'] = $apiMethod;		
		
		$this->apiEndPoint = $this->getAPIEndPoint($apiType);		
		
		$curlHandler = curl_init();

		if ( $curlHandler === FALSE ) {

			return null; 

		}
		
		curl_setopt($curlHandler, CURLOPT_URL, $this->apiEndPoint);
		curl_setopt($curlHandler, CURLOPT_HEADER, FALSE);
		curl_setopt($curlHandler, CURLOPT_POST, TRUE);
		curl_setopt($curlHandler, CURLOPT_TIMEOUT, 5000);
		curl_setopt($curlHandler, CURLOPT_POSTFIELDS, http_build_query($parameters));
		curl_setopt($curlHandler, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curlHandler, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, TRUE);
		
		$output = curl_exec($curlHandler);
		curl_close($curlHandler);

		return $output;
		
	}

	protected function __construct() {

	}

	private function __clone() {

	}

	private function __wakeup() {
		
	}

	/*
	*	A method to validate API credentials
	*
	*	@return TRUE if valid or FALSE.
	*/
	public function validateAPICredentials() {
		
		$parameter = array();
		
		$ret = $this->getResponse('validate_credentials', 'membership', $parameter);
				
		if ( is_null($ret) ) {

			return FALSE;
		}
			
		if ( $ret === '100' ) {

			return TRUE;

		} else {

			return FALSE;

		}

	}
	/*
	*	A method to get the current CRM`s url
	*	@return ID
	*/
	private function getCrmUrl()
	{
		$crmUrl = str_replace('/admin/', '', $this->apiBaseUrl);
		return $crmUrl;	
	}
	/*
	*	A method to get the Id of current CRM from DB
	*	@return ID
	*/
	public function getCrmIds()
	{
		$dbApi = DBApi::getInstance();
		$crmIds = $dbApi->getActiveCrmIdsByUrl($this->getCrmUrl());
		return $crmIds;
	}	
	/*
	*	A method to get all campaign from LLCRM.
	*
	*	@return campaign array (id, name) or null
	*/
	public function getAllCampaign($crmId) {

		$parameter = array();

		$ret = $this->getResponse('campaign_find_active', 'membership', $parameter);

		if( !is_null($ret) ) {

			parse_str($ret, $data);	
		
			if ( $data['response'] == 100 ) {

				$campaignIds = explode(',', $data['campaign_id']);
				$campaignNames = explode(',', $data['campaign_name']);
				
				$campaigns['ids'] = $campaignIds;
				$campaigns['names'] = $campaignNames;
				$campaigns['labels'] = $this->getLabelsOfCampaigns($crmId, $campaigns['ids']);
				$campaigns['length'] = count($campaignIds);
				return $campaigns;

			} else {

				return null;

			}
		} else {

			return null;
		}

	}
	public function getAllCampaign1()
    {
        $parameter = array();
        $ret = $this->getResponse('campaign_find_active', 'membership', $parameter);

        if( !is_null($ret) ) {

            parse_str($ret, $data);

            if ( $data['response'] == 100 ) {

                $campaignIds = explode(',', $data['campaign_id']);
                $campaignNames = explode(',', $data['campaign_name']);

                $campaigns['ids'] = $campaignIds;
                $campaigns['names'] = $campaignNames;
                return $campaigns;
            } else {
                return null;
            }
        } else {

            return null;
        }
    }
	/*
	*	A method to get campaigns meeting criteria
	*	@ campaignIds - array of campaign ids to search
	*	@ pageNumber - page number
	*	@ items4Page - item count per page
	*	@ return array of campaigns(id, name)
	*/
	public function getCampaigns($crmId, $campaignIds = array(), $pageNumber = -1, $items4Page = -1) {
		
		$allCampaign = $this->getAllCampaign($crmId);		
		$campaigns = array();
		$cIds = array();
		$cNames = array();

		if(count($campaignIds) > 0) {						
			$index = -1;
			foreach($campaignIds as $cId) {				
				$index = array_search($cId, $allCampaign['ids']);								
				if($index !== FALSE) {
					$cName = $allCampaign['names'][$index];
					$cIds[] = $cId;
					$cNames[] = $cName;					
				}
			}

			if($index != -1) 
			{
				$campaigns['ids'] = $cIds;
				$campaigns['names'] = $cNames;	
			}			
			
		} else {
			$allCampaign['ids'] = array_reverse($allCampaign['ids']);
			$allCampaign['names'] = array_reverse($allCampaign['names']);
			$campaigns = $allCampaign;
		}

		$campaigns['length'] = count($campaigns['ids']);

		if($pageNumber >= 1 && $items4Page != -1) {
			if(count($campaigns['ids']) > ($pageNumber - 1) * $items4Page) {
				$campaignIds = array_slice($campaigns['ids'], ($pageNumber - 1) * $items4Page, $items4Page);
				$campaignNames = array_slice($campaigns['names'], ($pageNumber - 1) * $items4Page, $items4Page);
			} else {
				$campaignIds = array();
				$campaignNames = array();
			}			
			$campaigns['ids'] = $campaignIds;
			$campaigns['names'] = $campaignNames;
		}

		$campaigns['labels'] = $this->getLabelsOfCampaigns($crmId, $campaigns['ids']);

		return $campaigns;
	}
	/*
	*	A method to get the labels of campaigns
	*	@return array of labels
	*/
	private function getLabelsOfCampaigns($crmId,$campaignIds) {

		$dbApi = DBApi::getInstance();		

		$allLabel = $dbApi->getAllLabels();
		foreach ($campaignIds as $cId) {
			$labelIds = $dbApi->getCampaignLabelingById($cId, $crmId);			
			$labelIds = explode(',', trim($labelIds, ','));			
			$labelName = "";
			
			foreach ($labelIds as $lId) 
			{	
				$lName = "";			
				foreach ($allLabel as $label) 
				{						
					if($label[0] == $lId)
					{
						$lName = $label[1];
						break;
					}
				}
				$labelName = $labelName.$lName." ";
			}				

			$labelNames[] = trim($labelName, " ");
		}
		return $labelNames;
	}
	/*
	public function getCampaigns($needCampaignIds) {

		$found = false;

		foreach ($needCampaignIds as $cID) {			

			$parameter['campaign_id'] = $cID;

			$res = $this->getResponse('campaign_view', 'membership', $parameter);
			
			if( !is_null($res) ) {

				parse_str($res, $data);

				if( $data['response_code'] === '100') {
					
					$campaign['id'] = $cID;
					$campaign['name'] = $data['campaign_name'];

					$campaigns[] = $campaign;

					$found = true;

				} else if ( $data['response_code'] === '200') {
					
					return null; // invalid login credentials

				} else {
					
					continue; // invalid campaign id
				}

			} else {
				
				return null;
			}

		}

		if( !$found ) {

			return null;

		} else {

			return $campaigns;
		}


	}*/
	
}

?>