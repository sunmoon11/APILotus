<?php

class TimeUtils {
	protected static $instance;
	
	public static function getInstance() {
		
		if( is_null( static::$instance ) ) {

			static::$instance = new TimeUtils();

		}

		return static::$instance;
	}
	protected function __construct() {

	}

	private function __clone() {

	}

	private function __wakeup() {
		
	}	
	public function getDateOfCurrentDay()
	{
		return date('m/d/Y');
	}
	public function getDateOfYesterday()
    {
        return date('m/d/Y', strtotime('-1 day'));
    }
	public function getDayOfWeek()
	{
		$day = date('D');
		return $day;
	}
	public function getHour()
	{
		return date('H');
	}
	public function getDateOfCurrentMonday()
	{
		if(date('D') != 'Mon')
			$monday =  date('m/d/Y', strtotime('last Monday'));
		else 
			$monday =  date('m/d/Y');

		return $monday;
	}
	public function getDateOfCurrentSunday()
	{
		$sunday = date('m/d/Y', strtotime('sunday'));
		return $sunday;
	}	
	public function getDateOf2WeeksPrior()
	{
		$sunday = date('m/d/Y', strtotime('-4 weeks sunday'));
		$saturday = date('m/d/Y', strtotime('-3 weeks saturday'));

		return array($sunday, $saturday);
	}
	public function getDateOfCurrentWeek()
	{
		$monday = $this->getDateOfCurrentMonday();
		$day = date('m/d/Y');

		return array($monday, $day);
	}
	public function checkPasswordUpdate($date)
	{
		$today = new DateTime('now');
		$date = new DateTime($date);
		$interval = date_diff($today, $date);
		$days = $interval->format('%a');
		
		return $days;
	}
	public function checkInCurrentWeek($date)
	{
		
		if(date('D') != 'Mon')
			$monday =  date('Y-m-d', strtotime('last Monday'));
		else 
			$monday =  date('Y-m-d');

		$sunday = date('Y-m-d', strtotime('sunday'));	

		echo $monday.'~'.$sunday."\n";
		if($date >= $monday && $sunday >= $date)
			return true;
		else
			return false;
	}
}
?>