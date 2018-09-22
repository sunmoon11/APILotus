<?php

class DBApi
{
    protected static $instance;
    private $host_name = "localhost";
    private $db_name = "commercials_apilotus";
    private $username = "root";
    private $password = "apilotusdb123456";
    private $subdomain = "primary";

    public static function getInstance()
    {
        if (is_null(static::$instance))
            static::$instance = new DBApi();

        return static::$instance;
    }

    public function __construct()
    {
        if ($this->getSubDomain() == "") {
            if(session_status()!=PHP_SESSION_ACTIVE)
                session_start();
            if (isset($_SESSION['sub_domain']))
                $subDomain = $_SESSION['sub_domain'];
            else
                $subDomain = "";
            session_write_close();
            $this->setSubDomain($subDomain);
        }
        $this->conn = @mysqli_connect($this->host_name, $this->username, $this->password, $this->db_name, 3306) or die('error');
    }

    public function __destruct()
    {
        unset($this->host_name, $this->db_name, $this->username, $this->password, $this->subdomain);
    }

    private function __clone()
    {

    }

    private function __wakeup()
    {

    }

    public function getSubDomain()
    {
        return $this->subdomain;
    }

    /*
	*@desription
	*	Set subdomain, this should be called before calling any apis first.
	*@param
	*	Subdomain name
	*/
    public function setSubDomain($subdomain)
    {
        $this->subdomain = $subdomain;
    }

