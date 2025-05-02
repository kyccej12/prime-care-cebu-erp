<?php
	include("../handlers/_generics.php");
	ini_set('max_execution_time',0);
	ini_set('memory_limit',-1);

	$con = new _init;
	$today = date('Y-m-d');

	list($oneMos) = $con->getArray("SELECT DATE_SUB('$today', INTERVAL 1 MONTH);");
	list($thisWeek) = $con->getArray("SELECT DATE_SUB('$today', INTERVAL 7 DAY);");
	list($lastWeek) = $con->getArray("SELECT DATE_SUB('$today', INTERVAL 14 DAY);");

	if($_REQUEST['displayPending'] == 'Y') {
		$f = " and b.so_date = '$today' ";
	}

	switch($_REQUEST['type']) {
		case "1":
			$f = " and b.so_date >= '$thisWeek' and b.so_date != '$today' ";
		break;
		case "2":
			$f = " and b.so_date >= '$lastWeek' and b.so_date < '$thisWeek' ";
		break;
		case "3":
			$f = " and b.so_date >= '$oneMos' and b.so_date < '$lastWeek' ";
		break;
		case "4":
			$f = " and a.created_on > '2022-10-01' ";
		break;
		default:
			$f = " and b.so_date = '$today' ";
		break;
	}

	$data = array();
	$datares = $con->dbquery("SELECT record_id AS id, LPAD(a.so_no,6,0) AS sono, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, '' as result_date, b.patient_name AS pname, FLOOR(DATEDIFF(b.so_date,c.birthdate)/364.25) AS age, IF(c.gender='M','Male','Female') AS gender, c.employer, a.procedure, lotno, DATE_FORMAT(CONCAT(extractdate,' ',extractime),'%m/%d/%Y %h:%i %p') AS tstamp, e.samplestatus AS `status`,a.status AS ostat, a.serialno, a.code FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_sampletype d ON a.sampletype = d.id LEFT JOIN options_samplestatus e ON a.status = e.id LEFT JOIN services_master f ON a.code = f.code WHERE f.category = '2' AND a.extracted = 'Y' $f;");
	
    while($row = $datares->fetch_array()){
		list($rdate) = $con->getArray("select date_format(result_date,'%m/%d/%Y') from lab_descriptive where serialno = '$row[serialno]';");
		$row['result_date'] = $rdate;
        $data[] = array_map('utf8_encode',$row);
		$rdate = '';
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);

?>