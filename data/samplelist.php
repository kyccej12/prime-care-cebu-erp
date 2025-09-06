<?php
	include("../handlers/_generics.php");
	$con = new _init;

	// $cutoff = '2023-01-01';
	$today = date('Y-m-d');

	list($cutoff) = $con->getArray("SELECT DATE_SUB('$today',INTERVAL 3 MONTH);");

	$data = array();
	$datares = $con->dbquery("SELECT record_id AS id, LPAD(a.so_no,6,0) AS sono, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, b.patient_name AS pname, YEAR(b.so_date) - YEAR(c.birthdate) AS age, IF(c.gender='M','Male','Female') AS gender,a.code,a.procedure,d.sample_type,serialno,DATE_FORMAT(CONCAT(extractdate,' ',extractime),'%m/%d/%Y %h:%i %p') AS tstamp,e.samplestatus AS `status`,a.status AS ostat FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_sampletype d ON a.sampletype = d.id LEFT JOIN options_samplestatus e ON a.status = e.id LEFT JOIN services_master f ON a.code = f.code WHERE f.category = '1' and b.so_date > '$cutoff' and a.status = '1' AND a.extracted = 'Y';");
	
    while($row = $datares->fetch_array(MYSQLI_ASSOC)){

        $data[] = array_map('utf8_encode',$row);

	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);

?>