    /*
	*@desription
	*	Check the connection to MySQL
	*@return
	*	TRUE:success
	*/
    private function checkConnection()
    {
        if (!$this->conn) {
            $this->conn = @mysqli_connect($this->host_name, $this->username, $this->password, $this->db_name);
            if (!$this->conn)
                return FALSE;
        }

        return TRUE;
    }
    /*
	*@description
	*	Get all users for subdomain
	*@return
	*	null, all user array
	*/
    public function getAllUsers()
    {
        if (!$this->checkConnection())
            return null;

        try {
            $arrayUsers = array();

            $query = 'SELECT * FROM ' . $this->subdomain . '_user_account';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $user_count = mysqli_num_rows($result);
            if ($user_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $arrayUsers[] = array($row['id'], $row['user_name'], $row['password'], $row['display_name'], $row['user_role'], $row['user_status'], $row['crm_permissions'], $row['sms'], $row['email'], $row['bot'], $row['sms_enable'], $row['email_enable'], $row['bot_enable']);
                }
            }

            return $arrayUsers;
        } catch (Exception $e) {
            return null;
        }
    }
    public function getAllUsersInSubDomain($subDomain)
    {
        $arrayUsers = array();
        if (!$this->checkConnection())
            return $arrayUsers;

        try {
            $query = 'SELECT * FROM ' . $subDomain . '_user_account';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $user_count = mysqli_num_rows($result);
            if ($user_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $arrayUsers[] = array($row['id'], $row['user_name'], $row['password'], $row['display_name'], $row['user_role'], $row['user_status'], $row['crm_permissions'], $row['sms'], $row['email'], $row['bot'], $row['sms_enable'], $row['email_enable'], $row['bot_enable']);
                }
            }

            return $arrayUsers;
        } catch (Exception $e) {
            return $arrayUsers;
        }
    }
    /*
	* @description
		Get all crms for subdomain
	* @return
		null, crm array
	*/
    public function getAllCrm()
    {
        if (!$this->checkConnection())
            return null;

        try {
            $currentDay = date('Y-m-d');
            $arrayCrm = array();

            $query = 'SELECT * FROM ' . $this->db_name . '.' . $this->subdomain . '_crm_account';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $arrayCrm[] = array($row['id'], $row['crm_name'], $row['crm_url'], $row['user_name'], $row['password'], $row['api_user_name'], $row['api_password'], $row['sales_goal'], $row['paused'], $row['password_updated'], $currentDay);
                }
            }

            return $arrayCrm;
        } catch (Exception $e) {
            return null;
        }
    }

    /*
    *@description
    *	Get all active crms.
    *	Only returns all crms not paused currently
    *@return
    *	active crm array
    */
    public function getAllActiveCrm()
    {
        $result = array();
        $allCrm = $this->getAllCrm();

        foreach ($allCrm as $crm) {
            if ($crm[8] == 0)
                $result[] = $crm;
        }
        return $result;
    }

    /*
	*@description
	*	Get all active crms by account permission.
	*	Only returns all crms not paused currently
	*@param
	*	Account Id
	*@return
	*	active crm array
	*/
    public function getAllActiveCrmsByAccountId($accountId)
    {
        $result = array();
        $allCrm = $this->getAllCrmsByAccountId($accountId);

        foreach ($allCrm as $crm) {
            if ($crm[8] == 0)
                $result[] = $crm;
        }
        return $result;
    }

    /*
	*@description
	*	Get all crms by account permission
	*@param
	*	Account Id
	*@return
	*	crm array
	*/
    public function getAllCrmsByAccountId($accountId)
    {
        $permissionString = $this->getCrmPermissionOfAccount($accountId);
        if ($permissionString == '')
            return array();

        $arrayPermission = explode(',', $permissionString);

        $allCrm = $this->getAllCrm();
        $arrayCrm = array();
        foreach ($allCrm as $crm) {
            if (in_array($crm[0], $arrayPermission))
                $arrayCrm[] = $crm;
        }
        return $arrayCrm;
    }

    /*
	*@description
	*	Get Crm Ids by Url
	*@return
	*	CrmId array
	*/
    public function getActiveCrmIdsByUrl($crmUrl)
    {
        if (!$this->checkConnection())
            return null;

        try {
            $crmIds = array();
            $query = 'SELECT * FROM ' . $this->subdomain . '_crm_account WHERE crm_url="' . $crmUrl . '" and paused=0';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $crmIds[] = $row['id'];
                }
            }
            return $crmIds;
        } catch (Exception $e) {
            return null;
        }
    }

    /*
	*@description
	*	Get Active crm by Id
	*@return
	*	null, array of crm info
	*/
    public function getActiveCrmById($crmID)
    {
        if (!$this->checkConnection())
            return null;

        try {
            $currentDay = date('Y-m-d');
            $retCrm = array();

            $query = 'SELECT * FROM ' . $this->subdomain . '_crm_account WHERE id=' . $crmID . ' and paused=0';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                $row = mysqli_fetch_assoc($result);
                $retCrm = array($row['crm_url'], $row['user_name'], $row['password'], $row['api_user_name'], $row['api_password'], $row['sales_goal'], $row['paused'], $row['password_updated'], $currentDay);
            }

            return $retCrm;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getActiveKKCrmById($crmID)
    {
        if (!$this->checkConnection())
            return null;

        try {
            $currentDay = date('Y-m-d');
            $retCrm = array();

            $query = 'SELECT * FROM ' . $this->subdomain . '_kkcrm_account WHERE id=' . $crmID . ' and paused=0';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                $row = mysqli_fetch_assoc($result);
                $retCrm = array($row['crm_url'], $row['user_name'], $row['password'], $row['api_user_name'], $row['api_password'], $row['sales_goal'], $row['paused'], $row['password_updated'], $currentDay);
            }

            return $retCrm;
        } catch (Exception $e) {
            return null;
        }
    }

    /*
	*@description
	*	Authorize the user name/password
	*@return
	*	null, array of account info
	*/
    public function validateAccount($userName, $password)
    {
        if (!$this->checkConnection())
            return null;

        try {
            $accountInfo = null;

            $query = 'SELECT * FROM ' . $this->subdomain . '_user_account WHERE user_name="' . $userName . '" and password="' . $password . '" and user_status=1';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);

            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $accountInfo = array($row['display_name'], $row['user_role'], $row['user_status'], $row['id'], $row['user_name'], $row['email']);
            }

            return $accountInfo;

        } catch (Exception $e) {

            return null;
        }
    }

    /*
	*@description
	*	Get all atomic labels defined for campaign labeling in subdomain
	*@return
	*	null, array of labels
	*/
    public function getAllLabels()
    {
        if (!$this->checkConnection())
            return null;

        try {

            $query = 'SELECT * FROM ' . $this->subdomain . '_atomic_label';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);

            $labels = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $labels[] = array($row['id'], $row['label_name'], $row['type']);
                }
            }

            return $labels;

        } catch (Exception $e) {
            return null;
        }
    }

    /*
	*@description
	*	Get affiliate setting (label, sales_goal) by affiliate id
	*@param
	*	Affiliate id, Account id (crm permission by user)
	*@ret
	*	Array of Affiliate setting
	*/
    public function getAffiliateSetting($affiliateId, $accountId)
    {
        if (!$this->checkConnection())
            return array();

        try {
            $allCrm = $this->getAllActiveCrmsByAccountId($accountId);

            $query = 'SELECT * FROM ' . $this->db_name . '.' . $this->subdomain . '_label_affiliate WHERE affiliate_id=' . $affiliateId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            $setting = array();
            $label = '';
            $goals = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    foreach ($allCrm as $crmInfo) {
                        if ($crmInfo[0] == $row['crm_id']) {
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

    /*
	*@description
	*	Get all table
	*@ret
	*	null, array(id, label, crm_id, sales_goal)
	*/
    public function getAllAffiliateLabel()
    {
        if (!$this->checkConnection())
            return null;

        try {

            $query = 'SELECT * FROM ' . $this->subdomain . '_label_affiliate';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            $labels = array();
            if ($count > 0) {
                $i = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $labels[$i] = array($row['affiliate_id'], $row['crm_id'], $row['sales_goal']);
                    $i++;
                }
            }

            return $labels;

        } catch (Exception $e) {
            return null;
        }
    }

    /*
	*@description
	*	Get all affiliate ids and labels
	*@ret
	*	null, array(id, label)
	*/
    public function getAllAffiliateLabels()
    {
        if (!$this->checkConnection())
            return null;

        try {

            $query = 'SELECT affiliate_id, label FROM ' . $this->subdomain . '_label_affiliate GROUP BY affiliate_id, label';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            $labels = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $labels[] = array($row['affiliate_id'], $row['label']);
                }
            }

            return $labels;

        } catch (Exception $e) {
            return null;
        }
    }

    /*
	*@description
	*	Get campaign labeling as string ids seperated comma
	*@param
	*	Campaign id, Crm id
	*@ret
	*	empty, string
	*/
    public function getCampaignLabelingById($campaignId, $crmId)
    {
        if (!$this->checkConnection())
            return "";

        try {

            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE campaign_id=' . $campaignId . ' and crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            $labelIds = "";
            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $labelIds = $row['label_ids'];
            }

            return $labelIds;

        } catch (Exception $e) {

            return "";
        }
    }

    /*
	*@description
	*	Add new Crm
	*@param
		Crm name, url, user name, password, api user name, api password, sales goal weekly, paused, user id
	*@ret
	*	Boolean
	*/
    public function addCrm($crmName, $crmUrl, $crmUserName, $crmPassword, $apiUserName, $apiPassword, $salesGoal, $paused, $userId)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $currentDay = date('Y-m-d');
            $query = 'INSERT INTO ' . $this->subdomain . '_crm_account (id, crm_name, crm_url, user_name, password, api_user_name, api_password, sales_goal, paused, password_updated) VALUES (null,"'
                . $crmName . '","' . $crmUrl . '","' . $crmUserName . '","' . $crmPassword . '","' . $apiUserName . '","' . $apiPassword . '",' . $salesGoal . ',' . $paused . ',"' . $currentDay . '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                // update permission in user_account
                $query = 'SELECT id FROM ' . $this->subdomain . '_crm_account WHERE crm_name="' . $crmName . '"';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                $count = mysqli_num_rows($result);

                if ($count > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $id = $row['id'];

                    $permissions = $this->getCrmPermissionOfAccount($userId);
                    $permissions = $permissions . ',' . $id;

                    return $this->setCrmPermissionOfAccount($userId, $permissions);
                }
                return false;

            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }

    }

    /*
	*@description
	*	Update password of Crm and its date
	*@param
	*	Crm Id, New password
	*@ret
	*	Boolean
	*/
    public function updateCrmPassword($crmID, $crmPassword)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $currentDay = date('Y-m-d');
            $query = 'UPDATE ' . $this->subdomain . '_crm_account SET password="' . $crmPassword . '",password_updated="' . $currentDay . '" WHERE id=' . $crmID;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }

    /*
	*@description
	*	Update api password of Crm
	*@param
	*	Crm Id, New api password
	*@ret
	*	Boolean
	*/
    public function updateCrmApiPassword($crmID, $apiPassword)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'UPDATE ' . $this->subdomain . '_crm_account SET api_password="' . $apiPassword . '" WHERE id=' . $crmID;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }

    public function updateCrmGoal($crmID, $goal)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'UPDATE ' . $this->subdomain . '_crm_account SET sales_goal=' . $goal . ' WHERE id=' . $crmID;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }

    /*
	*@description
	*	Update Crm Info
	*@param
	*	Crm Id, name, url, user name, api user name, sales goal weekly, paused
	*@ret
	*	Boolean
	*/
    public function updateCrm($crmId, $crmName, $crmUrl, $crmUserName, $apiUserName, $salesGoal, $paused)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'UPDATE ' . $this->subdomain . '_crm_account SET crm_name="' . $crmName . '",crm_url="' . $crmUrl . '",user_name="' . $crmUserName . '",api_user_name="' . $apiUserName . '",sales_goal=' . $salesGoal . ',paused=' . $paused . ' WHERE id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }

    /*
	*@description
	*	Delete Crm Info
	*@param
	*	Crm Id, User Id
	*@ret
	*	Boolean
	*/
    public function deleteCrm($crmId, $userId)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_crm_account WHERE id=' . $crmId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                // update permission in user_account
                $permissions = $this->getCrmPermissionOfAccount($userId);
                if (strpos($permissions, ',' . $crmId) !== false) {
                    $permissions = str_replace(',' . $crmId, '', $permissions);
                } else if (strpos($permissions, $crmId . ',') !== false) {
                    $permissions = str_replace($crmId . ',', '', $permissions);
                } else if (strpos($permissions, $crmId) !== false) {
                    $permissions = str_replace($crmId, '', $permissions);
                }

                return $this->setCrmPermissionOfAccount($userId, $permissions);

            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }

    /*
	*@description
	*	Add new basic label for campaign labelling but its type should be 3 (verticals)
	*@param
	*	Label name
	*@ret
	*	Boolean
	*/
    public function addLabel($labelName)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'INSERT INTO ' . $this->subdomain . '_atomic_label (id, label_name, type) VALUES (null,"' . $labelName . '", 3)';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }

    /*
	*@description
	*	Update basic label for campaign
	*@param
	*	Label id, new label
	*@ret
	*	Boolean
	*/
    public function updateLabel($labelId, $newLabel)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'UPDATE ' . $this->subdomain . '_atomic_label SET label_name="' . $newLabel . '" WHERE id=' . $labelId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }

    /*
	*@description
	*	Delete basic label for campaign
	*@param
	*	Label id,
	*@ret
	*	Boolean
	*/
    public function deleteLabel($labelId)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_atomic_label WHERE id=' . $labelId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                // delete label goal
                $query = 'DELETE FROM '.$this->subdomain.'_labels_goal WHERE label_id='.$labelId;
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if ($result) {
                    return true;
                    // update campaign labeling
//                    $query = 'UPDATE ' . $this->subdomain . '_label_campaign SET label_ids="' . $labelIds . '" WHERE crm_id=' . $crmId . ' and campaign_id=' . $cId;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }

    /*
	*@description
	*	Delete labeling of campaign
	*@param
	*	Crm Id, Array of campaign Id
	*@ret
	*	Boolean
	*/
    public function deleteCampaignsInLabeling($crmId, $campaignIds)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $campaignIds = explode(',', $campaignIds);
            foreach ($campaignIds as $cId) {
                $query = 'DELETE FROM ' . $this->subdomain . '_label_campaign WHERE crm_id=' . $crmId . ' and campaign_id=' . $cId;

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if (!$result)
                    return false;
            }

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /*
	*@description
	*	Update campaign labeling
	*@param
	*	Crm Id, Array of campaign Id, Array of new label ids
	*@ret
	*	Boolean
	*/
    public function updateLabelingOfCampaigns($crmId, $campaignIds, $labelIds)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $campaignIds = explode(',', $campaignIds);
            $labelIds = ',' . $labelIds . ',';

            foreach ($campaignIds as $cId) {
                $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE crm_id=' . $crmId . ' and campaign_id=' . $cId;

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if (mysqli_num_rows($result) > 0) {
                    $query = 'UPDATE ' . $this->subdomain . '_label_campaign SET label_ids="' . $labelIds . '" WHERE crm_id=' . $crmId . ' and campaign_id=' . $cId;

                    $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                } else {
                    $query = 'INSERT INTO ' . $this->subdomain . '_label_campaign (id, crm_id, campaign_id, label_ids) VALUES(null,' . $crmId . ',' . $cId . ',"' . $labelIds . '")';

                    $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                }
                if (!$result)
                    return false;
            }

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /*
	*@description
	*	Update password of User Account
	*@param
	*	User Id, New Password
	*@ret
	*	Boolean
	*/
    public function updateAccountPassword($userID, $password)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'UPDATE ' . $this->subdomain . '_user_account SET password="' . $password . '" WHERE id=' . $userID;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if (!$result)
                return false;
            else
                return true;

        } catch (Exception $e) {

            return false;
        }
    }

    /*
	*@description
	*	Add new user account
	*@param
	*	User name, password, display name, status, role, sms, email, bot, sms_enable, email_enable, bot_enable
	*@ret
	*	Boolean
	*/
    public function addAccount($userName, $password, $displayName, $status, $role, $sms, $email, $bot, $sms_enable, $email_enable, $bot_enable)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'INSERT INTO ' . $this->subdomain . '_user_account (id, user_name, password, display_name, user_status, user_role, crm_permissions, sms, email, bot, sms_enable, email_enable, bot_enable) VALUES(null,"' . $userName . '","' . $password . '","' . $displayName . '",' . $status . ',' . $role . ', "", "' . $sms . '","' . $email . '","' . $bot . '",' . $sms_enable . ',' . $email_enable . ',' . $bot_enable . ')';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if (!$result)
                return false;
            else
                return true;

        } catch (Exception $e) {

            return false;
        }
    }

    /*
	*@description
	*	Delete user account
	*@param
	*	User Id
	*@ret
	*	Boolean
	*/
    public function deleteAccount($userId)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'DELETE FROM ' . $this->subdomain . '_user_account WHERE id=' . $userId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if (!$result)
                return false;
            else
                return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /*
	*@description
	*	Update user account
	*@param
	*	User Id, name, dispaly name, status, role, sms, email, bot, sms_enalble, email_enable, bot_enable
	*@ret
	*	Boolean
	*/
    public function updateAccount($userId, $userName, $displayName, $status, $role, $sms, $email, $bot, $sms_enable, $email_enable, $bot_enable)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'UPDATE ' . $this->subdomain . '_user_account SET user_name="' . $userName . '",display_name="' . $displayName . '",user_status=' . $status . ',user_role=' . $role . ',sms="' . $sms . '",email="' . $email . '",bot="' . $bot . '",sms_enable=' . $sms_enable . ',email_enable=' . $email_enable . ',bot_enable=' . $bot_enable . ' WHERE id=' . $userId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if (!$result)
                return false;
            else
                return true;

        } catch (Exception $e) {

            return false;
        }
    }

    /*
	*@description
	*	Get label id by name
	*@param
	*	Label name, all lable info
	*@ret
	*	id
	*/
    private function getLabelIdByName($labelName, $allLabel)
    {
        foreach ($allLabel as $label) {
            if ($label[1] == $labelName)
                return $label[0];
        }
        return -1;
    }

    /*
	*@description
	*	Get campaign labeling by ids
	*@param
	*	Label ids
	*@ret
	*	string
	*/
    public function getCampaignLabelingFromIds($labelIds)
    {
        $allLabel = $this->getAllLabels();
        $ids = explode(',', $labelIds);

        $labelName = '';
        foreach ($ids as $id) {
            $name = '';
            foreach ($allLabel as $label) {
                if ($label[0] == $id) {
                    $name = $label[1];
                    break;
                }
            }
            if ($name != '') {
                if ($labelName == '')
                    $labelName = $name;
                else
                    $labelName = $labelName . ' ' . $name;
            }
        }

        return $labelName;
    }

    /*
	*@description
	*	Get campaign labeling of Crm
	*@param
	*	Crm Id
	*@ret
	*	null, array
	*/
    public function getCampaignLabelingByCrmId($crmId)
    {
        if (!$this->checkConnection())
            return null;

        try {

            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE crm_id=' . $crmId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            $campaignLabeling = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $campaignLabeling[] = array($row['campaign_id'], $row['label_ids']);
                }
            }

            return $campaignLabeling;

        } catch (Exception $e) {
            return null;
        }
    }

    /*
	*@description
	*	Get all STEP1 campaign ids
	*@ret
	*	null, array
	*/
    public function getAllSTEP1CampaignIds()
    {

        $allLabel = $this->getAllLabels();
        if ($allLabel == null)
            return null;
        $step1Label = $this->getLabelIdByName('Step1', $allLabel);
        $prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);
        $tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $step1Label .
                ',%" AND label_ids NOT LIKE "%,' . $prepaidLabel . ',%" AND label_ids NOT LIKE "%,' . $tabletLabel . ',%"';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $campaignIds = array();
            $crmIds = array();
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $campaignIds[] = $row['campaign_id'];
                    $crmIds[] = $row['crm_id'];
                }
            }

            return array($campaignIds, $crmIds);

        } catch (Exception $e) {
            return null;
        }
    }
    /*
     *
     */
    public function getPrepaidCampaignIds($crmId)
    {
        $allLabel = $this->getAllLabels();
        if ($allLabel == null)
            return null;
        $prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);
        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $prepaidLabel .',%" AND crm_id=' . $crmId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $campaignIds = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $campaignIds[] = $row['campaign_id'];
                }
            }

            return $campaignIds;

        } catch (Exception $e) {
            return null;
        }
    }
    public function getPrepaidCampaign($crmId)
    {
        $allLabel = $this->getAllLabels();
        if ($allLabel == null)
            return null;
        $prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);
        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $prepaidLabel .',%" AND crm_id=' . $crmId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $ret = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['campaign_id'], $row['label_ids']);
                }
            }

            return $ret;

        } catch (Exception $e) {
            return null;
        }
    }
    /*
	*@description
	*	Get STEP1 campaign ids of Crm
	*@param
	*	Crm Id
	*@ret
	*	null, array
	*/
    public function getSTEP1CampaignIds($crmId)
    {
        $allLabel = $this->getAllLabels();
        if ($allLabel == null)
            return null;
        $step1Label = $this->getLabelIdByName('Step1', $allLabel);
        $prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);
        $tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $step1Label .
                ',%" AND label_ids NOT LIKE "%,' . $prepaidLabel . ',%" AND label_ids NOT LIKE "%,' . $tabletLabel . ',%" AND crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $campaignIds = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $campaignIds[] = $row['campaign_id'];
                }
            }

            return $campaignIds;

        } catch (Exception $e) {
            return null;
        }
    }
    public function getSTEP1Campaign($crmId)
    {
        $allLabel = $this->getAllLabels();
        if ($allLabel == null)
            return null;
        $step1Label = $this->getLabelIdByName('Step1', $allLabel);
        $prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);
        $tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $step1Label .
                ',%" AND label_ids NOT LIKE "%,' . $prepaidLabel . ',%" AND label_ids NOT LIKE "%,' . $tabletLabel . ',%" AND crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $ret = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['campaign_id'], $row['label_ids']);
                }
            }
            return $ret;

        } catch (Exception $e) {
            return null;
        }
    }

    /*
	*@description
	*	Get all STEP2 campaign ids
	*@ret
	*	null, array of campaign id, crm id
	*/
    public function getAllSTEP2CampaignIds()
    {

        $allLabel = $this->getAllLabels();
        $step2Label = $this->getLabelIdByName('Step2', $allLabel);
        $prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);
        $tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $step2Label .
                ',%" AND label_ids NOT LIKE "%,' . $prepaidLabel . ',%" AND label_ids NOT LIKE "%,' . $tabletLabel . ',%"';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $campaignIds = array();
            $crmIds = array();
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $campaignIds[] = $row['campaign_id'];
                    $crmIds[] = $row['crm_id'];
                }
            }

            return array($campaignIds, $crmIds);

        } catch (Exception $e) {
            return null;
        }
    }

    /*
	*@description
	*	Get STEP2 campaign id of Crm
	*@param
	*	Crm Id
	*@ret
	*	null, array
	*/
    public function getSTEP2CampaignIds($crmId)
    {
        $allLabel = $this->getAllLabels();
        $step2Label = $this->getLabelIdByName('Step2', $allLabel);
        $prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);
        $tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $step2Label .
                ',%" AND label_ids NOT LIKE "%,' . $prepaidLabel . ',%" AND label_ids NOT LIKE "%,' . $tabletLabel .
                ',%" AND crm_id=' . $crmId;


            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $campaignIds = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $campaignIds[] = $row['campaign_id'];
                }
            }

            return $campaignIds;

        } catch (Exception $e) {
            return null;
        }
    }
    public function getSTEP2Campaign($crmId)
    {
        $allLabel = $this->getAllLabels();
        $step2Label = $this->getLabelIdByName('Step2', $allLabel);
        $prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);
        $tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $step2Label .
                ',%" AND label_ids NOT LIKE "%,' . $prepaidLabel . ',%" AND label_ids NOT LIKE "%,' . $tabletLabel .
                ',%" AND crm_id=' . $crmId;


            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $ret = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['campaign_id'], $row['label_ids']);
                }
            }

            return $ret;

        } catch (Exception $e) {
            return null;
        }
    }
    /*
	*@description
	*	Get all STEP2NonPrepaid campaign ids
	*@ret
	*	null, array
	*/
    public function getAllSTEP2NonPPCampaignIds()
    {

        $allLabel = $this->getAllLabels();
        $step2Label = $this->getLabelIdByName('Step2', $allLabel);
        $prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);

        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $step2Label .
                ',%" AND label_ids NOT LIKE "%,' . $prepaidLabel . ',%"';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $campaignIds = array();
            $crmIds = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $campaignIds[] = $row['campaign_id'];
                    $crmIds[] = $row['crm_id'];
                }
            }

            return array($campaignIds, $crmIds);

        } catch (Exception $e) {
            return null;
        }
    }

    /*
	*@description
	*	Get STEP1NonPrepaid campaign ids of Crm
	*@param
	*	Crm Id
	*@ret
	*	null, array
	*/
    public function getSTEP1NonPPCampaignIds($crmId)
    {
        $allLabel = $this->getAllLabels();
        $step1Label = $this->getLabelIdByName('Step1', $allLabel);
        $prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);

        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $step1Label .
                ',%" AND label_ids NOT LIKE "%,' . $prepaidLabel . ',%" AND crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $campaignIds = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $campaignIds[] = $row['campaign_id'];
                }
            }

            return $campaignIds;

        } catch (Exception $e) {
            return null;
        }
    }
    public function getSTEP1NonPPCampaign($crmId)
    {
        $allLabel = $this->getAllLabels();
        $step1Label = $this->getLabelIdByName('Step1', $allLabel);
        $prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);

        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $step1Label .
                ',%" AND label_ids NOT LIKE "%,' . $prepaidLabel . ',%" AND crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $ret = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['campaign_id'], $row['label_ids']);
                }
            }

            return $ret;

        } catch (Exception $e) {
            return null;
        }
    }
    /*
	*@description
	*	Get STEP2NonPrepaid campaign ids of Crm
	*@param
	*	Crm Id
	*@ret
	*	null, array
	*/
    public function getSTEP2NonPPCampaignIds($crmId)
    {
        $allLabel = $this->getAllLabels();
        $step2Label = $this->getLabelIdByName('Step2', $allLabel);
        $prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);

        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $step2Label .
                ',%" AND label_ids NOT LIKE "%,' . $prepaidLabel . ',%" AND crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $campaignIds = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $campaignIds[] = $row['campaign_id'];
                }
            }

            return $campaignIds;

        } catch (Exception $e) {
            return null;
        }
    }
    public function getSTEP2NonPPCampaign($crmId)
    {
        $allLabel = $this->getAllLabels();
        $step2Label = $this->getLabelIdByName('Step2', $allLabel);
        $prepaidLabel = $this->getLabelIdByName('Prepaids', $allLabel);

        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $step2Label .
                ',%" AND label_ids NOT LIKE "%,' . $prepaidLabel . ',%" AND crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $ret = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['campaign_id'], $row['label_ids']);
                }
            }

            return $ret;

        } catch (Exception $e) {
            return null;
        }
    }
    /*
	*@description
	*	Get all tablet campaign ids
	*@ret
	*	null, array
	*/
    public function getAllTabletCampaignIds()
    {

        $allLabel = $this->getAllLabels();
        $tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $tabletLabel . ',%"';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $campaignIds = array();
            $crmIds = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $campaignIds[] = $row['campaign_id'];
                    $crmIds[] = $row['crm_id'];
                }
            }

            return array($campaignIds, $crmIds);

        } catch (Exception $e) {
            return null;
        }
    }

    /*
	*@description
	*	Get tablet campaign ids of Crm
	*@param
	*	Crm Id
	*@ret
	*	null, array
	*/
    public function getTabletCampaignIds($crmId)
    {
        $allLabel = $this->getAllLabels();
        $tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $tabletLabel . ',%" AND crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $campaignIds = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $campaignIds[] = $row['campaign_id'];
                }
            }

            return $campaignIds;

        } catch (Exception $e) {
            return null;
        }
    }
    public function getTabletCampaign($crmId)
    {
        $allLabel = $this->getAllLabels();
        $tabletLabel = $this->getLabelIdByName('Tablet', $allLabel);

        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE label_ids LIKE ",' . $tabletLabel . ',%" AND crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $ret = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['campaign_id'], $row['label_ids']);
                }
            }

            return $ret;

        } catch (Exception $e) {
            return null;
        }
    }
    /*
	*@description
	*	Add Crm token
	*@param
	*	Crm Id, token, timestamp
	*@ret
	*	Boolean
	*/
    public function addCrmToken($crmId, $crmToken, $timestamp)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_crm_token WHERE crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (mysqli_num_rows($result) > 0) {
                $query = 'UPDATE ' . $this->subdomain . '_crm_token SET crm_token="' . $crmToken . '", timestamp=' . $timestamp . ' WHERE crm_id=' . $crmId;

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            } else {
                $query = 'INSERT INTO ' . $this->subdomain . '_crm_token (id, crm_id, crm_token, timestamp) VALUES(null,' .
                    $crmId . ',"' . $crmToken . '",' . $timestamp . ')';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            }

            if (!$result)
                return false;
            else
                return true;

        } catch (Exception $e) {

            return false;
        }
    }

    public function addKKCrmToken($crmId, $crmToken, $timestamp)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_kkcrm_token WHERE crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (mysqli_num_rows($result) > 0) {
                $query = 'UPDATE ' . $this->subdomain . '_kkcrm_token SET crm_token="' . $crmToken . '", timestamp=' . $timestamp . ' WHERE crm_id=' . $crmId;

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            } else {
                $query = 'INSERT INTO ' . $this->subdomain . '_kkcrm_token (id, crm_id, crm_token, timestamp) VALUES(null,' .
                    $crmId . ',"' . $crmToken . '",' . $timestamp . ')';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            }

            if (!$result)
                return false;
            else
                return true;

        } catch (Exception $e) {

            return false;
        }
    }

    /*
	*@description
	*	Get token of Crm
	*@param
	*	Crm Id
	*@ret
	*	null, array(token,timestamp)
	*/
    public function getCrmToken($crmId)
    {
        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_crm_token WHERE crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if (!$result)
                return null;

            $token = array();
            if (mysqli_num_rows($result) > 0) {
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

    public function getKKCrmToken($crmId)
    {
        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_kkcrm_token WHERE crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if (!$result)
                return null;

            $token = array();
            if (mysqli_num_rows($result) > 0) {
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

    /*
	*@description
	*	Get all affiliate
	*@ret
	*	null, array
	*/
    public function getAllAffiliate()
    {
        if (!$this->checkConnection())
            return null;
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_affiliate ORDER BY affiliate_id ASC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return null;
            $affiliates = array();
            $affilateCount = 0;
            if (mysqli_num_rows($result) > 0) {
                $aId = '';
                $goals = array();
                $label = '';
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($aId !== $row['affiliate_id']) {
                        if ($aId != '') {
                            $affiliate = array($aId, $label, $goals);
                            $affiliates[] = $affiliate;
                            $affilateCount++;
                            $goals = array();
                        }
                        $aId = $row['affiliate_id'];
                        $label = $row['label'];
                    }
                    $goals[] = array($row['crm_id'], $row['sales_goal']);
                }
                $affiliate = array($aId, $label, $goals);
                $affiliates[] = $affiliate;
                $affilateCount++;
            }

            $ret['affiliates'] = $affiliates;
            $ret['length'] = $affilateCount;

            return $affiliates;

        } catch (Exception $e) {
            return null;
        }
    }

    /*
	*@description
	*	Get affiliate
	*@param
	*	page number, item number
	*@ret
	*	null, array
	*/
    public function getAffiliate($pageNumber, $items4Page)
    {
        if (!$this->checkConnection())
            return null;
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_affiliate ORDER BY affiliate_id ASC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return null;
            $affiliates = array();
            $affilateCount = 0;
            if (mysqli_num_rows($result) > 0) {
                $aId = '';
                $goals = array();
                $label = '';
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($aId !== $row['affiliate_id']) {
                        if ($aId != '') {
                            $affiliate = array($aId, $label, $goals);
                            $affiliates[] = $affiliate;
                            $affilateCount++;
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
                $affilateCount++;
            }

            $ret['affiliates'] = $affiliates;
            $ret['length'] = $affilateCount;

            return $ret;

        } catch (Exception $e) {
            return null;
        }
    }

    /*
	*@description
	*	Add new affiliate
	*@param
	*	Affiliate id, label, crm ids, goals
	*@ret
	*	Boolean
	*/
    public function addAffiliate($affiliateId, $affiliateLabel, $crmIds, $goals)
    {
        if (!$this->checkConnection())
            return false;
        try {

            $result = $this->deleteAffiliate($affiliateId);
            if (!$result)
                return false;
            $crmIds = explode(',', $crmIds);
            $goals = explode(',', $goals);

            for ($i = 0; $i < count($crmIds); $i++) {
                $crmId = $crmIds[$i];
                $goal = $goals[$i];

                $query = 'INSERT INTO ' . $this->subdomain . '_label_affiliate (id, affiliate_id, label, crm_id, sales_goal) VALUES (null,"' .
                    $affiliateId . '","' . $affiliateLabel . '",' . $crmId . ',' . $goal . ')';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if (!$result)
                    return false;
            }

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /*
	*@description
	*	Delete affiliate
	*@param
	*	Affiliate id
	*@ret
	*	Boolean
	*/
    public function deleteAffiliate($affiliateId)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'DELETE FROM ' . $this->subdomain . '_label_affiliate WHERE affiliate_id="' . $affiliateId . '"';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return false;
            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /*
	*@description
	*	Update affiliate
	*@param
	*	Affiliate id, label, crm ids, goals
	*@ret
	*	Boolean
	*/
    public function updateAffiliate($affiliateId, $affiliateLabel, $crmIds, $goals)
    {
        if (!$this->checkConnection())
            return false;
        try {

            $result = $this->deleteAffiliate($affiliateId);
            if (!$result)
                return false;

            $crmIds = explode(',', $crmIds);
            $goals = explode(',', $goals);

            for ($i = 0; $i < count($crmIds); $i++) {
                $crmId = $crmIds[$i];
                $goal = $goals[$i];

                $query = 'INSERT INTO ' . $this->subdomain . '_label_affiliate (id, affiliate_id, label, crm_id, sales_goal) VALUES (null,"' . $affiliateId . '","' . $affiliateLabel . '",' . $crmId . ',' . $goal . ')';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if (!$result)
                    return false;
            }

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /*
	*@description
	*	Get affiliate sum of affiliate_progress
	*@param
	*	user token
	*@ret
	*	array(affiliate_id, sumSTEP1, sumSTEP2, sumTablet, sumSTEP2NNPP, affiliate_label)
	*/
    public function getAffiliateSum($userToken)
    {
        $allLabel = $this->getAllLabels();
        if ($allLabel == null)
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
            for ($i = 0; $i < count($crmIds); $i++) {
                $camId = $campaignIds[$i];
                $crmId = $crmIds[$i];

                $cond = $cond . '(campaign_id=' . $camId;
                $cond = $cond . ' AND crm_id=' . $crmId . ')';

                if ($i != count($crmIds) - 1) {
                    $cond = $cond . ' OR ';
                }
            }
            $affiliate_progress_table = $this->subdomain . '_affiliate_progress';
            $query = 'SELECT affiliate_id, sum(initial_customer) AS sum FROM ' . $affiliate_progress_table . ' WHERE user_token=' . $userToken;

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY affiliate_id';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumSTEP1[] = array($row['affiliate_id'], $row['sum']);
                    }
                }
            }

            // STEP2
            $sumSTEP2 = array();

            $campaignIds = $allSTEP2CampaignIds[0];
            $crmIds = $allSTEP2CampaignIds[1];

            $cond = "";
            for ($i = 0; $i < count($crmIds); $i++) {
                $camId = $campaignIds[$i];
                $crmId = $crmIds[$i];

                $cond = $cond . '(campaign_id=' . $camId;
                $cond = $cond . ' AND crm_id=' . $crmId . ')';
                if ($i != count($crmIds) - 1) {
                    $cond = $cond . ' OR ';
                }
            }

            $query = 'SELECT affiliate_id, sum(initial_customer) AS sum FROM ' . $affiliate_progress_table . ' WHERE user_token=' . $userToken;

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY affiliate_id';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumSTEP2[] = array($row['affiliate_id'], $row['sum']);
                    }
                }
            }
            // STEP2NNPP
            $sumSTEP2NNPP = array();

            $campaignIds = $allSTEP2NPPCampaignIds[0];
            $crmIds = $allSTEP2NPPCampaignIds[1];

            $cond = "";
            for ($i = 0; $i < count($crmIds); $i++) {
                $camId = $campaignIds[$i];
                $crmId = $crmIds[$i];

                $cond = $cond . '(campaign_id=' . $camId;
                $cond = $cond . ' AND crm_id=' . $crmId . ')';

                if ($i != count($crmIds) - 1) {
                    $cond = $cond . ' OR ';
                }
            }
            $query = 'SELECT affiliate_id, sum(initial_customer) AS sum FROM ' . $affiliate_progress_table . ' WHERE user_token=' . $userToken;

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY affiliate_id';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumSTEP2NNPP[] = array($row['affiliate_id'], $row['sum']);
                    }
                }
            }

            // TABLET
            $sumTABLET = array();

            $campaignIds = $allTabletCampaignIds[0];
            $crmIds = $allTabletCampaignIds[1];

            $cond = "";
            for ($i = 0; $i < count($crmIds); $i++) {
                $camId = $campaignIds[$i];
                $crmId = $crmIds[$i];

                $cond = $cond . '(campaign_id=' . $camId;
                $cond = $cond . ' AND crm_id=' . $crmId . ')';
                if ($i != count($crmIds) - 1) {
                    $cond = $cond . ' OR ';
                }
            }
            $query = 'SELECT affiliate_id, sum(initial_customer) AS sum FROM ' . $affiliate_progress_table . ' WHERE user_token=' . $userToken;

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY affiliate_id';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumTABLET[] = array($row['affiliate_id'], $row['sum']);
                    }
                }
            }

            // get all affiliates per user
            $query = 'SELECT affiliate_id, affiliate_label FROM ' . $affiliate_progress_table . ' WHERE user_token =' . $userToken . ' GROUP BY affiliate_id, affiliate_label';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $affiliateIds = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $affiliateIds[] = array($row['affiliate_id'], $row['affiliate_label']);
                }
            }

            // arrange sum per affiliate
            $ret = array();
            foreach ($affiliateIds as $affiliateId) {
                // step1
                $sumSTEP1_affiliate = '0';
                foreach ($sumSTEP1 as $sumSTEP1Affiliate) {
                    if ($sumSTEP1Affiliate[0] == $affiliateId[0]) {
                        $sumSTEP1_affiliate = $sumSTEP1Affiliate[1];
                        break;
                    }

                }
                // step2
                $sumSTEP2_affiliate = '0';
                foreach ($sumSTEP2 as $sumSTEP2Affiliate) {
                    if ($sumSTEP2Affiliate[0] == $affiliateId[0]) {
                        $sumSTEP2_affiliate = $sumSTEP2Affiliate[1];
                        break;
                    }
                }
                // step2 non prepaids
                $sumSTEP2NNPP_affiliate = '0';
                foreach ($sumSTEP2NNPP as $sumSTEP2NNPPAffiliate) {
                    if ($sumSTEP2NNPPAffiliate[0] == $affiliateId[0]) {
                        $sumSTEP2NNPP_affiliate = $sumSTEP2NNPPAffiliate[1];
                        break;
                    }
                }
                // tablet
                $sumTablet_affiliate = '0';
                foreach ($sumTABLET as $sumTABLETAffiliate) {
                    if ($sumTABLETAffiliate[0] == $affiliateId[0]) {
                        $sumTablet_affiliate = $sumTABLETAffiliate[1];
                        break;
                    }
                }
                $ret[] = array($affiliateId[0], $sumSTEP1_affiliate, $sumSTEP2_affiliate, $sumTablet_affiliate, $sumSTEP2NNPP_affiliate, $affiliateId[1]);
            }
            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }

    /*
	*@description
	*	Delete affiliate progress
	*@param
	*	user token
	*@ret
	*	Boolean
	*/
    public function deleteAffiliateProgress($userToken)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_affiliate_progress WHERE user_token=' . $userToken;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return false;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /*
	*@description
	*	Delete affiliate progress by Crm id
	*@param
	*	user token, crm id
	*@ret
	*	Boolean
	*/
    public function deleteAffiliateProgressByCrmID($userToken, $crmID)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_affiliate_progress WHERE user_token=' . $userToken . ' AND crm_id=' . $crmID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return false;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }
    public function deleteAffiliateCacheByCrmID($crmID, $type)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_affiliate_progress_cache WHERE date_type='.$type.' AND crm_id=' . $crmID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return false;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }
    /*
	*@description
	*	Add affiliate progress
	*@param
	*	data
	*@ret
	*	Boolean
	*/
    public function addAffiliateProgress($data)
    {
        if (!$this->checkConnection())
            return false;

        try {

            foreach ($data as $item) {

                $query = 'INSERT INTO ' . $this->subdomain . '_affiliate_progress (id, crm_id, campaign_id, initial_customer, affiliate_id, affiliate_label, user_token) VALUES (null,' . $item['crm_id'] . ',' . $item['campaign_id'] . ',' . $item['initial_customer'] . ',"'
                    . $item['affiliate_id'] . '","' . $item['label'] . '",' . $item['user_token'] . ')';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if (!$result)
                    return false;
            }

            return true;

        } catch (Exception $e) {
            return false;
        }

    }
    public function addAffiliateCache($data, $startDate, $endDate, $type)
    {
        if (!$this->checkConnection())
            return false;

        try {

            foreach ($data as $item) {

                $query = 'INSERT INTO ' . $this->subdomain . '_affiliate_progress_cache (id, crm_id, campaign_id, initial_customer, affiliate_id, affiliate_label, start_date, end_date, date_type) VALUES (null,' . $item['crm_id'] . ',' . $item['campaign_id'] . ',' . $item['initial_customer'] . ',"'
                    . $item['affiliate_id'] . '","' . $item['label'] . '","' . $startDate.'","'.$endDate.'",'.$type. ')';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if (!$result)
                    return false;
            }

            return true;

        } catch (Exception $e) {
            return false;
        }
    }
    /*
	*@description
	*	Check if campaign label exists
	*@param
	*	campaign id, crm id
	*@ret
	*	Boolean
	*/
    public function checkLabelingOfCampaign($campaignId, $crmId)
    {
        if (!$this->checkConnection())
            return false;
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_label_campaign WHERE campaign_id=' . $campaignId . ' AND crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (mysqli_num_rows($result) > 0)
                return true;
            else
                return false;

        } catch (Exception $e) {
            return false;
        }
    }

    /*
	*@description
	*	Get affiliate by crm
	*@param
	*	crm id
	*@ret
	*	array
	*/
    public function getAffiliatesByCrmId($crmId)
    {
        $affiliates = array();

        if (!$this->checkConnection())
            return $affiliates;

        try {

            $query = 'SELECT * FROM ' . $this->subdomain . '_label_affiliate WHERE crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $affiliates[] = array($row['affiliate_id'], $row['label'], $row['sales_goal']);
                }
            }
            return $affiliates;

        } catch (Exception $e) {
            return array();
        }
    }
    public function getAffiliateCacheSumPerCRM($userID, $date_type, $affiliateID)
    {
        $crmPermissions = $this->getCrmPermissionOfAccount($userID);
        $crmPermissions = explode(',', $crmPermissions);

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
            $flag = false;
            for ($i = 0; $i < count($crmIds); $i++)
            {
                $crmId = $crmIds[$i];
                if (in_array($crmId, $crmPermissions))
                {
                    if (!$flag)
                        $flag = true;
                    else if ($cond != "")
                        $cond = $cond . ' OR ';

                    $camId = $campaignIds[$i];
                    $cond = $cond . '(campaign_id=' . $camId;
                    $cond = $cond . ' AND crm_id=' . $crmId . ')';
                }
            }
            $affiliate_cache_table = $this->subdomain . '_affiliate_progress_cache';

            $query = 'SELECT crm_id,sum(initial_customer) AS sum FROM ' . $affiliate_cache_table . ' WHERE date_type=' . $date_type . ' AND affiliate_id="' . $affiliateID . '"';

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY affiliate_id';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumSTEP1[] = array($row['crm_id'], $row['sum']);
                    }
                }
            }
            // STEP2
            $sumSTEP2 = array();

            $campaignIds = $allSTEP2CampaignIds[0];
            $crmIds = $allSTEP2CampaignIds[1];

            $cond = "";
            $flag = false;
            for ($i = 0; $i < count($crmIds); $i++)
            {
                $crmId = $crmIds[$i];
                if (in_array($crmId, $crmPermissions))
                {
                    if (!$flag)
                        $flag = true;
                    else if ($cond != "")
                        $cond = $cond . ' OR ';

                    $camId = $campaignIds[$i];
                    $cond = $cond . '(campaign_id=' . $camId;
                    $cond = $cond . ' AND crm_id=' . $crmId . ')';
                }
            }

            $query = 'SELECT crm_id,sum(initial_customer) AS sum FROM ' . $affiliate_cache_table . ' WHERE date_type=' . $date_type . ' AND affiliate_id="' . $affiliateID . '"';

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY affiliate_id';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumSTEP2[] = array($row['crm_id'], $row['sum']);
                    }
                }
            }
            // STEP2NNPP
            $sumSTEP2NNPP = array();

            $campaignIds = $allSTEP2NPPCampaignIds[0];
            $crmIds = $allSTEP2NPPCampaignIds[1];

            $cond = "";
            $flag = false;
            for ($i = 0; $i < count($crmIds); $i++)
            {
                $crmId = $crmIds[$i];
                if (in_array($crmId, $crmPermissions))
                {
                    if (!$flag)
                        $flag = true;
                    else if ($cond != "")
                        $cond = $cond . ' OR ';

                    $camId = $campaignIds[$i];
                    $cond = $cond . '(campaign_id=' . $camId;
                    $cond = $cond . ' AND crm_id=' . $crmId . ')';
                }
            }
            $query = 'SELECT crm_id,sum(initial_customer) AS sum FROM ' . $affiliate_cache_table . ' WHERE date_type=' . $date_type . ' AND affiliate_id="' . $affiliateID . '"';

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY affiliate_id';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumSTEP2NNPP[] = array($row['crm_id'], $row['sum']);
                    }
                }
            }

            // TABLET
            $sumTABLET = array();

            $campaignIds = $allTabletCampaignIds[0];
            $crmIds = $allTabletCampaignIds[1];

            $cond = "";
            $flag = false;
            for ($i = 0; $i < count($crmIds); $i++)
            {
                $crmId = $crmIds[$i];
                if (in_array($crmId, $crmPermissions))
                {
                    if (!$flag)
                        $flag = true;
                    else if ($cond != "")
                        $cond = $cond . ' OR ';

                    $camId = $campaignIds[$i];
                    $cond = $cond . '(campaign_id=' . $camId;
                    $cond = $cond . ' AND crm_id=' . $crmId . ')';
                }
            }
            $query = 'SELECT crm_id,sum(initial_customer) AS sum FROM ' . $affiliate_cache_table . ' WHERE date_type=' . $date_type . ' AND affiliate_id="' . $affiliateID . '"';

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY affiliate_id';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumTABLET[] = array($row['crm_id'], $row['sum']);
                    }
                }
            }

            // get all crmId per user
            $query = 'SELECT crm_id FROM ' . $affiliate_cache_table . ' WHERE date_type =' . $date_type . ' GROUP BY crm_id DESC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $crmIds = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
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
                    if ($sumSTEP1Crm[0] == $crmId) {
                        $sumSTEP1_crm = $sumSTEP1Crm[1];
                        break;
                    }
                }
                // step2
                $sumSTEP2_crm = '0';
                foreach ($sumSTEP2 as $sumSTEP2Crm) {
                    if ($sumSTEP2Crm[0] == $crmId) {
                        $sumSTEP2_crm = $sumSTEP2Crm[1];
                        break;
                    }
                }
                // step2 non prepaids
                $sumSTEP2NNPP_crm = '0';
                foreach ($sumSTEP2NNPP as $sumSTEP2NNPPCrm) {
                    if ($sumSTEP2NNPPCrm[0] == $crmId) {
                        $sumSTEP2NNPP_crm = $sumSTEP2NNPPCrm[1];
                        break;
                    }
                }
                // tablet
                $sumTablet_crm = '0';
                foreach ($sumTABLET as $sumTABLETCrm) {
                    if ($sumTABLETCrm[0] == $crmId) {
                        $sumTablet_crm = $sumTABLETCrm[1];
                        break;
                    }
                }
                $crmName = $this->getCrmNameByCrmId($crmId, $crmTable);
                $sales_goal = $this->getAffiliateSalesGoal($affiliateID, $crmId, $affiliateLableTable);

                $ret[] = array($crmId, $sumSTEP1_crm, $sumSTEP2_crm, $sumTablet_crm, $sumSTEP2NNPP_crm, $crmName, $sales_goal);
            }
            return $ret;
        } catch (Exception $e) {
            return array();
        }
    }
    public function getAffiliateCacheSum($userID, $date_type)
    {
        $crmPermissions = $this->getCrmPermissionOfAccount($userID);
        $crmPermissions = explode(',', $crmPermissions);

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
            $flag = false;
            for ($i = 0; $i < count($crmIds); $i++)
            {
                $crmId = $crmIds[$i];
                if (in_array($crmId, $crmPermissions))
                {
                    if (!$flag)
                        $flag = true;
                    else if ($cond != "")
                        $cond = $cond . ' OR ';

                    $camId = $campaignIds[$i];
                    $cond = $cond . '(campaign_id=' . $camId;
                    $cond = $cond . ' AND crm_id=' . $crmId . ')';
                }
            }
            $affiliate_cache_table = $this->subdomain . '_affiliate_progress_cache';

            $query = 'SELECT affiliate_id, sum(initial_customer) AS sum FROM ' . $affiliate_cache_table . ' WHERE date_type=' . $date_type;

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY affiliate_id';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumSTEP1[] = array($row['affiliate_id'], $row['sum']);
                    }
                }
            }
            // STEP2
            $sumSTEP2 = array();

            $campaignIds = $allSTEP2CampaignIds[0];
            $crmIds = $allSTEP2CampaignIds[1];

            $cond = "";
            $flag = false;
            for ($i = 0; $i < count($crmIds); $i++)
            {
                $crmId = $crmIds[$i];
                if (in_array($crmId, $crmPermissions))
                {
                    if (!$flag)
                        $flag = true;
                    else if ($cond != "")
                        $cond = $cond . ' OR ';

                    $camId = $campaignIds[$i];
                    $cond = $cond . '(campaign_id=' . $camId;
                    $cond = $cond . ' AND crm_id=' . $crmId . ')';
                }
            }

            $query = 'SELECT affiliate_id, sum(initial_customer) AS sum FROM ' . $affiliate_cache_table . ' WHERE date_type=' . $date_type;

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY affiliate_id';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumSTEP2[] = array($row['affiliate_id'], $row['sum']);
                    }
                }
            }
            // STEP2NNPP
            $sumSTEP2NNPP = array();

            $campaignIds = $allSTEP2NPPCampaignIds[0];
            $crmIds = $allSTEP2NPPCampaignIds[1];

            $cond = "";
            $flag = false;
            for ($i = 0; $i < count($crmIds); $i++)
            {
                $crmId = $crmIds[$i];
                if (in_array($crmId, $crmPermissions))
                {
                    if (!$flag)
                        $flag = true;
                    else if ($cond != "")
                        $cond = $cond . ' OR ';

                    $camId = $campaignIds[$i];
                    $cond = $cond . '(campaign_id=' . $camId;
                    $cond = $cond . ' AND crm_id=' . $crmId . ')';
                }
            }
            $query = 'SELECT affiliate_id, sum(initial_customer) AS sum FROM ' . $affiliate_cache_table . ' WHERE date_type=' . $date_type;

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY affiliate_id';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumSTEP2NNPP[] = array($row['affiliate_id'], $row['sum']);
                    }
                }
            }

            // TABLET
            $sumTABLET = array();

            $campaignIds = $allTabletCampaignIds[0];
            $crmIds = $allTabletCampaignIds[1];

            $cond = "";
            $flag = false;
            for ($i = 0; $i < count($crmIds); $i++)
            {
                $crmId = $crmIds[$i];
                if (in_array($crmId, $crmPermissions))
                {
                    if (!$flag)
                        $flag = true;
                    else if ($cond != "")
                        $cond = $cond . ' OR ';

                    $camId = $campaignIds[$i];
                    $cond = $cond . '(campaign_id=' . $camId;
                    $cond = $cond . ' AND crm_id=' . $crmId . ')';
                }
            }
            $query = 'SELECT affiliate_id, sum(initial_customer) AS sum FROM ' . $affiliate_cache_table . ' WHERE date_type=' . $date_type;

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY affiliate_id';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumTABLET[] = array($row['affiliate_id'], $row['sum']);
                    }
                }
            }
            // get all affiliates per user
            $crmPermissionString = join("','", $crmPermissions);
            $query = 'SELECT affiliate_id, affiliate_label FROM ' . $affiliate_cache_table . ' WHERE date_type =' . $date_type.' AND crm_id IN'."('$crmPermissionString')".' GROUP BY affiliate_id, affiliate_label';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $affiliateIds = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $affiliateIds[] = array($row['affiliate_id'], $row['affiliate_label']);
                }
            }

            // arrange sum per affiliate
            $ret = array();
            foreach ($affiliateIds as $affiliateId) {
                // step1
                $sumSTEP1_affiliate = '0';
                foreach ($sumSTEP1 as $sumSTEP1Affiliate) {
                    if ($sumSTEP1Affiliate[0] == $affiliateId[0]) {
                        $sumSTEP1_affiliate = $sumSTEP1Affiliate[1];
                        break;
                    }

                }
                // step2
                $sumSTEP2_affiliate = '0';
                foreach ($sumSTEP2 as $sumSTEP2Affiliate) {
                    if ($sumSTEP2Affiliate[0] == $affiliateId[0]) {
                        $sumSTEP2_affiliate = $sumSTEP2Affiliate[1];
                        break;
                    }
                }
                // step2 non prepaids
                $sumSTEP2NNPP_affiliate = '0';
                foreach ($sumSTEP2NNPP as $sumSTEP2NNPPAffiliate) {
                    if ($sumSTEP2NNPPAffiliate[0] == $affiliateId[0]) {
                        $sumSTEP2NNPP_affiliate = $sumSTEP2NNPPAffiliate[1];
                        break;
                    }
                }
                // tablet
                $sumTablet_affiliate = '0';
                foreach ($sumTABLET as $sumTABLETAffiliate) {
                    if ($sumTABLETAffiliate[0] == $affiliateId[0]) {
                        $sumTablet_affiliate = $sumTABLETAffiliate[1];
                        break;
                    }
                }
                $ret[] = array($affiliateId[0], $sumSTEP1_affiliate, $sumSTEP2_affiliate, $sumTablet_affiliate, $sumSTEP2NNPP_affiliate, $affiliateId[1]);
            }
            $campaignStatus = array();
            // get campaign status
            foreach ($crmPermissions as $crmID)
            {
                $query = 'SELECT campaign_id FROM ' . $affiliate_cache_table . ' WHERE date_type=' . $date_type. ' AND crm_id='.$crmID;
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                $campaignCount = mysqli_num_rows($result);
                $crmName = $this->getCrmName($crmID);
                $campaignStatus[] = array($crmID, $crmName, $campaignCount);
            }
            return array($campaignStatus, $ret);

        } catch (Exception $e) {
            return array();
        }
    }
    /*
	*@description
	*	Get affiliate sum per Crm
	*@param
	*	user token, affiliate id
	*@ret
	*	null, array
	*/
    public function getAffiliateSumPerCrm($userToken, $affiliateId)
    {
        $allLabel = $this->getAllLabels();
        if ($allLabel == null)
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
            for ($i = 0; $i < count($crmIds); $i++) {
                $camId = $campaignIds[$i];
                $crmId = $crmIds[$i];

                $cond = $cond . '(campaign_id=' . $camId;
                $cond = $cond . ' AND crm_id=' . $crmId . ')';
                if ($i != count($crmIds) - 1) {
                    $cond = $cond . ' OR ';
                }
            }

            $affiliate_progress_table = $this->subdomain . '_affiliate_progress';

            $query = 'SELECT crm_id,sum(initial_customer) AS sum FROM ' . $affiliate_progress_table . ' WHERE user_token=' . $userToken . ' AND affiliate_id="' . $affiliateId . '"';

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY crm_id';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumSTEP1[] = array($row['crm_id'], $row['sum']);
                    }
                }
            }

            // STEP2
            $sumSTEP2 = array();

            $campaignIds = $allSTEP2CampaignIds[0];
            $crmIds = $allSTEP2CampaignIds[1];

            $cond = "";
            for ($i = 0; $i < count($crmIds); $i++) {
                $camId = $campaignIds[$i];
                $crmId = $crmIds[$i];

                $cond = $cond . '(campaign_id=' . $camId;
                $cond = $cond . ' AND crm_id=' . $crmId . ')';
                if ($i != count($crmIds) - 1) {
                    $cond = $cond . ' OR ';
                }
            }
            $query = 'SELECT crm_id,sum(initial_customer) AS sum FROM ' . $affiliate_progress_table . ' WHERE user_token=' . $userToken . ' AND affiliate_id="' . $affiliateId . '"';

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY crm_id';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumSTEP2[] = array($row['crm_id'], $row['sum']);
                    }
                }
            }
            // STEP2NNPP
            $sumSTEP2NNPP = array();

            $campaignIds = $allSTEP2NPPCampaignIds[0];
            $crmIds = $allSTEP2NPPCampaignIds[1];

            $cond = "";
            for ($i = 0; $i < count($crmIds); $i++) {
                $camId = $campaignIds[$i];
                $crmId = $crmIds[$i];

                $cond = $cond . '(campaign_id=' . $camId;
                $cond = $cond . ' AND crm_id=' . $crmId . ')';
                if ($i != count($crmIds) - 1) {
                    $cond = $cond . ' OR ';
                }
            }
            $query = 'SELECT crm_id,sum(initial_customer) AS sum FROM ' . $affiliate_progress_table . ' WHERE user_token=' . $userToken . ' AND affiliate_id="' . $affiliateId . '"';

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY crm_id';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumSTEP2NNPP[] = array($row['crm_id'], $row['sum']);
                    }
                }
            }

            // TABLET
            $sumTABLET = array();

            $campaignIds = $allTabletCampaignIds[0];
            $crmIds = $allTabletCampaignIds[1];

            $cond = "";
            for ($i = 0; $i < count($crmIds); $i++) {
                $camId = $campaignIds[$i];
                $crmId = $crmIds[$i];

                $cond = $cond . "(campaign_id=" . $camId;
                $cond = $cond . " AND crm_id=" . $crmId . ")";
                if ($i != count($crmIds) - 1) {
                    $cond = $cond . " OR ";
                }
            }
            $query = 'SELECT crm_id, sum(initial_customer) AS sum FROM ' . $affiliate_progress_table . ' WHERE user_token=' . $userToken . ' AND affiliate_id="' . $affiliateId . '"';

            if ($cond !== "") {
                $query = $query . ' AND (' . $cond . ') GROUP BY crm_id';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sumTABLET[] = array($row['crm_id'], $row['sum']);
                    }
                }
            }

            // get all crmId per user
            $query = 'SELECT crm_id FROM ' . $affiliate_progress_table . ' WHERE user_token =' . $userToken . ' GROUP BY crm_id DESC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $crmIds = array();

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
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
                    if ($sumSTEP1Crm[0] == $crmId) {
                        $sumSTEP1_crm = $sumSTEP1Crm[1];
                        break;
                    }

                }
                // step2
                $sumSTEP2_crm = '0';
                foreach ($sumSTEP2 as $sumSTEP2Crm) {
                    if ($sumSTEP2Crm[0] == $crmId) {
                        $sumSTEP2_crm = $sumSTEP2Crm[1];
                        break;
                    }
                }
                // step2 non prepaids
                $sumSTEP2NNPP_crm = '0';
                foreach ($sumSTEP2NNPP as $sumSTEP2NNPPCrm) {
                    if ($sumSTEP2NNPPCrm[0] == $crmId) {
                        $sumSTEP2NNPP_crm = $sumSTEP2NNPPCrm[1];
                        break;
                    }
                }
                // tablet
                $sumTablet_crm = '0';
                foreach ($sumTABLET as $sumTABLETCrm) {
                    if ($sumTABLETCrm[0] == $crmId) {
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

    /*
	*@description
	*	Get Crm Name by id
	*@param
	*	crm id, crm table
	*@ret
	*	string
	*/
    private function getCrmNameByCrmId($crmId, $crmTable)
    {
        foreach ($crmTable as $row) {
            if ($row[0] == $crmId)
                return $row[1];
        }
        return '';
    }

    /*
	*@description
	*	Get Crm name by id
	*@param
	*	crm id
	*@ret
	*	string
	*/
    public function getCrmName($crmId)
    {
        $allCrm = $this->getAllCrm();
        $ret = $this->getCrmNameByCrmId($crmId, $allCrm);
        return $ret;
    }

    /*
	*@description
	*	Get affiliate sales goal
	*@param
	*	affiliate id, crm id, table
	*@ret
	*	sales goal
	*/
    private function getAffiliateSalesGoal($affiliateId, $crmId, $table)
    {
        foreach ($table as $row) {
            if ($row['0'] == $affiliateId && $row['1'] == $crmId)
                return $row['2'];
        }
        return '0';
    }

    /*
	*@description
	*	Get crm permissions of account
	*@param
	*	Account Id
	*@return
	*	Permission string
	*/
    private function getCrmPermissionOfAccount($accountId)
    {
        if (!$this->checkConnection())
            return '';

        try {
            $strPermissions = '';

            $query = 'SELECT crm_permissions FROM ' . $this->subdomain . '_user_account WHERE id=' . $accountId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $strPermissions = $row['crm_permissions'];
            }

            return $strPermissions;
        } catch (Exception $e) {
            return '';
        }
    }

    /*
	*@description
	*	Get crm info of account
	*@param
	*	Account Id
	*@return
	*	array(id, name, permission)
	*/
    public function getCrmNamePermissionOfAccount($accountId)
    {
        $permissionString = $this->getCrmPermissionOfAccount($accountId);

        $permissions = explode(',', $permissionString);
        $result = array();

        $allCrm = $this->getAllCrm();
        foreach ($allCrm as $value) {
            $crmId = $value[0];
            $crmName = $value[1];
            $permission = 0;
            if (in_array($crmId, $permissions)) {
                $permission = 1;
            }
            $result[] = array($crmId, $crmName, $permission);
        }

        return $result;
    }

    /*
	*@description
	*	Set Crm permission of account as string ids seperated by comma
	*@param
	*	Account Id, Permission string
	*@ret
	*	Boolean
	*/
    public function setCrmPermissionOfAccount($accountId, $strPermissions)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'UPDATE ' . $this->subdomain . '_user_account SET crm_permissions="' . $strPermissions . '" WHERE id=' . $accountId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE)
                return true;
            else
                return false;

        } catch (Exception $e) {
            return false;
        }
    }

    /*
	*@description
	*	Apis ofr retention export for quick and deep
	*/
    public function deleteRetentionQuickByCrmId($userToken, $crmId)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_retention_quick_export WHERE user_token="' . $userToken . '" and crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return false;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteRetentionExportByCrmId($userToken, $crmId)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_retention_export WHERE user_token="' . $userToken . '" and crm_id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return false;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteRetentionForInitialAlertByCrmID($crmId, $day)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_retention_initial_alert WHERE crm_id=' . $crmId . ' and day=' . $day;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return false;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteRetentionQuickByCampaignId($userToken, $crmId, $campaignId)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_retention_quick_export WHERE user_token="' . $userToken . '" and crm_id=' . $crmId . ' and campaign_id=' . $campaignId . ' and affiliate_id !=""';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return false;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteRetentionExportByCampaignId($userToken, $crmId, $campaignId)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_retention_export WHERE user_token="' . $userToken . '" and crm_id=' . $crmId . ' and campaign_id=' . $campaignId . ' and affiliate_id !=""';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return false;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteRetentionQuickByAffiliateId($userToken, $crmId, $campaignId, $affiliateId)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_retention_quick_export WHERE user_token="' . $userToken . '" and crm_id=' . $crmId . ' and campaign_id=' . $campaignId . ' and affiliate_id="' . $affiliateId . '" and subaffiliate_id != ""';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return false;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteRetentionExportByAffiliateId($userToken, $crmId, $campaignId, $affiliateId)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_retention_export WHERE user_token="' . $userToken . '" and crm_id=' . $crmId . ' and campaign_id=' . $campaignId . ' and affiliate_id="' . $affiliateId . '" and subaffiliate_id != ""';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return false;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    public function writeRetentionForInitialAlert($crmId, $crmName, $campaignId, $affiliateId, $subAffiliateId, $gross_order, $net_approved, $approval_rate, $day, $has_child, $fromDate, $toDate, $timestamp)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'INSERT INTO ' . $this->subdomain . '_retention_initial_alert (id, crm_id, crm_name, campaign_id, affiliate_id, subaffiliate_id, gross_order, net_approved, approval_rate, day, has_child, from_date, to_date, timestamp) VALUES (null,'
                . $crmId . ',"' . $crmName . '",' . $campaignId . ',"' . $affiliateId . '","' . $subAffiliateId . '",' . $gross_order . ',' . $net_approved . ',' . $approval_rate . ',' . $day . ',' . $has_child . ',"' . $fromDate . '","' . $toDate . '","' . $timestamp . '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return false;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    public function writeRetentionExport($crmId, $crmName, $campaignId, $campaignName, $campaignLabel, $affiliateId, $affiliateLabel, $subAffiliateId, $subAffiliateLabel, $init1, $init2, $init3, $init4, $init5, $init6, $second1, $second2, $second3, $second4, $second5, $second6, $userToken)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'INSERT INTO ' . $this->subdomain . '_retention_export (id, crm_id, crm_name, campaign_id, campaign_name, campaign_label, affiliate_id, affiliate_label, subaffiliate_id, subaffiliate_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6, user_token) VALUES (null,'
                . $crmId . ',"' . $crmName . '",' . $campaignId . ',"' . $campaignName . '","' . $campaignLabel . '","' . $affiliateId . '","' . $affiliateLabel . '","' . $subAffiliateId . '","' . $subAffiliateLabel . '","' . $init1 . '","' . $init2 . '","' . $init3 . '","' . $init4 . '","' . $init5 . '","' . $init6 . '","' . $second1 . '","' . $second2 . '","' . $second3 . '","' . $second4 . '","' . $second5 . '","' . $second6 . '","' . $userToken . '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return false;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    public function writeRetentionQuickExport($crmId, $crmName, $campaignId, $campaignName, $campaignLabel, $affiliateId, $affiliateLabel, $subAffiliateId, $subAffiliateLabel, $init1, $init2, $init3, $init4, $init5, $init6, $second1, $second2, $second3, $second4, $second5, $second6, $userToken)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'INSERT INTO ' . $this->subdomain . '_retention_quick_export (id, crm_id, crm_name, campaign_id, campaign_name, campaign_label, affiliate_id, affiliate_label, subaffiliate_id, subaffiliate_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6, user_token) VALUES (null,'
                . $crmId . ',"' . $crmName . '",' . $campaignId . ',"' . $campaignName . '","' . $campaignLabel . '","' . $affiliateId . '","' . $affiliateLabel . '","' . $subAffiliateId . '","' . $subAffiliateLabel . '","' . $init1 . '","' . $init2 . '","' . $init3 . '","' . $init4 . '","' . $init5 . '","' . $init6 . '","' . $second1 . '","' . $second2 . '","' . $second3 . '","' . $second4 . '","' . $second5 . '","' . $second6 . '","' . $userToken . '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (!$result)
                return false;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    public function GetCRMList4Export($user_token)
    {
        if (!$this->checkConnection())
            return array();

        try {

            $ret = array();

            $query = 'SELECT crm_id, crm_name FROM ' . $this->subdomain . '_retention_export WHERE user_token="' . $user_token . '" GROUP BY crm_id, crm_name';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                $i = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[$i] = array($row['crm_id'], $row['crm_name']);
                    $i++;
                }
            }

            return $ret;


        } catch (Exception $e) {
            return array();
        }
    }

    public function GetCampaignList4QuickExport($crm_id, $user_token)
    {
        if (!$this->checkConnection())
            return array();

        try {

            $ret = array();

            $query = 'SELECT campaign_id, campaign_name, campaign_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6 FROM ' . $this->subdomain . '_retention_quick_export WHERE user_token="' . $user_token . '" and crm_id=' . $crm_id . ' and affiliate_id ="" and subaffiliate_id = ""';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['campaign_id'], $row['campaign_name'], $row['campaign_label'], $row['init1'], $row['init2'], $row['init3'], $row['init4'], $row['init5'], $row['init6'], $row['second1'], $row['second2'], $row['second3'], $row['second4'], $row['second5'], $row['second6']);
                }
            }

            return $ret;


        } catch (Exception $e) {
            return array();
        }
    }

    public function GetCampaignList4Export($crm_id, $user_token)
    {
        if (!$this->checkConnection())
            return array();

        try {

            $ret = array();

            $query = 'SELECT campaign_id, campaign_name, campaign_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6 FROM ' . $this->subdomain . '_retention_export WHERE user_token="' . $user_token . '" and crm_id=' . $crm_id . ' and affiliate_id ="" and subaffiliate_id = ""';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['campaign_id'], $row['campaign_name'], $row['campaign_label'], $row['init1'], $row['init2'], $row['init3'], $row['init4'], $row['init5'], $row['init6'], $row['second1'], $row['second2'], $row['second3'], $row['second4'], $row['second5'], $row['second6']);
                }
            }

            return $ret;


        } catch (Exception $e) {
            return array();
        }
    }

    public function GetAffiliateList4QuickExport($crm_id, $campaign_id, $user_token)
    {
        if (!$this->checkConnection())
            return array();

        try {

            $ret = array();

            $query = 'SELECT affiliate_id, affiliate_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6 FROM ' . $this->subdomain . '_retention_quick_export WHERE user_token="' . $user_token . '" and crm_id=' . $crm_id . ' and campaign_id=' . $campaign_id . ' and affiliate_id != ""' . ' and subaffiliate_id = ""';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['affiliate_id'], $row['affiliate_label'], $row['init1'], $row['init2'], $row['init3'], $row['init4'], $row['init5'], $row['init6'], $row['second1'], $row['second2'], $row['second3'], $row['second4'], $row['second5'], $row['second6']);
                }
            }

            return $ret;


        } catch (Exception $e) {
            return array();
        }
    }

    public function GetAffiliateList4Export($crm_id, $campaign_id, $user_token)
    {
        if (!$this->checkConnection())
            return array();

        try {

            $ret = array();

            $query = 'SELECT affiliate_id, affiliate_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6 FROM ' . $this->subdomain . '_retention_export WHERE user_token="' . $user_token . '" and crm_id=' . $crm_id . ' and campaign_id=' . $campaign_id . ' and affiliate_id != ""' . ' and subaffiliate_id = ""';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['affiliate_id'], $row['affiliate_label'], $row['init1'], $row['init2'], $row['init3'], $row['init4'], $row['init5'], $row['init6'], $row['second1'], $row['second2'], $row['second3'], $row['second4'], $row['second5'], $row['second6']);
                }
            }

            return $ret;


        } catch (Exception $e) {
            return array();
        }
    }

    public function GetSubAffiliateList4QuickExport($crm_id, $campaign_id, $affiliate_id, $user_token)
    {
        if (!$this->checkConnection())
            return array();

        try {

            $ret = array();

            $query = 'SELECT subaffiliate_id, subaffiliate_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6 FROM ' . $this->subdomain . '_retention_quick_export WHERE user_token="' . $user_token . '" and crm_id=' . $crm_id . ' and campaign_id=' . $campaign_id . ' and affiliate_id ="' . $affiliate_id . '" and subaffiliate_id != ""';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['subaffiliate_id'], $row['subaffiliate_label'], $row['init1'], $row['init2'], $row['init3'], $row['init4'], $row['init5'], $row['init6'], $row['second1'], $row['second2'], $row['second3'], $row['second4'], $row['second5'], $row['second6']);
                }
            }

            return $ret;


        } catch (Exception $e) {
            return array();
        }
    }

    public function GetSubAffiliateList4Export($crm_id, $campaign_id, $affiliate_id, $user_token)
    {
        if (!$this->checkConnection())
            return array();

        try {

            $ret = array();

            $query = 'SELECT subaffiliate_id, subaffiliate_label, init1, init2, init3, init4, init5, init6, second1, second2, second3, second4, second5, second6 FROM ' . $this->subdomain . '_retention_export WHERE user_token="' . $user_token . '" and crm_id=' . $crm_id . ' and campaign_id=' . $campaign_id . ' and affiliate_id ="' . $affiliate_id . '" and subaffiliate_id != ""';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['subaffiliate_id'], $row['subaffiliate_label'], $row['init1'], $row['init2'], $row['init3'], $row['init4'], $row['init5'], $row['init6'], $row['second1'], $row['second2'], $row['second3'], $row['second4'], $row['second5'], $row['second6']);
                }
            }

            return $ret;


        } catch (Exception $e) {
            return array();
        }
    }

    public function getBlockedIpList()
    {
        if (!$this->checkConnection())
            return array();

        try {

            $ret = array();

            $query = 'SELECT * FROM ' . $this->subdomain . '_blocked_ip';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $ip_count = mysqli_num_rows($result);
            if ($ip_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['id'], $row['ip'], $row['description']);
                }
            }

            return $ret;


        } catch (Exception $e) {
            return array();
        }
    }

    public function addBlockedIp($ip, $description)
    {
        if (!$this->checkConnection())
            return false;
        try {

            $query = 'INSERT INTO ' . $this->subdomain . '_blocked_ip (id, ip, description) VALUES(null,"' . $ip . '","' . $description . '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE)
                return true;

            return false;

        } catch (Exception $e) {
            return false;
        }
    }

    public function updateBlockedIp($id, $ip, $description)
    {
        if (!$this->checkConnection())
            return false;
        try {

            $query = 'UPDATE ' . $this->subdomain . '_blocked_ip SET ip="' . $ip . '" , description="' . $description . '" WHERE id=' . $id;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE)
                return true;

            return false;

        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteBlockedIp($id)
    {
        if (!$this->checkConnection())
            return false;
        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_blocked_ip WHERE id=' . $id;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE)
                return true;

            return false;

        } catch (Exception $e) {
            return false;
        }
    }

    public function checkClientIp()
    {
        $blockedIpList = $this->getBlockedIpList();

        if (count($blockedIpList) > 0) {
            $ipaddress = '';
            if (!empty($_SERVER['HTTP_CLIENT_IP']) && getenv('HTTP_CLIENT_IP')) {
                $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && getenv('HTTP_X_FORWARDED_FOR')) {
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($_SERVER['REMOTE_HOST']) && getenv('REMOTE_HOST')) {
                $ipaddress = $_SERVER['REMOTE_HOST'];
            } elseif (!empty($_SERVER['REMOTE_ADDR']) && getenv('REMOTE_ADDR')) {
                $ipaddress = $_SERVER['REMOTE_ADDR'];
            } else {
                $ipaddress = 'UNKNOWN';
            }

            foreach ($blockedIpList as $blockedIp) {
                $ipAtomic = explode('.', $ipaddress);
                $blockAtomic = explode('.', $blockedIp[1]);

                $block = true;

                for ($i = 0; $i < count($ipAtomic); $i++) {
                    if ($blockAtomic[$i] == '*')
                        continue;
                    if ($ipAtomic[$i] != $blockAtomic[$i])
                        $block = false;
                }
                if ($block)
                    return false;
            }
            return true;
        } else {
            return true;
        }

    }

    public function getAlertLevelList($type)
    {
        if (!$this->checkConnection())
            return array();

        $alertTypes = $this->getAlertType();

        $alert_setting_table = $this->subdomain . '_alert_setting';
        $alert_schedule_table = $this->subdomain . '_alert_schedule';

        try {
            $query = 'SELECT ' . $alert_setting_table . '.*,' . $alert_schedule_table . '.show_status FROM ' . $alert_setting_table . ' LEFT JOIN ' . $alert_schedule_table . ' ON ' . $alert_setting_table . '.type=' . $alert_schedule_table . '.alert_type WHERE type=' . $type . ' ORDER BY type ASC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            $ret = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $name = '';
                    foreach ($alertTypes as $alert) {
                        if ($alert[0] == $row['type']) {
                            $name = $alert[2];
                            break;
                        }
                    }

                    $ret[] = array($row['aid'], $row['crm_id'], $row['type'], $name, $row['value1'], $row['value2'], $alert[3], $alert[4], $row['show_status']);
                }
            }
            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }

    public function getAlertLevelListByCrm($crmId)
    {
        if (!$this->checkConnection())
            return array();

        $alertTypes = $this->getAlertType();

        try {
            $alert_setting_table = $this->subdomain . '_alert_setting';
            $alert_schedule_table = $this->subdomain . '_alert_schedule';

            $query = 'SELECT ' . $alert_setting_table . '.*,' . $alert_schedule_table . '.show_status FROM ' . $alert_setting_table . ' LEFT JOIN ' . $alert_schedule_table . ' ON ' . $alert_setting_table . '.type=' . $alert_schedule_table . '.alert_type WHERE crm_id=' . $crmId . ' ORDER BY type ASC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            $ret = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $name = '';
                    foreach ($alertTypes as $alert) {
                        if ($alert[0] == $row['type']) {
                            $name = $alert[2];
                            break;
                        }
                    }
                    $ret[] = array($row['aid'], $row['crm_id'], $row['type'], $name, $row['value1'], $row['value2'], $alert[3], $alert[4], $row['show_status']);
                }
            } else {
                foreach ($alertTypes as $alertType) {
                    $ret[] = array($alertType[0], $crmId, $alertType[1], $alertType[2], 0, 0, $alertType[3], $alertType[4], $alertType[10]);
                }
            }

            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }

    public function updateAlertLevel($type, $crmID, $value1, $value2)
    {
        if (!$this->checkConnection())
            return false;
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_alert_setting WHERE type=' . $type . ' AND crm_id=' . $crmID;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if (mysqli_num_rows($result) > 0) {
                $query = 'UPDATE ' . $this->subdomain . '_alert_setting SET value1=' . $value1 . ', value2=' . $value2 . ' WHERE type=' . $type . ' AND crm_id=' . $crmID;

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if ($result === TRUE)
                    return true;
                else
                    return false;

            } else {
                $query = 'INSERT INTO ' . $this->subdomain . '_alert_setting (aid, crm_id, type, value1, value2) VALUES (null,' . $crmID . ',' . $type . ',' . $value1 . ',' . $value2 . ')';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if ($result === TRUE)
                    return true;
                else
                    return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function addAlertReceiver($type, $address, $status, $chatid)
    {
        if (!$this->checkConnection())
            return false;
        try {
            $query = 'INSERT INTO ' . $this->subdomain . '_alert_receiver(rid, type, address, status, chatid) VALUES (null,' . $type . ',"' . $address . '",' . $status . ',"' . $chatid . '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE)
                return true;
            return false;

        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteAlertReceiver($receiverID)
    {
        if (!$this->checkConnection())
            return false;
        try {
            $query = 'DELETE FROM ' . $this->subdomain . '_alert_receiver WHERE rid=' . $receiverID;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE)
                return true;
            return false;

        } catch (Exception $e) {
            return false;
        }
    }

    public function updateAlertReceiver($receiverID, $type, $address, $status, $chatid)
    {
        if (!$this->checkConnection())
            return false;
        try {
            $query = 'UPDATE ' . $this->subdomain . '_alert_receiver SET type=' . $type . ',address="' . $address . '",status=' . $status . ',chatid="' . $chatid . '" WHERE rid=' . $receiverID;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE)
                return true;
            return false;

        } catch (Exception $e) {
            return false;
        }
    }

    public function getAlertReceiverList()
    {
        if (!$this->checkConnection())
            return array();
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_alert_receiver';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            $ret = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['rid'], $row['type'], $row['address'], $row['status'], $row['chatid']);
                }
            }

            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }

    public function getEnabledEmails()
    {
        $receivers = $this->getAlertReceiverList();
        $emails = array();
        foreach ($receivers as $value) {
            if ($value[1] == 1 && $value[3] == 1) {
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
        foreach ($receivers as $value) {
            if ($value[1] == 0 && $value[3] == 1) {
                // enabled email address
                $phones[] = $value[2];
            }
        }
        return $phones;
    }

    public function getTelegramChatIDList()
    {
        if (!$this->checkConnection())
            return array();
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_alert_receiver WHERE type=2 AND status=1';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            $ret = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($row['chatid'] != '')
                        $ret[] = $row['chatid'];
                }
            }

            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }

    public function getAlertType()
    {
        if (!$this->checkConnection())
            return array();
        try {
            $alert_schedule_table = $this->subdomain . '_alert_schedule';
            $query = 'SELECT admin_alert_type.*,' . $alert_schedule_table . '.alert_day,' . $alert_schedule_table . '.alert_hour,' . $alert_schedule_table . '.sms,' . $alert_schedule_table . '.email,' . $alert_schedule_table . '.telegram_bot,' . $alert_schedule_table . '.show_status FROM admin_alert_type LEFT JOIN ' . $alert_schedule_table . ' ON ' . 'admin_alert_type.alert_type=' . $alert_schedule_table . '.alert_type';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            $ret = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['aid'], $row['alert_type'], $row['alert_name'], $row['alert_formula'], $row['report_date'], $row['alert_day'], $row['alert_hour'], $row['sms'], $row['email'], $row['telegram_bot'], $row['show_status']);
                }
            }

            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }

    public function getAlertTypeByType($type)
    {
        if (!$this->checkConnection())
            return array();
        try {
            $alert_schedule_table = $this->subdomain . '_alert_schedule';
            $query = 'SELECT admin_alert_type.*,' . $alert_schedule_table . '.alert_day,' . $alert_schedule_table . '.alert_hour,' . $alert_schedule_table . '.sms,' . $alert_schedule_table . '.email,' . $alert_schedule_table . '.telegram_bot,' . $alert_schedule_table . '.show_status FROM admin_alert_type LEFT JOIN ' . $alert_schedule_table . ' ON ' . 'admin_alert_type.alert_type=' . $alert_schedule_table . '.alert_type WHERE admin_alert_type.alert_type=' . $type;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret = array($row['aid'], $row['alert_type'], $row['alert_name'], $row['alert_formula'], $row['report_date'], $row['alert_day'], $row['alert_hour'], $row['sms'], $row['email'], $row['telegram_bot'], $row['show_status']);
                }
            }

            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }

    public function updateAlertStatus($crmID, $type, $value, $level, $status, $from_date, $to_date, $timestamp)
    {
        $query = 'SELECT * FROM ' . $this->subdomain . '_alert_status WHERE crm_id=' . $crmID . ' and alert_type=' . $type . ' and from_date="' . $from_date . '" and to_date="' . $to_date . '"';

        $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
        if (mysqli_num_rows($result) > 0) {
            $query = 'UPDATE ' . $this->subdomain . '_alert_status SET crm_id=' . $crmID . ', alert_type=' . $type . ', value=' . $value . ', level=' . $level . ',alert_read=0, alert_delete=0' . ', status=' . $status . ',timestamp="' . $timestamp . '", from_date="' . $from_date . '", to_date="' . $to_date . '" WHERE crm_id=' . $crmID . ' and alert_type=' . $type . ' and from_date="' . $from_date . '" and to_date="' . $to_date . '"';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

        } else {
            $query = 'INSERT INTO ' . $this->subdomain . '_alert_status (aid, crm_id, alert_type, value, level, status, timestamp, alert_read, alert_delete, from_date, to_date) VALUES(null,' . $crmID . ',' . $type . ',' . $value . ',' . $level . ',' . $status . ',"' . $timestamp . '", 0, 0,"' . $from_date . '","' . $to_date . '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
        }
        if (!$result)
            return false;

        return true;
    }
    public function updateKKCrmAlertStatus($crmID, $type, $value, $level, $status, $from_date, $to_date, $timestamp)
    {
        $query = 'SELECT * FROM ' . $this->subdomain . '_kkcrm_alert_status WHERE crm_id=' . $crmID . ' and alert_type=' . $type . ' and from_date="' . $from_date . '" and to_date="' . $to_date . '"';

        $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
        if (mysqli_num_rows($result) > 0) {
            $query = 'UPDATE ' . $this->subdomain . '_kkcrm_alert_status SET crm_id=' . $crmID . ', alert_type=' . $type . ', value=' . $value . ', level=' . $level . ',alert_read=0, alert_delete=0' . ', status=' . $status . ',timestamp="' . $timestamp . '", from_date="' . $from_date . '", to_date="' . $to_date . '" WHERE crm_id=' . $crmID . ' and alert_type=' . $type . ' and from_date="' . $from_date . '" and to_date="' . $to_date . '"';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

        } else {
            $query = 'INSERT INTO ' . $this->subdomain . '_kkcrm_alert_status (aid, crm_id, alert_type, value, level, status, timestamp, alert_read, alert_delete, from_date, to_date) VALUES(null,' . $crmID . ',' . $type . ',' . $value . ',' . $level . ',' . $status . ',"' . $timestamp . '", 0, 0,"' . $from_date . '","' . $to_date . '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
        }
        if (!$result)
            return false;

        return true;
    }
    public function getRecentAlertList($userId)
    {
        // count = 10

        if (!$this->checkConnection())
            return array();
        $allCrm = $this->getAllActiveCrmsByAccountId($userId);

        try {
            $alert_status_table = $this->subdomain . '_alert_status';
            $query = 'SELECT ' . $alert_status_table . '.*, admin_alert_type.alert_name FROM ' . $alert_status_table . ' LEFT JOIN admin_alert_type ON ' . $alert_status_table . '.alert_type=admin_alert_type.alert_type WHERE alert_delete=0 AND status=1 AND admin_alert_type.alert_type < 11 ORDER BY timestamp DESC, alert_type ASC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            $ret = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $crmName = '';
                    foreach ($allCrm as $crm) {
                        if ($crm[0] == $row['crm_id']) {
                            $crmName = $crm[1];
                            break;
                        }
                    }
                    if ($crmName != '') {
                        $ret[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $row['alert_name'], $crmName);
                    }

                }
            }

            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }
    public function getRecentAlertCount($userId)
    {
        // count = 10
        $ret = 0;
        if (!$this->checkConnection())
            return $ret;
        $allCrm = $this->getAllActiveCrmsByAccountId($userId);

        try {
            $alert_status_table = $this->subdomain . '_alert_status';
            $query = 'SELECT ' . $alert_status_table . '.*, admin_alert_type.alert_name FROM ' . $alert_status_table . ' LEFT JOIN admin_alert_type ON ' . $alert_status_table . '.alert_type=admin_alert_type.alert_type WHERE alert_delete=0 AND status=1 AND admin_alert_type.alert_type < 11 ORDER BY timestamp DESC, alert_type ASC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $crmName = '';
                    foreach ($allCrm as $crm) {
                        if ($crm[0] == $row['crm_id']) {
                            $crmName = $crm[1];
                            break;
                        }
                    }
                    if ($crmName != '') {
                       $ret ++;
                    }
                }
            }
            return $ret;

        } catch (Exception $e) {
            return $ret;
        }
    }

    public function deleteAlertItem($alertId)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'UPDATE ' . $this->subdomain . '_alert_status SET alert_delete=1 WHERE aid=' . $alertId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
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
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'UPDATE ' . $this->subdomain . '_alert_status SET alert_delete=1';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
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
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'UPDATE ' . $this->subdomain . '_alert_status SET alert_read=1 WHERE aid=' . $alertId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
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

        if (!$this->checkConnection())
            return $ret;

        $fromTime = date('Y-m-d', strtotime($fromDate));
        $fromTime = $fromTime . ' 00:00:00';
        $toTime = date('Y-m-d', strtotime($toDate));
        $toTime = $toTime . ' 23:59:59';
        $allCrm = $this->getAllCrm();

        $data = array();
        try {
            $alert_status_table = $this->subdomain . '_alert_status';
            if ($crmID == '0')
                $query = 'SELECT ' . $alert_status_table . '.*, admin_alert_type.alert_name FROM ' . $alert_status_table . ' LEFT JOIN admin_alert_type ON ' . $alert_status_table . '.alert_type=admin_alert_type.alert_type WHERE ' . $alert_status_table . '.status=1 and ' . $alert_status_table . '.timestamp BETWEEN "' . $fromTime . '" AND "' . $toTime . '" ORDER BY ' . $alert_status_table . '.timestamp DESC';
            else
                $query = 'SELECT ' . $alert_status_table . '.*, admin_alert_type.alert_name FROM ' . $alert_status_table . ' LEFT JOIN admin_alert_type ON ' . $alert_status_table . '.alert_type=admin_alert_type.alert_type WHERE ' . $alert_status_table . '.crm_id=' . $crmID . ' and ' . $alert_status_table . '.status=1 and ' . $alert_status_table . '.timestamp BETWEEN "' . $fromTime . '" AND "' . $toTime . '" ORDER BY ' . $alert_status_table . '.timestamp DESC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);


            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $crmName = '';
                    foreach ($allCrm as $crm) {
                        if ($crm[0] == $row['crm_id']) {
                            $crmName = $crm[1];
                            break;
                        }
                    }
                    $data[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $row['alert_name'], $crmName);
                }
                $ret['data'] = array_slice($data, ($pageNumber - 1) * $items4Page, $items4Page);
                $ret['length'] = count($data);
            } else {
                $ret['data'] = array();
                $ret['length'] = 0;
            }

            return $ret;

        } catch (Exception $e) {
            return $ret;
        }
    }

    public function getAlertReport($alertType, $date, $userId)
    {
        // report for current day
        if (!$this->checkConnection())
            return array();

        // $time =  date('Y-m-d', strtotime($date));
        $fromTime = $date . ' 00:00:00';
        $toTime = $date . ' 23:59:59';

        $allCrm = $this->getAllActiveCrmsByAccountId($userId);
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_alert_status WHERE alert_type=' . $alertType . ' and timestamp BETWEEN "' . $fromTime . '" AND "' . $toTime . '" ORDER BY timestamp DESC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            $ret = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $crmName = '';
                    foreach ($allCrm as $crm) {
                        if ($crm[0] == $row['crm_id']) {
                            $crmName = $crm[1];
                            break;
                        }
                    }
                    $ret[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $crmName);
                }
            }
            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }

    public function getAllLatestAlertReportByType($type)
    {
        // report for current day
        if (!$this->checkConnection())
            return array();

        $allCrm = $this->getAllCrm();
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_alert_status WHERE alert_type=' . $type . ' ORDER BY crm_id ASC, timestamp DESC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            $ret = array();
            if ($count > 0) {
                $crmOldName = '';
                $crmName = '';
                $crmGoal = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    foreach ($allCrm as $crm) {
                        if ($crm[0] == $row['crm_id']) {
                            $crmName = $crm[1];
                            $crmGoal = $crm[7];
                            break;
                        }
                    }
                    if ($crmOldName != $crmName) {
                        $crmOldName = $crmName;
                        $ret[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $crmName, $crmGoal);
                    }
                }
            }
            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }

    public function getLatestAlertReportByType($crmID, $type)
    {
        // report for current day
        if (!$this->checkConnection())
            return array();

        $allCrm = $this->getAllCrm();
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_alert_status WHERE crm_id=' . $crmID . ' and alert_type=' . $type . ' ORDER BY timestamp DESC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            $ret = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $crmName = '';
                    foreach ($allCrm as $crm) {
                        if ($crm[0] == $crmID) {
                            $crmName = $crm[1];
                            break;
                        }
                    }
                    $ret[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $crmName);
                }
            }
            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }
    public function getLatestKKCrmAlertReportByType($crmID, $type)
    {
        // report for current day
        if (!$this->checkConnection())
            return array();

        $allCrm = $this->getKKCrmAccountList(null);
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_kkcrm_alert_status WHERE crm_id=' . $crmID . ' and alert_type=' . $type . ' ORDER BY timestamp DESC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            $ret = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $crmName = '';
                    foreach ($allCrm as $crm) {
                        if ($crm[0] == $crmID) {
                            $crmName = $crm[1];
                            break;
                        }
                    }
                    $ret[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $crmName);
                }
            }
            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }
    public function getAlertReportByType($crmID, $date, $type)
    {
        // report for current day
        if (!$this->checkConnection())
            return array();

        $fromTime = $date . ' 00:00:00';
        $toTime = $date . ' 23:59:59';

        $allCrm = $this->getAllCrm();
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_alert_status WHERE crm_id=' . $crmID . ' and alert_type=' . $type . ' and timestamp BETWEEN "' . $fromTime . '" AND "' . $toTime . '" ORDER BY timestamp DESC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            $ret = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $crmName = '';
                    foreach ($allCrm as $crm) {
                        if ($crm[0] == $crmID) {
                            $crmName = $crm[1];
                            break;
                        }
                    }
                    $ret[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $crmName);
                }
            }
            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }
    public function getKKCrmAlertReportByType($crmID, $date, $type)
    {
        // report for current day
        if (!$this->checkConnection())
            return array();

        $fromTime = $date . ' 00:00:00';
        $toTime = $date . ' 23:59:59';

        $allCrm = $this->getKKCrmActiveList(null);
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_kkcrm_alert_status WHERE crm_id=' . $crmID . ' and alert_type=' . $type . ' and timestamp BETWEEN "' . $fromTime . '" AND "' . $toTime . '" ORDER BY timestamp DESC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            $ret = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $crmName = '';
                    foreach ($allCrm as $crm) {
                        if ($crm[0] == $crmID) {
                            $crmName = $crm[1];
                            break;
                        }
                    }
                    $ret[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $crmName);
                }
            }
            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }
    public function getAllAlertReportByType($date, $type)
    {
        // report for current day
        if (!$this->checkConnection())
            return array();

        // $time =  date('Y-m-d', strtotime($date));

        $fromTime = $date . ' 00:00:00';
        $toTime = $date . ' 23:59:59';

        $allCrm = $this->getAllCrm();
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_alert_status WHERE alert_type=' . $type . ' and timestamp BETWEEN "' . $fromTime . '" AND "' . $toTime . '" ORDER BY timestamp DESC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            $ret = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $crmName = '';
                    foreach ($allCrm as $crm) {
                        if ($crm[0] == $row['crm_id']) {
                            $crmName = $crm[1];
                            break;
                        }
                    }
                    $ret[] = array($row['aid'], $row['crm_id'], $row['alert_type'], $row['value'], $row['level'], $row['status'], $row['timestamp'], $row['alert_read'], $row['alert_delete'], $row['from_date'], $row['to_date'], $crmName);
                }
            }
            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }

    public function getAlertScheduleByType($type)
    {
        $ret = array();
        if (!$this->checkConnection())
            return $ret;
        try {
            $table = $this->subdomain . '_alert_schedule';
            $query = "SELECT * FROM " . $table . " WHERE alert_type=" . $type;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['alert_type'], $row['alert_day'], $row['alert_hour'], $row['sms'], $row['email'], $row['telegram_bot'], $row['show_status']);
                }
            }
            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }

    public function getAlertSchedule()
    {
        $ret = array();
        if (!$this->checkConnection())
            return $ret;
        try {
            $table = $this->subdomain . '_alert_schedule';
            $query = "SELECT * FROM " . $table;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['alert_type'], $row['alert_day'], $row['alert_hour'], $row['sms'], $row['email'], $row['telegram_bot'], $row['show_status']);
                }
            }
            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }

    public function changeAlertSchedule($type, $days, $hours, $sms, $email, $bot)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $alert_schedule_table = $this->subdomain . '_alert_schedule';
            $query = 'SELECT * FROM '.$alert_schedule_table.' WHERE alert_type='.$type;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                $query = 'UPDATE ' . $alert_schedule_table . ' SET alert_day="' . $days . '",alert_hour="' . $hours . '",sms=' . $sms . ',email=' . $email . ',telegram_bot=' . $bot . ' WHERE alert_type=' . $type;
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if ($result)
                    return true;
                else
                    return false;
            } else {
                $query = 'INSERT INTO ' . $alert_schedule_table . '(aid, alert_type, alert_day, alert_hour, sms, email, telegram_bot) VALUES (null,' . $type . ',"' . $days . '","' . $hours . '",' . $sms . ',' . $email . ',' . $bot.')';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if ($result)
                    return true;
                else
                    return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function storeDashboardData($crmID, $crmName, $Step1, $Step2, $takeRate, $tablet, $tabletRate, $goal)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'DELETE FROM ' . $this->subdomain . '_report_dashboard WHERE crm_id=' . $crmID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                $query = 'INSERT INTO ' . $this->subdomain . '_report_dashboard (id, crm_id, crm_name, step1, step2, takerate, tablet, tabletrate, crm_goal) VALUES (null,' . $crmID . ',"' . $crmName . '",' . $Step1 . ',' . $Step2 . ',' . $takeRate . ',' . $tablet . ',' . $tabletRate . ',' . $goal . ')';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if ($result === TRUE)
                    return true;
                else
                    return false;
            } else {
                return false;
            }

        } catch (Exception $e) {

            return false;
        }
    }
    public function storeKKCrmDashboardData($crmID, $crmName, $Step1, $Step2, $takeRate, $tablet, $tabletRate, $goal)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'DELETE FROM ' . $this->subdomain . '_kkcrm_report_dashboard WHERE crm_id=' . $crmID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                $query = 'INSERT INTO ' . $this->subdomain . '_kkcrm_report_dashboard (id, crm_id, crm_name, step1, step2, takerate, tablet, tabletrate, crm_goal) VALUES (null,' . $crmID . ',"' . $crmName . '",' . $Step1 . ',' . $Step2 . ',' . $takeRate . ',' . $tablet . ',' . $tabletRate . ',' . $goal . ')';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if ($result === TRUE)
                    return true;
                else
                    return false;
            } else {
                return false;
            }

        } catch (Exception $e) {

            return false;
        }
    }
    public function getDashboardData()
    {
        if (!$this->checkConnection())
            return array();

        try {
            $data = array();

            $query = 'SELECT * FROM ' . $this->subdomain . '_report_dashboard ORDER BY crm_id ASC';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = array($row['crm_id'], $row['crm_name'], $row['step1'], $row['step2'], $row['takerate'], $row['tablet'], $row['tabletrate'], $row['crm_goal']);
                }
            }

            return $data;
        } catch (Exception $e) {
            return array();
        }
    }
    public function getKKCrmDashboardData($crmID)
    {
        if (!$this->checkConnection())
            return array();

        try {
            $data = array();

            $query = 'SELECT * FROM ' . $this->subdomain . '_kkcrm_report_dashboard where crm_id='.$crmID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = array($row['crm_id'], $row['crm_name'], $row['step1'], $row['step2'], $row['takerate'], $row['tablet'], $row['tabletrate'], $row['crm_goal']);
                }
            }

            return $data;
        } catch (Exception $e) {
            return array();
        }
    }
    public function backupDb()
    {
        $path = '/home/ubuntu/db_backup/';
        exec("sudo rm -r " . $path . '*');
        // backup admin tables
        $name = 'admin';
        $path = '/home/ubuntu/db_backup/' . $name;
        mkdir($path, 0755, true);
        exec("sudo chown -R mysql:mysql " . $path);
        $this->backupTable($name, '_account_info');
        $this->backupTable($name, '_alert_type');
        $this->backupTable($name, '_billing_info');
        $this->backupTable($name, '_blocked_ip');
        $this->backupTable($name, '_panel_account');
        $this->backupTable($name, '_subdomain');

        $subDomains = $this->getAllSubDomain();
        // backup tables for sub domain
        foreach ($subDomains as $item) {
            $name = $item[1];
            $path = '/home/ubuntu/db_backup/' . $name;
            if (mkdir($path, 0755, true)) {
                exec("sudo chown -R mysql:mysql " . $path);

                $this->backupTable($name, '_crm_account');
//                $this->backupTable($name, '_dashboard_columns');
                $this->backupTable($name, '_alert_receiver');
                $this->backupTable($name, '_alert_setting');
                $this->backupTable($name, '_alert_schedule');
                $this->backupTable($name, '_atomic_label');
                $this->backupTable($name, '_label_affiliate');
                $this->backupTable($name, '_label_campaign');
                $this->backupTable($name, '_labels_goal');
                $this->backupTable($name, '_user_account');
                $this->backupTable($name, '_blocked_ip');
            }
        }
    }

    private function backupTable($subDomain, $tableName)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $path = '/home/ubuntu/db_backup/' . $subDomain . '/';
            $tableName = $subDomain . $tableName;
            $file = $path . $tableName . '.sql';
