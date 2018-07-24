<?php
require_once '../api/DBApi.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == "")
{
    session_write_close();
    echo 'no_cookie';
    return;
}
$userID = $_SESSION['user_id'];
session_write_close();

$dbApi = DBApi::getInstance();
if ($dbApi->getSubDomain() == '')
{
    echo 'no_cookie';
    return;
}

$siteID = $_GET['site_id'];
$metricsDate = $_GET['metrics_date'];
if ($metricsDate == 'DAY')
    $result = getHistory(0, $userID, $siteID);
else if ($metricsDate == 'WEEK')
    $result = getHistory(1, $userID, $siteID);
else
    $result = getHistory(2, $userID, $siteID);

echo json_encode($result);
return;



function getHistory($type, $userID, $siteID)
{
    $dbApi = DBApi::getInstance();
    $current_time = date('Y-m-d H:i');

    if ($type == 2)
    {
        $unit = 1;
        $start_time = date('Y-m-d', strtotime($current_time. '-1 month'));
        $end_time = date('Y-m-d', strtotime($current_time.'+1 day'));
    }else
    {
        if ($type == 0)
            $unit = 5; // 5 minute unit
        else
            $unit = 30; // 30 minute unit

        $minute = date('i');
        if ($minute % $unit != 0)
        {
            $before = $minute % $unit;
            $end_time = date('Y-m-d H:i', strtotime($current_time. '-'.$before.' minute'));
        } else
            $end_time = $current_time;

        if ($type == 0)
            $start_time = date('Y-m-d H:i', strtotime($end_time. '-1 day'));
        else
            $start_time = date('Y-m-d H:i', strtotime($end_time. '-1 week'));

        $start_time = date('Y-m-d H:i', strtotime($start_time.'-'.$unit.' minute'));
    }
    $history = $dbApi->getMonitorHistory($userID, $siteID, $start_time, $end_time);

    if ($history != array())
    {
        $site_name = $history[0];
        $data = $history[1];
        if ($type == 2)
        {
            $time0 = $start_time;
            $time1 = date('Y-m-d', strtotime($time0.'+'.$unit.' day'));
            $up_time = array();
            // Calculate Up Time
            while (true)
            {
                // Calculate Up Time
                $minute_sum = 0;
                foreach ($data as $item)
                {
                    if ($item[2] == 200)
                        continue;
                    $item_start_time = date('Y-m-d H:i', strtotime($item[3]));
                    $item_end_time = date('Y-m-d H:i', strtotime($item[4]));

                    if ($time0 <= $item_end_time && $time1 > $item_start_time )
                    {
                        $start = $item_start_time >= $time0 ? $item_start_time : $time0;
                        $end = $item_end_time < $time1 ? $item_end_time : $time1;
                        $start = new DateTime($start);
                        $end = new DateTime($end);
                        $diff = date_diff($end, $start, true);

                        $minute_sum += (int)$diff->format('%d') * 24  * 60;
                        $minute_sum += ((int)$diff->format('%h')) * 60;
                        $minute_sum += (int)$diff->format('%i');
                    }
                }
                if ($minute_sum > 1440)
                    $minute_sum = 1440;
                $percent = (float)(1440 - $minute_sum) / 1440.0 * 100;
//                $percent = number_format($percent, 0, '.', '');
                $up_time[] = array($time0, (int)$percent);

                $time0 = $time1;
                $time1 = date('Y-m-d H:i', strtotime($time1.'+'.$unit.' day'));
                if ($time0 > $end_time)
                    break;
            }
            return array($start_time, $unit, $up_time);

        } else
        {
            $time0 = $start_time;
            $time1 = date('Y-m-d H:i', strtotime($time0.'+'.$unit.' minute'));
            $up_time = array();
            while (true)
            {
                // Calculate Up Time
                $minute_sum = 0;
                foreach ($data as $item)
                {
                    if($item[2] == 200)
                        continue;
                    $item_start_time = date('Y-m-d H:i', strtotime($item[3]));
                    $item_end_time = date('Y-m-d H:i', strtotime($item[4]));

                    if ($time0 <= $item_end_time && $time1 >= $item_start_time)
                    {

                        $start = $item_start_time >= $time0 ? $item_start_time : $time0;
                        $end = $item_end_time <= $time1 ? $item_end_time : $time1;
                        $start = new DateTime($start);
                        $end = new DateTime($end);
                        $diff = date_diff($end, $start, true);
                        $diff = $diff->format('%i');
                        $minute_sum += (int)$diff;
                    }
                }
                if ($minute_sum > $unit)
                    $minute_sum = $unit;
                $percent = (float)($unit - $minute_sum) / (float)$unit * 100;
//                $percent = number_format($percent, 2, '.', '');
                $up_time[] = array($time1, (int)$percent);

                $time0 = $time1;
                $time1 = date('Y-m-d H:i', strtotime($time1.'+'.$unit.' minute'));
                if ($time1 > $end_time)
                    break;
            }
            return array($start_time, $unit, $up_time);
        }
    } else
    {
        return array($start_time, $unit, array());
    }

}