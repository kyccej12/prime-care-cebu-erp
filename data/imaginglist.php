<?php
	include("../handlers/_generics.php");
	$con = new _init;
	$today = date('Y-m-d');

	switch($_REQUEST['type']) {
		case "1":
			$f = " and b.so_date = '$today' and extracted = 'N' ";
		break;
		case "2":
			$f = " and b.so_date < '$today' and extracted = 'N' ";
		break;
		case "3":
			$f = " and extracted = 'Y' ";
		break;
		default:
			$f = " and extracted = 'N' ";
		break;
	}

	$data = array();
	$datares = $con->dbquery("SELECT a.record_id AS id, LPAD(b.priority_no,6,0) AS priority, LPAD(a.so_no,6,0) AS so, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, b.patient_name, IF(c.gender='M','Male','Female') AS gender, DATE_FORMAT(c.birthdate,'%m/%d/%Y') AS birthdate, FLOOR(ROUND(DATEDIFF(b.so_date,c.birthdate) / 364.25,2)) AS age, a.code, IF(a.parent_code!=a.code,CONCAT(f.description,' :: ',a.procedure),a.procedure) AS `procedure`, a.physician,a.parent_code FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN services_master e ON a.code = e.code LEFT JOIN services_master f ON a.parent_code = f.code WHERE e.category = '2' $f;");

    while($row = $datares->fetch_array(MYSQLI_ASSOC)){
        $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);

?>