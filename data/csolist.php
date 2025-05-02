<?php
	session_start();
	include("../handlers/initDB.php");
	$con = new myDB;

	$data = array();
	
	$datares = $con->dbquery("SELECT LPAD(cso_no,6,0) AS cso_no, DATE_FORMAT(cso_date,'%m/%d/%Y') AS csdate, CONCAT(DATE_FORMAT(`from`,'%m/%d/%Y'),' - ',DATE_FORMAT(`until`,'%m/%d/%Y')) AS duration, cso_type, customer_name AS cname, company, remarks, format(amount,2) as amount, `status` FROM cso_header WHERE branch = '$_SESSION[branchid]';");
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);

?>