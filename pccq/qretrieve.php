<?php
	include("../handlers/initDB.php");
	$con = new myDB;
	
	function formatPriorityNo($no,$station) {
		if($no != '') {
			switch($callStation) {
				case "MED. CLINIC":
					$priority = "M" . str_pad($no,3,'0',STR_PAD_LEFT);
				break;
				case "DENTAL CLINIC":
					$priority = "D" . str_pad($no,3,'0',STR_PAD_LEFT);
				break;
				default:
					$priority = str_pad($no,4,'0',STR_PAD_LEFT);
				break;
			}
		}

		return $priority;

	}


	$myday = date('Y-m-d');
	
	$query = $con->dbquery("select record_id,priority_no,calling_station from pccmain.queueing where picked = 'N' and date_queued = '$myday' order by time_queued asc limit 1;");
	list($rid,$priority_no,$callStation) = $query->fetch_array();
		
	if($priority_no != '') {
		$con->dbquery("update ignore queueing set picked = 'Y', time_picked=now() where record_id = '$rid';");
		$p1 = $con->getArray("SELECT priority_no, calling_station FROM pccmain.queueing WHERE picked = 'Y' AND date_queued = '$myday' ORDER BY time_picked DESC LIMIT 1,1");
		$p2 = $con->getArray("SELECT priority_no, calling_station FROM pccmain.queueing WHERE picked = 'Y' AND date_queued = '$myday' ORDER BY time_picked DESC LIMIT 2,1");
		$p3 = $con->getArray("SELECT priority_no, calling_station FROM pccmain.queueing WHERE picked = 'Y' AND date_queued = '$myday' ORDER BY time_picked DESC LIMIT 3,1");

		echo json_encode(array("callStation"=>$callStation,"priorityNo"=>formatPriorityNo($priority_no,$callStation),"callStation_p1"=>$p1[1],"callNumber_p1"=>formatPriorityNo($p1[0],$p1[1]),"callStation_p2"=>$p2[1],"callNumber_p2"=>formatPriorityNo($p2[0],$p2[1]),"callStation_p3"=>$p3[1],"callNumber_p3"=>formatPriorityNo($p3[0],$p3[1])));
	} else {
		echo json_encode(array("callStation"=>""));
	}

?>