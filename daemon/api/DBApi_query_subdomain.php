<?php

class DBApi
{
	protected static $instance;

	private $host_name = "localhost";


	private $db_name = "commercials_apilotus";
	private $username = "root";
	private $password = "rootroot";
	private $subdomain = "primary";
	// private $password = "";

/*
	private $db_name = "u799155424_llcrm";
	private $username = "u799155424_root";
	private $password = "ZWHYHBvQg5cP";
*/	
	

	public static function getInstance() 
	{
		if (is_null(static::$instance))
			static::$instance = new DBApi();

		return static::$instance;
	}
	
	protected function __construct() 
	{
		$this->conn = @mysqli_connect($this->host_name, $this->username, $this->password, $this->db_name, 3306) or die('error');
	}

	private function __clone() 
	{

	}

	private function __wakeup() 
	{
		
	}

	private function checkConnection()
	{
		if (!$this->conn)
		{
			$this->conn = @mysqli_connect($this->host_name, $this->username, $this->password, $this->db_name);
			if (!$this->conn)	return FALSE;
		}

		return TRUE;
	}
	public function getAllUsers()
	{
		if (!$this->checkConnection())
			return null;
			
		try
		{
			$arrayUsers = array();

			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_user_account';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$user_count = mysqli_num_rows($result);
			if ($user_count > 0) 
			{
				$i = 0;
				while($row = mysqli_fetch_assoc($result)) 
				{
					$arrayUsers[$i] = array($row['id'], $row['user_name'], $row['password'], $row['display_name'], $row['user_role'], $row['user_status'], $row['crm_permissions'], $row['sms'], $row['email'], $row['bot'], $row['sms_enable'], $row['email_enable'], $row['bot_enable']);
					$i ++;
				}
			} 

			return $arrayUsers;
		}
		catch(Exception $e) 
		{
			return null;
		}	
	}
	public function getAllCrm() 
	{
		if (!$this->checkConnection())
			return null;

		try
		{
			$currentDay = date('Y-m-d');			
			$arrayCrm = array();

			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_crm_account';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$crm_count = mysqli_num_rows($result);
			if ($crm_count > 0) 
			{
				$i = 0;
				while($row = mysqli_fetch_assoc($result)) 
				{
					$arrayCrm[$i] = array($row['id'], $row['crm_name'], $row['crm_url'], $row['user_name'], $row['password'], $row['api_user_name'], $row['api_password'], $row['sales_goal'], $row['paused'], $row['password_updated'], $currentDay);
					$i ++;
				}
			} 

			return $arrayCrm;
		}
		catch(Exception $e) 
		{
			return null;
		}
	}
	public function getAllCrmByAccountId($accountId)
	{
		$permissionString = $this->getPermissionString($accountId);
		$arrayPermission = explode(',', $permissionString);
		
		$allCrm = $this->getAllCrm();

		$arrayCrm = array();
		foreach ($allCrm as $crm) 
		{
			if(in_array($crm[0], $arrayPermission) && $crm[8] == 0)
				$arrayCrm[] = $crm;	
		}
		return $arrayCrm;
	}
	public function getAllCrmByAccountIdInSetting($accountId)
	{
		$permissionString = $this->getPermissionString($accountId);
		$arrayPermission = explode(',', $permissionString);
		
		$allCrm = $this->getAllCrm();

		$arrayCrm = array();
		foreach ($allCrm as $crm) 
		{
			if(in_array($crm[0], $arrayPermission))
				$arrayCrm[] = $crm;	
		}
		return $arrayCrm;
	}
	public function getCrmIdByUrl($crmUrl) 
	{
		if (!$this->checkConnection())
			return null;

		try
		{
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_crm_account WHERE crm_url="'.$crmUrl.'" and paused=0';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$crm_count = mysqli_num_rows($result);
			$crmId = null;
			if ($crm_count > 0) 
			{
				$row = mysqli_fetch_assoc($result);
				$crmId = $row['id'];
			} 

			return $crmId;
		}
		catch(Exception $e) 
		{
			return null;
		}
	}
	public function getCrmById($crmID) 
	{
		if (!$this->checkConnection())
			return null;

		try
		{
			$currentDay = date('Y-m-d');
			$arrayCrm = array();

			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_crm_account WHERE id='.$crmID.' and paused=0';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$crm_count = mysqli_num_rows($result);
			if ($crm_count > 0) 
			{
				$row = mysqli_fetch_assoc($result);
				$arrayCrm = array($row['crm_url'], $row['user_name'], $row['password'], $row['api_user_name'], $row['api_password'], $row['sales_goal'], $row['paused'], $row['password_updated'], $currentDay);
			} 

			return $arrayCrm;
		}
		catch(Exception $e) 
		{
			return null;
		}
	}
	public function validateUser($userName, $password) 
	{
		if(!$this->checkConnection())
			return null;

		try {			

			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_user_account WHERE user_name="'.$userName.'" and password="'.$password.'" and user_status=1';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$userCount = mysqli_num_rows($result);			
			
			$user = null;
			if($userCount > 0) 
			{
				$row = mysqli_fetch_assoc($result);
				$user = array($row['display_name'], $row['user_role'], $row['user_status'], $row['id'], $row['user_name']);				
			}

			return $user;
			
		} catch (Exception $e) {
			
			return null;
		}
	}
	public function getAllLabel()
	{
		if(!$this->checkConnection())
			return null;

		try {
			
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$count = mysqli_num_rows($result);

			$labels = array();
			if($count > 0) 
			{
				$i = 0;
				while($row = mysqli_fetch_assoc($result))
				{
					$labels[$i] = array($row['id'], $row['label_name']);
					$i++;
				}				
			}

			return $labels;

		} catch (Exception $e) {
			return null;
		}
	}
	public function getAffiliateSetting($affiliateId, $userId)
	{
		if(!$this->checkConnection())
			return array();

		try {
			$allCrm = $this->getAllCrmByAccountId($userId);

			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_affiliate WHERE affiliate_id='.$affiliateId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);

			$setting = array();
			$label = '';
			$goals = array();
			if($count > 0) 
			{				
				while($row = mysqli_fetch_assoc($result))
				{					
					foreach ($allCrm as $crmInfo) {
						if($crmInfo[0] == $row['crm_id']) {
							$label = $row['label'];
							$goals[] = array($row['crm_id'], $crmInfo[1], $row['sales_goal']);						
						}
					}					
				}
				$setting['label'] = $label;
				$setting['goals'] = $goals;
			}

			return $setting;

		} catch (Exception $e) {
			return array();
		}
	}	
	public function getAllAffiliateLabel()
	{
		if(!$this->checkConnection())
			return null;

		try {
			
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_affiliate';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);

			$labels = array();
			if($count > 0) 
			{
				$i = 0;
				while($row = mysqli_fetch_assoc($result))
				{
					$labels[$i] = array($row['affiliate_id'], $row['crm_id'], $row['sales_goal']);
					$i++;
				}				
			}

			return $labels;

		} catch (Exception $e) {
			return null;
		}
	}
	public function getAllAffiliateLabels()
	{
		if(!$this->checkConnection())
			return null;

		try {	
			
			$query = 'SELECT affiliate_id, label FROM '.$this->db_name.'.'.$this->subdomain.'_label_affiliate GROUP BY affiliate_id, label';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);

			$labels = array();
			if($count > 0) 
			{
				$i = 0;
				while($row = mysqli_fetch_assoc($result))
				{
					$labels[$i] = array($row['affiliate_id'], $row['label']);
					$i++;
				}				
			}

			return $labels;

		} catch (Exception $e) {
			return null;
		}
	}
	public function getLabelIdsbyCampaignId($campaignId, $crmId) 
	{
		if(!$this->checkConnection())
			return "";

		try {
						
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_campaign WHERE campaign_id='.$campaignId.' and crm_id='.$crmId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$count = mysqli_num_rows($result);			
			$labelIds = "";			
			if($count > 0) 
			{
				$row = mysqli_fetch_assoc($result);
				$labelIds = $row['label_ids'];
			}
			
			return $labelIds;
			
		} catch (Exception $e) {
			
			return "";
		}	
	}
	public function addCrm($crmName, $crmUrl, $crmUserName, $crmPassword, $apiUserName, $apiPassword, $salesGoal, $paused, $userId) 
	{
		if(!$this->checkConnection())
			return false;

		try {		
						
			$currentDay = date('Y-m-d');
			$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_crm_account (id, crm_name, crm_url, user_name, password, api_user_name, api_password, sales_goal, paused, password_updated) VALUES (null,"'
				.$crmName.'","'.$crmUrl.'","'.$crmUserName.'","'.$crmPassword.'","'.$apiUserName.'","'.$apiPassword.'",'.$salesGoal.','.$paused.',"'.$currentDay.'")';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if($result === TRUE) {
				// update permission in user_account
				$query = 'SELECT id FROM '.$this->db_name.'.'.$this->subdomain.'_crm_account WHERE crm_name="'.$crmName.'"';

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
				$count = mysqli_num_rows($result);

				if($count > 0)
				{
					$row = mysqli_fetch_assoc($result);
					$id = $row['id'];

					$permissions = $this->getPermissionString($userId);
					$permissions = $permissions.','.$id;

					return $this->setPermissionList($userId, $permissions);
				}
				return false;
				
			} else {
				return false;
			}			
		} catch (Exception $e) {
			
			return false;
		}
		
	}
	public function updateCrmPassword($crmID, $crmPassword)
	{
		if(!$this->checkConnection())
			return false;

		try {
			$currentDay = date('Y-m-d');
			$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_crm_account SET password="'.$crmPassword.'",password_updated="'.$currentDay.'" WHERE id='.$crmID;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if($result === TRUE) {
				return true;
			} else {
				return false;
			}			
		} catch (Exception $e) {
			
			return false;
		}	
	}
	public function updateCrmApiPassword($crmID, $apiPassword)
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_crm_account SET api_password="'.$apiPassword.'" WHERE id='.$crmID;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if($result === TRUE) {
				return true;
			} else {
				return false;
			}			
		} catch (Exception $e) {
			
			return false;
		}	
	}
	public function updateCrm($crmId, $crmName, $crmUrl, $crmUserName, $apiUserName, $salesGoal, $paused)
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_crm_account SET crm_name="'.$crmName.'",crm_url="'.$crmUrl.'",user_name="'.$crmUserName.'",api_user_name="'.$apiUserName.'",sales_goal='.$salesGoal.',paused='.$paused.' WHERE id='.$crmId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if($result === TRUE) {
				return true;
			} else {
				return false;
			}			
		} catch (Exception $e) {
			
			return false;
		}
	}
	public function deleteCrm($crmId, $userId)
	{
		if(!$this->checkConnection())
			return false;

		try {
									
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_crm_account WHERE id='.$crmId;
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if($result === TRUE) {
				// update permission in user_account
				$permissions = $this->getPermissionString($userId);
				if(strpos($permissions, ','.$crmId) !== false)
				{
					$permissions = str_replace(','.$crmId, '', $permissions);
				} else if (strpos($permissions, $crmId.',') !== false) {
					$permissions = str_replace($crmId.',', '', $permissions);
				}else if(strpos($permissions, $crmId) !== false)
				{
					$permissions = str_replace($crmId, '', $permissions);
				}

				return $this->setPermissionList($userId, $permissions);

			} else {
				return false;
			}			
		} catch (Exception $e) {
			
			return false;
		}
	}
	public function addLabel($labelName) 
	{
		if(!$this->checkConnection())
			return false;

		try {
									
			$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_label (id, label_name) VALUES (null,"'.$labelName.'")';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if($result === TRUE) {
				return true;
			} else {
				return false;
			}			
		} catch (Exception $e) {
			
			return false;
		}
	}
	public function editLabel($labelId, $newName)
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_label SET label_name="'.$newName.'" WHERE id='.$labelId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if($result === TRUE) {
				return true;
			} else {
				return false;
			}			
		} catch (Exception $e) {
			
			return false;
		}
	}
	public function deleteLabel($labelId)
	{
		if(!$this->checkConnection())
			return false;

		try {
									
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_label WHERE id='.$labelId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if($result === TRUE) {
				return true;
			} else {
				return false;
			}			
		} catch (Exception $e) {
			
			return false;
		}
	}
	public function deleteCampaignsInLabeling($crmId, $campaignIds)
	{
		if(!$this->checkConnection())
			return false;

		try {
			$campaignIds = explode(',', $campaignIds);
			foreach ($campaignIds as $cId)
			{
				$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_label_campaign WHERE crm_id='.$crmId.' and campaign_id='.$cId;

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
				if(!$result)
					return false;
			}

			return true;					
						
		} catch (Exception $e) {			
			return false;
		}	
	}
	public function updateLabelsOfCampaignsInLabeling($crmId, $campaignIds, $labelIds)
	{
		if(!$this->checkConnection())
			return false;

		try {
			$campaignIds = explode(',', $campaignIds);
			$labelIds = ','.$labelIds.',';

			foreach ($campaignIds as $cId)
			{
				$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_campaign WHERE crm_id='.$crmId.' and campaign_id='.$cId;

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));				
				if(mysqli_num_rows($result) > 0) 
				{
					$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_label_campaign SET label_ids="'.$labelIds.'" WHERE crm_id='.$crmId.
					' and campaign_id='.$cId;

					$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

				} else 
				{
					$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_label_campaign (id, crm_id, campaign_id, label_ids) VALUES(null,'.
							$crmId.','.$cId.',"'.$labelIds.'")';

					$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));					
				}
				if(!$result)
					return false;
			}

			return true;					
						
		} catch (Exception $e) {			
			return false;
		}

	}
	public function updateUserPassword($userID, $password)
	{
		if(!$this->checkConnection())
			return false;

		try {
			$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_user_account SET password="'.$password.'" WHERE id='.$userID;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if(!$result)
				return false;
			else 
				return true;

		} catch (Exception $e) {

			return false;
		}
	}
	public function addUser($userName, $password, $displayName, $state, $role, $sms, $email, $bot, $sms_enable, $email_enable, $bot_enable) 
	{
		if(!$this->checkConnection())
			return false;

		try {
			$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_user_account (id, user_name, password, display_name, user_status, user_role, crm_permissions, sms, email, bot, sms_enable, email_enable, bot_enable) VALUES(null,"'.$userName.'","'.$password.'","'.$displayName.'",'.$state.','.$role.', "", "'.$sms.'","'.$email.'","'.$bot.'",'.$sms_enable.','.$email_enable.','.$bot_enable.')';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if(!$result)
				return false;
			else 
				return true;

		} catch (Exception $e) {

			return false;
		}
	}
	public function deleteUser($userId) 
	{
		if(!$this->checkConnection())
			return false;

		try {
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_user_account WHERE id='.$userId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if(!$result)
				return false;
			else 
				return true;

		} catch (Exception $e) {
			return false;
		}
	}
	public function updateUser($userId, $userName, $displayName, $state, $role, $sms, $email, $bot, $sms_enable, $email_enable, $bot_enable)
	{
		if(!$this->checkConnection())
			return false;

		try {
			$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_user_account SET user_name="'.$userName.'",display_name="'.$displayName.'",user_status='.$state.',user_role='.$role.',sms="'.$sms.'",email="'.$email.'",bot="'.$bot.'",sms_enable='.$sms_enable.',email_enable='.$email_enable.',bot_enable='.$bot_enable.' WHERE id='.$userId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if(!$result)
				return false;
			else 
				return true;

		} catch (Exception $e) {

			return false;
		}
	}
	private function getLabelIdByName($labelName, $allLabel)
	{
		foreach ($allLabel as $label) {
			if($label[1] == $labelName)
				return $label[0];
		}
		return -1;
	}
	public function getCampaignLabelByIds($labelIds)
	{
		$allLabel = $this->getAllLabel();
		$ids = explode(',', $labelIds);

		$labelName = '';
		foreach ($ids as $id)
		{
			$name = '';
			foreach ($allLabel as $label) 
			{
				if($label[0] == $id)
				{					
					$name = $label[1];
					break;
				}
			}
			if($name != '')
			{
				if($labelName == '')
					$labelName = $name;
				else 
					$labelName = $labelName.' '.$name;
			}
		}

		return $labelName;
	}
	public function getCampaignLabelingByCrmId($crmId)
	{
		if(!$this->checkConnection())
			return null;

		try {
			
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_campaign WHERE crm_id='.$crmId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$count = mysqli_num_rows($result);

			$labels = array();
			if($count > 0) 
			{
				$i = 0;
				while($row = mysqli_fetch_assoc($result))
				{
					$labels[$i] = array($row['campaign_id'], $row['label_ids']);
					$i++;
				}				
			}

			return $labels;

		} catch (Exception $e) {
			return null;
		}
	}
	public function getAllSTEP1CampaignIds() {

		$allLabel = $this->getAllLabel();
		if($allLabel == null)
			return null;
		$step1Label = $this->getLabelIdByName('Step1', $allLabel);
		$prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);
		$tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

		if(!$this->checkConnection())
			return null;

		try {
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_campaign WHERE label_ids LIKE ",'.$step1Label.
			',%" AND label_ids NOT LIKE "%,'.$prepaidLabel.',%" AND label_ids NOT LIKE "%,'.$tabletLabel.',%"';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$campaignIds = array();
			$crmIds = array();
			if(mysqli_num_rows($result) > 0) 
			{				
				while($row = mysqli_fetch_assoc($result))
				{
					$campaignIds[] = $row['campaign_id'];
					$crmIds[] = $row['crm_id'];					
				}				
			}

			return array($campaignIds, $crmIds);

		} catch (Exception $e) {
			return null;
		}

	}

	public function getSTEP1CampaignIds($crmId)
	{
		$allLabel = $this->getAllLabel();
		if($allLabel == null)
			return null;
		$step1Label = $this->getLabelIdByName('Step1', $allLabel);
		$prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);
		$tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

		if(!$this->checkConnection())
			return null;

		try {
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_campaign WHERE label_ids LIKE ",'.$step1Label.
			',%" AND label_ids NOT LIKE "%,'.$prepaidLabel.',%" AND label_ids NOT LIKE "%,'.$tabletLabel.',%" AND crm_id='.$crmId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$campaignIds = array();

			if(mysqli_num_rows($result) > 0) 
			{				
				while($row = mysqli_fetch_assoc($result))
				{
					$campaignIds[] = $row['campaign_id'];					
				}				
			}

			return $campaignIds;

		} catch (Exception $e) {
			return null;
		}

	}
	public function getAllSTEP2CampaignIds() {

		$allLabel = $this->getAllLabel();
		$step2Label = $this->getLabelIdByName('Step2', $allLabel);
		$prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);
		$tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

		if(!$this->checkConnection())
			return null;

		try {
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_campaign WHERE label_ids LIKE ",'.$step2Label.
			',%" AND label_ids NOT LIKE "%,'.$prepaidLabel.',%" AND label_ids NOT LIKE "%,'.$tabletLabel.',%"';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$campaignIds = array();
			$crmIds = array();
			if(mysqli_num_rows($result) > 0) 
			{				
				while($row = mysqli_fetch_assoc($result))
				{
					$campaignIds[] = $row['campaign_id'];
					$crmIds[] = $row['crm_id'];					
				}				
			}

			return array($campaignIds, $crmIds);

		} catch (Exception $e) {
			return null;
		}
	}
	public function getSTEP2CampaignIds($crmId)
	{
		$allLabel = $this->getAllLabel();
		$step2Label = $this->getLabelIdByName('Step2', $allLabel);
		$prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);
		$tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

		if(!$this->checkConnection())
			return null;

		try {
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_campaign WHERE label_ids LIKE ",'.$step2Label.
			',%" AND label_ids NOT LIKE "%,'.$prepaidLabel.',%" AND label_ids NOT LIKE "%,'.$tabletLabel.
			',%" AND crm_id='.$crmId;


			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$campaignIds = array();

			if(mysqli_num_rows($result) > 0) 
			{				
				while($row = mysqli_fetch_assoc($result))
				{
					$campaignIds[] = $row['campaign_id'];					
				}				
			}

			return $campaignIds;

		} catch (Exception $e) {
			return null;
		}
	}
	public function getAllSTEP2NonPPCampaignIds() {

		$allLabel = $this->getAllLabel();
		$step2Label = $this->getLabelIdByName('Step2', $allLabel);
		$prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);		

		if(!$this->checkConnection())
			return null;

		try {
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_campaign WHERE label_ids LIKE ",'.$step2Label.
			',%" AND label_ids NOT LIKE "%,'.$prepaidLabel.',%"';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$campaignIds = array();
			$crmIds = array();

			if(mysqli_num_rows($result) > 0) 
			{				
				while($row = mysqli_fetch_assoc($result))
				{
					$campaignIds[] = $row['campaign_id'];
					$crmIds[] = $row['crm_id'];					
				}				
			}

			return array($campaignIds, $crmIds);

		} catch (Exception $e) {
			return null;
		}
	}
	public function getSTEP1NonPPCampaignIds($crmId)
	{
		$allLabel = $this->getAllLabel();
		$step1Label = $this->getLabelIdByName('Step1', $allLabel);
		$prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);		

		if(!$this->checkConnection())
			return null;

		try {
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_campaign WHERE label_ids LIKE ",'.$step1Label.
			',%" AND label_ids NOT LIKE "%,'.$prepaidLabel.',%" AND crm_id='.$crmId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$campaignIds = array();

			if(mysqli_num_rows($result) > 0) 
			{				
				while($row = mysqli_fetch_assoc($result))
				{
					$campaignIds[] = $row['campaign_id'];					
				}				
			}

			return $campaignIds;

		} catch (Exception $e) {
			return null;
		}
	}
	public function getSTEP2NonPPCampaignIds($crmId)
	{
		$allLabel = $this->getAllLabel();
		$step2Label = $this->getLabelIdByName('Step2', $allLabel);
		$prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);		

		if(!$this->checkConnection())
			return null;

		try {
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_campaign WHERE label_ids LIKE ",'.$step2Label.
			',%" AND label_ids NOT LIKE "%,'.$prepaidLabel.',%" AND crm_id='.$crmId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$campaignIds = array();

			if(mysqli_num_rows($result) > 0) 
			{				
				while($row = mysqli_fetch_assoc($result))
				{
					$campaignIds[] = $row['campaign_id'];					
				}				
			}

			return $campaignIds;

		} catch (Exception $e) {
			return null;
		}
	}
	public function getAllTabletCampaignIds() {

		$allLabel = $this->getAllLabel();				
		$tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

		if(!$this->checkConnection())
			return null;

		try {
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_campaign WHERE label_ids LIKE ",'.$tabletLabel.',"';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$campaignIds = array();
			$crmIds = array();

			if(mysqli_num_rows($result) > 0) 
			{				
				while($row = mysqli_fetch_assoc($result))
				{
					$campaignIds[] = $row['campaign_id'];
					$crmIds[] = $row['crm_id'];					
				}				
			}

			return array($campaignIds, $crmIds);

		} catch (Exception $e) {
			return null;
		}
	}
	public function getTabletCampaignIds($crmId)
	{
		$allLabel = $this->getAllLabel();				
		$tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

		if(!$this->checkConnection())
			return null;

		try {
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_campaign WHERE label_ids LIKE ",'.$tabletLabel.
			'," AND crm_id='.$crmId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$campaignIds = array();

			if(mysqli_num_rows($result) > 0) 
			{				
				while($row = mysqli_fetch_assoc($result))
				{
					$campaignIds[] = $row['campaign_id'];					
				}				
			}

			return $campaignIds;

		} catch (Exception $e) {
			return null;
		}
	}
	public function addCrmToken($crmId, $crmToken, $timestamp)
	{
		if(!$this->checkConnection())
			return false;

		try {
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_crm_token WHERE crm_id='.$crmId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));				
			if(mysqli_num_rows($result) > 0) 
			{
				$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_crm_token SET crm_token="'.$crmToken.'", timestamp='.$timestamp.' WHERE crm_id='.$crmId;

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			} else 
			{
				$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_crm_token (id, crm_id, crm_token, timestamp) VALUES(null,'.
						$crmId.',"'.$crmToken.'",'.$timestamp.')';

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));					
			}
			
			if(!$result)
				return false;
			else
				return true;

		} catch (Exception $e) {

			return false;
		}
	}
	public function getCrmToken($crmId)
	{
		if(!$this->checkConnection())
			return null;

		try {
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_crm_token WHERE crm_id='.$crmId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			
			if(!$result)
				return null;

			$token = array();				
			if(mysqli_num_rows($result) > 0) 
			{
				$row = mysqli_fetch_assoc($result);
				$token[] = $row['crm_token'];
				$token[] = $row['timestamp'];
			} else {
				return null;
			}

			return $token;

		} catch (Exception $e) {

			return null;
		}
	}
	public function getAllAffiliate()
	{
		if(!$this->checkConnection())
			return null;
		try {
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_affiliate ORDER BY affiliate_id ASC';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return null;
			$affiliates = array();
			$affilateCount = 0;
			if(mysqli_num_rows($result) > 0) 
			{
				$aId = '';				
				$goals = array();
				$label = '';		
				while($row = mysqli_fetch_assoc($result))
				{	
					if($aId !== $row['affiliate_id'])
					{						
						if($aId != '')
						{							
							$affiliate = array($aId, $label, $goals);
							$affiliates[] = $affiliate;
							$affilateCount ++;
							$goals = array();
						}
						$aId = $row['affiliate_id'];						
						$label = $row['label'];						
					} 
					$goals[] = array($row['crm_id'], $row['sales_goal']);
				}
				$affiliate = array($aId, $label, $goals);
				$affiliates[] = $affiliate;
				$affilateCount ++;
			}
			
			$ret['affiliates'] = $affiliates;
			$ret['length'] = $affilateCount;

			return $affiliates;

		} catch (Exception $e) {
			return null;
		}	
	}
	public function getAffiliate($pageNumber, $items4Page)
	{
		if(!$this->checkConnection())
			return null;
		try {
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_affiliate ORDER BY affiliate_id ASC';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return null;
			$affiliates = array();
			$affilateCount = 0;
			if(mysqli_num_rows($result) > 0) 
			{
				$aId = '';				
				$goals = array();
				$label = '';				
				while($row = mysqli_fetch_assoc($result))
				{	
					if($aId !== $row['affiliate_id']) {						
						if($aId != '') {							
							$affiliate = array($aId, $label, $goals);
							$affiliates[] = $affiliate;
							$affilateCount ++;
							$goals = array();		
						}
						$aId = $row['affiliate_id'];						
						$label = $row['label'];						
					} 
					$goals[] = array($row['crm_id'], $row['sales_goal']);
				}
				$affiliate = array($aId, $label, $goals);
				$affiliates[] = $affiliate;
				$affiliates = array_slice($affiliates, ($pageNumber - 1) * $items4Page, $items4Page);
				$affilateCount ++;				
			}

			$ret['affiliates'] = $affiliates;
			$ret['length'] = $affilateCount;

			return $ret;

		} catch (Exception $e) {
			return null;
		}
	}
	public function addAffiliate($affiliateId, $affiliateLabel, $crmIds, $goals)
	{
		if(!$this->checkConnection())
			return false;
		try {
			
			$result = $this->deleteAffiliate($affiliateId);
			if(!$result)
				return false;
			$crmIds = explode(',', $crmIds);
			$goals = explode(',', $goals);

			for($i = 0; $i < count($crmIds); $i ++) {
				$crmId = $crmIds[$i];
				$goal = $goals[$i];

				$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_label_affiliate (id, affiliate_id, label, crm_id, sales_goal) VALUES (null,"'.
					$affiliateId.'","'.$affiliateLabel.'",'.$crmId.','.$goal.')';

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
				if(!$result)
					return false;
			}	

			return true;

		} catch (Exception $e) {
			return false;
		}
	}	

	public function deleteAffiliate($affiliateId)
	{
		if(!$this->checkConnection())
			return false;

		try {						
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_label_affiliate WHERE affiliate_id="'.$affiliateId.'"';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return false;			
			return true;

		} catch (Exception $e) {
			return false;
		}
	}
	public function updateAffiliate($affiliateId, $affiliateLabel, $crmIds, $goals) 
	{
		if(!$this->checkConnection())
			return false;
		try {
			
			$result = $this->deleteAffiliate($affiliateId);
			if(!$result)
				return false;
			
			$crmIds = explode(',', $crmIds);
			$goals = explode(',', $goals);

			for($i = 0; $i < count($crmIds); $i ++) {
				$crmId = $crmIds[$i];
				$goal = $goals[$i];

				$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_label_affiliate (id, affiliate_id, label, crm_id, sales_goal) VALUES (null,"'.
					$affiliateId.'","'.$affiliateLabel.'",'.$crmId.','.$goal.')';

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
				if(!$result)
					return false;
			}	

			return true;

		} catch (Exception $e) {
			return false;
		}
	}
	public function getAffiliateSum($userToken)
	{
		$allLabel = $this->getAllLabel();
		if($allLabel == null)
			return array();
		
		$allSTEP1CampaignIds = $this->getAllSTEP1CampaignIds();
		$allSTEP2CampaignIds = $this->getAllSTEP2CampaignIds();
		$allSTEP2NPPCampaignIds = $this->getAllSTEP2NonPPCampaignIds();
		$allTabletCampaignIds = $this->getAllTabletCampaignIds();		
		

		try {
			// STEP1
			$sumSTEP1 = array();

			$campaignIds = $allSTEP1CampaignIds[0];
			$crmIds = $allSTEP1CampaignIds[1];

			$cond = "";
			for($i=0;$i < count($crmIds);$i++) {
				$camId = $campaignIds[$i];
				$crmId = $crmIds[$i];

				$cond = $cond.'(campaign_id='.$camId;
				$cond = $cond.' AND crm_id='.$crmId.')';

				if($i != count($crmIds) -1) {
					$cond = $cond.' OR ';
				}
			}
			$affiliate_progress_table = $this->subdomain.'_affiliate_progress';
			$query = 'SELECT affiliate_id, sum(initial_customer) AS sum FROM '.$affiliate_progress_table.' WHERE user_token='.$userToken;
			
			if($cond !== "") {
				$query = $query.' AND ('.$cond.') GROUP BY affiliate_id';

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));	

				if(mysqli_num_rows($result) > 0) {				
					while($row = mysqli_fetch_assoc($result)) {
						$sumSTEP1[] = array($row['affiliate_id'], $row['sum']);					
					}				
				}
			}			

			// STEP2 			
			$sumSTEP2 = array();

			$campaignIds = $allSTEP2CampaignIds[0];
			$crmIds = $allSTEP2CampaignIds[1];

			$cond = "";
			for($i=0;$i < count($crmIds);$i++) {
				$camId = $campaignIds[$i];
				$crmId = $crmIds[$i];

				$cond = $cond.'(campaign_id='.$camId;
				$cond = $cond.' AND crm_id='.$crmId.')';
				if($i != count($crmIds) -1) {
					$cond = $cond.' OR ';
				}
			}

			$query = 'SELECT affiliate_id, sum(initial_customer) AS sum FROM '.$affiliate_progress_table.' WHERE user_token='.$userToken;
			
			if($cond !== "") {
				$query = $query.' AND ('.$cond.') GROUP BY affiliate_id';

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));	

				if(mysqli_num_rows($result) > 0) {				
					while($row = mysqli_fetch_assoc($result)) {
						$sumSTEP2[] = array($row['affiliate_id'], $row['sum']);					
					}				
				}
			}
			// STEP2NNPP 			
			$sumSTEP2NNPP = array();

			$campaignIds = $allSTEP2NPPCampaignIds[0];
			$crmIds = $allSTEP2NPPCampaignIds[1];

			$cond = "";
			for($i=0;$i < count($crmIds);$i++) {
				$camId = $campaignIds[$i];
				$crmId = $crmIds[$i];

				$cond = $cond.'(campaign_id='.$camId;
				$cond = $cond.' AND crm_id='.$crmId.')';

				if($i != count($crmIds) -1) {
					$cond = $cond.' OR ';
				}
			}
			$query = 'SELECT affiliate_id, sum(initial_customer) AS sum FROM '.$affiliate_progress_table.' WHERE user_token='.$userToken;
			
			if($cond !== "") {
				$query = $query.' AND ('.$cond.') GROUP BY affiliate_id';

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));	

				if(mysqli_num_rows($result) > 0) {				
					while($row = mysqli_fetch_assoc($result)) {
						$sumSTEP2NNPP[] = array($row['affiliate_id'], $row['sum']);					
					}				
				}
			}
			
			// TABLET 			
			$sumTABLET = array();

			$campaignIds = $allTabletCampaignIds[0];
			$crmIds = $allTabletCampaignIds[1];

			$cond = "";
			for($i=0;$i < count($crmIds);$i++) {
				$camId = $campaignIds[$i];
				$crmId = $crmIds[$i];

				$cond = $cond.'(campaign_id='.$camId;
				$cond = $cond.' AND crm_id='.$crmId.')';
				if($i != count($crmIds) -1) {
					$cond = $cond.' OR ';
				}
			}
			$query = 'SELECT affiliate_id, sum(initial_customer) AS sum FROM '.$affiliate_progress_table.' WHERE user_token='.$userToken;
			
			if($cond !== "") {
				$query = $query.' AND ('.$cond.') GROUP BY affiliate_id';

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));	

				if(mysqli_num_rows($result) > 0) {				
					while($row = mysqli_fetch_assoc($result)) {
						$sumTABLET[] = array($row['affiliate_id'], $row['sum']);					
					}				
				}
			}
			
			// get all affiliates per user
			$query = 'SELECT affiliate_id, affiliate_label FROM '.$affiliate_progress_table.' WHERE user_token ='.$userToken.' GROUP BY affiliate_id, affiliate_label';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$affiliateIds = array();

			if(mysqli_num_rows($result) > 0) 
			{				
				while($row = mysqli_fetch_assoc($result))
				{
					$affiliateIds[] = array($row['affiliate_id'], $row['affiliate_label']);					
				}				
			}

			// arrange sum per affiliate
			$ret = array();
			foreach ($affiliateIds as $affiliateId) {
				// step1
				$sumSTEP1_affiliate = '0';
				foreach ($sumSTEP1 as $sumSTEP1Affiliate) {
					if($sumSTEP1Affiliate[0] == $affiliateId[0]) {
						$sumSTEP1_affiliate = $sumSTEP1Affiliate[1];
						break;
					}

				}
				// step2
				$sumSTEP2_affiliate = '0';
				foreach ($sumSTEP2 as $sumSTEP2Affiliate) {
					if($sumSTEP2Affiliate[0] == $affiliateId[0]) {
						$sumSTEP2_affiliate = $sumSTEP2Affiliate[1];
						break;
					}
				}
				// step2 non prepaids
				$sumSTEP2NNPP_affiliate = '0';
				foreach ($sumSTEP2NNPP as $sumSTEP2NNPPAffiliate) {
					if($sumSTEP2NNPPAffiliate[0] == $affiliateId[0]) {
						$sumSTEP2NNPP_affiliate = $sumSTEP2NNPPAffiliate[1];
						break;
					}
				}
				// tablet
				$sumTablet_affiliate = '0';
				foreach ($sumTABLET as $sumTABLETAffiliate) {
					if($sumTABLETAffiliate[0] == $affiliateId[0]) {
						$sumTablet_affiliate = $sumTABLETAffiliate[1];
						break;
					}
				}
				$ret[] = array($affiliateId[0], $sumSTEP1_affiliate, $sumSTEP2_affiliate, $sumTablet_affiliate,$sumSTEP2NNPP_affiliate, $affiliateId[1]);
			}
			return $ret;

		} catch (Exception $e) {
			return array();
		}
	}

	public function deleteAffiliateProgress($userToken) 
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_affiliate_progress WHERE user_token='.$userToken;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return false;

			return true;

		} catch (Exception $e) {
			return false;
		}
	}

	public function deleteAffiliateProgressByCrmID($userToken, $crmID) 
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_affiliate_progress WHERE user_token='.$userToken.' AND crm_id='.$crmID;			
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return false;

			return true;

		} catch (Exception $e) {
			return false;
		}
	}

	public function addAffiliateProgress($data) 
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			foreach ($data as $item) {

				$query = 'INSERT INTO '.$this->subdomain.'_affiliate_progress (id, crm_id, campaign_id, initial_customer, affiliate_id, affiliate_label, user_token) VALUES (null,'.$item['crm_id'].','.$item['campaign_id'].','.$item['initial_customer'].',"'
				.$item['affiliate_id'].'","'.$item['label'].'",'.$item['user_token'].')';

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
				if(!$result)
					return false;			
			}
			
			return true;

		} catch (Exception $e) {
			return false;
		}
		
	}	
	public function checkLabelingOfCampaign($campaignId, $crmId)
	{
		if(!$this->checkConnection())
			return false;
		try {
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_campaign WHERE campaign_id='.$campaignId.' AND crm_id='.$crmId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(mysqli_num_rows($result) > 0)
				return true;
			else
				return false;

		} catch (Exception $e) {
			return false;
		}
	}
	public function getAffiliatesByCrmId($crmId) 
	{
		$affiliates = array();

		if(!$this->checkConnection())
			return $affiliates;

		try {

			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_label_affiliate WHERE crm_id='.
				$crmId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(mysqli_num_rows($result) > 0)
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$affiliates[] = array($row['affiliate_id'], $row['label'], $row['sales_goal']);					
				}				
			}
			return $affiliates;

		} catch (Exception $e) {
			return array();
		}
	}
	public function getAffiliateSumPerCrm($userToken, $affiliateId)
	{
		$allLabel = $this->getAllLabel();
		if($allLabel == null)
			return null;
		
		$allSTEP1CampaignIds = $this->getAllSTEP1CampaignIds();
		$allSTEP2CampaignIds = $this->getAllSTEP2CampaignIds();
		$allSTEP2NPPCampaignIds = $this->getAllSTEP2NonPPCampaignIds();
		$allTabletCampaignIds = $this->getAllTabletCampaignIds();		
		

		try {
			// STEP1
			$sumSTEP1 = array();

			$campaignIds = $allSTEP1CampaignIds[0];
			$crmIds = $allSTEP1CampaignIds[1];

			$cond = "";
			for($i=0;$i < count($crmIds);$i++) {
				$camId = $campaignIds[$i];
				$crmId = $crmIds[$i];

				$cond = $cond.'(campaign_id='.$camId;
				$cond = $cond.' AND crm_id='.$crmId.')';
				if($i != count($crmIds) -1) {
					$cond = $cond.' OR ';
				}
			}
			$affiliate_progress_table = $this->subdomain.'_affiliate_progress';
			$query = 'SELECT crm_id,sum(initial_customer) AS sum FROM '.$affiliate_progress_table.' WHERE user_token='.$userToken.' AND affiliate_id="'.$affiliateId.'"';
			
			if($cond !== "") {
				$query = $query.' AND ('.$cond.') GROUP BY crm_id';

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));	

				if(mysqli_num_rows($result) > 0) {				
					while($row = mysqli_fetch_assoc($result)) {
						$sumSTEP1[] = array($row['crm_id'], $row['sum']);
					}				
				}
			}			

			// STEP2 			
			$sumSTEP2 = array();

			$campaignIds = $allSTEP2CampaignIds[0];
			$crmIds = $allSTEP2CampaignIds[1];

			$cond = "";
			for($i=0;$i < count($crmIds);$i++) {
				$camId = $campaignIds[$i];
				$crmId = $crmIds[$i];

				$cond = $cond.'(campaign_id='.$camId;
				$cond = $cond.' AND crm_id='.$crmId.')';
				if($i != count($crmIds) -1) {
					$cond = $cond.' OR ';
				}
			}
			$query = 'SELECT crm_id,sum(initial_customer) AS sum FROM '.$affiliate_progress_table.' WHERE user_token='.$userToken.' AND affiliate_id="'.$affiliateId.'"';
			
			if($cond !== "") {
				$query = $query.' AND ('.$cond.') GROUP BY crm_id';

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

				if(mysqli_num_rows($result) > 0) {				
					while($row = mysqli_fetch_assoc($result)) {
						$sumSTEP2[] = array($row['crm_id'], $row['sum']);
					}				
				}
			}
			// STEP2NNPP 			
			$sumSTEP2NNPP = array();

			$campaignIds = $allSTEP2NPPCampaignIds[0];
			$crmIds = $allSTEP2NPPCampaignIds[1];

			$cond = "";
			for($i=0;$i < count($crmIds);$i++) {
				$camId = $campaignIds[$i];
				$crmId = $crmIds[$i];

				$cond = $cond.'(campaign_id='.$camId;
				$cond = $cond.' AND crm_id='.$crmId.')';
				if($i != count($crmIds) -1) {
					$cond = $cond.' OR ';
				}
			}
			$query = 'SELECT crm_id,sum(initial_customer) AS sum FROM '.$affiliate_progress_table.' WHERE user_token='.$userToken.' AND affiliate_id="'.$affiliateId.'"';
			
			if($cond !== "") {
				$query = $query.' AND ('.$cond.') GROUP BY crm_id';

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));	

				if(mysqli_num_rows($result) > 0) {				
					while($row = mysqli_fetch_assoc($result)) {
						$sumSTEP2NNPP[] = array($row['crm_id'], $row['sum']);
					}				
				}
			}
			
			// TABLET 			
			$sumTABLET = array();

			$campaignIds = $allTabletCampaignIds[0];
			$crmIds = $allTabletCampaignIds[1];

			$cond = "";
			for($i=0;$i < count($crmIds);$i++) {
				$camId = $campaignIds[$i];
				$crmId = $crmIds[$i];

				$cond = $cond."(campaign_id=".$camId;
				$cond = $cond." AND crm_id=".$crmId.")";
				if($i != count($crmIds) -1) {
					$cond = $cond." OR ";
				}
			}
			$query = 'SELECT crm_id, sum(initial_customer) AS sum FROM '.$affiliate_progress_table.' WHERE user_token='.$userToken.' AND affiliate_id="'.$affiliateId.'"';
			
			if($cond !== "") {
				$query = $query.' AND ('.$cond.') GROUP BY crm_id';

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));	

				if(mysqli_num_rows($result) > 0) {				
					while($row = mysqli_fetch_assoc($result)) {
						$sumTABLET[] = array($row['crm_id'], $row['sum']);
					}				
				}
			}
			
			// get all crmId per user
			$query = 'SELECT crm_id FROM '.$affiliate_progress_table.' WHERE user_token ='.$userToken.' GROUP BY crm_id DESC';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$crmIds = array();

			if(mysqli_num_rows($result) > 0) 
			{				
				while($row = mysqli_fetch_assoc($result))
				{
					$crmIds[] = $row['crm_id'];					
				}				
			}

			// arrange sum per crm
			$crmTable = $this->getAllCrm();
			$affiliateLableTable = $this->getAllAffiliateLabel();

			$ret = array();
			foreach ($crmIds as $crmId) {
				// step1
				$sumSTEP1_crm = '0';
				foreach ($sumSTEP1 as $sumSTEP1Crm) {
					if($sumSTEP1Crm[0] == $crmId) {
						$sumSTEP1_crm = $sumSTEP1Crm[1];
						break;
					}

				}
				// step2
				$sumSTEP2_crm = '0';
				foreach ($sumSTEP2 as $sumSTEP2Crm) {
					if($sumSTEP2Crm[0] == $crmId) {
						$sumSTEP2_crm = $sumSTEP2Crm[1];
						break;
					}
				}
				// step2 non prepaids
				$sumSTEP2NNPP_crm = '0';
				foreach ($sumSTEP2NNPP as $sumSTEP2NNPPCrm) {
					if($sumSTEP2NNPPCrm[0] == $crmId) {
						$sumSTEP2NNPP_crm = $sumSTEP2NNPPCrm[1];
						break;
					}
				}
				// tablet
				$sumTablet_crm = '0';
				foreach ($sumTABLET as $sumTABLETCrm) {
					if($sumTABLETCrm[0] == $crmId) {
						$sumTablet_crm = $sumTABLETCrm[1];
						break;
					}
				}
				$crmName = $this->getCrmNameByCrmId($crmId, $crmTable);
				$sales_goal = $this->getAffiliateSalesGoal($affiliateId, $crmId, $affiliateLableTable);

				$ret[] = array($crmId, $sumSTEP1_crm, $sumSTEP2_crm, $sumTablet_crm, $sumSTEP2NNPP_crm, $crmName, $sales_goal);
			}
			return $ret;

		} catch (Exception $e) {
			return null;
		}
	}
	private function getCrmNameByCrmId($crmId, $crmTable) 
	{
		foreach ($crmTable as $row)
		{
			if($row[0] == $crmId) 
				return $row[1];
		}
		return '';
	}
	public function getCrmName($crmId)
	{
		$allCrm = $this->getAllCrm();
		$ret = $this->getCrmNameByCrmId($crmId, $allCrm);
		return $ret;
	}
	private function getAffiliateSalesGoal($affiliateId, $crmId, $table)
	{
		foreach ($table as $row)
		{
			if($row['0'] == $affiliateId && $row['1'] == $crmId)
				return $row['2'];	
		}
		return '0';
	}
	private function getPermissionString($accountId)
	{
		if (!$this->checkConnection())
			return null;
			
		try
		{			

			$query = 'SELECT crm_permissions FROM '.$this->db_name.'.'.$this->subdomain.'_user_account WHERE id='.$accountId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$count = mysqli_num_rows($result);
			if ($count > 0) 
			{
				$row = mysqli_fetch_assoc($result); 
				$permissions = $row['crm_permissions'];				
			}

			return $permissions;
		}
		catch(Exception $e) 
		{
			return null;
		}	
	}
	public function getPermissionList($accountId)
	{
		$permissionString = $this->getPermissionString($accountId);
	
		$permissions = explode(',', $permissionString);
		$result = array();
		
		$allCrm = $this->getAllCrm();
		foreach ($allCrm as $value) 
		{
			$crmId = $value[0];
			$crmName = $value[1];
			$permission = 0;
			if(in_array($crmId, $permissions))
			{
				$permission = 1;
			} 
			$result[] = array($crmId, $crmName, $permission);
		}

		return $result;
	}
	public function setPermissionList($accountId, $permissionList)
	{
		if (!$this->checkConnection())
			return false;
			
		try
		{			

			$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_user_account SET crm_permissions="'.$permissionList.'" WHERE id='.$accountId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if($result === TRUE) 
				return true;
			else
				return false;

		}
		catch(Exception $e) 
		{
			return false;
		}
	}
	public function deleteRetentionQuickByCrmId($userToken, $crmId)
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_retention_quick_export WHERE user_token="'.$userToken.'" and crm_id='.$crmId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return false;

			return true;

		} catch (Exception $e) {
			return false;
		}		
	}
	public function deleteRetentionExportByCrmId($userToken, $crmId)
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_retention_export WHERE user_token="'.$userToken.'" and crm_id='.$crmId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return false;

			return true;

		} catch (Exception $e) {
			return false;
		}		
	}
	public function deleteRetentionForInitialAlertByCrmID($crmId, $day)
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_retention_initial_alert WHERE crm_id='.$crmId.' and day='.$day;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return false;

			return true;

		} catch (Exception $e) {
			return false;
		}
	}
	public function deleteRetentionQuickByCampaignId($userToken, $crmId, $campaignId)
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_retention_quick_export WHERE user_token="'.$userToken.'" and crm_id='.$crmId.' and campaign_id='.$campaignId.' and affiliate_id !=""';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return false;

			return true;

		} catch (Exception $e) {
			return false;
		}		
	}
	public function deleteRetentionExportByCampaignId($userToken, $crmId, $campaignId)
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_retention_export WHERE user_token="'.$userToken.'" and crm_id='.$crmId.' and campaign_id='.$campaignId.' and affiliate_id !=""';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return false;

			return true;

		} catch (Exception $e) {
			return false;
		}		
	}
	public function deleteRetentionQuickByAffiliateId($userToken, $crmId, $campaignId, $affiliateId)
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_retention_quick_export WHERE user_token="'.$userToken.'" and crm_id='.$crmId.' and campaign_id='.$campaignId.' and affiliate_id="'.$affiliateId.'" and subaffiliate_id != ""';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return false;

			return true;

		} catch (Exception $e) {
			return false;
		}		
	}
	public function deleteRetentionExportByAffiliateId($userToken, $crmId, $campaignId, $affiliateId)
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_retention_export WHERE user_token="'.$userToken.'" and crm_id='.$crmId.' and campaign_id='.$campaignId.' and affiliate_id="'.$affiliateId.'" and subaffiliate_id != ""';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return false;

			return true;

		} catch (Exception $e) {
			return false;
		}		
	}
	public function writeRetentionForInitialAlert($crmId, $crmName, $campaignId, $affiliateId, $subAffiliateId, $gross_order, $net_approved, $approval_rate, $day, $has_child, $fromDate, $toDate, $timestamp)
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_retention_initial_alert (id, crm_id, crm_name, campaign_id, affiliate_id, subaffiliate_id, gross_order, net_approved, approval_rate, day, has_child, from_date, to_date, timestamp) VALUES (null,'
				.$crmId.',"'.$crmName.'",'.$campaignId.',"'.$affiliateId.'","'.$subAffiliateId.'",'.$gross_order.','.$net_approved.','.$approval_rate.','.$day.','.$has_child.',"'.$fromDate.'","'.$toDate.'","'.$timestamp.'")';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return false;

			return true;

		} catch (Exception $e) {
			return false;
		}
	}
	public function writeRetentionExport($crmId, $crmName, $campaignId, $campaignName, $campaignLabel, $affiliateId, $affiliateLabel, $subAffiliateId, $subAffiliateLabel, $init1, $init2, $init3, $init4, $init5, $init6, $second1, $second2, $second3, $second4, $second5, $second6, $userToken)
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_retention_export (id, crm_id, crm_name, campaign_id, campaign_name, campaign_label, affiliate_id, affiliate_label, subaffiliate_id, subaffiliate_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6, user_token) VALUES (null,'
				.$crmId.',"'.$crmName.'",'.$campaignId.',"'.$campaignName.'","'.$campaignLabel.'","'.$affiliateId.'","'.$affiliateLabel.'","'.$subAffiliateId.'","'.$subAffiliateLabel.'","'.$init1.'","'.$init2.'","'.$init3.'","'.$init4.'","'.$init5.'","'.$init6.'","'.$second1.'","'.$second2.'","'.$second3.'","'.$second4.'","'.$second5.'","'.$second6.'","'.$userToken.'")';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return false;

			return true;

		} catch (Exception $e) {
			return false;
		}
	}
	public function writeRetentionQuickExport($crmId, $crmName, $campaignId, $campaignName, $campaignLabel, $affiliateId, $affiliateLabel, $subAffiliateId, $subAffiliateLabel, $init1, $init2, $init3, $init4, $init5, $init6, $second1, $second2, $second3, $second4, $second5, $second6, $userToken)
	{
		if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_retention_quick_export (id, crm_id, crm_name, campaign_id, campaign_name, campaign_label, affiliate_id, affiliate_label, subaffiliate_id, subaffiliate_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6, user_token) VALUES (null,'
				.$crmId.',"'.$crmName.'",'.$campaignId.',"'.$campaignName.'","'.$campaignLabel.'","'.$affiliateId.'","'.$affiliateLabel.'","'.$subAffiliateId.'","'.$subAffiliateLabel.'","'.$init1.'","'.$init2.'","'.$init3.'","'.$init4.'","'.$init5.'","'.$init6.'","'.$second1.'","'.$second2.'","'.$second3.'","'.$second4.'","'.$second5.'","'.$second6.'","'.$userToken.'")';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if(!$result)
				return false;

			return true;

		} catch (Exception $e) {
			return false;
		}
	}
	public function GetCRMList4Export($user_token)
	{
		if(!$this->checkConnection())
			return array();

		try
		{

			$ret = array();

			$query = 'SELECT crm_id, crm_name FROM '.$this->db_name.'.'.$this->subdomain.'_retention_export WHERE user_token="'.$user_token.'" GROUP BY crm_id, crm_name';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$crm_count = mysqli_num_rows($result);
			if ($crm_count > 0) 
			{
				$i = 0;
				while($row = mysqli_fetch_assoc($result)) 
				{
					$ret[$i] = array($row['crm_id'], $row['crm_name']);
					$i ++;
				}
			} 

			return $ret;


		}catch (Exception $e)
		{
			return array();
		}		
	}
	public function GetCampaignList4QuickExport($crm_id, $user_token)
	{
		if(!$this->checkConnection())
			return array();

		try
		{

			$ret = array();
			
			$query = 'SELECT campaign_id, campaign_name, campaign_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6 FROM '.$this->db_name.'.'.$this->subdomain.'_retention_quick_export WHERE user_token="'.$user_token.'" and crm_id='.$crm_id.' and affiliate_id ="" and subaffiliate_id = ""';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$crm_count = mysqli_num_rows($result);
			if ($crm_count > 0) 
			{
				$i = 0;
				while($row = mysqli_fetch_assoc($result)) 
				{
					$ret[$i] = array($row['campaign_id'], $row['campaign_name'], $row['campaign_label'], $row['init1'], $row['init2'], $row['init3'], $row['init4'], $row['init5'], $row['init6'], $row['second1'], $row['second2'], $row['second3'], $row['second4'], $row['second5'], $row['second6']);
					$i ++;
				}
			} 

			return $ret;


		}catch (Exception $e)
		{
			return array();
		}
	}
	public function GetCampaignList4Export($crm_id, $user_token)
	{
		if(!$this->checkConnection())
			return array();

		try
		{

			$ret = array();
			
			$query = 'SELECT campaign_id, campaign_name, campaign_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6 FROM '.$this->db_name.'.'.$this->subdomain.'_retention_export WHERE user_token="'.$user_token.'" and crm_id='.$crm_id.' and affiliate_id ="" and subaffiliate_id = ""';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$crm_count = mysqli_num_rows($result);
			if ($crm_count > 0) 
			{
				$i = 0;
				while($row = mysqli_fetch_assoc($result)) 
				{
					$ret[$i] = array($row['campaign_id'], $row['campaign_name'], $row['campaign_label'], $row['init1'], $row['init2'], $row['init3'], $row['init4'], $row['init5'], $row['init6'], $row['second1'], $row['second2'], $row['second3'], $row['second4'], $row['second5'], $row['second6']);
					$i ++;
				}
			} 

			return $ret;


		}catch (Exception $e)
		{
			return array();
		}
	}
	public function GetAffiliateList4QuickExport($crm_id, $campaign_id, $user_token)
	{
		if(!$this->checkConnection())
			return array();

		try
		{

			$ret = array();

			$query = 'SELECT affiliate_id, affiliate_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6 FROM '.$this->db_name.'.'.$this->subdomain.'_retention_quick_export WHERE user_token="'.$user_token.'" and crm_id='.$crm_id.' and campaign_id='.$campaign_id.' and affiliate_id != ""'.' and subaffiliate_id = ""';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$crm_count = mysqli_num_rows($result);
			if ($crm_count > 0) 
			{
				$i = 0;
				while($row = mysqli_fetch_assoc($result)) 
				{
					$ret[$i] = array($row['affiliate_id'], $row['affiliate_label'], $row['init1'], $row['init2'], $row['init3'], $row['init4'], $row['init5'], $row['init6'], $row['second1'], $row['second2'], $row['second3'], $row['second4'], $row['second5'], $row['second6']);
					$i ++;
				}
			} 

			return $ret;


		}catch (Exception $e)
		{
			return array();
		}
	}
	public function GetAffiliateList4Export($crm_id, $campaign_id, $user_token)
	{
		if(!$this->checkConnection())
			return array();

		try
		{

			$ret = array();

			$query = 'SELECT affiliate_id, affiliate_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6 FROM '.$this->db_name.'.'.$this->subdomain.'_retention_export WHERE user_token="'.$user_token.'" and crm_id='.$crm_id.' and campaign_id='.$campaign_id.' and affiliate_id != ""'.' and subaffiliate_id = ""';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$crm_count = mysqli_num_rows($result);
			if ($crm_count > 0) 
			{
				$i = 0;
				while($row = mysqli_fetch_assoc($result)) 
				{
					$ret[$i] = array($row['affiliate_id'], $row['affiliate_label'], $row['init1'], $row['init2'], $row['init3'], $row['init4'], $row['init5'], $row['init6'], $row['second1'], $row['second2'], $row['second3'], $row['second4'], $row['second5'], $row['second6']);
					$i ++;
				}
			} 

			return $ret;


		}catch (Exception $e)
		{
			return array();
		}
	}
	public function GetSubAffiliateList4QuickExport($crm_id, $campaign_id, $affiliate_id, $user_token)
	{
		if(!$this->checkConnection())
			return array();

		try
		{

			$ret = array();

			$query = 'SELECT subaffiliate_id, subaffiliate_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6 FROM '.$this->db_name.'.'.$this->subdomain.'_retention_quick_export WHERE user_token="'.$user_token.'" and crm_id='.$crm_id.' and campaign_id='.$campaign_id.' and affiliate_id ="'.$affiliate_id.'" and subaffiliate_id != ""';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$crm_count = mysqli_num_rows($result);
			if ($crm_count > 0) 
			{
				$i = 0;
				while($row = mysqli_fetch_assoc($result)) 
				{
					$ret[$i] = array($row['subaffiliate_id'], $row['subaffiliate_label'], $row['init1'], $row['init2'], $row['init3'], $row['init4'], $row['init5'], $row['init6'], $row['second1'], $row['second2'], $row['second3'], $row['second4'], $row['second5'], $row['second6']);
					$i ++;
				}
			} 

			return $ret;


		}catch (Exception $e)
		{
			return array();
		}
	}
	public function GetSubAffiliateList4Export($crm_id, $campaign_id, $affiliate_id, $user_token)
	{
		if(!$this->checkConnection())
			return array();

		try
		{

			$ret = array();

			$query = 'SELECT subaffiliate_id, subaffiliate_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6 FROM '.$this->db_name.'.'.$this->subdomain.'_retention_export WHERE user_token="'.$user_token.'" and crm_id='.$crm_id.' and campaign_id='.$campaign_id.' and affiliate_id ="'.$affiliate_id.'" and subaffiliate_id != ""';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$crm_count = mysqli_num_rows($result);
			if ($crm_count > 0) 
			{
				$i = 0;
				while($row = mysqli_fetch_assoc($result)) 
				{
					$ret[$i] = array($row['subaffiliate_id'], $row['subaffiliate_label'], $row['init1'], $row['init2'], $row['init3'], $row['init4'], $row['init5'], $row['init6'], $row['second1'], $row['second2'], $row['second3'], $row['second4'], $row['second5'], $row['second6']);
					$i ++;
				}
			} 

			return $ret;


		}catch (Exception $e)
		{
			return array();
		}
	}

	public function getBlockedIpList()
	{
		if(!$this->checkConnection())
			return array();

		try
		{

			$ret = array();

			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_blocked_ip';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$ip_count = mysqli_num_rows($result);
			if ($ip_count > 0) 
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$ret[] = array($row['id'], $row['ip'], $row['description']);
				}
			}			 

			return $ret;


		}catch (Exception $e)
		{
			return array();
		}
	}
	public function addBlockedIp($ip, $description)
	{
		if(!$this->checkConnection())
			return false;
		try
		{

			$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_blocked_ip (id, ip, description) VALUES(null,"'.$ip.'","'.$description.'")';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if ($result === TRUE) 
				return true;

			return false;

		}catch (Exception $e)
		{
			return false;
		}
	}
	public function editBlockedIp($id, $ip, $description)
	{
		if(!$this->checkConnection())
			return false;
		try
		{

			$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_blocked_ip SET ip="'.$ip.'" , description="'.$description.'" WHERE id='.$id;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if ($result === TRUE) 
				return true;

			return false;

		}catch (Exception $e)
		{
			return false;
		}	
	}
	public function deleteBlockedIp($id)
	{
		if(!$this->checkConnection())
			return false;
		try
		{

			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_blocked_ip WHERE id='.$id;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if ($result === TRUE) 
				return true;

			return false;

		}catch (Exception $e)
		{
			return false;
		}	
	}
	public function checkClientIp()
    {        
        $blockedIpList = $this->getBlockedIpList();
        
        if(count($blockedIpList) > 0)
        {
            $ipaddress = '';
            if(!empty($_SERVER['HTTP_CLIENT_IP']) && getenv('HTTP_CLIENT_IP'))
            {  
        		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];  
    		} 
    		elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && getenv('HTTP_X_FORWARDED_FOR'))
    		{  
        		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];  
    		} 
    		elseif(!empty($_SERVER['REMOTE_HOST']) && getenv('REMOTE_HOST'))
    		{  
        		$ipaddress = $_SERVER['REMOTE_HOST'];  
    		} 
    		elseif(!empty($_SERVER['REMOTE_ADDR']) && getenv('REMOTE_ADDR'))
    		{  
        		$ipaddress = $_SERVER['REMOTE_ADDR'];  
    		}
    		else
    		{    			
            	$ipaddress = 'UNKNOWN';
    		}            
            
            foreach ($blockedIpList as $blockedIp) 
            {
            	$ipAtomic = explode('.', $ipaddress);
            	$blockAtomic = explode('.', $blockedIp[1]);

            	$block = true;

            	for($i = 0; $i < count($ipAtomic); $i++)
            	{
            		if($blockAtomic[$i] == '*')
            			continue;            		
            		if($ipAtomic[$i] != $blockAtomic[$i])
            			$block = false;
            	}
            	if($block)
            		return false;                
            }
            return true;
        }
        else
        {
        	return true;
        }
        
    }
    public function getAlertLevelList($type) 
    {
    	if(!$this->checkConnection())
			return array();

    	$alertTypes = $this->getAlertType();
    	
    	$alert_setting_table = $this->subdomain.'_alert_setting';
    	$alert_schedule_table = $this->subdomain.'_alert_schedule';

		try
		{
			$query = 'SELECT '.$alert_setting_table.'.*,'.$alert_schedule_table.'.show_status FROM '.$alert_setting_table.' LEFT JOIN '.$alert_schedule_table.' ON '.$alert_setting_table.'.type='.$alert_schedule_table.'.alert_type WHERE type='.$type.' ORDER BY type ASC';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);

			$ret = array();
			if ($count > 0) 
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$name = '';
					foreach ($alertTypes as $alert) 
					{
						if($alert[0] == $row['type'])
						{
							$name = $alert[2];
							break;
						}	
					}

					$ret[] = array($row['aid'], $row['crm_id'], $row['type'], $name, $row['value1'], $row['value2'], $alert[3], $alert[4], $row['show_status']);
				}				
			}
			return $ret;

		}catch (Exception $e)
		{
			return array();
		}
    }
    public function getAlertLevelListByCrm($crmId) 
    {
    	if(!$this->checkConnection())
			return array();

    	$alertTypes = $this->getAlertType();
    	
		try
		{
			$alert_setting_table = $this->subdomain.'_alert_setting';
    		$alert_schedule_table = $this->subdomain.'_alert_schedule';

			$query = 'SELECT '.$alert_setting_table.'.*,'.$alert_schedule_table.'.show_status FROM '.$alert_setting_table.' LEFT JOIN '.$alert_schedule_table.' ON '.$alert_setting_table.'.type='.$alert_schedule_table.'.alert_type WHERE crm_id='.$crmId.' ORDER BY type ASC';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);

			$ret = array();
			if ($count > 0) 
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$name = '';
					foreach ($alertTypes as $alert) 
					{
						if($alert[0] == $row['type'])
						{
							$name = $alert[2];
							break;
						}	
					}

					$ret[] = array($row['aid'], $row['crm_id'], $row['type'], $name, $row['value1'], $row['value2'], $alert[3], $alert[4], $row['show_status']);
				}				
			}
			return $ret;

		}catch (Exception $e)
		{
			return array();
		}
    }
    public function changeAlertLevel($type, $crmID, $value1, $value2)
    {
    	if(!$this->checkConnection())
			return false;
		try
		{
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_alert_setting WHERE type='.$type.' AND crm_id='.$crmID;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));				
			if(mysqli_num_rows($result) > 0) 
			{
				$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_alert_setting SET value1='.$value1.', value2='.$value2.' WHERE type='.$type.' AND crm_id='.$crmID;

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
				if($result === TRUE)
					return true;
				else 
					return false;

			}			
			else 
			{
				$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_alert_setting (aid, crm_id, type, value1, value2) VALUES (null,'.$crmID.','.$type.','.$value1.','.$value2.')';

				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
				if($result === TRUE)
					return true;
				else 
					return false;
			}
		}
		catch (Exception $e)
		{
			return false;
		}
    }
    public function addAlertReceiver($type, $address, $status, $chatid)
    {
    	if(!$this->checkConnection())
			return false;
		try
		{
			$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_alert_receiver(rid, type, address, status, chatid) VALUES (null,'.$type.',"'.$address.'",'.$status.',"'.$chatid.'")';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if($result === TRUE)
				return true;
			return false;

		}catch (Exception $e)
		{
			return false;
		}
    }
    public function deleteAlertReceiver($receiverID)
    {
    	if(!$this->checkConnection())
			return false;
		try
		{
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_alert_receiver WHERE rid='.$receiverID;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if($result === TRUE)
				return true;
			return false;

		}catch (Exception $e)
		{
			return false;
		}
    }
    public function changeAlertReceiver($receiverID, $type, $address, $status, $chatid)
    {
    	if(!$this->checkConnection())
			return false;
		try
		{
			$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_alert_receiver SET type='.$type.',address="'.$address.'",status='.$status.',chatid="'.$chatid.'" WHERE rid='.$receiverID;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if($result === TRUE)
				return true;
			return false;

		}catch (Exception $e)
		{
			return false;
		}
    }
    public function getAlertReceiverList()
    {
    	if(!$this->checkConnection())
			return array();
		try
		{
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_alert_receiver';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);

			$ret = array();
			if ($count > 0) 
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$ret[] = array($row['rid'], $row['type'], $row['address'], $row['status'], $row['chatid']);
				}				
			}

			return $ret;

		}catch (Exception $e)
		{
			return array();
		}
    }
    public function getEnabledEmails()
    {
    	$receivers = $this->getAlertReceiverList();
		$emails = array();		
		foreach ($receivers as $value)
		{
			if($value[1] == 1 && $value[3] == 1)
			{
				// enabled email address
				$emails[] = $value[2];
			}			
		}
		return $emails;
    }
    public function getEnabledPhoneNumbers()
    {
    	$receivers = $this->getAlertReceiverList();
		$phones = array();
		foreach ($receivers as $value)
		{
			if($value[1] == 0 && $value[3] == 1)
			{
				// enabled email address
				$phones[] = $value[2];
			}			
		}
		return $phones;
    }
    public function getTelegramChatIDList()
    {
    	if(!$this->checkConnection())
			return array();
		try
		{
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_alert_receiver WHERE type=2 AND status=1';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);

			$ret = array();
			if ($count > 0) 
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					if ($row['chatid'] != '')
						$ret[] = $row['chatid'];
				}				
			}

			return $ret;

		}catch (Exception $e)
		{
			return array();
		}
    }

    public function getAlertType()
    {
    	if(!$this->checkConnection())
			return array();
		try
		{
			$alert_schedule_table = $this->subdomain.'_alert_schedule';
			$query = 'SELECT alert_type.*,'.$alert_schedule_table.'.alert_day,'.$alert_schedule_table.'.alert_hour,'.$alert_schedule_table.'.sms,'.$alert_schedule_table.'.email,'.$alert_schedule_table.'.telegram_bot,'.$alert_schedule_table.'.show_status FROM alert_type LEFT JOIN '.$alert_schedule_table.' ON '.'alert_type.alert_type='.$alert_schedule_table.'.alert_type';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);

			$ret = array();
			if ($count > 0) 
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$ret[] = array($row['aid'], $row['alert_type'], $row['alert_name'], $row['alert_formula'], $row['report_date'], $row['alert_day'], $row['alert_hour'], $row['sms'], $row['email'], $row['telegram_bot'], $row['show_status']);
				}				
			}

			return $ret;

		}catch (Exception $e)
		{
			return array();
		}
    }
    public function getAlertTypeByType($type)
    {
    	if(!$this->checkConnection())
			return array();
		try
		{
			$alert_schedule_table = $this->subdomain.'_alert_schedule';
			$query = 'SELECT alert_type.*,'.$alert_schedule_table.'.alert_day,'.$alert_schedule_table.'.alert_hour,'.$alert_schedule_table.'.sms,'.$alert_schedule_table.'.email,'.$alert_schedule_table.'.telegram_bot,'.$alert_schedule_table.'.show_status FROM alert_type LEFT JOIN '.$alert_schedule_table.' ON '.'alert_type.alert_type='.$alert_schedule_table.'.alert_type WHERE alert_type='.$type;			

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);
			
			if ($count > 0) 
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$ret = array($row['aid'], $row['alert_type'], $row['alert_name'], $row['alert_formula'], $row['report_date'], $row['alert_day'], $row['alert_hour'], $row['sms'], $row['email'], $row['telegram_bot'], $row['show_status']);
				}				
			}

			return $ret;

		}catch (Exception $e)
		{
			return array();
		}	
    }
    public function updateAlertStatus($crmID, $type, $value, $level, $status, $from_date, $to_date, $timestamp)
    {
    	$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_alert_status WHERE crm_id='.$crmID.' and alert_type='.$type.' and from_date="'.$from_date.'" and to_date="'.$to_date.'"';

		$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));				
		if(mysqli_num_rows($result) > 0) 
		{
			$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_alert_status SET crm_id='.$crmID.', alert_type='.$type.', value='.$value.', level='.$level.',alert_read=0, alert_delete=0'.', status='.$status.',timestamp="'.$timestamp.'", from_date="'.$from_date.'", to_date="'.$to_date.'" WHERE crm_id='.$crmID.' and alert_type='.$type.' and from_date="'.$from_date.'" and to_date="'.$to_date.'"';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

		} else 
		{
			$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_alert_status (aid, crm_id, alert_type, value, level, status, timestamp, alert_read, alert_delete, from_date, to_date) VALUES(null,'.$crmID.','.$type.','.$value.','.$level.','.$status.',"'.$timestamp.'", 0, 0,"'.$from_date.'","'.$to_date.'")';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));					
		}
		if(!$result)
			return false;

		return true;
    }
    public function getRecentAlertList($userId)
    {
    	// count = 10

    	if(!$this->checkConnection())
			return array();
		$allCrm = $this->getAllCrmByAccountId($userId);

		try
		{
			$alert_status_table = $this->subdomain.'_alert_status';
			$query = 'SELECT '.$alert_status_table.'.*, alert_type.alert_name FROM '.$alert_status_table.' LEFT JOIN alert_type ON '.$alert_status_table.'.alert_type=alert_type.alert_type WHERE alert_delete=0 AND status=1 ORDER BY timestamp DESC, alert_type ASC';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);

			$ret = array();
			if ($count > 0) 
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$crmName = '';
					foreach ($allCrm as $crm) 
					{
						if($crm[0] == $row['crm_id'])
						{
							$crmName = $crm[1];
							break;
						}
					}
					if($crmName != '') {
						$ret[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $row['alert_name'], $crmName);	
					}
					
				}				
			}

			return $ret;

		}catch (Exception $e)
		{
			return array();
		}
    }
    public function deleteAlertItem($alertId)
    {
    	if(!$this->checkConnection())
			return false;

		try {
									
			$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_alert_status SET alert_delete=1 WHERE aid='.$alertId;
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if($result === TRUE) {
				return true;
			} else {
				return false;
			}			
		} catch (Exception $e) {
			
			return false;
		}
    }
    public function deleteAlertItemAll()
    {
    	if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_alert_status SET alert_delete=1';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if($result === TRUE) {
				return true;
			} else {
				return false;
			}			
		} catch (Exception $e) {
			
			return false;
		}	
    }
    public function readAlertItem($alertId)
    {
    	if(!$this->checkConnection())
			return false;

		try {
			
			$query = 'UPDATE '.$this->db_name.'.'.$this->subdomain.'_alert_status SET alert_read=1 WHERE aid='.$alertId;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if($result === TRUE) {
				return true;
			} else {
				return false;
			}			
		} catch (Exception $e) {
			
			return false;
		}
    }
    public function getAlertHistory($crmID, $fromDate, $toDate, $pageNumber, $items4Page)
    {
    	$ret['data'] = array();
		$ret['length'] = 0;

    	if(!$this->checkConnection())
			return $ret;
		
		$fromTime = date('Y-m-d', strtotime($fromDate));
		$fromTime = $fromTime.' 00:00:00';
		$toTime = date('Y-m-d', strtotime($toDate));
		$toTime = $toTime.' 23:59:59';
		$allCrm = $this->getAllCrm();

		$data = array();
		try
		{
			$alert_status_table = $this->subdomain.'_alert_status';
			if ($crmID == '0')
				$query = 'SELECT '.$alert_status_table.'.*, alert_type.alert_name FROM '.$alert_status_table.' LEFT JOIN alert_type ON '.$alert_status_table.'.alert_type=alert_type.alert_type WHERE '.$alert_status_table.'.status=1 and '.$alert_status_table.'.timestamp BETWEEN "'.$fromTime.'" AND "'.$toTime.'" ORDER BY '.$alert_status_table.'.timestamp DESC';
			else
				$query = 'SELECT '.$alert_status_table.'.*, alert_type.alert_name FROM '.$alert_status_table.' LEFT JOIN alert_type ON '.$alert_status_table.'.alert_type=alert_type.alert_type WHERE '.$alert_status_table.'.crm_id='.$crmID.' and '.$alert_status_table.'.status=1 and '.$alert_status_table.'.timestamp BETWEEN "'.$fromTime.'" AND "'.$toTime.'" ORDER BY '.$alert_status_table.'.timestamp DESC';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);

			
			if ($count > 0)
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{	
					$crmName = '';
					foreach ($allCrm as $crm) 
					{
						if($crm[0] == $row['crm_id'])
						{
							$crmName = $crm[1];
							break;
						}
					}
					$data[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $row['alert_name'], $crmName);
				}
				$ret['data'] = array_slice($data, ($pageNumber - 1) * $items4Page, $items4Page);
				$ret['length'] = count($data);
			} else 
			{
				$ret['data'] = array();
				$ret['length'] = 0;
			}				

			return $ret;

		}catch (Exception $e)
		{
			return $ret;
		}
    }
    public function getAlertReport($alertType, $date, $userId) 
    {
    	// report for current day
    	if(!$this->checkConnection())
			return array();

		// $time =  date('Y-m-d', strtotime($date));
		$fromTime = $date.' 00:00:00';		
		$toTime = $date.' 23:59:59';
		
		$allCrm = $this->getAllCrmByAccountId($userId);
		try
		{
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_alert_status WHERE alert_type='.$alertType.' and timestamp BETWEEN "'.$fromTime.'" AND "'.$toTime.'" ORDER BY timestamp DESC';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);
			$ret = array();
			if ($count > 0)
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$crmName = '';
					foreach ($allCrm as $crm) 
					{
						if($crm[0] == $row['crm_id'])
						{
							$crmName = $crm[1];
							break;
						}
					}
					$ret[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $crmName);
				}
			}
			return $ret;

		}catch (Exception $e)
		{
			return array();
		}
    }
    public function getAllLatestAlertReportByType($type)
    {
    	// report for current day
    	if(!$this->checkConnection())
			return array();
		
		$allCrm = $this->getAllCrm();
		try
		{
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_alert_status WHERE alert_type='.$type.' ORDER BY crm_id ASC, timestamp DESC';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);
			$ret = array();
			if ($count > 0)
			{				
				$crmOldName = '';
				$crmName = '';
				$crmGoal = 0;
				while($row = mysqli_fetch_assoc($result)) 
				{					
					foreach ($allCrm as $crm) 
					{
						if($crm[0] == $row['crm_id'])
						{
							$crmName = $crm[1];
							$crmGoal = $crm[7];
							break;
						}
					}
					if($crmOldName != $crmName)
					{
						$crmOldName = $crmName;
						$ret[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $crmName, $crmGoal);
					}					
				}				
			}
			return $ret;

		}catch (Exception $e)
		{
			return array();
		}	
    }
    public function getLatestAlertReportByType($crmID, $type)
    {
    	// report for current day
    	if(!$this->checkConnection())
			return array();
		
		$allCrm = $this->getAllCrm();
		try
		{
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_alert_status WHERE crm_id='.$crmID.' and alert_type='.$type.' ORDER BY timestamp DESC';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);
			$ret = array();
			if ($count > 0)
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$crmName = '';
					foreach ($allCrm as $crm) 
					{
						if($crm[0] == $crmID)
						{
							$crmName = $crm[1];
							break;
						}
					}
					$ret[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $crmName);
				}				
			}
			return $ret;

		}catch (Exception $e)
		{
			return array();
		}
    }
    public function getAlertReportByType($crmID, $date, $type)
    {
    	// report for current day
    	if(!$this->checkConnection())
			return array();

		$fromTime = $date.' 00:00:00';
		$toTime = $date.' 23:59:59';
		
		$allCrm = $this->getAllCrm();
		try
		{
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_alert_status WHERE crm_id='.$crmID.' and alert_type='.$type.' and timestamp BETWEEN "'.$fromTime.'" AND "'.$toTime.'" ORDER BY timestamp DESC';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);
			$ret = array();
			if ($count > 0)
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$crmName = '';
					foreach ($allCrm as $crm) 
					{
						if($crm[0] == $crmID)
						{
							$crmName = $crm[1];
							break;
						}
					}
					$ret[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $crmName);
				}				
			}
			return $ret;

		}catch (Exception $e)
		{
			return array();
		}	
    }
    public function getAllAlertReportByType($date, $type)
    {
    	// report for current day
    	if(!$this->checkConnection())
			return array();

		// $time =  date('Y-m-d', strtotime($date));
		
		$fromTime = $date.' 00:00:00';		
		$toTime = $date.' 23:59:59';
		
		$allCrm = $this->getAllCrm();
		try
		{
			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_alert_status WHERE alert_type='.$type.' and timestamp BETWEEN "'.$fromTime.'" AND "'.$toTime.'" ORDER BY timestamp DESC';

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);
			$ret = array();
			if ($count > 0)
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$crmName = '';
					foreach ($allCrm as $crm) 
					{
						if($crm[0] == $row['crm_id'])
						{
							$crmName = $crm[1];
							break;
						}
					}
					$ret[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $crmName);
				}				
			}
			return $ret;

		}catch (Exception $e)
		{
			return array();
		}	
    }
    public function changeAlertSchedule($type, $days, $hours, $sms, $email, $bot)
    {
  		if(!$this->checkConnection())
			return false;

		try {
			
			$alert_schedule_table = $this->subdomain.'_alert_schedule';
			$query = 'UPDATE '.$alert_schedule_table.' SET alert_day="'.$days.'",alert_hour="'.$hours.'",sms='.$sms.',email='.$email.',telegram_bot='.$bot.' WHERE alert_type='.$type;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if($result === TRUE) {
				return true;
			} else {
				return false;
			}			
		} catch (Exception $e) {
			
			return false;
		}	  	
    }
    public function storeDashboardData($crmID, $crmName, $Step1, $Step2, $takeRate, $tablet, $tabletRate, $goal)
    {
  		if(!$this->checkConnection())
			return false;

		try {
			$query = 'DELETE FROM '.$this->db_name.'.'.$this->subdomain.'_report_dashboard WHERE crm_id='.$crmID;
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			if($result === TRUE) 
			{
				$query = 'INSERT INTO '.$this->db_name.'.'.$this->subdomain.'_report_dashboard (id, crm_id, crm_name, step1, step2, takerate, tablet, tabletrate, crm_goal) VALUES (null,'.$crmID.',"'.$crmName.'",'.$Step1.','.$Step2.','.$takeRate.','.$tablet.','.$tabletRate.','.$goal.')';
				$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
				if($result === TRUE)
					return true;
				else
					return false;				
			}
			else 
			{
				return false;
			}

		} catch (Exception $e) 
		{
			
			return false;
		}  	
    }
    public function getDashboardData()
    {
  		if (!$this->checkConnection())
			return array();
			
		try
		{
			$data = array();

			$query = 'SELECT * FROM '.$this->db_name.'.'.$this->subdomain.'_report_dashboard ORDER BY crm_id ASC';
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

			$count = mysqli_num_rows($result);
			if ($count > 0) 
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$data[] = array($row['crm_id'], $row['crm_name'], $row['step1'], $row['step2'], $row['takerate'], $row['tablet'], $row['tabletrate'], $row['crm_goal']);				
				}
			} 

			return $data;
		}
		catch(Exception $e) 
		{
			return array();
		}
    }
    public function backupDb()
    {
    	// $this->backupTable('crm_account');
    	// $this->backupTable('alert_receiver');
    	// $this->backupTable('alert_setting');
    	// $this->backupTable('alert_type');
    	// $this->backupTable('label');
    	// $this->backupTable('label_affiliate');
    	// $this->backupTable('label_campaign');
    	// $this->backupTable('user_account');
    	
    }
    private function backupTable($tableName)
    {
    	if(!$this->checkConnection())
			return false;

		try {

			$file = '/home/ubuntu/db_backup/'.$tableName.'.sql';
			unlink($file);		
			$query = "SELECT * INTO OUTFILE '$file' FROM ".$this->db_name.'.'.$this->subdomain.'_'.$tableName;
			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			if($result == true)
    			echo $tableName.":success \n";
    		else 
    			echo $tableName.":failed \n";

			return $result;

		} catch (Exception $e) 
		{
			
			return false;
		}	
    }

    // APIs for sub affiliate alert (detailed alert with affiliate/sub affiliate)
    public function getSTEP1ApprovalRateForInitialAlertByCrm($crmId, $day)
    {
    	if (!$this->checkConnection())
			return array(0, date('m/d/Y'), date('m/d/Y'));

		$campaignIdsSTEP1 = $this->getSTEP1CampaignIds($crmId);

		try
		{				
			$stringCampaigns = join("','", $campaignIdsSTEP1);			
			$query = 'SELECT sum(gross_order) as sumGross, sum(net_approved) as sumNet, from_date, to_date , crm_name FROM '.$this->db_name.'.'.$this->subdomain.'_retention_initial_alert WHERE crm_id='.$crmId.' and day='.$day." and affiliate_id=''"." and campaign_id IN ('$stringCampaigns')";

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);
			if ($count > 0) 
			{				
				$row = mysqli_fetch_assoc($result); 
				
				if($row['sumGross'] == 0)
					return array(0, $row['from_date'], $row['to_date']);

				return array($row['sumNet'] / $row['sumGross'] * 100, $row['from_date'], $row['to_date'], $row['crm_name']);

			}

			return array(0, date('m/d/Y'), date('m/d/Y'));
			
		}
		catch(Exception $e) 
		{
			return array(0, date('m/d/Y'), date('m/d/Y'));;
		}
    }
    /*
    *
    */
    public function getSTEP1CampaignsForInitialAlertByCrm($crmId, $level, $day)
    {
    	if (!$this->checkConnection())
			return array();
			
		try
		{
			$data = array();
			$campaignIdsSTEP1 = $this->getSTEP1CampaignIds($crmId);
			$stringCampaigns = join("','", $campaignIdsSTEP1);			
			$query = 'SELECT campaign_id, approval_rate FROM '.$this->db_name.'.'.$this->subdomain.'_retention_initial_alert WHERE crm_id='.$crmId.' and day='.$day.' and has_child = 1'.' and approval_rate <'.$level;
			$query = $query." and campaign_id IN ('$stringCampaigns')";

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);
			if ($count > 0)
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$data[] = $row['campaign_id'];
				}
			} 

			return $data;
		}
		catch(Exception $e) 
		{
			return array();
		}
    }
    /* This api uses after when complete fetching all data in detailed
    *
    */
    public function getCampaignDetailsForInitialAlertByCrm($crmId, $level, $day, $minOrder)
    {
    	if (!$this->checkConnection())
			return array();
			
		try
		{
			$data = array();
			$campaignIdsSTEP1 = $this->getSTEP1CampaignIds($crmId);
			$stringCampaigns = implode(',', $campaignIdsSTEP1);
			$query = 'SELECT campaign_id,approval_rate,has_child FROM '.$this->db_name.'.'.$this->subdomain.'_retention_initial_alert WHERE crm_id='.$crmId.' and day='.$day.' and campaign_id IN ('.$stringCampaigns.') and approval_rate <'.$level." and affiliate_id ='' and gross_order >=".$minOrder;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);
			if ($count > 0)
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$data[] = array($row['campaign_id'], $row['has_child'], $row['approval_rate']);
				}
			} 

			return $data;
		}
		catch(Exception $e) 
		{
			return array();
		}
    }
    /* 
    * 
    */
    public function getAffiliateIdsForInitialAlertBySTEP1CampaignId($crmId, $level, $day)
    {
    	if (!$this->checkConnection())
			return array();
			
		try
		{
			$data = array();
			$campaignIdsSTEP1 = $this->getSTEP1CampaignIds($crmId);
			$stringCampaigns = implode(',', $campaignIdsSTEP1);
			$query = 'SELECT campaign_id, affiliate_id FROM '.$this->db_name.'.'.$this->subdomain.'_retention_initial_alert WHERE crm_id='.$crmId.' and day='.$day.' and campaign_id IN ('.$stringCampaigns.') and approval_rate <'.$level." and has_child = 1 and affiliate_id !=''";

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);
			if ($count > 0)
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$data[] = array($row['campaign_id'], $row['affiliate_id']);
				}
			} 

			return $data;
		}
		catch(Exception $e) 
		{
			return array();
		}
    }
    /*
    *
    */
    public function getAffiliateDetailsForInitialAlertByCampaignId($crmId, $campaignId, $level, $day, $minOrder)
    {
    	if (!$this->checkConnection())
			return array();
			
		try
		{
			$data = array();			
			$query = 'SELECT affiliate_id, approval_rate,has_child FROM '.$this->db_name.'.'.$this->subdomain.'_retention_initial_alert WHERE crm_id='.$crmId.' and day='.$day.' and campaign_id='.$campaignId.' and approval_rate <'.$level." and subaffiliate_id ='' and affiliate_id != '' and gross_order >=".$minOrder;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);
			if ($count > 0)
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$data[] = array($row['affiliate_id'], $row['has_child'], $row['approval_rate']);
				}
			} 

			return $data;
		}
		catch(Exception $e) 
		{
			return array();
		}
    }
    /*
    *
    */
    public function getSubAffiliateDetailsForInitialAlertByAffiliateId($crmId, $campaignId, $affiliateId, $level, $day, $minOrder)
    {
    	if (!$this->checkConnection())
			return array();
			
		try
		{
			$data = array();			
			$query = 'SELECT subaffiliate_id ,approval_rate FROM '.$this->db_name.'.'.$this->subdomain.'_retention_initial_alert WHERE crm_id='.$crmId.' and day='.$day.' and campaign_id='.$campaignId.' and affiliate_id='.$affiliateId.' and approval_rate <'.$level." and subaffiliate_id != '' and gross_order >=".$minOrder;

			$result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
			$count = mysqli_num_rows($result);
			if ($count > 0)
			{				
				while($row = mysqli_fetch_assoc($result)) 
				{
					$data[] = array($row['subaffiliate_id'], $row['approval_rate']);
				}
			} 

			return $data;
		}
		catch(Exception $e) 
		{
			return array();
		}
    }
}


?>