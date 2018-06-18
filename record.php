<?php

$fp = fopen('./log/record_log.txt', 'a+');
function _log($msg){
	
	global $fp;
	
	if(!$fp) { $fp = fopen('./log/record_log.txt', 'a+'); }
	
	return fprintf($fp, date('Y-m-d H:i:s') . " - ".$_SERVER['REMOTE_ADDR']." - %s\n", $msg);
	
}



//http://owntracks.org/booklet/tech/http/
# Obtain the JSON payload from an OwnTracks app POSTed via HTTP
# and insert into database table.

header("Content-type: application/json");
require_once('./config.inc.php');

$payload = file_get_contents("php://input");
_log("Payload = ".$payload);
$data =  @json_decode($payload, true);

if (array_key_exists('_type', $data)) $type = strval($data['_type']);
if (array_key_exists('topic', $data)) $topic = explode("/", strval($data['topic']),4);

$responses = array(); //response Array

# CREATE TABLE locations (dt TIMESTAMP, tid CHAR(2), lat DECIMAL(9,6), lon DECIMAL(9,6));
$mysqli = new mysqli($_config['sql_host'], $_config['sql_user'], $_config['sql_pass'], $_config['sql_db']);

if ($type == 'location') {
	
	//http://owntracks.org/booklet/tech/json/
	//iiiissddissiiidsiis
    if (array_key_exists('acc', $data)) $accuracy = intval($data['acc']);
    if (array_key_exists('alt', $data)) $altitude = intval($data['alt']);
    if (array_key_exists('batt', $data)) $battery_level = intval($data['batt']);
	if (array_key_exists('cog', $data)) $heading = intval($data['cog']);
	if (array_key_exists('desc', $data)) $description = strval($data['desc']);
	if (array_key_exists('event', $data)) $event = strval($data['event']);
	if (array_key_exists('lat', $data)) $latitude = floatval($data['lat']);
	if (array_key_exists('lon', $data)) $longitude = floatval($data['lon']);
	if (array_key_exists('rad', $data)) $radius = intval($data['rad']);
	if (array_key_exists('t', $data)) $trig = strval($data['t']);
	if (array_key_exists('tid', $data)) $tracker_id = strval($data['tid']);
	if (array_key_exists('tst', $data)) $epoch = intval($data['tst']);
	if (array_key_exists('vac', $data)) $vertical_accuracy = intval($data['vac']);
	if (array_key_exists('vel', $data)) $velocity = intval($data['vel']);
	if (array_key_exists('p', $data)) $pressure = floatval($data['p']);
	if (array_key_exists('conn', $data)) $connection = strval($data['conn']);
	
	
	$sql = "SELECT epoch FROM ".$_config['sql_prefix']."locations WHERE tracker_id=? AND epoch=?";
	
	_log("Duplicate SQL = ".$sql);
	
	if ($stmt = $mysqli->prepare($sql)){
    	$stmt->bind_param('si',$tracker_id,$epoch);
    	$stmt->execute();
		$stmt->store_result();
		
		_log("Duplicate SQL : Rows found =  ".$stmt->num_rows);

	    //record only if same data found at same epoch / tracker_id
	    if($stmt->num_rows == 0) {

	    	$inregions = "";
	    	if (array_key_exists('inregions', $data) && is_array($data['inregions'])){
	    		$inregions = json_encode($data['inregions']);
	    	} 

			$sql = "INSERT INTO ".$_config['sql_prefix']."locations (accuracy, altitude, battery_level, heading, description, event, latitude, longitude, radius, trig, tracker_id, epoch, vertical_accuracy, velocity, pressure, connection, place_id, osm_id, inregions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		    $stmt = $mysqli->prepare($sql);
		    $stmt->bind_param('iiiissddissiiidsiis', $accuracy, $altitude, $battery_level, $heading, $description, $event, $latitude, $longitude, $radius, $trig, $tracker_id, $epoch, $vertical_accuracy, $velocity, $pressure, $connection, $place_id, $osm_id, $inregions);
			    
		    if ($stmt->execute()){
		    	
		    	# bind parameters (s = string, i = integer, d = double,  b = blob)
			    http_response_code(200);
				//opentracks does not support msg as response $response['msg'] = "OK record saved";
				_log("Insert OK");
			
		    }else{
				http_response_code(500);
				die("Can't write to database : ".$stmt->error);
				//opentracks does not support msg as response $response['msg'] = "Can't write to database";
				_log("Insert KO - Can't write to database : ".$stmt->error);
			}

	    }else{
	    	_log("Duplicate location found for epoc $epoch / tid '$tracker_id' - no insert");
	    }
	    $stmt->close();
	
    }else{
		http_response_code(500);
		die("Can't read from database");
		//opentracks does not support msg as response $response['msg'] = "Can't read from database";
		_log("Can't read from database");
	}
 
} else if ($type == 'steps') {
	//save steps
	//request via {"_type" : "cmd", "action": "reportSteps" 'from'  : unix_epoch(f, delta), 'to' : unix_epoch(t, delta)}
	/**
	{"from":1521927322,"tst":1528475234,"steps":0,"_type":"steps","distance":0,"topic":"owntracks\/owntracks\/73CF224C-5FC4-4324-BCA9-6D7454AEF09B\/step","to":1522010122,"floorsdown":0,"floorsup":0}
	*/
	_log("Insert Steps");

	if (array_key_exists('tst', $data)) $epoch = intval($data['tst']);
	if (array_key_exists('steps', $data)) $steps = intval($data['steps']);
	if (array_key_exists('from', $data)) $from = intval($data['from']);
	if (array_key_exists('to', $data)) $to = intval($data['to']);
	if (array_key_exists('distance', $data)) $distance = floatval($data['distance']);
	if (array_key_exists('floorsdown', $data)) $floorsdown = intval($data['floorsdown']);
	if (array_key_exists('floorsup', $data)) $floorsup = intval($data['floorsup']);
	 

	_log('keys $epoch.$from.$to.$steps.$distance.$floorsdown.$floorsup.$topic[2]');
	_log("keys ".$epoch."|".$from."|".$to."|".$steps."|".$distance."|".$floorsdown."|".$floorsup."|".$topic[2]);

	$sql = "SELECT `to` FROM ".$_config['sql_prefix']."steps WHERE tid=? AND `from`=FROM_UNIXTIME(?)";
	
	_log("Duplicate SQL = ".$sql);
	
	if ($stmt = $mysqli->prepare($sql)){
    	$stmt->bind_param('si',$topic[2],$from);
    	$stmt->execute();
		$stmt->store_result();
		
		_log("Duplicate SQL : Rows found =  ".$stmt->num_rows);

	    //record only if same data found at same epoch / tracker_id
	    if($stmt->num_rows == 0) {




			$sql = "INSERT INTO ".$_config['sql_prefix']."`steps` (`tst`, `from`, `to`, `steps`, `distance`, `floorsdown`, `floorsup`, `tid`) VALUES (FROM_UNIXTIME(?), FROM_UNIXTIME(?), FROM_UNIXTIME(?), ?, ?, ?, ?, ?)";

			_log("Insert steps sql ".$sql);
		    $stmt = $mysqli->prepare($sql);
		    $stmt->bind_param('iiiidiis', $epoch,$from,$to,$steps,$distance,$floorsdown,$floorsup,$topic[2]);
					    
		    if ($stmt->execute()){
		    	
		    	# bind parameters (s = string, i = integer, d = double,  b = blob)
			    http_response_code(200);
				//opentracks does not support msg as response $response['msg'] = "OK record saved";
				_log("Insert Steps OK");
			
		    }else{
				http_response_code(500);
				_log("Insert steps KO - Can't write to database : ".$stmt->error);
				die("Can't write to database : ".$stmt->error);
				//opentracks does not support msg as response $response['msg'] = "Can't write to database";
			}
		} else {
			http_response_code(200);
			_log("do not insert, dublicate from time");
		}
	} else {
		_log("could not connect mysql");
	}
} else {
	http_response_code(204);
	//opentracks does not support msg as response $response['msg'] = "OK type is not location";
	_log("OK type is not location : " . $data['_type']);
}

