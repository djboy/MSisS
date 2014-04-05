<?php
###########################################################################################
#	Project: MSiS
#	Description: check if it is the Jewish Sabbath by user location (IP based)
#	Author: Mordi Sacks
#	Version: 1.0
#	Created: 02/04/2014
#	License: GPL
###########################################################################################

function isSabbath(){
	// get IP
	$ip = $_SERVER['REMOTE_ADDR'];

	// get IP info (server good for 240 requests per minute)
	// license http://ip-api.com/docs/#usage_limits
	$json = @file_get_contents("http://ip-api.com/json/".$ip);

	// check is server is up
	if($json !== false){
		// parse json
		$ipData = json_decode($json, true);
		
		// check if we have timezone
		if($ipData["timezone"] != ""){
			// we have time zone, lets set it
			date_default_timezone_set($ipData["timezone"]); 
		} else {
			// we don't have time zone, using default
		}
		
		// get sun info
		// check if we have lat and lon
		if($ipData["lat"] != "" && $ipData["lon"] != ""){
			// we have user approximate location 
			$data = date_sun_info(strtotime("today"), $ipData["lat"], $ipData["lon"]);
		} else {
			// we don't have user location using pre defined location (Jerusalem)
			$data = date_sun_info(strtotime("today"), 31.768319000000000000, 35.213709999999990000);
		}
		//check if is Friday or Saturday
		if(date("D") == "Fri"){
			// is Friday
			// check if Sabbath started
			// add precaution
			// (Jewish people usually accept Sabbath 10 minutes early, but since we cannot determine accurate location we add another minute)
			$add_precaution = $data["sunset"] - (60*11);
			if(date("U") > $add_precaution){
				// is Sabbath
				return true;
			} else {
				// is not Sabbath
				return false;
			}
		
		} elseif(date("D") == "Sat"){
			// is Saturday
			// check if Sabbath is over
			
			// add 11 minutes for extra precaution
			// (in Jewish calender they only add 10, but since we cannot determine accurate location we add another minute just to be safe)
			$add_precaution = $data["civil_twilight_end"]+(60*11);
			if(date("U") < $add_precaution){
				// is Sabbath
				return true ;
			} else {
				// is not Sabbath
				return false;
			}
		} else {
			// not Sabbath
			return false ;
		}
	} else {
		// server is down
		return "Server down";
	}
}
?>