//			unlink($file);
            $query = "SELECT * INTO OUTFILE '$file' FROM " . $tableName;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result == true)
                echo $tableName . ":success \n";
            else
                echo $tableName . ":failed \n";

            return $result;

        } catch (Exception $e) {

            return false;
        }
    }

    // APIs for sub affiliate alert (detailed alert with affiliate/sub affiliate)
    public function getSTEP1ApprovalRateForInitialAlertByCrm($crmId, $day)
    {
        if (!$this->checkConnection())
            return array(0, date('m/d/Y'), date('m/d/Y'));

        $campaignIdsSTEP1 = $this->getSTEP1CampaignIds($crmId);

        try {
            $stringCampaigns = join("','", $campaignIdsSTEP1);
            $query = 'SELECT sum(gross_order) as sumGross, sum(net_approved) as sumNet, from_date, to_date , crm_name FROM ' . $this->subdomain . '_retention_initial_alert WHERE crm_id=' . $crmId . ' and day=' . $day . " and affiliate_id=''" . " and campaign_id IN ('$stringCampaigns')";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);

                if ($row['sumGross'] == 0)
                    return array(0, $row['from_date'], $row['to_date']);

                return array($row['sumNet'] / $row['sumGross'] * 100, $row['from_date'], $row['to_date'], $row['crm_name']);

            }

            return array(0, date('m/d/Y'), date('m/d/Y'));

        } catch (Exception $e) {
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

        try {
            $data = array();
            $campaignIdsSTEP1 = $this->getSTEP1CampaignIds($crmId);
            $stringCampaigns = join("','", $campaignIdsSTEP1);
            $query = 'SELECT campaign_id, approval_rate FROM ' . $this->subdomain . '_retention_initial_alert WHERE crm_id=' . $crmId . ' and day=' . $day . ' and has_child = 1' . ' and approval_rate <' . $level;
            $query = $query . " and campaign_id IN ('$stringCampaigns')";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = $row['campaign_id'];
                }
            }

            return $data;
        } catch (Exception $e) {
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

        try {
            $data = array();
            $campaignIdsSTEP1 = $this->getSTEP1CampaignIds($crmId);
            $stringCampaigns = implode(',', $campaignIdsSTEP1);
            $query = 'SELECT campaign_id,approval_rate,has_child FROM ' . $this->subdomain . '_retention_initial_alert WHERE crm_id=' . $crmId . ' and day=' . $day . ' and campaign_id IN (' . $stringCampaigns . ') and approval_rate <' . $level . " and affiliate_id ='' and gross_order >=" . $minOrder;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = array($row['campaign_id'], $row['has_child'], $row['approval_rate']);
                }
            }

            return $data;
        } catch (Exception $e) {
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

        try {
            $data = array();
            $campaignIdsSTEP1 = $this->getSTEP1CampaignIds($crmId);
            $stringCampaigns = implode(',', $campaignIdsSTEP1);
            $query = 'SELECT campaign_id, affiliate_id FROM ' . $this->subdomain . '_retention_initial_alert WHERE crm_id=' . $crmId . ' and day=' . $day . ' and campaign_id IN (' . $stringCampaigns . ') and approval_rate <' . $level . " and has_child = 1 and affiliate_id !=''";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = array($row['campaign_id'], $row['affiliate_id']);
                }
            }

            return $data;
        } catch (Exception $e) {
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

        try {
            $data = array();
            $query = 'SELECT affiliate_id, approval_rate,has_child FROM ' . $this->subdomain . '_retention_initial_alert WHERE crm_id=' . $crmId . ' and day=' . $day . ' and campaign_id=' . $campaignId . ' and approval_rate <' . $level . " and subaffiliate_id ='' and affiliate_id != '' and gross_order >=" . $minOrder;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = array($row['affiliate_id'], $row['has_child'], $row['approval_rate']);
                }
            }

            return $data;
        } catch (Exception $e) {
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

        try {
            $data = array();
            $query = 'SELECT subaffiliate_id ,approval_rate FROM ' . $this->subdomain . '_retention_initial_alert WHERE crm_id=' . $crmId . ' and day=' . $day . ' and campaign_id=' . $campaignId . ' and affiliate_id=' . $affiliateId . ' and approval_rate <' . $level . " and subaffiliate_id != '' and gross_order >=" . $minOrder;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = array($row['subaffiliate_id'], $row['approval_rate']);
                }
            }

            return $data;
        } catch (Exception $e) {
            return array();
        }
    }

    // Commericals API

    /*
    *@description
    *	check if subdomain registered
    *@param
    *	subdomain name
    *@ret
    *	id if registered, -1 if not
    */
    public function checkIfSubdomainRegistered($subdomain)
    {
        if (!$this->checkConnection())
            return -1;

        try {
            $query = 'SELECT sid FROM admin_subdomain WHERE subdomain="' . $subdomain . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                return $row['sid'];
            }
            return -1;
        } catch (Exception $e) {
            return -1;
        }
    }

    /*
    *@description
    *	check if user id registered in admin user table
    *@param
    *	user id
    *@ret
    *	true if registered
    */
    public function checkIfUserIdRegistered($userId)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'SELECT aid FROM admin_account_info WHERE user_id="' . $userId . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
