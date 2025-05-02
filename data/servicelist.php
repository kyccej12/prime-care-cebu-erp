<?php
	include("../includes/dbUSEi.php");
	
	$data = array();
	//$datares = $con->query("SELECT a.id, a.code, description, b.category, c.subcategory, unit_price, e.sample_type, d.type as container_type, result_type, CONCAT(ROUND(result_tat),' hrs.') as result_tat, if(with_subtests='Y','Yes','No') as wtest  FROM services_master a LEFT JOIN options_servicecat b ON a.category = b.id LEFT JOIN options_servicesubcat c ON a.subcategory = c.id LEFT JOIN options_containers d on a.container_type = d.id LEFT JOIN options_sampletype e on a.sample_type = e.id WHERE a.file_status != 'Deleted';");
	$datares = $con->query("SELECT a.id, a.code, description, b.category, c.subcategory, unit_price, e.sample_type, d.type AS container_type, result_type, CONCAT(ROUND(result_tat),' hrs.') AS result_tat, IF(with_subtests='Y','Yes','No') AS wtest, f.fullname AS updated_by, DATE_FORMAT(a.updated_on,'%m/%d/%Y %h:%i %p') AS updated_on FROM services_master a LEFT JOIN options_servicecat b ON a.category = b.id LEFT JOIN options_servicesubcat c ON a.subcategory = c.id LEFT JOIN options_containers d ON a.container_type = d.id LEFT JOIN options_sampletype e ON a.sample_type = e.id LEFT JOIN user_info f ON a.updated_by = f.emp_id WHERE a.file_status != 'Deleted';");
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);

?>