<?php
	//ini_set("display_errors","On");
	session_start();
	ini_set('max_execution_time',0);
	ini_set('memory_limit',-1);

	include("../handlers/_generics.php");
	$con = new _init;
	$today = date('Y-m-d');

	switch($_REQUEST['displayType']) {
		case "1":
			list($dtf) = $con->getArray("SELECT DATE_SUB('$today',INTERVAL 7 DAY);");
			$f = " and b.so_date between '$dtf' and '$today' ";
		break;
		case "2":
			list($dtf) = $con->getArray("SELECT DATE_SUB('$today',INTERVAL 30 DAY);");
			$f = " and b.so_date between '$dtf' and '$today' ";
		break;
		case "3":
			$f = " and b.so_date between '2023-01-01' and '$today' ";
		break;
		default:
			$f = "and b.so_date = '$today'";
		break;

	}


	$data = array();
	//$datares = $con->dbquery("SELECT record_id AS id, LPAD(a.so_no,6,0) AS sono, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, b.patient_name AS pname, FLOOR(DATEDIFF(b.so_date,c.birthdate)/364.25) AS age, c.gender, c.employer, a.procedure, IF(a.released='Y','Yes','No') AS released, d.fullname AS rby, IF(release_date IS NOT NULL,DATE_FORMAT(release_date,'%m/%d/%Y'),'') AS rdate, released_to,a.code, a.serialno, a.so_no as xso FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN user_info d ON a.released_by = d.emp_id WHERE a.status = '4' AND a.branch = '$_SESSION[branchid]' $f;");
	$datares = $con->dbquery("SELECT record_id AS id, LPAD(a.so_no,6,0) AS sono, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, b.patient_name AS pname, FLOOR(DATEDIFF(b.so_date,c.birthdate)/364.25) AS age, c.gender, c.employer, a.procedure, IF(a.released='Y','Yes','No') AS released, IF(release_date IS NOT NULL,DATE_FORMAT(release_date,'%m/%d/%Y'),'') AS rdate, released_to, e.fullname AS rby, released_by,a.code, a.serialno, a.so_no AS xso, d.fullname AS validated_by, DATE_FORMAT(a.validated_on,'%m/%d/%Y %h:%i %p') AS validated_on FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN user_info d ON a.validated_by = d.emp_id LEFT JOIN user_info e ON a.released_by = e.emp_id WHERE a.status = '4' AND a.branch = '$_SESSION[branchid]' $f;");


    while($row = $datares->fetch_array()){
		$row['sono'] = "<a onclick=\"#\" href=\"javascript: parent.viewSO('$row[sono]');\" style=\"text-decoration: none;\">$row[sono]</a>";
        $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);

?>