//				$row = mysqli_fetch_assoc($result);
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /*
    *@description
    *	check if user id and email registered in admin user table
    *@param
    *	user id, email
    *@ret
    *	true if registered
    */
    public function checkIfUserIdAndEmailRegistered($userId, $email)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'SELECT aid FROM admin_account_info WHERE user_id="' . $userId . '" and email="' . $email . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            return true;
        }
    }

    /*
    *@description
    *	get verifycode of user
    *@param
    *	user id
    *@ret
    *	code
    */
    public function getVerifyCodeOfUser($userId)
    {
        $retCode = '';

        if (!$this->checkConnection())
            return $retCode;

        try {
            $query = 'SELECT verify_code FROM admin_account_info WHERE user_id="' . $userId . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $retCode = $row['verify_code'];
            }

            return $retCode;
        } catch (Exception $e) {

            return $retCode;
        }
    }

    /*
    *@description
    *	get verifycode of user
    *@param
    *	user id
    *@ret
    *	code
    */
    public function checkIfUserVerified($userId)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT is_verified FROM admin_account_info WHERE user_id="' . $userId . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                if ($row['is_verified'] == 1) {
                    $ret = true;
                }
            }

            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    /*
    *@description
    *	Set user verified
    *@param
    *	user id
    *@ret
    *	true
    */
    public function setUserVerified($userId)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM admin_account_info WHERE user_id="' . $userId . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                $query = 'UPDATE admin_account_info SET is_verified=1 WHERE user_id="' . $userId . '"';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if ($result)
                    $ret = true;
            }

            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    /*
    *@description
    *	check if email registered
    *@param
    *	email address
    *@ret
    *	true if registered
    */
    public function checkIfEmailRegistered($emailAddress)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT aid FROM admin_account_info WHERE email="' . $emailAddress . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                $ret = true;
            }

            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    /*
    *@description
    *	Get the email of who created sub domain
    *@param
    *	subdomain
    *@ret
    *	email
    */
    public function getEmailBySubDomain($subDomain) {
        $ret = '';

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT admin_account_info.*,admin_subdomain.subdomain FROM admin_account_info LEFT JOIN admin_subdomain ON admin_account_info.domain_id=admin_subdomain.sid WHERE subdomain="' . $subDomain . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $ret = $row['email'];
            }

            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    /*
    *@description
    *	Get the user id of who created sub domain
    *@param
    *	subdomain
    *@ret
    *	user id
    */
    public function getUserIDBySubDomain($subDomain) {
        $ret = '';

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT admin_account_info.*,admin_subdomain.subdomain FROM admin_account_info LEFT JOIN admin_subdomain ON admin_account_info.domain_id=admin_subdomain.sid WHERE subdomain="' . $subDomain . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $ret = $row['user_id'];
            }

            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    /*
    *@description
    *	Get user info
    *@param
    *	user id
    *@ret
    *	array(first name, last name, display name, subdomain, sms, telegram bot)
    */
    public function getUserInfoByUserId($userId)
    {
        $ret = array();

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT admin_account_info.*,admin_subdomain.subdomain FROM admin_account_info LEFT JOIN admin_subdomain ON admin_account_info.domain_id=admin_subdomain.sid WHERE user_id="' . $userId . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $ret = array($row['first_name'], $row['last_name'], $row['display_name'], $row['subdomain'], $row['sms_number'], $row['telegram_bot_id']);
            }

            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    /*
    *@description
    *	Add new user to admin user table
    *@param
    *	user id, email, password, verify code
    *@ret
    *	true
    */
    public function addNewUser($userId, $emailAddress, $password, $verifyCode, $created_datetime = null, $update_datetime = null)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $created_datetime = date("Y-m-d H:i:s");
            $update_datetime = date("Y-m-d H:i:s");

            $query = 'INSERT INTO admin_account_info (aid, user_id, email, password, verify_code, create_time, update_time) VALUES(null, "' . $userId . '","' . $emailAddress . '","' . $password . '","' . $verifyCode . '","' . $created_datetime . '","' . $update_datetime . '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }

            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    public function addNewUserToSubdomain($userId, $password, $displayName, $userRole, $sms, $email, $bot, $subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = 'INSERT INTO ' . $subdomain . '_user_account (id, user_name, password, display_name, user_status, user_role, sms, email, bot, sms_enable, email_enable, bot_enable) VALUES(null, "' . $userId . '","' . $password . '","' . $displayName . '", 1, ' . $userRole . ',"' . $sms . '","' . $email . '","' . $bot . '",1,1,1)';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    /*
    *@description
    *	Add new subdomain
    *@param
    *	subdomain
    *@ret
    *	id
    */
    public function addNewSubdomain($subDomain)
    {
        $ret = -1;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'INSERT INTO admin_subdomain(sid, subdomain) VALUES (null, "' . $subDomain . '")';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $query = 'SELECT sid FROM admin_subdomain WHERE subdomain="' . $subDomain . '"';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                $count = mysqli_num_rows($result);
                if ($count > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $ret = $row['sid'];
                }
            }

            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    /*
    *@description
    *	delete subdomain
    *@param
    *	subdomain
    *@ret
    *	true
    */

    public function deleteSubdomain($subDomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'DELETE FROM admin_subdomain WHERE subdomain="' . $subDomain . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }

            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    /*
    *@description
    *	Get all sub domain
    *@ret
    *	array()
    */

    public function getAllSubDomain()
    {
        $ret = array();

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM admin_subdomain';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['sid'], $row['subdomain']);
                }
            }

            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    /*
    *@description
    *	Delete all tables of sub domain
    *@param
    *	sub domain name
    *@ret
    *	true
    */
    private function dropTable_affiliate_progress($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_affiliate_progress";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_affiliate_progress_cache($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_affiliate_progress_cache";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_alert_receiver($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_alert_receiver";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_alert_schedule($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_alert_schedule";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function dropTable_alert_setting($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_alert_setting";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function dropTable_alert_status($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_alert_status";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function dropTable_atomic_label($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_atomic_label";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function dropTable_blocked_ip($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_blocked_ip";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function dropTable_crm_account($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_crm_account";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function dropTable_crm_token($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_crm_token";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_kkcrm_account($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_kkcrm_account";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_kkcrm_alert_status($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_kkcrm_alert_status";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_kkcrm_token($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_kkcrm_token";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_kkcrm_campaign_category($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_kkcrm_campaign_category";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_kkcrm_report_dashboard($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_kkcrm_campaign_category";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_crm_position($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_crm_position";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_dashboard_columns($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_dashboard_columns";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_export_filter($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_export_filter";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function dropTable_label_affiliate($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_label_affiliate";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function dropTable_label_campaign($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_label_campaign";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_monitor_issue($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_monitor_issue";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_monitor_schedule($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_monitor_schedule";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_monitor_sites($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_monitor_sites";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_monitor_status($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_monitor_sites";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_labels_goal($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_labels_goal";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function dropTable_report_dashboard($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_report_dashboard";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function dropTable_retention_export($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_retention_export";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function dropTable_retention_initial_alert($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_retention_initial_alert";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function dropTable_retention_quick_export($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_retention_quick_export";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function dropTable_user_account($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = "DROP TABLE " . $subdomain . "_user_account";
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    public function dropAllTablesOfSubdomain($subdomain)
    {
        $this->dropTable_affiliate_progress($subdomain);
        $this->dropTable_affiliate_progress_cache($subdomain);
        $this->dropTable_alert_receiver($subdomain);
        $this->dropTable_alert_schedule($subdomain);
        $this->dropTable_alert_setting($subdomain);
        $this->dropTable_alert_status($subdomain);
        $this->dropTable_atomic_label($subdomain);
        $this->dropTable_blocked_ip($subdomain);
        $this->dropTable_crm_account($subdomain);
        $this->dropTable_crm_position($subdomain);
        $this->dropTable_crm_token($subdomain);
//        $this->dropTable_dashboard_columns($subdomain);
        $this->dropTable_export_filter($subdomain);
        $this->dropTable_kkcrm_account($subdomain);
        $this->dropTable_kkcrm_alert_status($subdomain);
        $this->dropTable_kkcrm_campaign_category($subdomain);
        $this->dropTable_kkcrm_report_dashboard($subdomain);
        $this->dropTable_kkcrm_token($subdomain);
        $this->dropTable_label_affiliate($subdomain);
        $this->dropTable_label_campaign($subdomain);
        $this->dropTable_labels_goal($subdomain);
        $this->dropTable_monitor_issue($subdomain);
        $this->dropTable_monitor_schedule($subdomain);
        $this->dropTable_monitor_sites($subdomain);
        $this->dropTable_monitor_status($subdomain);
        $this->dropTable_report_dashboard($subdomain);
        $this->dropTable_retention_export($subdomain);
        $this->dropTable_retention_initial_alert($subdomain);
        $this->dropTable_retention_quick_export($subdomain);
        $this->dropTable_user_account($subdomain);

//        $this->dropTable_affiliate_progress($subdomain);
//        $this->dropTable_alert_receiver($subdomain);
//        $this->dropTable_alert_schedule($subdomain);
//        $this->dropTable_alert_setting($subdomain);
//        $this->dropTable_alert_status($subdomain);
//        $this->dropTable_atomic_label($subdomain);
//        $this->dropTable_blocked_ip($subdomain);
//        $this->dropTable_crm_account($subdomain);
//        $this->dropTable_crm_token($subdomain);
////        $this->dropTable_dashboard_columns($subdomain);
//        $this->dropTable_export_filter($subdomain);
//        $this->dropTable_label_affiliate($subdomain);
//        $this->dropTable_label_campaign($subdomain);
//        $this->dropTable_labels_goal($subdomain);
//        $this->dropTable_report_dashboard($subdomain);
//        $this->dropTable_retention_export($subdomain);
//        $this->dropTable_retention_initial_alert($subdomain);
//        $this->dropTable_retention_quick_export($subdomain);
//        $this->dropTable_user_account($subdomain);
//        $this->dropTable_kkcrm_account($subdomain);
//        $this->dropTable_kkcrm_token($subdomain);
//        $this->dropTable_kkcrm_campaign_category($subdomain);
//        $this->dropTable_crm_position($subdomain);

    }

    /*
    *@description
    *	Create all tables for subdomain
    *@param
    *	sub domain name
    *@ret
    *	true
    */
    public function createAllTablesForSubdomain($subdomain)
    {
        $this->createTable_affiliate_progress($subdomain);
        $this->createTable_affiliate_progress_cache($subdomain);
        $this->createTable_alert_receiver($subdomain);
        $this->createTable_alert_schedule($subdomain);
        $this->createTable_alert_setting($subdomain);
        $this->createTable_alert_status($subdomain);
        $this->createTable_atomic_label($subdomain);
        $this->createTable_blocked_ip($subdomain);
        $this->createTable_crm_account($subdomain);
        $this->createTable_crm_position($subdomain);
        $this->createTable_crm_token($subdomain);
//        $this->createTable_dashboard_columns($subdomain);
        $this->createTable_export_filter($subdomain);
        $this->createTable_kkcrm_account($subdomain);
        $this->createTable_kkcrm_alert_status($subdomain);
        $this->createTable_kkcrm_campaign_category($subdomain);
        $this->createTable_kkcrm_report_dashboard($subdomain);
        $this->createTable_kkcrm_token($subdomain);
        $this->createTable_label_affiliate($subdomain);
        $this->createTable_label_campaign($subdomain);
        $this->createTable_labels_goal($subdomain);
        $this->createTable_monitor_issue($subdomain);
        $this->createTable_monitor_schedule($subdomain);
        $this->createTable_monitor_sites($subdomain);
        $this->createTable_monitor_status($subdomain);
        $this->createTable_report_dashboard($subdomain);
        $this->createTable_retention_export($subdomain);
        $this->createTable_retention_initial_alert($subdomain);
        $this->createTable_retention_quick_export($subdomain);
        $this->createTable_user_account($subdomain);

    }

    private function createTable_affiliate_progress($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_affiliate_progress
                (
                id INT(11) NOT NULL AUTO_INCREMENT,
                crm_id INT(11) NOT NULL,
                campaign_id INT(11) NOT NULL,
                initial_customer INT(11) NOT NULL,
                affiliate_id CHAR(20) CHARACTER SET latin1 NOT NULL,
                affiliate_label CHAR(20) CHARACTER SET latin1 NOT NULL,
                user_token INT(11) NOT NULL,
                PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_affiliate_progress_cache($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_affiliate_progress_cache
                (
                id INT(11) NOT NULL AUTO_INCREMENT,
                crm_id INT(11) NOT NULL,
                campaign_id INT(11) NOT NULL,
                initial_customer INT(11) NOT NULL,
                affiliate_id CHAR(20) CHARACTER SET latin1 NOT NULL,
                affiliate_label CHAR(20) CHARACTER SET latin1 NOT NULL,
                start_date timestamp NULL DEFAULT NULL,
                end_date timestamp NULL DEFAULT NULL,
                date_type INT(2) DEFAULT NULL,
                PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_alert_receiver($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_alert_receiver
                (
                    rid int(11) NOT NULL AUTO_INCREMENT,
                    type int(11) NOT NULL,
                    address char(50) CHARACTER SET latin1 DEFAULT NULL,
                    status int(11) NOT NULL,
                    chatid char(200) CHARACTER SET latin1 DEFAULT NULL,
                    PRIMARY KEY (rid)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function createTable_alert_schedule($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_alert_schedule
                (
                    aid int(11) NOT NULL AUTO_INCREMENT,
                    alert_type int(11) NOT NULL,
                    alert_day char(40) CHARACTER SET latin1 DEFAULT NULL,
                    alert_hour char(150) CHARACTER SET latin1 DEFAULT NULL,
                    sms tinyint(1) DEFAULT NULL,
                    email tinyint(1) DEFAULT NULL,
                    telegram_bot tinyint(1) DEFAULT NULL,
                    show_status tinyint(1) NOT NULL,
                    PRIMARY KEY (aid)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $query = "INSERT  INTO " . $subdomain . "_alert_schedule(aid,alert_type,alert_day,alert_hour,sms,email,telegram_bot,show_status) values
                    (1,1,NULL,NULL,1,1,1,1),
                    (2,2,NULL,NULL,1,1,1,1),
                    (3,3,NULL,NULL,1,1,1,1),
                    (4,4,NULL,NULL,1,1,1,1),
                    (5,5,NULL,NULL,1,1,1,1),
                    (6,6,NULL,NULL,1,1,1,1),
                    (7,7,NULL,NULL,1,1,1,1),
                    (8,8,NULL,NULL,1,1,1,1),
                    (9,9,NULL,NULL,1,1,1,1),
                    (10,10,NULL,NULL,1,1,1,1),
                    (11,11,NULL,NULL,1,1,1,0),
                    (12,12,NULL,NULL,1,1,1,0),
                    (13,13,NULL,NULL,1,1,1,0)";

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                return $result;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function createTable_alert_setting($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_alert_setting
                (
                    aid int(11) NOT NULL AUTO_INCREMENT,
                    crm_id int(11) NOT NULL,
                    type int(11) NOT NULL,
                    value1 int(11) NOT NULL,
                    value2 int(11) NOT NULL,
                    PRIMARY KEY (aid)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function createTable_alert_status($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_alert_status
                (
                    aid int(11) NOT NULL AUTO_INCREMENT,
                    crm_id int(11) NOT NULL,
                    alert_type int(11) NOT NULL,
                    value int(11) NOT NULL,
                    level int(11) NOT NULL,
                    status int(11) NOT NULL,
                    timestamp datetime NOT NULL,
                    alert_read int(11) NOT NULL,
                    alert_delete int(11) NOT NULL,
                    from_date date NOT NULL,
                    to_date date NOT NULL,
                    PRIMARY KEY (aid)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function createTable_atomic_label($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_atomic_label
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    label_name char(100) CHARACTER SET latin1 NOT NULL,
                    type int(1) NOT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function createTable_blocked_ip($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_blocked_ip
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    ip char(100) CHARACTER SET latin1 NOT NULL,
                    description text CHARACTER SET latin1,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function createTable_crm_account($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_crm_account
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    crm_name char(30) CHARACTER SET latin1 NOT NULL,
                    crm_url char(100) CHARACTER SET latin1 NOT NULL,
                    user_name char(30) CHARACTER SET latin1 NOT NULL,
                    password char(50) CHARACTER SET latin1 NOT NULL,
                    api_user_name char(30) CHARACTER SET latin1 NOT NULL,
                    api_password char(50) CHARACTER SET latin1 NOT NULL,
                    sales_goal int(10) NOT NULL,
                    paused int(1) NOT NULL,
                    password_updated date DEFAULT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_crm_position($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_crm_position
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    user_id int(11) NOT NULL,
                    crm_positions char(200) COLLATE utf8mb4_unicode_ci NOT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_crm_token($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_crm_token
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    crm_id int(11) NOT NULL,
                    crm_token varchar(64) CHARACTER SET latin1 NOT NULL,
                    timestamp bigint(20) NOT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_kkcrm_account($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_kkcrm_account
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    crm_name char(30) CHARACTER SET latin1 NOT NULL,
                    crm_url char(100) CHARACTER SET latin1 NOT NULL,
                    user_name char(30) CHARACTER SET latin1 NOT NULL,
                    password char(50) CHARACTER SET latin1 NOT NULL,
                    api_user_name char(30) CHARACTER SET latin1 NOT NULL,
                    api_password char(50) CHARACTER SET latin1 NOT NULL,
                    sales_goal int(10) NOT NULL,
                    paused int(1) NOT NULL,
                    password_updated date DEFAULT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_kkcrm_alert_status($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_kkcrm_alert_status
                (
                    aid int(11) NOT NULL AUTO_INCREMENT,
                    crm_id int(11) NOT NULL,
                    alert_type int(11) NOT NULL,
                    value int(11) NOT NULL,
                    level int(11) NOT NULL,
                    status int(11) NOT NULL,
                    timestamp datetime NOT NULL,
                    alert_read int(11) NOT NULL,
                    alert_delete int(11) NOT NULL,
                    from_date date NOT NULL,
                    to_date date NOT NULL,
                    PRIMARY KEY (aid)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_kkcrm_token($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_kkcrm_token
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    crm_id int(11) NOT NULL,
                    crm_token varchar(64) CHARACTER SET latin1 NOT NULL,
                    timestamp bigint(20) NOT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_kkcrm_campaign_category($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_kkcrm_campaign_category
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    category_name char(50) CHARACTER SET latin1 NOT NULL,
                    campaign_ids char(200) CHARACTER SET latin1 NOT NULL,
                    crm_id int(11) NOT NULL,
                    campaign_names text CHARACTER SET latin1,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_kkcrm_report_dashboard($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_kkcrm_report_dashboard
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    crm_id int(11) NOT NULL,
                    crm_name char(30) CHARACTER SET latin1 NOT NULL,
                    step1 int(11) NOT NULL,
                    step2 int(11) NOT NULL,
                    takerate float NOT NULL,
                    tablet int(11) NOT NULL,
                    tabletrate float NOT NULL,
                    crm_goal int(11) DEFAULT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_dashboard_columns($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_dashboard_columns
                (
                    cid int(11) NOT NULL AUTO_INCREMENT,
                    user_id int(11) NOT NULL,
                    show_columns char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                    PRIMARY KEY (cid)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_export_filter($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_export_filter
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    campaign_label char(100) CHARACTER SET latin1 NOT NULL,
                    checked tinyint(1) NOT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function createTable_label_affiliate($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_label_affiliate
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    affiliate_id char(20) CHARACTER SET latin1 NOT NULL,
                    label char(20) CHARACTER SET latin1 NOT NULL,
                    crm_id int(11) NOT NULL,
                    sales_goal int(5) NOT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function createTable_label_campaign($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_label_campaign
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    crm_id int(5) NOT NULL,
                    campaign_id int(10) NOT NULL,
                    label_ids char(20) CHARACTER SET latin1 NOT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_labels_goal($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_labels_goal
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    crm_id int(11) NOT NULL,
                    label_id int(11) NOT NULL,
                    goal int(11) DEFAULT NULL,
                    visible int(1) NOT NULL DEFAULT '1',
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_monitor_issue($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_monitor_issue
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    user_id int(11) NOT NULL,
                    issue_time timestamp NULL DEFAULT NULL,
                    100_sites text COLLATE utf8mb4_unicode_ci,
                    200_sites text COLLATE utf8mb4_unicode_ci,
                    300_sites text COLLATE utf8mb4_unicode_ci,
                    400_sites text COLLATE utf8mb4_unicode_ci,
                    500_sites text COLLATE utf8mb4_unicode_ci,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_monitor_schedule($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_monitor_schedule
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    user_id int(11) NOT NULL,
                    min_interval int(3) NOT NULL,
                    enable_sms int(1) NOT NULL DEFAULT '1',
                    enable_email int(1) NOT NULL DEFAULT '1',
                    enable_bot int(1) NOT NULL DEFAULT '1',
                    monitor_last_updated timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_monitor_sites($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_monitor_sites
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    user_id int(11) NOT NULL,
                    site_name char(100) CHARACTER SET latin1 NOT NULL,
                    site_url char(100) CHARACTER SET latin1 NOT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_monitor_status($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_monitor_status
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    site_id int(11) NOT NULL,
                    site_stats char(10) COLLATE utf8mb4_unicode_ci NOT NULL,
                    start_time timestamp NULL DEFAULT NULL,
                    end_time timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    private function createTable_report_dashboard($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_report_dashboard
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    crm_id int(11) NOT NULL,
                    crm_name char(30) CHARACTER SET latin1 NOT NULL,
                    step1 int(11) NOT NULL,
                    step2 int(11) NOT NULL,
                    takerate float NOT NULL,
                    tablet int(11) NOT NULL,
                    tabletrate float NOT NULL,
                    crm_goal int(11) DEFAULT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function createTable_retention_export($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_retention_export
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    crm_id int(11) NOT NULL,
                    crm_name char(30) CHARACTER SET latin1 DEFAULT NULL,
                    campaign_id int(11) NOT NULL,
                    campaign_name char(200) CHARACTER SET latin1 DEFAULT NULL,
                    campaign_label char(100) CHARACTER SET latin1 DEFAULT NULL,
                    affiliate_id char(50) CHARACTER SET latin1 DEFAULT NULL,
                    affiliate_label char(100) CHARACTER SET latin1 DEFAULT NULL,
                    subaffiliate_id char(100) CHARACTER SET latin1 DEFAULT NULL,
                    subaffiliate_label char(100) CHARACTER SET latin1 DEFAULT NULL,
                    init1 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    init2 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    init3 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    init4 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    init5 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    init6 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    second1 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    second2 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    second3 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    second4 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    second5 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    second6 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    user_token char(50) CHARACTER SET latin1 DEFAULT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function createTable_retention_initial_alert($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_retention_initial_alert
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    crm_id int(11) NOT NULL,
                    crm_name char(30) CHARACTER SET latin1 DEFAULT NULL,
                    campaign_id int(11) NOT NULL,
                    affiliate_id char(50) CHARACTER SET latin1 DEFAULT NULL,
                    subaffiliate_id char(50) CHARACTER SET latin1 DEFAULT NULL,
                    gross_order int(11) DEFAULT NULL,
                    net_approved int(11) DEFAULT NULL,
                    approval_rate float DEFAULT NULL,
                    day tinyint(1) DEFAULT NULL,
                    has_child tinyint(1) DEFAULT NULL,
                    from_date date NOT NULL,
                    to_date date NOT NULL,
                    timestamp datetime NOT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function createTable_retention_quick_export($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_retention_quick_export
                (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    crm_id int(11) NOT NULL,
                    crm_name char(30) CHARACTER SET latin1 DEFAULT NULL,
                    campaign_id int(11) NOT NULL,
                    campaign_name char(200) CHARACTER SET latin1 DEFAULT NULL,
                    campaign_label char(100) CHARACTER SET latin1 DEFAULT NULL,
                    affiliate_id char(50) CHARACTER SET latin1 DEFAULT NULL,
                    affiliate_label char(100) CHARACTER SET latin1 DEFAULT NULL,
                    subaffiliate_id char(100) CHARACTER SET latin1 DEFAULT NULL,
                    subaffiliate_label char(100) CHARACTER SET latin1 DEFAULT NULL,
                    init1 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    init2 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    init3 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    init4 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    init5 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    init6 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    second1 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    second2 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    second3 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    second4 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    second5 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    second6 char(30) CHARACTER SET latin1 DEFAULT NULL,
                    user_token char(50) CHARACTER SET latin1 NOT NULL,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    private function createTable_user_account($subdomain)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {

            $query = "CREATE TABLE " . $subdomain . "_user_account
                (
                    id int(11) unsigned NOT NULL AUTO_INCREMENT,
                    user_name char(30) CHARACTER SET latin1 NOT NULL,
                    password char(50) CHARACTER SET latin1 NOT NULL,
                    display_name char(30) CHARACTER SET latin1 NOT NULL,
                    user_status tinyint(1) NOT NULL,
                    user_role int(1) NOT NULL,
                    crm_permissions char(200) CHARACTER SET latin1 DEFAULT NULL,
                    sms char(30) CHARACTER SET latin1 DEFAULT NULL,
                    email char(30) CHARACTER SET latin1 DEFAULT NULL,
                    bot char(30) CHARACTER SET latin1 DEFAULT NULL,
                    sms_enable tinyint(1) DEFAULT '1',
                    email_enable tinyint(1) DEFAULT '1',
                    bot_enable tinyint(1) DEFAULT '1',
                    created_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id)
                ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $ret = true;
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    /*
    *@description
    *	Update user info to admin user info and add new user to subdomain user account too
    *@param
    *	user id, fisrt name, last name, display name, subdomain, sms, bot id
    *@ret
    *	true
    */
    public function updateUserInfo($userId, $firstName, $lastName, $displayName, $subdomain, $smsNumber, $botId, $update_datetime = null)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $subdomainId = $this->addNewSubdomain($subdomain);
            $this->createAllTablesForSubdomain($subdomain);
            $update_datetime = date("Y-m-d H:i:s");

            $query = 'UPDATE admin_account_info SET first_name="' . $firstName . '",last_name="' . $lastName . '",display_name="' . $displayName . '",domain_id=' . $subdomainId . ',sms_number="' . $smsNumber . '",telegram_bot_id="' . $botId . '",is_verified=1,update_time="' . $update_datetime . '"' . ' WHERE user_id="' . $userId . '"';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                $query = 'SELECT * FROM admin_account_info WHERE user_id="' . $userId . '"';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                $count = mysqli_num_rows($result);
                if ($count > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $password = $row['password'];
                    $displayName = $row['display_name'];
                    $email = $row['email'];
                    $sms = $row['sms_number'];
                    $bot = $row['telegram_bot_id'];

                    // create new user account to subdomain user accont as admin (9)
                    $ret = $this->addNewUserToSubdomain($userId, $password, $displayName, 9, $sms, $email, $bot, $subdomain);
                }

            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }

    /*
    *@description
    *	Sign in to subdomain apilotus
    *@param
    *	User id, Password, Sub domain name
    *@ret
    *   array(display_name, user_role, user_status, id, user_name)
    */
    public function signIn($userId, $password, $subdomain)
    {
        if (!$this->checkConnection())
            return array();

        try {
            $accountInfo = array();

            $query = 'SELECT * FROM ' . $subdomain . '_user_account WHERE user_name="' . $userId . '" and password="' . $password . '" and user_status=1';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);

            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $accountInfo = array($row['display_name'], $row['user_role'], $row['user_status'], $row['id'], $row['user_name']);
            }
            return $accountInfo;

        } catch (Exception $e) {

            return array();
        }
    }

    /*
     *@description
     *  Add card info
     *@param
     *  user id, email, customer id, subscription id, card id
     *@ret
     *  true if added
     */
    public function addCardInfo($userID, $subDomain, $email, $customerID, $subscriptionID, $cardID)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $create_time = date("Y-m-d H:i:s");
            $query = 'INSERT INTO admin_billing_info (bid, user_id, subdomain, email, customer_id, subscription_id, card_id, is_blocked, is_deleted, create_time, update_time) VALUES (null,"'
                . $userID . '","' . $subDomain . '","' . $email . '","' . $customerID . '","' . $subscriptionID . '","' . $cardID . '",0,0,"' . $create_time . '","' . $create_time . '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE)
                return true;
            else
                return false;
        } catch (Exception $e) {

            return false;
        }
    }

    /*
     *@description
     *  Update card info
     *@param
     *  user id, email, customer id, subscription id
     *@ret
     *  true if added
     */
    public function updateCardInfo($userID, $email, $customerID, $subscriptionID)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $update_time = date("Y-m-d H:i:s");
            $query = 'UPDATE admin_billing_info SET email="' . $email . '",customer_id="' . $customerID . '",subscription_id="' . $subscriptionID . '",update_time="' . $update_time . '" WHERE user_id="' . $userID . '"';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE)
                return true;
            else
                return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getPaymentCardID($subDomain)
    {
        $ret = '';
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT card_id FROM admin_billing_info WHERE subdomain="' . $subDomain . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $ret = $row['card_id'];
            }
            return $ret;

        } catch (Exception $e) {

            return $ret;
        }
    }

    public function getPaymentCustomerID($subDomain)
    {
        $ret = '';
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT customer_id FROM admin_billing_info WHERE subdomain="' . $subDomain . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $ret = $row['customer_id'];
            }
            return $ret;

        } catch (Exception $e) {

            return $ret;
        }
    }

    public function getPaymentSubscriptionID($subDomain)
    {
        $ret = '';
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT subscription_id FROM admin_billing_info WHERE subdomain="' . $subDomain . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $ret = $row['subscription_id'];
            }
            return $ret;

        } catch (Exception $e) {

            return $ret;
        }
    }

    public function updatePaymentCardID($subDomain, $cardID)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'UPDATE admin_billing_info SET card_id="'.$cardID.'" WHERE subdomain="'.$subDomain.'"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }

    public function updatePaymentCustomerID($subDomain, $customerID)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'UPDATE admin_billing_info SET customer_id="'.$customerID.'" WHERE subdomain="'.$subDomain.'"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }

    public function updatePaymentSubscriptionID($subDomain, $subscriptionID)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'UPDATE admin_billing_info SET subscription_id="'.$subscriptionID.'" WHERE subdomain="'.$subDomain.'"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }
    /*
     * @description
     * Get atomic labels registered by type
     * @param
     * type = 1(campaign type),2(desktop / mobile),3(verticals),4(custom)
     * @ret
     * array
     */
    public function getLabelsByType($type)
    {
        $ret = array();
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM '.$this->subDomain.'_atomic_label WHERE type='.$type;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result))
                {
                    $ret[] = array($row['id'], $row['label_name'], $row['type']);
                }
            }
            return $ret;

        } catch (Exception $e) {

            return $ret;
        }
    }
    /*
     * @description
     * Add or Update goal of label in CRM
     * @param
     * CRM ID, LABEL ID in atomic_label table, GOAL, VISIBLE
     * @ret
     * true
     */
    public function updateLabelGoal($crmId, $labelId, $goal, $visible)
    {
        $ret = false;
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM '.$this->subdomain.'_labels_goal WHERE crm_id='.$crmId.' and label_id='.$labelId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0)
            {
                $query = 'UPDATE ' . $this->subdomain . '_labels_goal SET goal=' . $goal .',visible='.$visible. ' WHERE crm_id=' . $crmId.' and label_id='.$labelId;
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if ($result)
                    $ret = true;
                else
                    $ret = false;
            }
            else
            {
                $query = 'INSERT INTO ' . $this->subdomain . '_labels_goal (id, crm_id, label_id, goal, visible) VALUES (null,'. $crmId . ',' . $labelId . ',' . $goal .','.$visible. ')';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                if ($result)
                    $ret = true;
                else
                    $ret = false;
            }

            return $ret;

        } catch (Exception $e) {
            return $ret;
        }
    }
    /*
	*@description
	*	Get atomic labels and goals by crm ,which their types are 3, in subdomain
    *@param
     * CRM ID
	*@return
	*	array(label, goal)
	*/
    public function getLabelsAndGoalsByCrm($crmId)
    {
        if (!$this->checkConnection())
            return array();

        try {
            $labelTable = $this->subdomain.'_atomic_label';
            $goalTable = $this->subdomain.'_labels_goal';
            $query = 'SELECT '.$labelTable.'.*,goalTable.goal,goalTable.visible FROM ' . $labelTable.' LEFT JOIN ( SELECT * FROM '.$goalTable.' WHERE crm_id='.$crmId.') as goalTable ON '.$labelTable.'.id='.'goalTable.label_id ORDER BY '.$labelTable.'.id ASC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);

            $ret = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($row['goal'] == null)
                        $goal = 0;
                    else
                        $goal = $row['goal'];
                    $ret[] = array($row['id'], $row['label_name'], $row['type'], $goal, $row['visible']);
                }
            }

            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }
    public function getLabelsAndGoalsByCrmAndType($crmId, $type)
    {
        if (!$this->checkConnection())
            return array();

        try {
            $labelTable = $this->subdomain.'_atomic_label';
            $goalTable = $this->subdomain.'_labels_goal';
            $query = 'SELECT '.$labelTable.'.*,goalTable.goal FROM ' . $labelTable.' RIGHT JOIN ( SELECT * FROM '.$goalTable.' WHERE crm_id='.$crmId.' and visible=1'.') as goalTable ON '.$labelTable.'.id='.'goalTable.label_id WHERE '.$labelTable.'.type='.$type.' ORDER BY '.$labelTable.'.id ASC';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);

            $ret = array();
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($row['goal'] == null)
                        $goal = 0;
                    else
                        $goal = $row['goal'];
                    $ret[] = array($row['id'], $row['label_name'], $row['type'], $goal);
                }
            }

            return $ret;

        } catch (Exception $e) {
            return array();
        }
    }
    public function validateAdminPanelAccount($userId, $password)
    {
        $ret = array();
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM admin_panel_account WHERE user_id="'.$userId.'" and password="'.$password.'"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $ret = array($row['id'], $row['user_id'], $row['display_name']);
            }
            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }
    public function checkUserID($userId, $subDomain)
    {
        $ret = false;
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM '.$subDomain.'_user_account WHERE id='.$userId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                $ret = true;
            }

            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }
    public function setBotByUserID($userId, $botId, $subDomain)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'UPDATE ' . $subDomain . '_user_account SET bot="' . $botId . '" WHERE id=' . $userId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }
    public function getDashboardShowColumns($userId)
    {
//        $ret = '';
//        if (!$this->checkConnection())
//            return $ret;
//
//        try {
//            $query = 'SELECT show_columns FROM '.$this->subdomain.'_dashboard_columns WHERE user_id='.$userId;
//            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
//            $count = mysqli_num_rows($result);
//            if ($count > 0) {
//                $row = mysqli_fetch_assoc($result);
//                $ret = $row['show_columns'];
//            }
//
//            return $ret;
//        } catch (Exception $e) {
//            return $ret;
//        }
    }
    public function updateDashboardShowColumns($userId, $showItems)
    {
//        $ret = false;
//        if (!$this->checkConnection())
//            return $ret;
//
//        try {
//            $query = 'SELECT * FROM '.$this->subdomain.'_dashboard_columns WHERE user_id='.$userId;
//            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
//            $count = mysqli_num_rows($result);
//            if ($count > 0)
//            {
//                $query = 'UPDATE ' . $this->subdomain . '_dashboard_columns SET show_columns="' . $showItems . '" WHERE user_id=' . $userId;
//            }
//            else
//            {
//                $query = 'INSERT INTO ' . $this->subdomain . '_dashboard_columns (cid, user_id, show_columns) VALUES (null,' . $userId . ',"' . $showItems . '")';
//            }
//            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
//
//            if ($result)
//                $ret = true;
//            else
//                $ret = false;
//            return $ret;
//        } catch (Exception $e) {
//            return $ret;
//        }
    }
    public function getSubDomainByBotID($botId)
    {
        $subDomains = $this->getAllSubDomain();
        foreach ($subDomains as $item)
        {
            $name = $item[1];
            if ($this->checkBotIdInSubDomain($name, $botId))
                return $name;
        }
        return '';
    }
    private function checkBotIdInSubDomain($subDomain, $botId)
    {
        $ret = false;
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM '.$subDomain.'_user_account WHERE bot="'.$botId.'"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0)
                $ret = true;
            else
                $ret = false;

            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }
    public function getUserCrmByBotID($chatID)
    {
        $ret = null;
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM '.$this->subdomain.'_user_account WHERE bot="'.$chatID.'"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0)
            {
                $user = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $userId = $row['id'];
                    $user['name'] = $row['display_name'];
                    $user['status'] = $row['user_status'];
                    $user['active_crm'] = $this->getAllActiveCrmsByAccountId($userId);
                    break;
                }
                $ret = $user;
            }

            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }
    public function getAllSubDomainList()
    {
        $ret = array();
        $subDomains = $this->getAllSubDomain();
        foreach ($subDomains as $item)
        {
            $domainId = $item[0];
            $domainName = $item[1];
            $users = $this->getAllUsersInSubDomain($domainName);
            $crmCounts = $this->getAllCrmCountInSubDomain($domainName);
            $numberOfUsers = 0; $numberOfUsersBlocked = 0;
            foreach ($users as $user)
            {
                if ($user[5] == 0)
                    $numberOfUsersBlocked ++;
                $numberOfUsers ++;
            }
            $ret[] = array($domainId, $domainName, $numberOfUsers, $numberOfUsersBlocked, $crmCounts[0], $crmCounts[1]);
        }
        return $ret;
    }
    private function getAllCrmCountInSubDomain($subDomain)
    {
        $ret = array(0, 0);
        if (!$this->checkConnection())
            return $ret;

        try {

            $query = 'SELECT * FROM ' . $subDomain . '_crm_account';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                $allCount = 0;
                $pausedCount = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $allCount ++;
                    if ($row['paused'] == 1)
                        $pausedCount ++;

                }
                $ret[0] = $allCount;
                $ret[1] = $pausedCount;
            }

            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }
    /*
     * return all user account info for admin panel
     */
    public function getAllCustomers()
    {
        $ret = array();

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT admin_account_info.*,admin_subdomain.subdomain FROM admin_account_info LEFT JOIN admin_subdomain ON admin_account_info.domain_id=admin_subdomain.sid';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result))
                {
                    $ret[] = array($row['aid'], $row['user_id'], $row['email'], $row['password'], $row['verify_code'], $row['first_name'], $row['last_name'], $row['display_name']
                    , $row['subdomain'], $row['sms_number'], $row['telegram_bot_id'], $row['is_verified'], $row['is_blocked'], $row['is_deleted'], $row['comment']
                    , $row['create_time'], $row['update_time']);
                }
            }

            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }
    /*
     * Get all accounts of Admin_panel_account
     */
    public function getProfileList()
    {
        $ret = array();
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM admin_panel_account' ;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result))
                {
                    $ret[] = array($row['id'], $row['user_id'], $row['password'], $row['display_name'], $row['email']);
                }

            }
            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }
    public function updateProfilePassword($userId, $password)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'UPDATE admin_panel_account SET password="'.$password.'" WHERE user_id="'.$userId.'"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }
    public function updateProfile($userID, $email, $displayName)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'UPDATE admin_panel_account SET display_name="' . $displayName . '",email="' . $email . '" WHERE user_id="'. $userID.'"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if (!$result)
                return false;
            else
                return true;

        } catch (Exception $e) {

            return false;
        }
    }

    public function getPaymentList()
    {
        $ret = array();
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM admin_billing_info' ;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result))
                {
                    $ret[] = array($row['bid'], $row['user_id'], $row['subdomain'], $row['email'], $row['customer_id'], $row['subscription_id'], $row['card_id'],
                        $row['is_blocked'], $row['is_deleted'], $row['create_time'], $row['update_time']);
                }

            }
            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }

    public function insertLoginUserInfo($ipAddress, $location, $userAgent, $userName, $subDomain)
    {
        $timestamp = date("Y-m-d H:i:s");
        if (!$this->checkConnection())
            return false;
        try {
            $query = 'INSERT INTO admin_panel_login_history (hid, ip, location, user_agent, login_date, user_name, subdomain) VALUES(null,"' . $ipAddress . '","' . $location . '","' . $userAgent . '","' . $timestamp.'","'.$userName.'","'.$subDomain. '")';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if (!$result)
                return false;
            else
                return true;

        } catch (Exception $e) {

            return false;
        }

    }
    public function getLoginHistory()
    {
        $ret = array();
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM admin_panel_login_history ORDER BY login_date DESC LIMIT 20' ;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result))
                {
                    $ret[] = array($row['hid'], $row['ip'], $row['location'], $row['user_agent'], $row['login_date'], $row['user_name'], $row['subdomain']);
                }
            }
            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }
    public function updateAdminAccountBlock($customerId, $blocked)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'UPDATE admin_account_info SET is_blocked='.$blocked.' WHERE aid=' .$customerId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }
    /*
     * return true if subdomain not existing or admin account of subdomain is blocked
     */
    public function checkSubDomainBlock($subDomain)
    {
        $sId = $this->checkIfSubdomainRegistered($subDomain);
        if ($sId == -1)
            return true;

        if (!$this->checkConnection())
            return true;

        try {

            $query = 'SELECT is_blocked FROM admin_account_info WHERE domain_id='.$sId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                if ($row['is_blocked'] == 0)
                    return false;

            }
            return true;
        } catch (Exception $e) {
            return true;
        }
    }
    public function updateAdminAccountPassword($userId, $password)
    {
        $ret = false;

        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'UPDATE admin_account_info SET password="' . $password . '" WHERE user_id="' . $userId . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result)
            {
                // Get Subdomain
                $query = 'SELECT admin_subdomain.subdomain FROM admin_subdomain LEFT JOIN admin_account_info ON admin_subdomain.sid=admin_account_info.domain_id WHERE admin_account_info.user_id="'.$userId.'"';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                $count = mysqli_num_rows($result);
                if ($count > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $subDomain = $row['subdomain'];
                    if ($subDomain != '')
                    {
                        $query = 'UPDATE '.$subDomain.'_user_account SET password="' . $password . '" WHERE user_name="' . $userId . '"';
                        $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                        if ($result)
                            $ret = true;

                    }
                }
            }
            return $ret;
        } catch (Exception $e) {

            return $ret;
        }
    }
    public function updateAccountStatus($userID, $status)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'UPDATE ' . $this->subdomain . '_user_account SET user_status=' . $status . ' WHERE id=' . $userID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if (!$result)
                return false;
            else
                return true;

        } catch (Exception $e) {

            return false;
        }
    }
    public function updateCRMPaused($crmID, $paused)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'UPDATE ' . $this->subdomain . '_crm_account SET paused=' . $paused . ' WHERE id=' . $crmID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }
    public function getFeatureEnableList($subDomain)
    {
        $ret = '';
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM admin_panel_feature_enable WHERE subdomain="'.$subDomain.'"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $ret = $row['feature_enable'];
            }
            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }
    public function updateAdminPanelFeatureEnable($subDomain, $items)
    {
        $ret = false;
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM admin_panel_feature_enable WHERE subdomain="'.$subDomain.'"';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0)
            {
                $query = 'UPDATE admin_panel_feature_enable SET feature_enable="' . $items . '" WHERE subdomain="' . $subDomain.'"';
            }
            else
            {
                $query = 'INSERT INTO admin_panel_feature_enable (cid, subdomain, feature_enable) VALUES (null,"' . $subDomain . '","' . $items . '")';
            }
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result)
                $ret = true;
            else
                $ret = false;
            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }
    public function getAdminPanelDashboardShowColumns($subDomain)
    {
        $ret = '';
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM admin_panel_dashboard_columns WHERE subdomain="'.$subDomain.'"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $ret = $row['show_columns'];
            }
            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }
    public function updateAdminPanelDashboardShowColumns($subDomain, $showItems)
    {
        $ret = false;
        if (!$this->checkConnection())
            return $ret;

        try {
            $query = 'SELECT * FROM admin_panel_dashboard_columns WHERE subdomain="'.$subDomain.'"';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0)
            {
                $query = 'UPDATE admin_panel_dashboard_columns SET show_columns="' . $showItems . '" WHERE subdomain="' . $subDomain.'"';
            }
            else
            {
                $query = 'INSERT INTO admin_panel_dashboard_columns (cid, subdomain, show_columns) VALUES (null,"' . $subDomain . '","' . $showItems . '")';
            }
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result)
                $ret = true;
            else
                $ret = false;
            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }
    public function addKKCrmAccount($crmName, $crmUrl, $crmUserName, $crmPassword, $apiUserName, $apiPassword, $salesGoal, $paused, $userId)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $currentDay = date('Y-m-d');
            $query = 'INSERT INTO ' . $this->subdomain . '_kkcrm_account (id, crm_name, crm_url, user_name, password, api_user_name, api_password, sales_goal, paused, password_updated) VALUES (null,"'
                . $crmName . '","' . $crmUrl . '","' . $crmUserName . '","' . $crmPassword . '","' . $apiUserName . '","' . $apiPassword . '",' . $salesGoal . ',' . $paused . ',"' . $currentDay . '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                // update permission in user_account
                $query = 'SELECT id FROM ' . $this->subdomain . '_kkcrm_account WHERE crm_name="' . $crmName . '"';

                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                $count = mysqli_num_rows($result);

                if ($count > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $id = $row['id'];
                    return true;
//                    $permissions = $this->getCrmPermissionOfAccount($userId);
//                    $permissions = $permissions . ',' . $id;
//
//                    return $this->setCrmPermissionOfAccount($userId, $permissions);
                }
                return false;

            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }
    public function updateKKCrmApiPassword($crmID, $apiPassword)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'UPDATE ' . $this->subdomain . '_kkcrm_account SET api_password="' . $apiPassword . '" WHERE id=' . $crmID;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }
    public function updateKKCrmPassword($crmID, $crmPassword)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $currentDay = date('Y-m-d');
            $query = 'UPDATE ' . $this->subdomain . '_kkcrm_account SET password="' . $crmPassword . '",password_updated="' . $currentDay . '" WHERE id=' . $crmID;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }
    public function deleteKKCrmAccount($crmID, $user_id)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_kkcrm_account WHERE id=' . $crmID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
//                // update permission in user_account
//                $permissions = $this->getCrmPermissionOfAccount($user_id);
//                if (strpos($permissions, ',' . $crmID) !== false) {
//                    $permissions = str_replace(',' . $crmID, '', $permissions);
//                } else if (strpos($permissions, $crmID . ',') !== false) {
//                    $permissions = str_replace($crmID . ',', '', $permissions);
//                } else if (strpos($permissions, $crmID) !== false) {
//                    $permissions = str_replace($crmID, '', $permissions);
//                }
//
//                return $this->setCrmPermissionOfAccount($user_id, $permissions);

            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }
    public function updateKKCrmAccount($crmId, $crmName, $crmUrl, $crmUserName, $apiUserName, $salesGoal, $paused)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'UPDATE ' . $this->subdomain . '_kkcrm_account SET crm_name="' . $crmName . '",crm_url="' . $crmUrl . '",user_name="' . $crmUserName . '",api_user_name="' . $apiUserName . '",sales_goal=' . $salesGoal . ',paused=' . $paused . ' WHERE id=' . $crmId;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }
    public function getKKCrmAccountList($user_id)
    {
        if (!$this->checkConnection())
            return null;

        try {
            $currentDay = date('Y-m-d');
            $arrayCrm = array();

            $query = 'SELECT * FROM ' . $this->subdomain . '_kkcrm_account';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $arrayCrm[] = array($row['id'], $row['crm_name'], $row['crm_url'], $row['user_name'], $row['password'], $row['api_user_name'], $row['api_password'], $row['sales_goal'], $row['paused'], $row['password_updated'], $currentDay);
                }
            }

            return $arrayCrm;
        } catch (Exception $e) {
            return null;
        }
    }
    public function getKKCrmActiveList($user_id)
    {
        $arrayCrm = array();
        if (!$this->checkConnection())
            return $arrayCrm;

        try {
            $currentDay = date('Y-m-d');

            $query = 'SELECT * FROM ' . $this->subdomain . '_kkcrm_account where paused=0';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $arrayCrm[] = array($row['id'], $row['crm_name'], $row['crm_url'], $row['user_name'], $row['password'], $row['api_user_name'], $row['api_password'], $row['sales_goal'], $row['paused'], $row['password_updated'], $currentDay);
                }
            }

            return $arrayCrm;
        } catch (Exception $e) {
            return $arrayCrm;
        }
    }
    public function getKKActiveCrmById($crmID)
    {
        if (!$this->checkConnection())
            return null;

        try {
            $currentDay = date('Y-m-d');
            $arrayCrm = array();

            $query = 'SELECT * FROM ' . $this->subdomain . '_kkcrm_account where id='.$crmID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $arrayCrm = array($row['id'], $row['crm_name'], $row['crm_url'], $row['user_name'], $row['password'], $row['api_user_name'], $row['api_password'], $row['sales_goal'], $row['paused'], $row['password_updated'], $currentDay);
                }
            }

            return $arrayCrm;
        } catch (Exception $e) {
            return null;
        }
    }
    public function addKKCrmCampaignCategory($crmID, $categoryName, $campaignIDs, $campaignNames)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'INSERT INTO ' . $this->subdomain . '_kkcrm_campaign_category (id, category_name, campaign_ids, crm_id, campaign_names) VALUES (null,"'. $categoryName . '","' . $campaignIDs.'",'.$crmID.',"'.$campaignNames.'")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            return $result;
        } catch (Exception $e) {

            return false;
        }

    }
    public function deleteKKCrmCampaignCategory($categoryID)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'DELETE FROM ' . $this->subdomain . '_kkcrm_campaign_category WHERE id=' . $categoryID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            return $result;
        } catch (Exception $e) {

            return false;
        }
    }
    public function updateKKCrmCampaignCategory($categoryID, $categoryName, $campaignIDs, $campaignNames)
    {
        if (!$this->checkConnection())
            return false;

        try {

            $query = 'UPDATE ' . $this->subdomain . '_kkcrm_campaign_category SET category_name="' . $categoryName . '",campaign_ids="' . $campaignIDs .'",campaign_names="'.$campaignNames. '" WHERE id=' . $categoryID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    public function getKKCrmCampaignCategoryList($crmID)
    {
        if (!$this->checkConnection())
            return array();

        try {
            $ret = array();

            $query = 'SELECT * FROM ' . $this->subdomain . '_kkcrm_campaign_category WHERE crm_id='.$crmID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['id'], $row['category_name'], $row['campaign_ids'], $row['campaign_names']);
                }
            }
            return $ret;
        } catch (Exception $e) {
            return array();
        }
    }
    public function getCrmPositions($userID)
    {
        $ret = '';
        if (!$this->checkConnection())
            return $ret;

        try {

            $query = 'SELECT * FROM ' . $this->subdomain . '_crm_position WHERE user_id='.$userID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                $row = mysqli_fetch_assoc($result);
                $ret = $row['crm_positions'];
            }
            return $ret;
        } catch (Exception $e) {
            return $ret;
        }
    }
    public function setCrmPositions($userID, $crmPositions)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_crm_position WHERE user_id=' .$userID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0)
            {
                $query = 'UPDATE ' . $this->subdomain . '_crm_position SET crm_positions="' . $crmPositions . '" WHERE user_id=' . $userID;
            }
            else
            {
                $query = 'INSERT INTO ' . $this->subdomain . '_crm_position (id, user_id, crm_positions) VALUES (null,'. $userID . ',"' . $crmPositions. '")';

            }
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            return $result;
        } catch (Exception $e) {

            return false;
        }
    }

    public function getMonitorSiteList()
    {
        if (!$this->checkConnection())
            return array();

        try {
            $ret = array();

            $query = 'SELECT * FROM ' . $this->subdomain . '_monitor_sites' ;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['id'], $row['site_name'], $row['site_url'], $row['user_id']);
                }
            }
            return $ret;
        } catch (Exception $e) {
            return array();
        }
    }
    public function getMonitorSiteListByUserId($userID)
    {
        if (!$this->checkConnection())
            return array();

        try {
            $ret = array();

            $query = 'SELECT * FROM ' . $this->subdomain . '_monitor_sites where user_id='.$userID ;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['id'], $row['site_name'], $row['site_url'], $row['user_id']);
                }
            }

            return $ret;
        } catch (Exception $e) {
            return array();
        }
    }
    public function getMonitorStatusByUserId($userId, $pageIndex = -1, $items4Page = -1)
    {
        if (!$this->checkConnection())
            return array();
        $schedule =  $this->getMonitorSchedule($userId);
        if($schedule != array())
        {

            $last_updated = $schedule[0][6];
            $last_updated = date('Y-m-d H:i', strtotime($last_updated));

            try {
                $ret = array();
                $status_table = $this->subdomain.'_monitor_status';
                $site_table = $this->subdomain.'_monitor_sites';

                $query = 'SELECT '.$status_table.'.*,'.$site_table.'.* FROM '.$status_table.' LEFT JOIN '.$site_table.' ON '.$status_table.'.site_id='.$site_table.'.id where '.$site_table.'.user_id='.$userId
                    .' AND '.$status_table.'.end_time >= "'.$last_updated.'" ORDER BY '.$site_table.'.id ASC';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                $count = mysqli_num_rows($result);
                if ($count > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $ret[] = array($row['site_id'], $row['site_name'], $row['site_url'], $row['site_stats'], $row['end_time']);
                    }
                }

                if ($pageIndex != -1 && $items4Page != -1)
                {
                    $total_count = count($ret);
                    $ret = array_slice($ret, ($pageIndex - 1) * $items4Page, $items4Page);
                    $data['total_count'] = $total_count;
                    $data['status'] = $ret;
                    return $data;
                }

                return $ret;
            } catch (Exception $e) {
                return array();
            }
        }

        return array();


    }
    public function addMonitorStatusIssues($userId, $issue_data, $timestamp)
    {
        $site100 = '';
        $site200 = '';
        $site300 = '';
        $site400 = '';
        $site500 = '';

        foreach ($issue_data as $data)
        {
            $site_name = $data[0];
            $site_status = intval($data[2]);

            if (100 <= $site_status && $site_status < 200)
            {
                if (strlen($site100) > 0)
                    $site100 .= ', '.$site_name.'('.$site_status.')';
                else
                    $site100 = $site_name.'('.$site_status.')';
            }
            if (200 <= $site_status && $site_status < 300)
            {
                if (strlen($site200) > 0)
                    $site200 .= ', '.$site_name.'('.$site_status.')';
                else
                    $site200 = $site_name.'('.$site_status.')';
            }
            if (300 <= $site_status && $site_status < 400)
            {
                if (strlen($site300) > 0)
                    $site300 .= ', '.$site_name.'('.$site_status.')';
                else
                    $site300 = $site_name.'('.$site_status.')';
            }
            if (400 <= $site_status && $site_status < 500)
            {
                if (strlen($site400) > 0)
                    $site400 .= ', '.$site_name.'('.$site_status.')';
                else
                    $site400 = $site_name.'('.$site_status.')';
            }
            if (500 <= $site_status && $site_status < 600)
            {
                if (strlen($site500) > 0)
                    $site500 .= ', '.$site_name.'('.$site_status.')';
                else
                    $site500 = $site_name.'('.$site_status.')';
            }
        }
        try {
            $query = 'INSERT INTO ' . $this->subdomain . '_monitor_issue (id, user_id, issue_time, 100_sites , 200_sites, 300_sites, 400_sites, 500_sites) VALUES (null,'
                . $userId . ',"' . $timestamp . '","' . $site100 . '","' . $site200.'","'.$site300.'","'.$site400.'","'.$site500. '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            return $result;
        } catch (Exception $e) {
            return false;
        }

    }
    private function getIrregularSiteList($userId)
    {
        if (!$this->checkConnection())
            return null;

        try {
            $ret = array();

            $query = 'SELECT * FROM ' . $this->subdomain . '_monitor_sites WHERE user_id='.$userId.' AND site_stats != "" AND site_stats != 200' ;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['id'], $row['site_name'], $row['site_url'], $row['site_stats']);
                }
            }
            return $ret;
        } catch (Exception $e) {
            return null;
        }
    }
    public function getMonitorSiteListPaging($userId , $pageNumber, $items4Page)
    {
        if (!$this->checkConnection())
            return array();

        try {
            $ret = array();

            $query = 'SELECT * FROM ' . $this->subdomain . '_monitor_sites where user_id='.$userId ;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['id'], $row['site_name'], $row['site_url']);
                }
            }
            if ($pageNumber == 0 && $items4Page == 0)
            {
                $data['length'] = $count;
                $data['urls'] = $ret;

                return $data;
            }
            $data = array_slice($ret, ($pageNumber - 1) * $items4Page, $items4Page);
            $sites['urls'] = $data;
            $sites['length'] = count($ret);
            return $sites;

        } catch (Exception $e) {
            return array();
        }

    }
    public function updateStatsByUrl($urlID, $stats, $timestamp)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_monitor_status WHERE site_id=' . $urlID. ' AND site_stats="'.$stats.'"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if($count > 0)
            {
                $query = 'UPDATE ' . $this->subdomain . '_monitor_status SET end_time="' . $timestamp .'" WHERE site_id=' . $urlID.' AND site_stats="'.$stats.'"';
            }
            else
            {
                $query = 'INSERT INTO ' . $this->subdomain . '_monitor_status (id, site_id, site_stats, start_time, end_time) VALUES (null,'
                    . $urlID . ',"' . $stats . '","' . $timestamp . '","' . $timestamp . '")';
            }
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    public function updateMonitorTime($start, $end, $userId)
    {
        if (!$this->checkConnection())
            return false;

        try {
//            if ($end === NULL)
//                $query = 'UPDATE ' . $this->subdomain . '_monitor_schedule SET last_started="' . $start .'",last_updated=NULL where user_id='.$userId;
//            else
                $query = 'UPDATE ' . $this->subdomain . '_monitor_schedule SET monitor_last_updated="' . $end .'" where user_id='.$userId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    public function getMonitorSchedule($userID = -1)
    {
        if (!$this->checkConnection())
            return null;

        try {
            $ret = array();

            if ($userID == -1)
                $query = 'SELECT * FROM ' . $this->subdomain . '_monitor_schedule' ;
            else
                $query = 'SELECT * FROM ' . $this->subdomain . '_monitor_schedule WHERE user_id='.$userID ;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $ret[] = array($row['id'], $row['min_interval'], $row['enable_sms'], $row['enable_email'], $row['enable_bot'], $row['user_id'], $row['monitor_last_updated']);
                }
            }
            return $ret;
        } catch (Exception $e) {
            return null;
        }
    }
    public function getMonitorIssues($userID, $limit)
    {
        $issues = array();
        if (!$this->checkConnection())
            return $issues;

        try {

            $query = 'SELECT * FROM ' . $this->subdomain . '_monitor_issue where user_id='.$userID.' ORDER BY issue_time DESC LIMIT '.$limit;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result))
                {
                    $issues[] = array($row['id'], $row['issue_time'], $row['100_sites'], $row['200_sites'], $row['300_sites'], $row['400_sites'], $row['500_sites']);
                }
            }
            return $issues;
        } catch (Exception $e) {
            return $issues;
        }
    }
    public function addSiteHistory($site_id, $site_name, $site_status, $start_time, $end_time, $user_id)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'INSERT INTO ' . $this->subdomain . '_monitor_sites_history (id, site_id, site_name, site_stats, start_time, end_time, user_id) VALUES (null,'
                . $site_id . ',"' . $site_name . '","' . $site_status . '","' . $start_time . '","' . $end_time . '",' . $user_id.')';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    public function checkUrl($userId, $siteName, $siteUrl)
    {
        if (!$this->checkConnection())
            return null;

        try {

            $query = 'SELECT * FROM ' . $this->subdomain . '_monitor_sites where user_id='.$userId.' and site_url="'.$siteUrl.'"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            return null;
        }
    }
    public function addMonitorSite($userId, $siteName, $siteUrl)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'INSERT INTO ' . $this->subdomain . '_monitor_sites (id, user_id, site_name, site_url) VALUES (null,'. $userId . ',"' . $siteName .'","'. $siteUrl.'")';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    public function deleteMonitorSite($userId, $siteId)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'DELETE FROM ' . $this->subdomain . '_monitor_sites WHERE user_id='.$userId.' and id='.$siteId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    public function updateMonitorSite($userId, $siteName, $siteUrl, $siteId)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'UPDATE ' . $this->subdomain . '_monitor_sites SET site_name="' . $siteName . '",site_url="' . $siteUrl . '" WHERE id=' . $siteId.' AND user_id='.$userId;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }
    public function updateMonitorSchedule($interval, $sms, $email, $telegram_bot, $userID)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'SELECT * FROM '.$this->subdomain.'_monitor_schedule WHERE user_id='.$userID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0)
            {
                $query = 'UPDATE ' . $this->subdomain . '_monitor_schedule SET min_interval=' . $interval . ',enable_sms=' . $sms .',enable_email='.$email.',enable_bot='.$telegram_bot.' WHERE user_id='.$userID;
            } else {
                $query = 'INSERT INTO ' . $this->subdomain . '_monitor_schedule (id, user_id, min_interval, enable_sms, enable_email, enable_bot) VALUES (null,'
                    . $userID . ',' . $interval . ',' . $sms . ',' . $email . ',' . $telegram_bot .')';

            }
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {

            return false;
        }
    }
    public function updateMonitorStatus($userID, $data, $timestamp, $stack_len)
    {
        if (!$this->checkConnection())
            return false;
        try {
            foreach ($data as $item)
            {
                $query = 'INSERT INTO ' . $this->subdomain . '_monitor_sites_alert (id, site_name, site_stats, updated_time, user_id) VALUES (null,"'
                    . $item[0] . '","' . $item[2] . '","' . $timestamp . '",' . $userID . ')';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            }
            $query = 'SELECT * FROM '.$this->subdomain.'_monitor_sites_alert WHERE user_id='.$userID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $rows = mysqli_num_rows($result);
            if($rows > $stack_len)
            {
                $query = 'DELETE FROM '.$this->subdomain.'_monitor_sites_alert WHERE user_id='.$userID.' ORDER BY id LIMIT '.($rows - $stack_len);
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            }
            return true;
        } catch (Exception $e) {

            return false;
        }
    }
    public function getMonitorStatus($userID, $pageNumber = -1, $items4Page = -1)
    {
        $status = array();
        if (!$this->checkConnection())
            return $status;

        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_monitor_sites_alert WHERE user_id='.$userID ;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $status[] = array($row['id'], $row['site_name'], $row['site_stats'], $row['updated_time']);
                }
            }

            if($pageNumber != -1 && $items4Page != -1)
                $status = array_slice($status, ($pageNumber - 1) * $items4Page, $items4Page);
            $ret['status'] = $status;
            $ret['length'] = $count;
            return $ret;
        } catch (Exception $e) {
            return $status;
        }
    }
    public function getMonitorHistory($userID, $siteID, $start_time, $end_time)
    {
        $history = array();
        if (!$this->checkConnection())
            return $history;

        try {
            $site_table = $this->subdomain.'_monitor_sites';
            $status_table = $this->subdomain.'_monitor_status';

            $time_condition = '(("'.$start_time.'" BETWEEN '.$status_table.'.start_time AND '.$status_table.'.end_time OR "'.
                            $end_time.'" BETWEEN '.$status_table.'.start_time AND '.$status_table.'.end_time) OR ('.$status_table.'.start_time BETWEEN "'.$start_time.
                    '" AND "'.$end_time.'" AND '.$status_table.'.end_time BETWEEN "'.$start_time.'" AND "'.$end_time.'"))';

            $query = 'SELECT '.$status_table.'.*,'.$site_table.'.* FROM ' .$status_table.' LEFT JOIN '.$site_table.' ON '.$site_table.'.id='.$status_table.'.site_id'. ' WHERE '.$site_table.'.user_id='.$userID.' AND '.$status_table.'.site_id='.$siteID.' AND '.$time_condition;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
            $site_name = '';
            if ($count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $history[] = array($row['id'], $row['site_name'], $row['site_stats'], $row['start_time'], $row['end_time'], $row['site_id']);
                    $site_name = $row['site_name'];
                }
            }
            return array($site_name, $history);
        } catch (Exception $e) {
            return $history;
        }
    }

    public function insertCustomerFeedback($client_name, $client_email, $client_comment)
    {
        $timestamp = date("Y-m-d H:i:s");
        if (!$this->checkConnection())
            return false;

        try 
        {
            $query = 'INSERT INTO admin_panel_feedback (id, client_name, client_email, client_comment, submit_date, process_status) VALUES(null, "' . $client_name . '","' . $client_email . '","' . $client_comment . '","' . $timestamp.'", "Waiting")';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result)
                return true;
        } 
        catch (Exception $e) 
        {
            return false;
        }

        return false;
    }

    public function getCustomerFeedbackList()
    {
        $ret = array();
        if (!$this->checkConnection())
            return $ret;

        try 
        {
            $query = 'SELECT * FROM admin_panel_feedback ORDER BY submit_date DESC';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            $count = mysqli_num_rows($result);
        
            if ($count > 0) 
            {
                while($row = mysqli_fetch_assoc($result))
                {
                    $ret[] = array($row['id'], $row['client_name'], $row['client_email'], $row['client_comment'], $row['submit_date'], $row['process_status']);
                }

            }
        } 
        catch (Exception $e) {
            
        }

        return $ret;
    }

    public function addCrmResult($crmID, $time, $crm_result, $fromDate, $toDate)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'INSERT INTO ' . $this->subdomain . '_crm_result (id, from_date, to_date, timestamp, crm_id, label_id, label_name, goal, step1, step2, tablet, prepaid, step1_nonpp, step2_nonpp, order_page, order_count, decline, gross_order) VALUES (null,"'
                . $fromDate . '","' . $toDate . '","' . $time . '",' . $crmID . ','
                . $crm_result[0] . ',"' . $crm_result[1] . '",' . $crm_result[2] . ','
                . $crm_result[3] . ',' . $crm_result[4] . ',' . $crm_result[5] . ','
                . $crm_result[6] . ',' . $crm_result[7] . ',' . $crm_result[8] . ','
                . $crm_result[9] . ',' . $crm_result[10] . ',' . $crm_result[11] . ',' . $crm_result[12] . ')';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE)
                return true;
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    public function addCrmResults($crmID, $crmGoal, $response, $fromDate, $toDate)
    {
        if (!$this->checkConnection())
            return 'error';

        try {
            $query = 'DELETE FROM ' . $this->subdomain . '_crm_result WHERE crm_id=' . $crmID . ' and from_date="' . $fromDate . '" and to_date="' . $toDate . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
        } catch (Exception $e) {
            return 'error';
        }

        $current_time = date('Y-m-d H:i:s');
        foreach ($response as $crm_result) {
            if (0 == $crm_result[0])
                $crm_result[2] = $crmGoal;
            $this->addCrmResult($crmID, $current_time, $crm_result, $fromDate, $toDate);
        }
        return true;
    }

    public function getCrmResult($crmID, $fromDate, $toDate)
    {
        if (!$this->checkConnection())
            return 'error';

        try {
            $arrayCrm = array();

            $query = 'SELECT * FROM ' . $this->subdomain . '_crm_result WHERE crm_id=' . $crmID . ' and from_date="' . $fromDate . '" and to_date="' . $toDate . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $arrayCrm[] = array($row['label_id'], $row['label_name'], $row['goal'], (int)$row['step1'], (int)$row['step2'], (int)$row['tablet'], (int)$row['prepaid'], (int)$row['step1_nonpp'], (int)$row['step2_nonpp'], (float)$row['order_page'], (int)$row['order_count'], (int)$row['decline'], (int)$row['gross_order']);
                }
            }

            return $arrayCrm;
        } catch (Exception $e) {
            return 'error';
        }
    }

    public function checkCrmResult($crmID, $fromDate, $toDate)
    {
        if (!$this->checkConnection())
            return 'error';

        try {
            $query = 'SELECT COUNT(crm_id) FROM ' . $this->subdomain . '_crm_result WHERE crm_id=' . $crmID . ' and from_date="' . $fromDate . '" and to_date="' . $toDate . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                $row = mysqli_fetch_assoc($result);
                $count = $row['COUNT(crm_id)'];
                if ((int)$count > 0)
                    return true;
            }

            return false;
        } catch (Exception $e) {
            return 'error';
        }
    }

    public function deleteCrmResult($crmID, $fromDate, $toDate)
    {
        if (!$this->checkConnection())
            return 'error';

        try {
            $query = 'DELETE FROM ' . $this->subdomain . '_crm_result WHERE crm_id=' . $crmID . ' and from_date="' . $fromDate . '" and to_date="' . $toDate . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
        } catch (Exception $e) {
            return 'error';
        }
        return 'success';
    }

    public function getAllTrials()
    {
        if (!$this->checkConnection())
            return null;

        $ret = array();

        try {
            $query = 'SELECT crm_id FROM ' . $this->subdomain . '_crm_trial';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while($row = mysqli_fetch_assoc($result))
                    $ret[] = $row['crm_id'];
            }
        } catch (Exception $e) {
            return null;
        }

        return $ret;
    }

    public function getAllTrialCrmsByAccountId($accountId)
    {
        if (!$this->checkConnection())
            return null;

        $permissionString = $this->getCrmPermissionOfAccount($accountId);
        if ($permissionString == '')
            return array();

        $arrayPermission = explode(',', $permissionString);

        $allTrial = $this->getAllTrials();
        $allCrm = $this->getAllCrm();
        $arrayCrm = array();
        foreach ($allTrial as $trial) {
            if (in_array($trial, $arrayPermission)) {
                foreach ($allCrm as $crm) {
                    if ($crm[0] == $trial) {
                        $arrayCrm[] = $crm;
                        break;
                    }
                }
            }
        }
        return $arrayCrm;
    }

    public function getAllActiveTrialCrmsByAccountId($accountId)
    {
        if (!$this->checkConnection())
            return null;

        $result = array();
        $allCrm = $this->getAllTrialCrmsByAccountId($accountId);

        foreach ($allCrm as $crm) {
            if ($crm[8] == 0)
                $result[] = $crm;
        }
        return $result;
    }

    public function getTrialCampaignById($crmID)
    {
        if (!$this->checkConnection())
            return null;

        try {
            $query = 'SELECT trial_ids FROM ' . $this->subdomain . '_crm_trial WHERE crm_id=' . $crmID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                $row = mysqli_fetch_assoc($result);
                return $row['trial_ids'];
            }
            return 'error';
        } catch (Exception $e) {
            return null;
        }
    }

    public function addTrialCampaign($crmID, $fromDate, $toDate, $trial_result)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'DELETE FROM ' . $this->subdomain . '_crm_trial_result WHERE crm_id=' . $crmID . ' and from_date="' . $fromDate . '" and to_date="' . $toDate . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
        } catch (Exception $e) {
            return false;
        }

        $current_time = date('Y-m-d H:i:s');
        try {
            $query = 'INSERT INTO ' . $this->subdomain . '_crm_trial_result (id, crm_id, from_date, to_date, timestamp, result) VALUES (null,'
                . $crmID . ',"'. $fromDate . '","' . $toDate . '","' . $current_time . '","' . str_replace('"', "'", $trial_result) . '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE)
                return true;
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    public function getTrialCampaignResultById($crmID, $fromDate, $toDate)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'SELECT result FROM ' . $this->subdomain . '_crm_trial_result WHERE crm_id=' . $crmID . ' AND from_date="' . $fromDate . '" AND to_date="' . $toDate . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                $row = mysqli_fetch_assoc($result);
                return $row['result'];
            }
            return false;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getTrialCampaignResult($fromDate, $toDate)
    {
        if (!$this->checkConnection())
            return false;

        $ret = array();
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_crm_trial_result WHERE from_date="' . $fromDate . '" AND to_date="' . $toDate . '" ORDER BY crm_id';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                while($row = mysqli_fetch_assoc($result))
                    $ret[] = array($row['crm_id'], $row['result']);
            }
        } catch (Exception $e) {
            return null;
        }
        return $ret;
    }

    public function getAllOffers()
    {
        if (!$this->checkConnection())
            return null;

        $ret = array();
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_offer';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result))
                    $ret[] = array($row['id'], $row['name'], $row['campaign_ids']);
            }

            return $ret;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getAllOffersOfAffiliates()
    {
        if (!$this->checkConnection())
            return null;

        $ret = array();
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_affiliate_offer';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result))
                    $ret[] = array($row['id'], $row['affiliate_id'], $row['offer_id']);
            }
            return $ret;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getOffersOfAffiliateID($affiliate_id)
    {
        if (!$this->checkConnection())
            return null;

        $ret = array();
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_affiliate_offer WHERE affiliate_id=' . $affiliate_id;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result))
                    $ret[] = array($row['id'], $row['affiliate_id'], $row['offer_id']);
            }
            return $ret;
        } catch (Exception $e) {
            return null;
        }
    }

    public function setOffersOfAffiliateID($affiliate_id, $offer_ids)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'DELETE FROM ' . $this->subdomain . '_affiliate_offer WHERE affiliate_id=' . $affiliate_id;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE) {
                $offer_ids = explode(',', $offer_ids);
                foreach ($offer_ids as $offer_id) {
                    $query = 'INSERT INTO ' . $this->subdomain . '_affiliate_offer (id, affiliate_id, offer_id) VALUES (null,' . $affiliate_id . ',' . $offer_id . ')';
                    $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                    if ($result !== TRUE) {
                        return false;
                    }
                }
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    public function getAllOffersWithCRMGoal()
    {
        if (!$this->checkConnection())
            return null;

        $ret = array();
        $all_offer_labels = $this->getOfferLabels();
        try {
            $query = 'SELECT po.*, pca.crm_name, pca.id as crm_id FROM ' . $this->subdomain . '_offer po LEFT JOIN ' . $this->subdomain . '_crm_account pca ON po.crm_id=pca.id';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    $label_ids = explode(',', $row['label_ids']);
                    $labels = "";
                    foreach ($label_ids as $label_id) {
                        foreach ($all_offer_labels as $offer_label) {
                            if ($label_id == $offer_label[0]) {
                                if ("" !== $labels)
                                    $labels = $labels.",";
                                $labels = $labels.$offer_label[1];
                            }
                        }
                    }
                    $ret[] = array($row['id'], $row['name'], $row['crm_name'], $row['crm_id'], $row['campaign_ids'], $row['label_ids'], $labels);
                }
            }

            return $ret;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getOffersByCrmID($crmID)
    {
        if (!$this->checkConnection())
            return null;

        $ret = array();
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_offer WHERE crm_id=' . $crmID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result))
                    $ret[] = array($row['id'], $row['name'], $row['campaign_ids']);
            }

            return $ret;
        } catch (Exception $e) {
            return null;
        }
    }

    public function addOffer($crmID, $name, $campaignIDs, $labelIDs)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'INSERT INTO ' . $this->subdomain . '_offer (id, name, crm_id, campaign_ids, label_ids) VALUES (null,"' . $name . '", ' . $crmID . ',"' . $campaignIDs . '","' . $labelIDs . '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function editOffer($offerID, $name, $campaignIDs, $labelIDs)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'UPDATE ' . $this->subdomain . '_offer SET name="' . $name . '", campaign_ids="' . $campaignIDs . '", label_ids="'. $labelIDs . '" WHERE id=' . $offerID;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteOffer($offerID)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'DELETE FROM ' . $this->subdomain . '_offer WHERE id=' . $offerID;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function getCapUpdate($fromDate, $toDate)
    {
        if (!$this->checkConnection())
            return null;

        $ret = array();
        try {
            $query = 'SELECT
                          pag.*, pa.name as affiliate_name, pa.afid,
                          po.name as offer_name, po.crm_id, po.crm_name, po.sales_goal, po.campaign_ids, po.label_ids
                      FROM
                          primary_affiliate_goal pag
                      LEFT JOIN primary_affiliate pa ON pag.affiliate_id = pa.id
                      LEFT JOIN (SELECT po.*, pca.crm_name, pca.sales_goal FROM primary_offer po LEFT JOIN primary_crm_account pca ON po.crm_id=pca.id) po ON pag.offer_id = po.id
                      WHERE
                          from_date = "' . $fromDate . '" AND to_date = "' . $toDate . '"
                      ORDER BY 2, 3';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result))
                    $ret[] = array(
                        $row['id'], $row['affiliate_id'], $row['offer_id'], $row['goal'],
                        $row['affiliate_name'], $row['afid'], $row['offer_name'],
                        $row['crm_id'], $row['crm_name'], $row['sales_goal'], $row['campaign_ids'], $row['label_ids']);
            }
            return $ret;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getAllAffiliations()
    {
        if (!$this->checkConnection())
            return null;

        $ret = array();
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_affiliate';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result))
                    $ret[] = array($row['id'], $row['name'], $row['afid']);
            }
            return $ret;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getAffiliationGoal($fromDate, $toDate)
    {
        if (!$this->checkConnection())
            return null;

        $ret = array();
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_affiliate_goal WHERE from_date="' . $fromDate . '" and to_date="' . $toDate . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result))
                    $ret[] = array($row['id'], $row['affiliate_id'], $row['offer_id'], $row['goal']);
            }
            return $ret;
        } catch (Exception $e) {
            return null;
        }
    }

    public function addAffiliation($name, $afid)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'INSERT INTO ' . $this->subdomain . '_affiliate (id, name, afid) VALUES (null,"' . $name . '", "' . $afid . '")';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function editAffiliation($affiliate_id, $name, $afid)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'UPDATE ' . $this->subdomain . '_affiliate SET name="' . $name . '", afid="' . $afid . '" WHERE id=' . $affiliate_id;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteAffiliation($affiliate_id)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'DELETE FROM ' . $this->subdomain . '_affiliate WHERE id=' . $affiliate_id;
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function addAffiliationGoal($affiliate_id, $offer_id, $goal, $from_date, $to_date)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'INSERT INTO ' . $this->subdomain . '_affiliate_goal VALUES (null,' . $affiliate_id . ', ' . $offer_id . ', "' . $from_date . '", "' . $to_date . '", '. $goal . ')';

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function editAffiliationGoal($affiliate_goal_id, $goal)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'UPDATE ' . $this->subdomain . '_affiliate_goal SET goal=' . $goal . ' WHERE id=' . $affiliate_goal_id;

            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            if ($result === TRUE) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function editAffiliationGoals($affiliate_id, $offer_ids, $offer_goals, $from_date, $to_date)
    {
        if (!$this->checkConnection())
            return false;

        foreach ($offer_ids as $index=>$offer_id) {
            try {
                $query = 'SELECT id FROM ' . $this->subdomain . '_affiliate_goal WHERE affiliate_id=' . $affiliate_id . ' AND offer_id=' . $offer_id . ' AND from_date="' . $from_date . '" AND to_date="' . $to_date . '"';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

                $count = mysqli_num_rows($result);
                if ($count > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $id = $row['id'];

                    $query = 'UPDATE ' . $this->subdomain . '_affiliate_goal SET goal=' . $offer_goals[$index] . ' WHERE id=' . $id;
                    $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                }
                else {
                    $query = 'INSERT INTO ' . $this->subdomain . '_affiliate_goal VALUES (null,' . $affiliate_id . ', ' . $offer_id . ', "' . $from_date . '", "' . $to_date . '", '. $offer_goals[$index] . ')';
                    $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                }
            } catch (Exception $e) {
                return false;
            }
        }
        return true;
    }

    public function getDashboardRefresh($date_type)
    {
        if (!$this->checkConnection())
            return false;

        $ret = "0";
        try {
            $query = 'SELECT refresh FROM ' . $this->subdomain . '_dashboard_refresh WHERE date_type="' . $date_type . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                $row = mysqli_fetch_assoc($result);
                $ret = $row['refresh'];

                if ("1" == $ret) {
                    $query = 'UPDATE ' . $this->subdomain . '_dashboard_refresh SET refresh=0 WHERE date_type="' . $date_type . '"';
                    $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
                }
            }
            else {
                $query = 'INSERT INTO ' . $this->subdomain . '_dashboard_refresh VALUES (null,"' . $date_type . '", 0)';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            }
        } catch (Exception $e) {
            return false;
        }
        return $ret;
    }

    public function updateDashboardRefresh($date_type)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'SELECT refresh FROM ' . $this->subdomain . '_dashboard_refresh WHERE date_type="' . $date_type . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                $query = 'UPDATE ' . $this->subdomain . '_dashboard_refresh SET refresh=1 WHERE date_type="' . $date_type . '"';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            }
            else {
                $query = 'INSERT INTO ' . $this->subdomain . '_dashboard_refresh VALUES (null,"' . $date_type . '", 1)';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function getOfferLabels()
    {
        if (!$this->checkConnection())
            return null;

        $ret = array();
        try {
            $query = 'SELECT * FROM ' . $this->subdomain . '_offer_label';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result))
                    $ret[] = array($row['id'], $row['label_name']);
            }
            return $ret;
        } catch (Exception $e) {
            return null;
        }
    }

    public function addCapUpdateResult($crmID, $fromDate, $toDate, $ret)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'SELECT id FROM ' . $this->subdomain . '_cap_update_result WHERE crm_id=' . $crmID . ' and from_date="' . $fromDate . '" and to_date="' . $toDate . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $count = mysqli_num_rows($result);
            $current_time = date('Y-m-d H:i:s');
            if ($count > 0) {
                $query = 'UPDATE ' . $this->subdomain . '_cap_update_result SET timestamp="'. $current_time . '", result="' . str_replace('"', "'", $ret) . '" WHERE crm_id=' . $crmID . ' and from_date="' . $fromDate . '" and to_date="' . $toDate . '"';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            }
            else {
                $query = 'INSERT INTO ' . $this->subdomain . '_cap_update_result VALUES (null,' . $crmID . ',"' . $fromDate . '","' . $toDate . '","' . $current_time . '","' . str_replace('"', "'", $ret) . '")';
                $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function getCapUpdateResult($crmID, $fromDate, $toDate)
    {
        if (!$this->checkConnection())
            return false;

        try {
            $query = 'SELECT result, timestamp FROM ' . $this->subdomain . '_cap_update_result WHERE crm_id=' . $crmID . ' AND from_date="' . $fromDate . '" AND to_date="' . $toDate . '"';
            $result = mysqli_query($this->conn, $query) or die(mysqli_error($this->conn));

            $crm_count = mysqli_num_rows($result);
            if ($crm_count > 0) {
                $row = mysqli_fetch_assoc($result);
                return array($row['result'], $row['timestamp']);
            }
            return false;
        } catch (Exception $e) {
            return null;
        }
    }
}
