<?php

/**
 * @package 	Aspen_Framework
 * @subpackage 	System
 * @author 		Michael Botsko
 * @copyright 	2009 Trellis Development, LLC
 * @since
 */

/**
 * Static class containing a collection of useful date methods.
 * @package Aspen_Framework
 */
class Date {


	/**
	 * Does not perform strotime func on a value that's already an integer
	 * @param mixed $str
	 * @return integer
	 * @access public
	 */
	static public function strtotime($str){
		return is_int($str) ? $str : strtotime($str);
	}


	/**
	 * Returns the current date time string with microseconds
	 * @param string $mtime
	 * @return integer
	 */
	static public function microtime($mtime = false){
		$mtime = $mtime ? $mtime : microtime();
		$mtime = explode(" ", $mtime);
		return $mtime[1] + $mtime[0];
	}


	/**
	 * Formats a microtime-based timestamp.
	 * @param integer $time
	 * @param string $format
	 * @return string
	 */
	static public function formatMicrotime($time = false, $format = 'Y-m-d H:i:s.u'){
		$time = $time ? $time : Date::microtime();
		$micro = sprintf("%06d",($time - floor($time)) * 1000000);
		$d = new DateTime( date('Y-m-d H:i:s.'.$micro,$time) );
		return $d->format($format);
	}


	/**
	 * Returns a count of days between two dates
	 * @param datetime $start
	 * @param datetime $end
	 * @return float
	 * @access public
	 */
	static public function daysBetween($start = false, $end = false ){
		$start = $start ? Date::strtotime($start) : time();
		$end = $end ? Date::strtotime($end) : time();
		return ($end - $start) / 86400;
	}


	/**
	 *
	 * @param <type> $gmdate
	 * @param <type> $format
	 * @param <type> $timezone
	 * @return <type>
	 */
	static public function tzFormatDate($gmdate, $format = false, $timezone = false){
		$cnv_date = new DateTime($gmdate);
		$cnv_date->setTimeZone(new DateTimeZone($timezone));
		return $cnv_date->format($format);
	}


	/**
	 *
	 * @param <type> $date
	 * @return <type>
	 */
	static public function isEmptyDate($date){
		$empty_date = str_replace(array(0, "-", ":", " "), '', $date);
		return strlen($empty_date) == 0;
	}


