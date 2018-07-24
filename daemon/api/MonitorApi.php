<?php

require_once 'RollingCurl.php';
require_once 'DBApi.php';
require_once 'AlertMethodApi.php';
require_once '../telegram/TelegramBot.php';

class MonitorApi
{
    protected static $instance = null;
    private $updated_time = array();

    public static function getInstance()
    {
        if( is_null( static::$instance ) )
        {
            static::$instance = new MonitorApi();

        }
        return static::$instance;
    }
    protected function __construct() {

    }

    private function __clone() {

    }

    private function __wakeup() {

    }
    private function getMonitorSites($domain = '', $userId)
    {
        $dbApi = DBApi::getInstance();
        $dbApi->setSubDomain($domain);
        $sites = $dbApi->getMonitorSiteListByUserId($userId);
        return $sites;
    }

    private function getMonitorScheduleOfAllDomain()
    {
        $dbApi = DBApi::getInstance();
        $domains = $dbApi->getAllSubDomain();

        $schedules = array();
        foreach ($domains as $domain)
        {
            $features = $dbApi->getFeatureEnableList($domain);
            $features = explode(',', $features);
            if (!in_array(2, $features))
                continue;

            $dbApi->setSubDomain($domain[1]);
            $schedule = $dbApi->getMonitorSchedule();
            $schedules[] = array($domain[1], $schedule);
        }
        return $schedules;
    }
    public function runMonitoring()
    {
        $schedules = $this->getMonitorScheduleOfAllDomain();

        if ($schedules != array())
        {
            $rc = new RollingCurl(array($this,'request_callback'));
            $rc->window_size = 20;
            $actual_monitor_info = array();

            foreach ($schedules as $schedule)
            {
                $domain = $schedule[0];
                $userSchedules = $schedule[1];
                foreach ($userSchedules as $user_schedule)
                {
                    $interval = $user_schedule[1];
                    $last_updated = $user_schedule[6];
                    $userId = $user_schedule[5];
                    $sms = $user_schedule[2];
                    $email = $user_schedule[3];
                    $telBot = $user_schedule[4];

                    $now = date("Y-m-d H:i:s");
                    $before = date("Y-m-d H:i", strtotime('-'.$interval.' minutes'));

                    $do = false;
                    if ($last_updated == null)
                        $do = true;
                    else
                    {
                        $last_updated = date("Y-m-d H:i", strtotime($last_updated));
                        if ($before >= $last_updated)
                            $do = true;
                    }

                    if($do)
                    {
                        $sites = $this->getMonitorSites($domain, $userId);
                        if ($sites != array())
                        {
                            $this->updated_time[$domain][$userId]['start'] = $now;
                            $actual_monitor_info[] = array($domain, $userId, $sms, $email, $telBot);
                            foreach ($sites as $item)
                            {
                                $request = new RollingCurlRequest($item[2]);
                                $request->options = array(CURLOPT_HEADER => 42, CURLOPT_NOBODY => true, CURLOPT_RETURNTRANSFER => true);
                                $request->__set('domain', $domain);
                                $request->__set('urlId', $item[0]);
                                $request->__set('userId', $userId);
                                $rc->add($request);
                            }
                        }
                    }
                }
            }
            $reqCount = count($rc->__get('requests'));
            if ($reqCount > 0)
            {
                // update time before running
//                echo "updating monitor start time \n";
//                if ($actual_monitor_info != array())
//                {
//                    foreach ($actual_monitor_info as $item)
//                    {
//                        $domain = $item[0];
//                        $userId = $item[1];
//                        $start = $this->updated_time[$domain][$userId]['start'];
//                        $end = $start;
//                        $dbApi = DBApi::getInstance();
//                        $dbApi->setSubDomain($domain);
//                        $dbApi->updateMonitorTime($start, $end, $userId);
//                    }
//                }
                $rc->execute();
                // update time after running
                echo "updating monitor end time \n";
                if ($actual_monitor_info != array())
                {
                    foreach ($actual_monitor_info as $item)
                    {
                        $domain = $item[0];
                        $userId = $item[1];
                        $start = $this->updated_time[$domain][$userId]['start'];
                        $end = $this->updated_time[$domain][$userId]['end'];
                        $dbApi = DBApi::getInstance();
                        $dbApi->setSubDomain($domain);
                        $dbApi->updateMonitorTime($start, $end, $userId);
                    }
                }

                // update status, history and alert
                if ($actual_monitor_info != array())
                {
                    foreach ($actual_monitor_info as $item)
                    {
                        $domain = $item[0];
                        $userId = $item[1];
                        $sms = $item[2];
                        $email = $item[3];
                        $telBot = $item[4];

                        // Get irregular sites
                        $issue_data = array();
                        $dbApi = DBApi::getInstance();
                        $dbApi->setSubDomain($domain);
                        $site_stats = $dbApi->getMonitorStatusByUserId($userId);
                        foreach ($site_stats as $stats)
                        {
                            if ($stats['3'] != 200)
                            {
                                // site name, url, status
                                $issue_data[] = array($stats[1], $stats[2], $stats[3], $stats[0]);
                            }
                        }
                        if ($issue_data != array())
                        {
                            // update status issue table
                            $dbApi->addMonitorStatusIssues($userId, $issue_data, $this->updated_time[$domain][$userId]['end']);


                            // send alerts
                            echo "sending monitor alerts to users \n";
                            if ($sms || $email || $telBot)
                            {
                                $userInfo = $dbApi->getAllUsers();
                                $alert_method = array();
                                foreach ($userInfo as $user)
                                {
                                    if ($user[0] == $userId && $user[5] == 1)
                                    {
                                        if ($sms == 1)
                                            $alert_method[] = array('sms', $user[7]);
                                        if ($email == 1)
                                            $alert_method[] = array('email', $user[8]);
                                        if ($telBot == 1)
                                            $alert_method[] = array('telegram_bot', $user[9]);
                                    }
                                }
                                if ($alert_method != array())
                                {
                                    // fire alerts through sms, email, telegram bot
                                    foreach ($alert_method as $method)
                                    {
                                        if ($method[0] == 'email')
                                        {
                                            $body = $this->dataToHTML($issue_data);
                                            $sender = AlertMethodApi::getInstance();
                                            $ret = $sender->sendEmail('alert@apilotus.com', array($method[1]), "Monitor Lotus Alert", $body);
                                            print_r($ret);
                                        } else
                                        {
                                            $text = $this->dataToText($issue_data);
                                            if ($method[0] == 'sms')
                                            {
                                                $sender = AlertMethodApi::getInstance();
                                                $ret = $sender->sendSMS(array($method[1]), $text);
                                                print_r($ret);
                                            } else
                                            {
                                                $bot = new TelegramBot();
                                                $bot->sendMessageByID($text, $method[1]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    private function dataToText($data)
    {
        $message = 'Monitor Lotus'."\r\n\r\n";
        $count = count($data);
        $count == 1 ? $numbering = false: $numbering = true;
        $index = 1;
        foreach ($data as $item)
        {
            if ($numbering)
                $text = $index.'. '.$item[0]."\r\n";
            else
                $text = $item[0]."\r\n";
            $message = $message.$text;

            $text = 'Url: '.$item[1]."\r\n";
            $message = $message.$text;
            $text = 'Response: '.$item[2]."\r\n";
            $message = $message.$text;
            $text = 'Status: '.$this->getStatusByCode($item[2])."\r\n";
            $message = $message.$text."\r\n";
            $index ++;
        }
        return $message;
    }
    private function dataToHTML($data)
    {
        $count = count($data);
        $count == 1 ? $numbering = false: $numbering = true;
        $index = 1;
        $message = '';
        foreach ($data as $item)
        {
            if ($numbering)
                $text = $index.'. '.$item[0]."<br />";
            else
                $text = $item[0]."<br />";
            $message = $message.$text;

            $text = 'Url: <a href="'.$item[1].'">'.$item[1].'</a><br />';
            $message = $message.$text;
            $text = 'Response: '.$item[2]."<br />";
            $message = $message.$text;
            $text = 'Status: '.$this->getStatusByCode($item[2])."<br />";
            $message = $message.$text."<br />";
            $index ++;
        }
        return "<html><body>".$message."</body></html>";
    }
    private function getStatusByCode($code)
    {
        switch ($code) {
            case 0:
                $text = 'Error while fetching URL';
                break;
            case 100:
                $text = 'Continue';
                break;
            case 101:
                $text = 'Switching Protocols';
                break;
            case 200:
                $text = 'OK';
                break;
            case 201:
                $text = 'Created';
                break;
            case 202:
                $text = 'Accepted';
                break;
            case 203:
                $text = 'Non-Authoritative Information';
                break;
            case 204:
                $text = 'No Content';
                break;
            case 205:
                $text = 'Reset Content';
                break;
            case 206:
                $text = 'Partial Content';
                break;
            case 300:
                $text = 'Multiple Choices';
                break;
            case 301:
                $text = 'Moved Permanently';
                break;
            case 302:
                $text = 'Moved Temporarily';
                break;
            case 303:
                $text = 'See Other';
                break;
            case 304:
                $text = 'Not Modified';
                break;
            case 305:
                $text = 'Use Proxy';
                break;
            case 400:
                $text = 'Bad Request';
                break;
            case 401:
                $text = 'Unauthorized';
                break;
            case 402:
                $text = 'Payment Required';
                break;
            case 403:
                $text = 'Forbidden';
                break;
            case 404:
                $text = 'Not Found';
                break;
            case 405:
                $text = 'Method Not Allowed';
                break;
            case 406:
                $text = 'Not Acceptable';
                break;
            case 407:
                $text = 'Proxy Authentication Required';
                break;
            case 408:
                $text = 'Request Time-out';
                break;
            case 409:
                $text = 'Conflict';
                break;
            case 410:
                $text = 'Gone';
                break;
            case 411:
                $text = 'Length Required';
                break;
            case 412:
                $text = 'Precondition Failed';
                break;
            case 413:
                $text = 'Request Entity Too Large';
                break;
            case 414:
                $text = 'Request-URI Too Large';
                break;
            case 415:
                $text = 'Unsupported Media Type';
                break;
            case 500:
                $text = 'Internal Server Error';
                break;
            case 501:
                $text = 'Not Implemented';
                break;
            case 502:
                $text = 'Bad Gateway';
                break;
            case 503:
                $text = 'Service Unavailable';
                break;
            case 504:
                $text = 'Gateway Time-out';
                break;
            case 505:
                $text = 'HTTP Version not supported';
                break;
            default:
                $text = 'Unknown http status code';
                break;
        }

        return $text;
    }
    function request_callback($response, $info, $domain, $urlId, $userId)
    {
        $dbApi = DBApi::getInstance();
        $dbApi->setSubDomain($domain);
        $stats = isset($info['http_code']) ? $info['http_code'] : 0;
        $ts = date("Y-m-d H:i:s");
        $this->updated_time[$domain][$userId]['end'] = $ts;
        $dbApi->updateStatsByUrl($urlId, $stats, $ts);
    }

}