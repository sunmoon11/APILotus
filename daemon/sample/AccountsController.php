<?php

namespace App\Controller;

use App\Controller\AppController;
use App\Lib\MerApi;
use Cake\Event\Event;
use Cake\I18n\Time;
use App\Model\Entity\SalesRequest;
use Cake\Core\Configure;

/**
 * Accounts Controller
 *
 * @property \App\Model\Table\AccountsTable $Accounts
 */
class AccountsController extends AppController{

    public function isAuthorized($user) {
		$this->printDebug(__FUNCTION__.": ".__LINE__.": username=".$user['username']);
        //if(in_array($this->request->action, ['index'])) {
            if(isset($user['level']) && $user['level'] != 5) {
				$this->printDebug(__FUNCTION__.": ".__LINE__.": username=".$user['username'].", level=".$user['level']);
                return true;
            } else {
                return false;
            }
        //}

        $certificate = parent::isCertificate($user);
		$this->printDebug(__FUNCTION__.": certificate=".$certificate);
        if(in_array($this->request->action, ['setting'])) {
            if($certificate) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index(){
		$siteMap = Configure::read('sitemap');
		if ($siteMap & 0x01)
			$siteId = 1;
		else if ($siteMap & 0x02)
			$siteId = 2;
		else
			$siteId = 3;
		$userInfo = $this->loadModel('Users')->get($this->Auth->user('id'));
		$maxAccountNum = $userInfo->accountnum;
		$curAccountNum = $this->Accounts->find('all', [
			'conditions' => [
				'user_id =' => $this->Auth->user('id'),
				'site_id =' => $siteId
			]
		])->count();

		$notification = array();
		$notification = $this->loadModel('Notifications')->find('all', [
            'order' => ['created' => 'DESC']
        ])->first();
		if (is_null($notification)) {
		    $tm = Time::now();
		    $notification['title'] = 'ツール事務局からのお知らせはありません。';
		    $notification['notification'] = '';
		    $notification['created'] = $tm->i18nFormat('yyyy-MM-dd HH:mm:ss');
        } else {
            $notification['title'] = $notification->title;
            $notification['notification'] = $notification->notification;
            $notification['created'] = $notification->created->i18nFormat('yyyy-MM-dd HH:mm:ss');
        }

		$this->set('notification', $notification);
		$this->set('maxAccountNum', $maxAccountNum);
		$this->set('curAccountNum', $curAccountNum);
		$this->set('siteMap', $siteMap);
    }

    public function schedule() {
        if($this->request->is('ajax')) {
            $publish_rule_type = $_POST['publish_rule_type'];
            $publish_start = $_POST['publish_hour'];
            $publish_minute = $_POST['publish_minute'];
            $publish_interval = $_POST['publish_interval'];
            $day_of_week = $_POST['day_of_week'];
            $interval_type = $_POST['interval_type'];
            $republish_interval_min = $_POST['republish_interval_min'];
            $republish_interval_max = $_POST['republish_interval_max'];
            $product_count = $_POST['product_count'];
            $num_comments = $_POST['num_comments'];
            $num_likes = $_POST['num_likes'];
            $switch_random_order = $_POST['switch_random_order'];

            $publish_start = ($publish_start == "")?0:$publish_start;
            $publish_interval = ($publish_interval == "")?1:$publish_interval;
            $republish_interval_min = ($republish_interval_min == "")?31:$republish_interval_min;
            $republish_interval_max = ($republish_interval_max == "")?50:$republish_interval_max;
            $product_count = ($product_count == "")?5:$product_count;
            $switch_random_order = ($switch_random_order == "1")?1:0;

            $publish_hourly_count = '';
            for ($h = 0; $h < 24; $h++) {
                $c = '0';
                if ('' != $_POST['h'.$h]) {
                    $c = $_POST['h'.$h];
                }

                $publish_hourly_count .= $c;
            }

            //check crontab status ....
            $result = "success";
            $msg = "OK";
            $bProcess = false;

            $curhours = date("H");
            $curdays = date("d");
            $curyears = date("Y");
            $curmonths = date("m");

			$accountId = $this->request->session()->read('accountId');
			$siteId = $this->request->session()->read('siteId');
            exec("ps -ef",$output);

            foreach($output as $line){
                $processExist = true;
                if (1 == $siteId) {
                    $processExist = strpos($line, "cake.php mercari_product_auto_publish " . $accountId . "u");
                } else if (2 == $siteId) {
                    $processExist = strpos($line, "cake.php furiru_product_auto_publish " . $accountId . "f");
                } else if (3 == $siteId) {
                    $processExist = strpos($line, "cake.php rakuma_product_auto_publish " . $accountId . "r");
                }

                if (false !== $processExist) {
                    $bProcess = true;
                }
            }

            if ($bProcess)
            {
                $publishSetting = $this->loadModel('PublishSettings')->find('all', [
                    'conditions' => ['account_id =' => $accountId]
                ])->first();
                if ($publishSetting)
                {
                    $startTime = $publishSetting->publish_hour;
                    $interval = $publishSetting->republish_interval_max;

                    $product_count_prev = count($this->loadModel('Accounts')->find('all', [
                            'conditions' => [
                                'account_id =' => $accountId,
                                'deleted =' => 0
                            ]
                        ])
                    );

                    $periodTime = ($interval * $product_count_prev / 60) + 1;

                    $endTime = $startTime + $periodTime;
                    $realPublishTime = ( $curhours < $publish_start && $publish_interval == 1)?$publish_start:$publish_start + (24 * $publish_interval);
                    if ($endTime >= $realPublishTime)
                    {
                        $days = intval($endTime / 24);
                        $days = ($days == 0)?$curdays."日":($curdays + $days)."日";
                        $endTime = $endTime % 24;

                        $result = "error";
                        $msg = "現在商品出品が進行されるので、出品開始時間の設定を行うことができません。<br> 商品出品は&nbsp;".$curyears."年".$curmonths."月".$days.$endTime."時&nbsp;に完了します。";


                        $publishSetting->product_count = $product_count;
                        $publishSetting->switch_random_order = $switch_random_order;
                        $this->loadModel('PublishSettings')->save($publishSetting);
                    }
                }
            }

            if ($republish_interval_min > $republish_interval_max)
            {
                $result = "error";
                $msg = "現在商品出品が進行されるので";
            }

            if ($result != "error")
            {
                $publishSetting = $this->loadModel('PublishSettings')->find('all', [
                    'conditions' => ['account_id =' => $accountId]
                ])->first();

                if (is_null($publishSetting)) {
                    $publishSetting = $this->loadModel('PublishSettings')->newEntity();
                    $publishSetting->account_id = $accountId;
                }

                $publishSetting->publish_rule_type = $publish_rule_type;
                $publishSetting->publish_hourly_count = $publish_hourly_count;
                $publishSetting->publish_hour = $publish_start;
                $publishSetting->publish_minute = $publish_minute;
                $publishSetting->publish_interval = $publish_interval;
                $publishSetting->day_of_week = $day_of_week;
                $publishSetting->interval_type = $interval_type;
                $publishSetting->republish_interval_min = $republish_interval_min;
                $publishSetting->republish_interval_max = $republish_interval_max;
                $publishSetting->product_count = $product_count;
                $publishSetting->num_comments = $num_comments;
                $publishSetting->num_likes = $num_likes;
                $publishSetting->switch_random_order = $switch_random_order;

                $this->loadModel('PublishSettings')->save($publishSetting);

                $this->setAllCrontab();
            }

            $this->set(compact('result','msg'));
            $this->set('_serialize', ['result','msg']);
        } else {
			$accountId = $this->request->session()->read('accountId');
			$this->printDebug(basename(__FILE__).":".__LINE__.": account_id=".$accountId);
            $publishSetting = $this->loadModel('PublishSettings')->find('all', [
                'conditions' => ['account_id =' => $accountId]
            ])->first();

            $this->set(compact('publishSetting'));
            $this->set('_serialize', ['publishSetting']);
        }
    }

    // Responsing ajax
    public function getListAccounts() {
		$siteMap = Configure::read('sitemap');
		$siteId1 = $siteMap & 0x01;
		$siteId2 = $siteMap & 0x02;
		$siteId3 = ($siteMap & 0x04) ? 3 : 0;
		$this->printDebug(__FUNCTION__.":".__LINE__.": user_id=".$this->Auth->user('id'));
        if($this->request->is('ajax')) {
            $allAccounts = $this->Accounts->find('all', [
                'conditions' => [
					'user_id =' => $this->Auth->user('id'),
					'or' => [
						['site_id =' => $siteId1],
						['site_id =' => $siteId2],
						['site_id =' => $siteId3],
					]
				],
				'order' => ['site_id' => 'ASC']
            ]);
            $result = "success";
            $msg = $allAccounts;

            $this->set(compact('result','msg'));
            $this->set('_serialize', ['result','msg']);
        } else {
//            $this->redirect(['action' => 'certification']);
        }
    }

	public function switchAccount($accountId) {
		$accountInfo = $this->Accounts->get($accountId);
		$session = $this->request->session();
		$oldSiteId = $session->read('siteId');
		$this->printDebug(__FUNCTION__.": ".__LINE__.": account_id=".$session->read('accountId').",site_id=".$session->read('siteId'));
		$session->write('accountId', $accountId);
		$session->write('siteId', $accountInfo->site_id);
		$this->printDebug(__FUNCTION__.": ".__LINE__.": account_id=".$session->read('accountId').",site_id=".$session->read('siteId'));

		$referer = $this->referer();
		$this->printDebug(__FUNCTION__.": ".__LINE__.": refer=".$referer);
		$search = array("mercari", "furiru", "rakuma");
		$replace = array("1" => "mercari", "2" => "furiru", "3" => "rakuma");
		$referer = str_replace($search, $replace[$accountInfo->site_id], $referer);
		$this->printDebug(__FUNCTION__.": ".__LINE__.": refer=".$referer);
		$this->redirect($referer);
		/*
		if ($oldSiteId != $accountInfo->site_id)
			$this->redirect('/accounts');
		else
			$this->redirect($this->referer());
		*/
	}

	private function mercariAccountLogin($accountInfo) {
		$merApi = new MerApi();
		$response = $merApi->getAccessToken($accountInfo->uuid);
//		$response = cr_get_access_token();
		if( $response ) {
			$this->printDebug(__FUNCTION__.":".__LINE__.":");
			$responseData = json_decode($response);
			if($responseData->result == "OK"){
				$token = $responseData->data->access_token;
				$tokenExpireDate = date("Y-m-d H:i:s", $responseData->data->expiration_date);

				// update account info
				//$accountInfo->keyword = $keyword;
				$accountInfo->token = $token;
				$accountInfo->expire_date = $tokenExpireDate;
				$this->Accounts->save($accountInfo);

				$response = $merApi->getGlobalToken($token);
//				$response = cr_get_global_token($token);
                if ($response){
				    $globalToken = '';
                    $responseData = json_decode($response);
                    if ($responseData->result == "OK") {
                        $globalToken = $responseData->data->global_access_token;
                    } else {
                        $result = "warning";
                        $msg = $responseData->errors[0]->message;
                    }

                    $this->printDebug(__FUNCTION__.":".__LINE__.":");
                    $response = $merApi->login($token, $globalToken, $accountInfo->keyword, $accountInfo->password);
//                    $response = cr_login($token, $globalToken, $accountInfo->keyword, $accountInfo->password);
                    if ($response) {
                        $this->printDebug(__FUNCTION__.":".__LINE__.": response=".$response);
                        $responseData = json_decode($response);
                        if ($responseData->result == "OK") {
                            $this->printDebug(__FUNCTION__.":".__LINE__.":");
                            $accountInfo->sellerid = $responseData->data->id;
                            $accountInfo->sellername = $responseData->data->name;
                            $accountInfo->photourl = $responseData->data->photo_thumbnail_url;
                            $accountInfo->status = 1;
                            $this->Accounts->save($accountInfo);

//                            $this->setAllCrontab();

                            $result = "success";
                            $msg = "ok";
                        } else {
                            $result = "warning";
                            $msg = $responseData->errors[0]->message;
                        }
                    } else {
                        $result = "warning";
                        $msg = $responseData->errors[0]->message;
                    }
                } else {
                    $result = "warning";
                    $msg = $responseData->errors[0]->message;
                }
			} else {
				$result = "warning";
				$msg = $responseData->errors[0]->message;
			}
		} else {
			$result = "warning";
			$msg = "Cannot login mercari.com";
		}

		$resultInfo['result'] = $result;
		$resultInfo['msg'] = $msg;

		return $resultInfo;
	}

	private function furiruAccountLogin($accountInfo) {
		$this->printDebug(__FUNCTION__.":".__LINE__.":");
		$common = new FuriruCommon();
		$responseData = $common->login($accountInfo->keyword, $accountInfo->password);
		if( $responseData->result == "OK" ) {
			$this->printDebug(__FUNCTION__.":".__LINE__.":");
			//$responseData = json_decode($response);
			//if($responseData->token){
				$token = $responseData->token;
				$cookie = $responseData->cookie;

				// update account info
				$accountInfo->token = $token;
				$accountInfo->cookie = $cookie;
				$this->Accounts->save($accountInfo);

				$this->printDebug(__FUNCTION__.":".__LINE__.":");
				$responseData = $common->getUserProfile($token, $cookie);
				if ($responseData->result == "success") {
					//$this->printDebug(__FUNCTION__.":".__LINE__.": response=".$response);
					//$responseData = json_decode($response);
					if ($responseData->data->user) {
						$this->printDebug(__FUNCTION__.":".__LINE__.":");
						$accountInfo->sellerid = $responseData->data->user->id;
						$accountInfo->sellername = $responseData->data->user->screen_name;
						$accountInfo->photourl = $responseData->data->user->profile_img_url;
						$accountInfo->status = 1;
						$this->Accounts->save($accountInfo);

//						$this->setAllCrontab();

						$result = "success";
						$msg = "ok";
					} else {
						$result = "warning";
						$msg = $responseData->errors[0]->message;
					}
				} else {
					$result = "warning";
					$msg = $responseData->errors[0]->message;
				}

				$responseData = $common->getUserSession1();
				if (false == $responseData['result']) {
				    $result = "warning";
				    $msg = "Cannot get the user session";
                } else {
				    $preUserSession = $responseData['cookie'];
				    $responseData = $common->getUserSession2($preUserSession);
				    if (false == $responseData['result']) {
                        $result = "warning";
                        $msg = "Cannot get the user session";
                    } else {
				        $userSession = $responseData['cookie'];
                        $expires= $responseData['expires']; // TODO: Convert a string datetime to MySQL's datetime

                        $timestamp = strtotime($expires);

				        $accountInfo->cookie_user = $userSession;
                        $accountInfo->expire_date = date("Y-m-d H:i:s", $timestamp);
				        $this->Accounts->save($accountInfo);

                        $result = "success";
                        $msg = "ok";
                    }
                }
			//} else {
			//	$result = "warning";
			//	$msg = $responseData->errors[0]->message;
			//}
		} else {
			$result = "warning";
			$msg = $responseData->message;
		}

		$resultInfo['result'] = $result;
		$resultInfo['msg'] = $msg;

		return $resultInfo;
	}

	private function rakumaAccountLogin($accountInfo) {
		$this->printDebug(__FUNCTION__.":".__LINE__.":");
		$common = new RakumaCommon();
		$responseData = $common->userLogin($accountInfo->keyword, $accountInfo->password);
		if( $responseData->result == "success" ) {
			$this->printDebug(__FUNCTION__.":".__LINE__.":");
			$token = $responseData->data->access_token;
			$tokenExpireDate = "2017-12-31 23:59:59";

			// update account info
			$accountInfo->token = $token;
			$accountInfo->cookie = "";
			$accountInfo->expire_date = $tokenExpireDate;
			$this->Accounts->save($accountInfo);

			$this->printDebug(__FUNCTION__.":".__LINE__.":");
			$responseData = $common->getUserProfile($token);
			if ($responseData->result == "success") {
				//$this->printDebug(__FUNCTION__.":".__LINE__.": response=".$response);
				//$responseData = json_decode($response);
				$this->printDebug(__FUNCTION__.":".__LINE__.":");
				$accountInfo->sellerid = $responseData->data->userInfo->userId;
				$accountInfo->sellername = $responseData->data->userInfo->userNickname;
				if (!empty($responseData->data->userInfo->profileImage))
					$accountInfo->photourl = "http://rakuma.r10s.jp/d/strg/ctrl/25/".$responseData->data->userInfo->profileImage;
				else
					$accountInfo->photourl = "img/no_avatar.jpg";
				$accountInfo->status = 1;
				$this->Accounts->save($accountInfo);

//				$this->setAllCrontab();

				$result = "success";
				$msg = "ok";
			} else {
				$result = "warning";
				$msg = "login error";//$responseData->errors[0]->message;
			}
		} else {
			$result = "warning";
			$msg = "login error";//$responseData->message;
		}

		$resultInfo['result'] = $result;
		$resultInfo['msg'] = $msg;

		return $resultInfo;
	}

	public function updateAccount() {
		$this->printDebug(__FUNCTION__.":".__LINE__.":");
		if($this->request->is('ajax')) {
			$this->printDebug(__FUNCTION__.":".__LINE__.":");
			$keyword = $_POST['keyword'];
			$password = $_POST['password'];
			$action = $_POST['action'];
			$accountId = $_POST['account_id'];
			$siteId = $_POST['site_id'];
			$this->printDebug(__FUNCTION__.":".__LINE__.":keyword=".$keyword.",password=".$password);
			$this->printDebug(__FUNCTION__.":".__LINE__.":accountId=".$accountId.",action=".$action);
			$this->printDebug(__FUNCTION__.":".__LINE__.":siteId=".$siteId);

			/*
			$result = "success";
			$msg = "OK";
			$this->set(compact('result','msg'));
			$this->set('_serialize', ['result','msg']);
			return;
			*/

			$accountInfo = $this->loadModel('Accounts')->find('all', [
				'conditions' => [
					'keyword =' => $keyword,
					'site_id =' => $siteId,
				]
			])->first();

			if(!is_null($accountInfo)) {
				$result = "error";
				$msg = '同じアカウントが既に登録されました。';

				$this->set(compact('result','msg'));
				$this->set('_serialize', ['result','msg']);
				return;
			}

			if ($action == 0) { // create
				$uuid = 0;
				if ($siteId == 1) {
					$merApi = new MerApi();
                    $uuid = $merApi->generate_uuid();
//					$uuid = '0BE0193B6F274ECF898BEF542DC57186'; // workaround
					$this->printDebug(__FUNCTION__.":".__LINE__.": uuid=".$uuid);
				}
				$accountInfo = $this->Accounts->newEntity();
				$accountInfo = $this->Accounts->patchEntity($accountInfo, [
					'user_id'  => $this->Auth->user('id'),
					'keyword'  => $keyword,
					'password'  => $password,
					'site_id' => $siteId,
					'uuid' => $uuid,
                    'payment_message_temp' => "",
                    'wait_shipping_message_temp' => "",
                    'shipping_message_temp' => ""
                ]);
			} else { // 1: modify, 2: replace, 3: translate
				$accountInfo = $this->Accounts->get($accountId);
				if (is_null($accountInfo)) {
					$result = "error";
					$msg = 'User not found';
					$this->set(compact('result','msg'));
					$this->set('_serialize', ['result','msg']);
					return;
				}
				$accountInfo->keyword = $keyword;
				$accountInfo->password = $password;
			}

            $resultInfo = array();

			$this->printDebug(__FUNCTION__.":".__LINE__.":");
			if ($this->Accounts->save($accountInfo)) {
				if ($action == 0) { // create
					$this->printDebug(__FUNCTION__.":".__LINE__.": account_id=".$accountInfo->id);
					// create publish setting
					$publishSetting = $this->loadModel('PublishSettings')->newEntity();
					$this->printDebug(__FUNCTION__.":".__LINE__.": account_id=".$accountInfo->id);
					$publishSetting->account_id = $accountInfo->id;
					$publishSetting->publish_time = 12;
                    $publishSetting->publish_minute = 17;
					$publishSetting->publish_interval = 1;
					$publishSetting->republish_interval_min = 31;
					$publishSetting->republish_interval_max = 60;
					$publishSetting->product_count = 5;
					$publishSetting->switch_random_order = 0;

					$this->loadModel('PublishSettings')->save($publishSetting);
					$this->printDebug(__FUNCTION__.":".__LINE__.": account_id=".$accountInfo->id);

					if ($siteId == 1) {
						$resultInfo = $this->mercariAccountLogin($accountInfo);
					} else if ($siteId == 2) {
						$resultInfo = $this->furiruAccountLogin($accountInfo);
					} else if ($siteId == 3) {
						$resultInfo = $this->rakumaAccountLogin($accountInfo);
					}

					$result = $resultInfo['result'];
					$msg = $resultInfo['msg'];
				} else if ($action == 3) { // translate
					// TODO
				} else { // modify, replace

					if ($siteId == 1) {
						$resultInfo = $this->mercariAccountLogin($accountInfo);
					} else if ($siteId == 2) {
						$resultInfo = $this->furiruAccountLogin($accountInfo);
					} else if ($siteId == 3) {
						$resultInfo = $this->rakumaAccountLogin($accountInfo);
					}

					$result = $resultInfo['result'];
					$msg = $resultInfo['msg'];
				}
			} else {
				$result = "error";
				$msg = '[CREATE] The user could not be saved. Please, try again.';
			}

			$this->set(compact('result','msg'));
			$this->set('_serialize', ['result','msg']);
		} else {
//			return $this->redirect(['action' => 'certification']);
		}
	}

	public function getToken() {
		$this->printDebug(__FUNCTION__.":".__LINE__.":");
		if($this->request->is('ajax')) {
			$this->printDebug(__FUNCTION__.":".__LINE__.":");
			$accountId = $_POST['account_id'];
			$this->printDebug(__FUNCTION__.":".__LINE__.":accountId=".$accountId);

			$accountInfo = $this->loadModel('Accounts')->get($accountId);

			if(is_null($accountInfo)) {
				$result = "error";
				$msg = 'そのアカウントはありません。';

				$this->set(compact('result','msg'));
				$this->set('_serialize', ['result','msg']);
				return;
			}

			if ($accountInfo->site_id == 1) {
				$resultInfo = $this->mercariAccountLogin($accountInfo);
			} else if ($accountInfo->site_id == 2) {
				$resultInfo = $this->furiruAccountLogin($accountInfo);
			} else if ($accountInfo->site_id == 3) {
				$resultInfo = $this->rakumaAccountLogin($accountInfo);
			}

			$result = $resultInfo['result'];
			$msg = $resultInfo['msg'];

			$this->set(compact('result','msg'));
			$this->set('_serialize', ['result','msg']);
		} else {
			return $this->redirect(['action' => 'certification']);
		}
	}

    public function deleteAccount()
    {
        if($this->request->is('ajax')) {
			$this->printDebug(__FUNCTION__.":".__LINE__.": account_id=".$_POST['account_id']);
            $accountInfo = $this->Accounts->get($_POST['account_id']);
			$curAccountId = $this->request->session()->read('accountId');

            if(!is_null($accountInfo)) {
				if ($curAccountId = $accountInfo->id)
					$curAccountId = $this->request->session()->delete('accountId');
                $this->Accounts->delete($accountInfo);
                $this->setAllCrontab();

				$this->printDebug(__FUNCTION__.":".__LINE__.":");
                $result = "success";
                $msg = 'Deleted';
                $this->set(compact('result','msg'));
                $this->set('_serialize', ['result','msg']);
            } else {
                $result = "error";
                $msg = 'User not found';
                $this->set(compact('result','msg'));
                $this->set('_serialize', ['result','msg']);
            }
        } else {
            return $this->redirect(['action' => 'certification']);
        }
    }

    public function tempSetting(){
        $this->loadModel('Accounts');
        $msg='';
		$session = $this->request->session();
		$accountId = $session->read('accountId');
        if($this->request->is('post')) {
			$account_info = $this->Accounts->get($accountId);
            $account_info['payment_message_temp'] = $_POST['paymentMessageTemp'];
            $account_info['wait_shipping_message_temp'] = $_POST['waitShippingMessageTemp'];
            $account_info['shipping_message_temp'] = $_POST['shippingMessageTemp'];
            $account_info['auto_message_reply'] = ($_POST['auto_message_reply'] == "1") ? 1 : 0;

            if($this->Accounts->save($account_info)){
                $msg='保存しました。';
            }else{
                $msg='保存できませんでした。';
            }

            $this->set(compact('msg'));
            $this->set('_serialize', ['msg']);
        }
        $account_info = $this->loadModel('Accounts')->get($accountId);
        $this->set('account_info', $account_info);
    }

	public function registerBank() {
		$common = new Common();
		$accountId = $this->request->session()->read('accountId');
		$accountInfo = $this->loadModel('Accounts')->get($accountId);
		//$this->printDebug(__FUNCTION__.":".__LINE__.": accountId=".$accountId);
        if ($this->request->is('ajax')) {
			$bankInfo = $this->loadModel('BankInfos')->find('all', [
				'conditions' => ['account_id =' => $accountId]
			])->first();

			$bankInfo->bank_id = $_POST['bank_id'];
			$bankInfo->kind_id = $_POST['kind_id'];
			$bankInfo->branch_id = $_POST['branch_id'];
			$bankInfo->account_number = $_POST['account_number'];
			$bankInfo->family_name = $_POST['family_name'];
			$bankInfo->first_name = $_POST['first_name'];
			$bankInfo->birth_day  = $_POST['birth_y'];
			$bankInfo->birth_day .= (strlen($_POST['birth_m']) == 1)?("0".$_POST['birth_m']):($_POST['birth_m']);
			$bankInfo->birth_day .= (strlen($_POST['birth_d']) == 1)?("0".$_POST['birth_d']):($_POST['birth_d']);

			$response = $common->getDeliverAddress($accountInfo->token);
			if ($response->result == "OK" && $response->data) {
				//$this->printDebug(__FUNCTION__.":".__LINE__.": bank_id=".$bankInfo->bank_id);
				$bankInfo->address_id = $response->data[0]->id;

				$this->loadModel('BankInfos')->save($bankInfo);
				//$this->printDebug(__FUNCTION__.":".__LINE__.": bank_id=".$bankInfo->bank_id);

				$response = $common->saveBankAccount($accountInfo->token, $bankInfo->bank_id, $bankInfo->kind_id, $bankInfo->branch_id, $bankInfo->account_number, $bankInfo->family_name, $bankInfo->first_name, $bankInfo->address_id, $bankInfo->birth_day);
				if ($response->result == "OK") {
					$result = "success";
					$msg = "OK";
				} else if ($response->result == "error") {
					$result = "error";
					$msg = $response->errors[0]->message;
				} else {
					$result = "fail";
					$msg = "Network Error";
				}
			} else if ($response->result == "error") {
				$result = "error";
				$msg = $response->errors[0]->message;
			} else if ($response->result == "fail") {
				$result = "fail";
				$msg = "Network Error";
			}

			$this->set(compact('result','msg'));
			$this->set('_serialize', ['result','msg']);
        } else if($this->request->is('get')) {
            $bankNames = $this->loadModel('BankNames')->find('all');//, ['recursive' => -1]);

			//$response = $common->getDeliverAddress($accountInfo->token);

			$response = $common->getBankAccounts($accountInfo->token);
			/*
			$bankInfo = $this->loadModel('BankInfos')->find('all', [
                'conditions' => ['account_id =' => $accountId],
				'recursive' => -1
            ])->first();
			*/

			$bankInfo = array();
			$bankInfo['bank_id'] = "";
			$bankInfo['kind_id'] = "";
			$bankInfo['branch_id'] = "";
			$bankInfo['account_number'] = "";
			$bankInfo['family_name'] = "";
			$bankInfo['first_name'] = "";
			$bankInfo['name'] = "";
			$bankInfo['kana'] = "";
			$bankInfo['code'] = "";
			if ($response->result == "OK") {
				//$this->printDebug(__FUNCTION__.": ".__LINE__.": result=OK");
				$data = $response->data;
				$bankInfo['bank_id'] = $data->bank_account->bank_id;
				$bankInfo['kind_id'] = $data->bank_account->kind;
				$bankInfo['branch_id'] = $data->bank_account->branch_id;
				$bankInfo['account_number'] = $data->bank_account->account_number;
				$bankInfo['family_name'] = $data->bank_account->family_name;
				$bankInfo['first_name'] = $data->bank_account->first_name;
				$bankInfo['name'] = $data->bank->name;
				$bankInfo['kana'] = $data->bank->kana;
				$bankInfo['code'] = $data->bank->code;
				$regstatus = "yes";
			} else if ($response->result == "error") {
				//$this->printDebug(__FUNCTION__.": ".__LINE__.": result=error");
				$result = "error";
				$msg = $response->errors[0]->message;
				$regstatus = "no";
			} else if ($response->result == "fail") {
				//$this->printDebug(__FUNCTION__.": ".__LINE__.": result=fail");
				$result = "fail";
				$msg = "Network Error";
				$regstatus = "no";
			}

			$curyear = date("Y");

			$this->set('bankNames', $bankNames);
			$this->set('bankInfo', $bankInfo);
			$this->set('curyear', $curyear);
			$this->set('regstatus', $regstatus);

			$userInfo = $this->Users->get($this->Auth->user('id'));
			if (empty($userInfo->bankpass)) {
				$this->set('status', 'auth');
			} else {
				$this->set('status', 'noauth');
			}
		}
	}

	public function furiruWithdrawalRequest()
    {
        $common = new FuriruCommon();

        $accountId = $this->request->session()->read('accountId');
        $accountInfo = $this->loadModel('Accounts')->get($accountId);

        $reqInfo = array();

        if ($this->request->is('ajax')) {
            $transfer = $_POST['transfer'];

            $response = $common->getBalanceWithdrawalAuthToken($accountInfo->token, $accountInfo->cookie, $accountInfo->cookie_user);
            if (true == $response->result) {
                $csrfToken = $response->csrf_token;
                $cookieWeb = $response->cookie_web;
                $response = $common->getBalanceWithdrawalConfirm($csrfToken, $cookieWeb, $accountInfo->cookie_user, $transfer);
                if (true == $response->result) {
                    $result = "success";
                    $msg = "OK";
                } else {
                    $result = "error";
                    $msg = "Cannot request the withdrawal";
                }
            } else {
                $result = "error";
                $msg = "Cannot request the withdrawal";
            }

            $this->set(compact('result','msg'));
            $this->set('_serialize', ['result','msg']);
        } else if ($this->request->is('get')) {
            $reqInfo = array();
            $response = $common->getBalance($accountInfo->token, $accountInfo->cookie);
            if (true == $response->result) {
                $reqInfo['balance'] = $response->balance;
                $reqInfo['point'] = $response->point;
                $reqInfo['withdrawal'] = $response->withdrawal;
                $reqInfo['bank'] = $response->bank;
                $reqInfo['idverify_pending'] = $response->idverify_pending;
            } else {
                $reqInfo['balance'] = '';
                $reqInfo['point'] = '';
                $reqInfo['withdrawal'] = '';
                $reqInfo['bank'] = '';
                $reqInfo['idverify_pending'] = '';
            }

            $response = $common->getBankInfo($accountInfo->token, $accountInfo->cookie);
            if (true == $response->result && null != $response->bank) {
                $reqInfo['bank_id'] = $response->bank->id;
                $reqInfo['bank_code_id'] = $response->bank->bank_code_id;
                $reqInfo['branch_code'] = $response->bank->branch_code;
                $reqInfo['deposit_type'] = $response->bank->deposit_type;
                $reqInfo['account_number'] = $response->bank->account_number;
                $reqInfo['last_name'] = $response->bank->last_name;
                $reqInfo['first_name'] = $response->bank->first_name;
                $reqInfo['name'] = $response->bank->name;
                $reqInfo['code'] = $response->bank->code;
                $reqInfo['branch_name'] = $response->bank->branch_name;
            } else {
                $reqInfo['bank_id'] = '';
                $reqInfo['bank_code_id'] = '';
                $reqInfo['deposit_type'] = '';
                $reqInfo['account_number'] = '';
                $reqInfo['last_name'] = '';
                $reqInfo['first_name'] = '';
                $reqInfo['name'] = '';
                $reqInfo['code'] = '';
                $reqInfo['branch_name'] = '';
            }

            $this->set('requestInfo', $reqInfo);
        }
    }

	public function requestSales() {
		$common = new Common();
		$accountId = $this->request->session()->read('accountId');
		$accountInfo = $this->loadModel('Accounts')->get($accountId);
		$this->printDebug(__FUNCTION__.":".__LINE__.": accountId=".$accountId);

		$reqInfo = array();

		if ($this->request->is('ajax')) {

			$reqInfo['bank_id'] = $_POST['bank_id'];
			$reqInfo['kind_id'] = $_POST['kind_id'];
			$reqInfo['branch_id'] = $_POST['branch_id'];
			$reqInfo['account_number'] = $_POST['account_number'];
			$reqInfo['family_name'] = $_POST['family_name'];
			$reqInfo['first_name'] = $_POST['first_name'];
			$reqInfo['request_price'] = $_POST['request_price'];
			$reqInfo['payment_fee'] = $_POST['payment_fee'];
			$reqInfo['withdraw_price'] = $_POST['withdraw_price'];
			$this->printDebug(__FUNCTION__.":".__LINE__.": request_price=".$reqInfo['request_price']);
			$this->printDebug(__FUNCTION__.":".__LINE__.": withdraw_price=".$reqInfo['withdraw_price']);

			$response = $common->sendBill($accountInfo->token, $reqInfo['bank_id'], $reqInfo['kind_id'], $reqInfo['branch_id'], $reqInfo['account_number'], $reqInfo['family_name'], $reqInfo['first_name'], $reqInfo['request_price'], $reqInfo['payment_fee']);

			if ($response->result == "OK") {
				$result = "success";
				$msg = "OK";
			} else if ($response->result == "error") {
				$result = "error";
				//ob_start();
				//print_r($response);
				//$responsedata = ob_get_clean();
				//$this->printDebug(__FUNCTION__.":".__LINE__.": row=".$responsedata);
				//$this->printDebug(basename(__FUNCTION__).":".__LINE__.": message=".$response->errors[0]->message);
				$msg = $response->errors[0]->message;
			} else if ($response->result == "fail") {
				$result = "fail";
				$msg = "Network Error";
			}

			$this->set(compact('result','msg'));
			$this->set('_serialize', ['result','msg']);
        } else if ($this->request->is('get')) {
			$this->printDebug(__FUNCTION__.":".__LINE__.": accountId=".$accountId);
			$kind_name =  array (
				[ 'id'=>1, 'name'=>'普通預金' ],
				[ 'id'=>2, 'name'=>'当座預金' ],
				[ 'id'=>3, 'name'=>'' ],
				[ 'id'=>4, 'name'=>'貯蓄預金' ],
			);

			$response = $common->getBankAccounts($accountInfo->token);
			if ($response->result == "OK") {
				$data = $response->data;
				$reqInfo['bank_id'] = $data->bank_account->bank_id;
				$reqInfo['kind_id'] = $data->bank_account->kind;
				$reqInfo['branch_id'] = $data->bank_account->branch_id;
				$reqInfo['account_number'] = $data->bank_account->account_number;
				$reqInfo['family_name'] = $data->bank_account->family_name;
				$reqInfo['first_name'] = $data->bank_account->first_name;

				$reqInfo['bank_name'] = $this->loadModel('BankNames')->find('all', [
					'conditions' => ['bank_id =' => $data->bank_account->bank_id]
				])->first()->bank_name;
				$reqInfo['kind_name'] = $kind_name[$data->bank_account->kind-1]['name'];
				$this->printDebug(basename(__FUNCTION__).":".__LINE__.": kind_name=".$reqInfo['kind_name']);
				$regstatus = "registered";
				$result = $common->getCurrentSales($accountInfo->token);
				if ($result->result == "OK") {
					$reqInfo['current_price'] = $result->data->current_sales;
					$reqInfo['payment_fee'] = $result->data->payment_fee;
				} else {
					$reqInfo['current_price'] = "";
					$reqInfo['payment_fee'] = "";
				}
			} else {
				$regstatus = "unregistered";
			}

			$this->set('regstatus', $regstatus);
			$this->set('requestInfo', $reqInfo);

			$userInfo = $this->Users->get($this->Auth->user('id'));
			if (empty($userInfo->bankpass)) {
				$this->set('authstatus', 'auth');
			} else {
				$this->set('authstatus', 'noauth');
			}
		}
	}

    public function mercariProfile()
    {
		$siteId = $this->request->session()->read('siteId');
		$accountId = $this->request->session()->read('accountId');
		$accountInfo = $this->loadModel('Accounts')->get($accountId);

		$common = new Common();

		$profile = array();
		if($this->request->is('ajax')) {
			$profile['name'] = $_POST['name'];
			$profile['descr'] = $_POST['introduction'];
			$profile['photo'] = "";//$_POST['profilePhoto'];
			//$profile['new'] = $_FILES['profile_photo']['name'];

			$img_dir = WWW_ROOT. "img/data/";
			if($_FILES['profile_photo']['name'] != "") {
				$this->printDebug(__FUNCTION__.": ".__LINE__.": photo=".$_FILES['profile_photo']['name']);
				$ext = substr(basename($_FILES['profile_photo']['name']), strpos(basename($_FILES['profile_photo']['name']), '.'), strlen(basename($_FILES['profile_photo']['name'])) - 1);
				$photoFile = $accountId . $ext;
				if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $img_dir . $photoFile)) {
					$profile['photo'] = $img_dir . $photoFile;
				}
			}

			$response = $common->updateProfile($accountInfo->token, $profile);

			if (!empty($profile['photo'])) {
				unlink($profile['photo']);
			}

			if ($response->result == "OK") {
				$result = "success";
				$msg = "OK";
			} else {
				$result = "fail";
				$msg = "";
			}

			$this->set(compact('result','msg'));
			$this->set('_serialize', ['result','msg']);
		} else {
			$result = $common->getProfile($accountInfo->token);
			if ($result->result == "OK") {
				$profile['name'] = $result->data->name;
				$profile['photo'] = $result->data->photo_thumbnail_url;
				$profile['descr'] = $result->data->introduction;
			} else {
				$profile['name'] = "";
				$profile['photo'] = "";
				$profile['descr'] = "";
			}

			$this->set('siteId', $siteId);
			$this->set('profile', $profile);
		}
    }

