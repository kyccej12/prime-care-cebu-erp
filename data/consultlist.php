<?php
	session_start();
	include("../handlers/_generics.php");
	$con = new _init;
	$searchString = '';

	if($_SESSION['so_no'] != '') { $searchString .= " and so_no = '$_SESSION[so_no]' "; }
	if(isset($_SESSION['clinic_no']) && $_SESSION['clinic_no'] != '') { $searchString .= " and clinic = '$_SESSION[clinic_no]' "; }
	
	
	$data = array();
	$datares = $con->dbquery("SELECT trans_id, so_no as so, priority_no as prio, gender, '' as age, compname, pid, DATE_FORMAT(so_date,'%m/%d/%Y') AS tdate, DATE_FORMAT(trans_time, '%h:%i %p') as ttime, pname, `procedure`, physician, `status`, DATE_FORMAT(birthdate,'%m/%d/%Y') AS bday, birthdate, clinic FROM consultation_form where 1=1 $searchString;");
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){

	$a = $con->getArray("SELECT *, DATE_FORMAT(birthdate,'%m/%d/%Y') AS bday FROM patient_info WHERE patient_id = '$row[pid]';");
	
	$today = date('Y-m-d');
	list($age) = $con->getArray("SELECT FLOOR(ROUND(DATEDIFF('$today','$a[birthdate]') / 364.25,2));");
	$row['age'] = $age;

	$row['compname'] = html_entity_decode($row['compname']);
	$row['pname'] = html_entity_decode($row['pname']);


	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
?>