	/**
	 * Prints a nicer date display
	 * @param string $date
	 * @param string $date_format_string The format to print the date, if needed
	 * @param mixed $empty_string What to print if the data is empty
	 * @param boolean $date_only Whether or not to display nice names or just dates
	 * @return string
	 * @access public
	 */
	static public function niceDate($date, $arg_opts = array()){

		// set options
		$opts = array(
					'format'=>"n/j/Y",
					'empty'=>"-",
					'date_only'=>false
				);
		$opts = array_merge($opts, $arg_opts);

		$return_date = $opts['empty'];

		if(!Date::isEmptyDate($date)){

			$date = Date::strtotime($date);
			$days_between = Date::daysBetween(date("Y-m-d"), date("Y-m-d", $date));

			if(!$opts['date_only']){
				if(date("Y-m-d", $date) == date("Y-m-d")){
					$return_date = 'Today';
				}
				elseif(date("Y-m-d", $date) == date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+1, date("y")))){
					$return_date = 'Tomorrow';
				}
				elseif(date("Y-m-d", $date) == date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")-1, date("y")))){
					$return_date = 'Yesterday';
				}
				elseif($days_between > 0 && $days_between < 7){
					// if this week
					if(date("W") == date("W", $date)){
						$return_date = "This " . date("l", $date);
					} else {
						$return_date = "Next " . date("l", $date);
					}
				}
				elseif($days_between > 7 && $days_between <= 14){
					$return_date = "Two Weeks";
				}
				elseif($days_between > 14 && $days_between <= 21){
					$return_date = "Three Weeks";
				}
				elseif($days_between > 21 && $days_between <= 60){
					$return_date = "Next Month";
				}
				elseif($days_between < 0 && $days_between > -7){
					$return_date = "Last " . date("l", $date);
				}
				elseif($days_between == -7){
					$return_date = "One Week Ago";
				}
				else {
					$return_date = date($opts['format'], $date);
				}
			} else {
				$return_date = date($opts['format'], $date);
			}
		}

		return $return_date;

	}


	/**
	 *
	 * @return <type>
	 */
	static public function timezone_us_quicklist(){

		$dates = array();

		$dates["Pacific/Honolulu"]		= "(GMT-10:00) Hawaii";
		$dates["America/Anchorage"]		= "(GMT-09:00) Alaska";
		$dates["America/Los_Angeles"]	= "(GMT-08:00) Pacific Time (US & Canada)";
		$dates["America/Phoenix"]		= "(GMT-07:00) Arizona";
		$dates["America/Denver"]		= "(GMT-07:00) Mountain Time (US & Canada)";
		$dates["America/Chicago"]		= "(GMT-06:00) Central Time (US & Canada)";
		$dates["America/New_York"]		= "(GMT-05:00) Eastern Time (US & Canada)";

		return $dates;

	}


	/**
	 *
	 * @param <type> $region
	 * @return <type>
	 */
	static public function timezone_list($region = 'all'){

		$dates = array();

		if($region == 'all' || $region == 'pacific'){
			$dates["Kwajalein"] = "(GMT-12:00) International Date Line West";
			$dates["Pacific/Midway"] = "(GMT-11:00) Midway Island";
			$dates["Pacific/Samoa"] = "(GMT-11:00) Samoa";
			$dates["Pacific/Honolulu"] = "(GMT-10:00) Hawaii";
		}

		if($region == 'all' || $region == 'america'){
			$dates["America/Anchorage"] = "(GMT-09:00) Alaska";
			$dates["America/Los_Angeles"] = "(GMT-08:00) Pacific Time (US & Canada)";
			$dates["America/Tijuana"] = "(GMT-08:00) Tijuana, Baja California";
			$dates["America/Denver"] = "(GMT-07:00) Mountain Time (US & Canada)";
			$dates["America/Chihuahua"] = "(GMT-07:00) Chihuahua";
			$dates["America/Mazatlan"] = "(GMT-07:00) Mazatlan";
			$dates["America/Phoenix"] = "(GMT-07:00) Arizona";
			$dates["America/Regina"] = "(GMT-06:00) Saskatchewan";
			$dates["America/Tegucigalpa"] = "(GMT-06:00) Central America";
			$dates["America/Chicago"] = "(GMT-06:00) Central Time (US & Canada)";
			$dates["America/Mexico_City"] = "(GMT-06:00) Mexico City";
			$dates["America/Monterrey"] = "(GMT-06:00) Monterrey";
			$dates["America/New_York"] = "(GMT-05:00) Eastern Time (US & Canada)";
			$dates["America/Bogota"] = "(GMT-05:00) Bogota";
			$dates["America/Lima"] = "(GMT-05:00) Lima";
			$dates["America/Rio_Branco"] = "(GMT-05:00) Rio Branco";
			$dates["America/Indiana/Indianapolis"] = "(GMT-05:00) Indiana (East)";
			$dates["America/Caracas"] = "(GMT-04:30) Caracas";
			$dates["America/Halifax"] = "(GMT-04:00) Atlantic Time (Canada)";
			$dates["America/Manaus"] = "(GMT-04:00) Manaus";
			$dates["America/Santiago"] = "(GMT-04:00) Santiago";
			$dates["America/La_Paz"] = "(GMT-04:00) La Paz";
			$dates["America/St_Johns"] = "(GMT-03:30) Newfoundland";
			$dates["America/Moncton"] = "(GMT-03:00) Georgetown";
			$dates["America/Sao_Paulo"] = "(GMT-03:00) Brasilia";
			$dates["America/Godthab"] = "(GMT-03:00) Greenland";
			$dates["America/Montevideo"] = "(GMT-03:00) Montevideo";
		}

		if($region == 'all' || $region == 'europe-africa'){
			$dates["Atlantic/South_Georgia"] = "(GMT-02:00) Mid-Atlantic";
			$dates["Atlantic/Azores"] = "(GMT-01:00) Azores";
			$dates["Atlantic/Cape_Verde"] = "(GMT-01:00) Cape Verde Is.";
			$dates["Europe/Dublin"] = "(GMT) Dublin";
			$dates["Europe/Lisbon"] = "(GMT) Lisbon";
			$dates["Europe/London"] = "(GMT) London";
			$dates["Africa/Monrovia"] = "(GMT) Monrovia";
			$dates["Atlantic/Reykjavik"] = "(GMT) Reykjavik";
			$dates["Africa/Casablanca"] = "(GMT) Casablanca";
			$dates["Europe/Belgrade"] = "(GMT+01:00) Belgrade";
			$dates["Europe/Bratislava"] = "(GMT+01:00) Bratislava";
			$dates["Europe/Budapest"] = "(GMT+01:00) Budapest";
			$dates["Europe/Ljubljana"] = "(GMT+01:00) Ljubljana";
			$dates["Europe/Prague"] = "(GMT+01:00) Prague";
			$dates["Europe/Sarajevo"] = "(GMT+01:00) Sarajevo";
			$dates["Europe/Skopje"] = "(GMT+01:00) Skopje";
			$dates["Europe/Warsaw"] = "(GMT+01:00) Warsaw";
			$dates["Europe/Zagreb"] = "(GMT+01:00) Zagreb";
			$dates["Europe/Brussels"] = "(GMT+01:00) Brussels";
			$dates["Europe/Copenhagen"] = "(GMT+01:00) Copenhagen";
			$dates["Europe/Madrid"] = "(GMT+01:00) Madrid";
			$dates["Europe/Paris"] = "(GMT+01:00) Paris";
			$dates["Africa/Algiers"] = "(GMT+01:00) West Central Africa";
			$dates["Europe/Amsterdam"] = "(GMT+01:00) Amsterdam";
			$dates["Europe/Berlin"] = "(GMT+01:00) Berlin";
			$dates["Europe/Rome"] = "(GMT+01:00) Rome";
			$dates["Europe/Stockholm"] = "(GMT+01:00) Stockholm";
			$dates["Europe/Vienna"] = "(GMT+01:00) Vienna";
			$dates["Europe/Minsk"] = "(GMT+02:00) Minsk";
			$dates["Africa/Cairo"] = "(GMT+02:00) Cairo";
			$dates["Europe/Helsinki"] = "(GMT+02:00) Helsinki";
			$dates["Europe/Riga"] = "(GMT+02:00) Riga";
			$dates["Europe/Sofia"] = "(GMT+02:00) Sofia";
			$dates["Europe/Tallinn"] = "(GMT+02:00) Tallinn";
			$dates["Europe/Vilnius"] = "(GMT+02:00) Vilnius";
			$dates["Europe/Athens"] = "(GMT+02:00) Athens";
			$dates["Europe/Bucharest"] = "(GMT+02:00) Bucharest";
			$dates["Europe/Istanbul"] = "(GMT+02:00) Istanbul";
		}

		if($region == 'all' || $region == 'asia'){
			$dates["Asia/Jerusalem"] = "(GMT+02:00) Jerusalem";
			$dates["Asia/Amman"] = "(GMT+02:00) Amman";
			$dates["Asia/Beirut"] = "(GMT+02:00) Beirut";
			$dates["Africa/Windhoek"] = "(GMT+02:00) Windhoek";
			$dates["Africa/Harare"] = "(GMT+02:00) Harare";
			$dates["Asia/Kuwait"] = "(GMT+03:00) Kuwait";
			$dates["Asia/Riyadh"] = "(GMT+03:00) Riyadh";
			$dates["Asia/Baghdad"] = "(GMT+03:00) Baghdad";
			$dates["Africa/Nairobi"] = "(GMT+03:00) Nairobi";
			$dates["Asia/Tbilisi"] = "(GMT+03:00) Tbilisi";
			$dates["Europe/Moscow"] = "(GMT+03:00) Moscow";
			$dates["Europe/Volgograd"] = "(GMT+03:00) Volgograd";
			$dates["Asia/Tehran"] = "(GMT+03:30) Tehran";
			$dates["Asia/Muscat"] = "(GMT+04:00) Muscat";
			$dates["Asia/Baku"] = "(GMT+04:00) Baku";
			$dates["Asia/Yerevan"] = "(GMT+04:00) Yerevan";
			$dates["Asia/Kabul"] = "(GMT+04:30) Kabul";
			$dates["Asia/Yekaterinburg"] = "(GMT+05:00) Ekaterinburg";
			$dates["Asia/Karachi"] = "(GMT+05:00) Karachi";
			$dates["Asia/Tashkent"] = "(GMT+05:00) Tashkent";
			$dates["Asia/Calcutta"] = "(GMT+05:30) Calcutta";
			$dates["Asia/Colombo"] = "(GMT+05:30) Sri Jayawardenepura";
			$dates["Asia/Katmandu"] = "(GMT+05:45) Kathmandu";
			$dates["Asia/Dhaka"] = "(GMT+06:00) Dhaka";
			$dates["Asia/Almaty"] = "(GMT+06:00) Almaty";
			$dates["Asia/Novosibirsk"] = "(GMT+06:00) Novosibirsk";
			$dates["Asia/Rangoon"] = "(GMT+06:30) Yangon (Rangoon)";
			$dates["Asia/Krasnoyarsk"] = "(GMT+07:00) Krasnoyarsk";
			$dates["Asia/Bangkok"] = "(GMT+07:00) Bangkok";
			$dates["Asia/Jakarta"] = "(GMT+07:00) Jakarta";
			$dates["Asia/Brunei"] = "(GMT+08:00) Beijing";
			$dates["Asia/Chongqing"] = "(GMT+08:00) Chongqing";
			$dates["Asia/Hong_Kong"] = "(GMT+08:00) Hong Kong";
			$dates["Asia/Urumqi"] = "(GMT+08:00) Urumqi";
			$dates["Asia/Irkutsk"] = "(GMT+08:00) Irkutsk";
			$dates["Asia/Ulaanbaatar"] = "(GMT+08:00) Ulaan Bataar";
			$dates["Asia/Kuala_Lumpur"] = "(GMT+08:00) Kuala Lumpur";
			$dates["Asia/Singapore"] = "(GMT+08:00) Singapore";
			$dates["Asia/Taipei"] = "(GMT+08:00) Taipei";
		}

		if($region == 'all' || $region == 'australia'){
			$dates["Australia/Perth"] = "(GMT+08:00) Perth";
		}

		if($region == 'all' || $region == 'asia'){
			$dates["Asia/Seoul"] = "(GMT+09:00) Seoul";
			$dates["Asia/Tokyo"] = "(GMT+09:00) Tokyo";
			$dates["Asia/Yakutsk"] = "(GMT+09:00) Yakutsk";
		}

		if($region == 'all' || $region == 'australia'){
			$dates["Australia/Darwin"] = "(GMT+09:30) Darwin";
			$dates["Australia/Adelaide"] = "(GMT+09:30) Adelaide";
			$dates["Australia/Canberra"] = "(GMT+10:00) Canberra";
			$dates["Australia/Melbourne"] = "(GMT+10:00) Melbourne";
			$dates["Australia/Sydney"] = "(GMT+10:00) Sydney";
			$dates["Australia/Brisbane"] = "(GMT+10:00) Brisbane";
			$dates["Australia/Hobart"] = "(GMT+10:00) Hobart";
		}

		if($region == 'all' || $region == 'asia'){
			$dates["Asia/Vladivostok"] = "(GMT+10:00) Vladivostok";
		}

		if($region == 'all' || $region == 'pacific'){
			$dates["Pacific/Guam"] = "(GMT+10:00) Guam";
			$dates["Pacific/Port_Moresby"] = "(GMT+10:00) Port Moresby";
		}

		if($region == 'all' || $region == 'asia'){
			$dates["Asia/Magadan"] = "(GMT+11:00) Magadan";
		}

		if($region == 'all' || $region == 'pacific'){
			$dates["Pacific/Fiji"] = "(GMT+12:00) Fiji";
		}

		if($region == 'all' || $region == 'asia'){
			$dates["Asia/Kamchatka"] = "(GMT+12:00) Kamchatka";
		}

		if($region == 'all' || $region == 'pacific'){
			$dates["Pacific/Auckland"] = "(GMT+12:00) Auckland";
			$dates["Pacific/Tongatapu"] = "(GMT+13:00) Nuku&acirc;&euro;&trade;alofa";
			$dates["Pacific/Kiritimati"] = "(GMT+14:00) Kiritimati";
		}

		return $dates;

	}
}
?>