    public function furiruProfile()
    {
		$accountId = $this->request->session()->read('accountId');
		$accountInfo = $this->loadModel('Accounts')->get($accountId);

		$common = new FuriruCommon();

		$profile = array();
		if($this->request->is('ajax')) {
			$profile['name'] = $_POST['name'];
			$profile['descr'] = $_POST['introduction'];

			$img_dir = WWW_ROOT. "img/data/";
			$profile['photo'] = "";
			if($_FILES['profile_photo']['name'] != "") {
				$this->printDebug(__FUNCTION__.": ".__LINE__.": photo=".$_FILES['profile_photo']['name']);
				$ext = substr(basename($_FILES['profile_photo']['name']), strpos(basename($_FILES['profile_photo']['name']), '.'), strlen(basename($_FILES['profile_photo']['name'])) - 1);
				$photoFile = $accountId . $ext;
				if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $img_dir . $photoFile)) {
					$profile['photo'] = $img_dir . $photoFile;
				}
			}

			$profile['shopphoto'] = "";
			if($_FILES['shop_photo']['name'] != "") {
				$this->printDebug(__FUNCTION__.": ".__LINE__.": photo=".$_FILES['shop_photo']['name']);
				$ext = substr(basename($_FILES['shop_photo']['name']), strpos(basename($_FILES['shop_photo']['name']), '.'), strlen(basename($_FILES['shop_photo']['name'])) - 1);
				$photoFile = $accountId . $ext;
				if (move_uploaded_file($_FILES['shop_photo']['tmp_name'], $img_dir . $photoFile)) {
					$profile['shopphoto'] = $img_dir . $photoFile;
				}
			}
			$profile['shopname'] = $_POST['shopname'];
			$profile['alias'] = "";