if ($_config['steps_request']){
	//check if request steps
	$sql = "SELECT * FROM ".$_config['sql_prefix']."steps WHERE tid=? order by `to` DESC LIMIT 1";
	_log("Last To SQL(".$topic[2].") = ".$sql);
	if ($stmt = $mysqli->prepare($sql)){
		$stmt->bind_param('s',$topic[2]);
	    $stmt->execute();
	    $result = $stmt->get_result();

	    $response["_type"] = "cmd";
		$response['action'] ="reportSteps";
		//record only if same data found at same epoch / tracker_id
		if($row = $result->fetch_assoc()) {
			_log("Last to ".$row['to']);
			$to_time = new DateTime($row['to']);
			$to_time = $to_time->getTimestamp();
			$now = new DateTime();
			$now = $now->getTimestamp();
			if( ($now - $to_time ) > $_config['steps_min_time_between_step_request']){
				$response['from'] = $to_time;
				$to_plus_one_day = $to_time + 60*60*24;
				if($to_plus_one_day < $now){
					$response['to'] = $to_plus_one_day;
				} else {
					$response['to'] = $now;
				}
				_log("request steps");
				$responses[] = $response;
			} else {
				_log("Last step entry to new");
			}
		} else {
			$responses[] = $response;
		}
		
	}
}

$outp = json_encode($responses);
_log($outp);
print($outp);

fclose($fp);
?>