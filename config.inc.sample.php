<?php
	
	//RENAME TO config.inc.php

	setlocale(LC_TIME, "fr_FR");

	$_config['sql_host'] = '';
	$_config['sql_user'] = '';
	$_config['sql_pass'] = '';
	$_config['sql_db'] = '';
	
	$_config['sql_prefix'] = '';

	$_config['steps_request'] = False; //request stepcount from devices
	$_config['steps_min_time_between_step_request'] = 60*60; //do not request steps if last submitted steps is newer then this value
	
	$_config['default_accuracy'] = 1000; //meters
	$_config['default_trackerID'] = 'all';
	
	$_config['geo_reverse_lookup_url'] = "http://193.63.75.109/reverse?format=json&zoom=18&accept-language=fr&addressdetails=0&email=sjobs@apple.com&";
	
	$_config['geo_reverse_boundingbox_url'] = "http://nominatim.openstreetmap.org/reverse?format=json&osm_type=W&osm_id=";

?>