			$result = $common->updateUserProfile($accountInfo->token, $profile);

			if ($profile['photo'] != "") {
				unlink($profile['photo']);
			}
			if ($profile['shop_photo'] != "") {
				unlink($profile['shop_photo']);
			}

			if ($result->result == true) {
				$result = "success";
				$msg = "OK";
			} else {
				$result = "fail";
				$msg = "";
			}

			$this->set(compact('result','msg'));
			$this->set('_serialize', ['result','msg']);
		} else {
			$response = $common->getUserProfile($accountInfo->token, $accountInfo->cookie);
			if (($response->result == "login")) {
				$expireTime = date_create(date("Y-m-d H:i:s"));
				$expireTime->modify("-24 hours");
				$accountInfo->expire_date = $expireTime->format("Y-m-d H:i:s");
				$this->Accounts->save($accountInfo);
				return $this->redirect(['action' => 'index']);
			} else if ($response->result == "success") {
				$profile['name'] = $response->data->user->screen_name;
				$profile['photo'] = $response->data->user->profile_img_url;
				$profile['descr'] = $response->data->user->bio;
				$profile['shopname'] = $response->data->user->shop->name;
				$profile['shopphoto'] = $response->data->user->shop->cover_image_url;
			} else {
				$profile['name'] = "";
				$profile['photo'] = "";
				$profile['descr'] = "";
				$profile['shopname'] = "";
				$profile['shopphoto'] = "";
			}

			$this->set('profile', $profile);
		}
    }


    public function rakumaProfile()
    {
		$accountId = $this->request->session()->read('accountId');
		$accountInfo = $this->loadModel('Accounts')->get($accountId);

		$common = new RakumaCommon();

		$profile = array();
		if($this->request->is('ajax')) {
		} else {
			$responseData = $common->getUserProfile($accountInfo->token);
			if ($responseData->result == "success") {
				//$this->printDebug(__FUNCTION__.":".__LINE__.": response=".$response);
				//$responseData = json_decode($response);
				$this->printDebug(__FUNCTION__.":".__LINE__.":");
				$profile['name'] = $responseData->data->userInfo->userNickname;
				$profile['descr'] = "";//$responseData->data->userInfo->userNickname;
				if (isset($responseData->data->userInfo->profileImage))
					$profile['photo'] = "http://rakuma.r10s.jp/d/strg/ctrl/25/".$responseData->data->userInfo->profileImage;
				else
					$profile['photo'] = "img/no_avatar.jpg";
			} else {
				$profile['name'] = "";
				$profile['photo'] = "";
				$profile['descr'] = "";
			}

			$this->set('profile', $profile);
		}
	}

	// 出品管理(3th) 売上管理(3th)
    public function profitManagement() {
        $common = new Common();
        $accountInfo = $this->loadModel('Accounts')->get($this->request->session()->read('accountId'));
        $response = $common->getCurrentSales($accountInfo);
        $result = null;
        $data = array();
        if ($response->result == 'OK') {
			$result = 'success';
			$msg = "OK";
			$current_sales = $response->data->current_sales;
			$payment_fee = $response->data->payment_fee;
			$num_ticket = $response->data->num_ticket;
			$this->set('current_sales', $current_sales);
			$this->set('payment_fee', $payment_fee);
			$this->set('num_ticket', $num_ticket);
        } else if ($response->result == "error") {
            $result = 'error';
			$msg = $response->errors[0]->message;
		} else {
			$result = "fail";
			$msg = "Network Error";
        }

		$this->set('result', $result);
		$this->set('msg', $msg);
    }

    public function setAllCrontab() {
        $this->setCrontab();
        $this->setCrontabCheckStatus();
        $this->setCrontabForAutoPublish();
        $this->runCrontab();
    }

    public function setCrontab() {
        $allAccounts = $this->loadModel('Accounts')->find('all');

        $cronjob_tmp_file = WWW_ROOT."files/cronjobs.txt";
        unlink($cronjob_tmp_file);

        foreach ($allAccounts as $index=>$account) {

            $accountId = $account->id;

            $newCronJobs = "* * * * * ";  // per one minute
            if ("1" == $account->site_id) {
                $newCronJobs .= getcwd() . "/../bin/cake mercari_product_manual_publish " . $accountId . 'u';
            } else if ("2" == $account->site_id) {
                $newCronJobs .= getcwd() . "/../bin/cake furiru_product_manual_publish " . $accountId . 'f';
            } else if ("3" == $account->site_id) {
                $newCronJobs .= getcwd() . "/../bin/cake rakuma_product_manual_publish " . $accountId . 'r';
            }
            $append_cronfile = "echo '";

            $append_cronfile .= $newCronJobs;
            $append_cronfile .= "'  >> ".$cronjob_tmp_file;

            shell_exec($append_cronfile);
        }
    }

    function setCrontabCheckStatus() {
        $allAccounts = $this->loadModel('Accounts')->find('all');

        $cronjob_tmp_file = WWW_ROOT."files/cronjobs.txt";

        foreach ($allAccounts as $index=>$account) {
            $accountId = $account->id;

            $newCronJobs = "*/60 * * * * ";  // per 60 minutes
            if ("1" == $account->site_id) {
                $newCronJobs .= getcwd() . "/../bin/cake mercari_product_check_status " . $accountId . 'u';
            } if ("2" == $account->site_id) {
                $newCronJobs .= getcwd() . "/../bin/cake furiru_product_check_status " . $accountId . 'f';
            } if ("3" == $account->site_id) {
                $newCronJobs .= getcwd() . "/../bin/cake rakuma_product_check_status " . $accountId . 'r';
            }
            $append_cronfile = "echo '";

            $append_cronfile .= $newCronJobs;
            $append_cronfile .= "'  >> " . $cronjob_tmp_file;

            shell_exec($append_cronfile);
        }
    }

    public function setCrontabForAutoPublish(){
        $publish_setting = $this->loadModel('PublishSettings')->find('all');

        $cronjob_tmp_file = WWW_ROOT."files/cronjobs.txt";

        foreach ($publish_setting as $index=>$setting) {

            $publish_rule_type = $setting->publish_rule_type;
            $publish_hourly_count = $setting->publish_hourly_count;
            $starthour = $setting->publish_hour;
            $startminute = $setting->publish_minute;
            $publish_interval = $setting->publish_interval;
            $day_of_week = $setting->day_of_week;
            $interval_type = $setting->interval_type;
            $accountId = $setting->account_id;

            $accountInfo = $this->loadModel('Accounts')->find('all', [
                'conditions' => ['id =' => $accountId]
            ])->first();

            if (is_null($accountInfo)) {
                continue;
            }

            $newCronJobs = '';

            if (2 == $publish_rule_type) {
                $startHours = '';
                for ($h = 0; $h < 24; $h++) {
                    $hour = $publish_hourly_count[$h];
                    if ('0' != $hour) {
                        $startHours = $startHours . $h . ',';
                    }
                }

                if ('' == $startHours) {
                    continue; // VERY IMPORTANCE
                } else {
                    $startHours = substr($startHours, 0, -1);
                }

                if ($interval_type == 0) {
                    $newCronJobs .= '0 ' . $startHours . ' */' . $publish_interval . ' * * ';
                } else {
                    $newCronJobs .= '0 ' . $startHours . ' * * ' . $day_of_week . ' ';
                }
            } else {
                if ($interval_type == 0) {
                    $newCronJobs .= $startminute . ' ' . $starthour . ' */' . $publish_interval . ' * * ';
                } else {
                    $newCronJobs .= $startminute . ' ' . $starthour . ' * * ' . $day_of_week . ' ';
                }
            }

            if ("1" == $accountInfo->site_id) {
                $newCronJobs .= getcwd() . "/../bin/cake mercari_product_auto_publish " . $accountId . "u";
            } else if ("2" == $accountInfo->site_id) {
                $newCronJobs .= getcwd() . "/../bin/cake furiru_product_auto_publish " . $accountId . "f";
            } else if ("3" == $accountInfo->site_id) {
                $newCronJobs .= getcwd() . "/../bin/cake rakuma_product_auto_publish " . $accountId . "r";
            }

            $append_cronfile = "echo '";

            $append_cronfile .= $newCronJobs;
            $append_cronfile .= "'  >> ".$cronjob_tmp_file;

            shell_exec($append_cronfile);
        }
    }

    public function runCrontab() {
        $cronjob_tmp_file = WWW_ROOT."files/cronjobs.txt";

        if (file_exists($cronjob_tmp_file)) {
            $install_cron = "crontab " . $cronjob_tmp_file;
            shell_exec($install_cron);
        } else {
            shell_exec("crontab -r");
        }
    }
}
