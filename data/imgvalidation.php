<?php
	include("../handlers/_generics.php");
	$con = new _init;


	$data = array();
	$datares = $con->dbquery("SELECT record_id AS id, LPAD(a.so_no,6,0) AS sono, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, b.patient_name AS pname,FLOOR(ROUND(DATEDIFF(b.so_date,c.birthdate) / 364.25,2)) AS age, IF(c.gender='M','Male','Female') AS gender,a.procedure,d.sample_type,serialno,DATE_FORMAT(CONCAT(extractdate,' ',extractime),'%m/%d/%Y %h:%i %p') AS tstamp,e.fullname AS createdby, DATE_FORMAT(a.created_on,'%m/%d/%Y %h:%i %p') AS createdon, a.code, g.samplestatus AS `status` FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_sampletype d ON a.sampletype = d.id LEFT JOIN user_info e ON a.created_by = e.emp_id LEFT JOIN services_master f ON a.code = f.code LEFT JOIN options_samplestatus g ON a.status = g.id WHERE f.category = '2' AND a.code LIKE '%O%' AND a.extractdate > '2024-06-01';");
	
    while($row = $datares->fetch_array(MYSQLI_ASSOC)){

        $data[] = array_map('utf8_encode',$row);

	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);

?>