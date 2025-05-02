<?php
	session_start();
	include("handlers/initDB.php");
	$con = new myDB;

	unset($my_arr);
	unset($my_arr_row);

	$term = trim(strip_tags($_GET['term'])); 
	$r = $con->dbquery("SELECT CONCAT('(',LPAD(file_id,6,0),') ',tradename) as employer, file_id,tradename,address,shipping_address from contact_info where (locate('$term',tradename) > 0 or locate('$term',file_id)) and company = '1' limit 30;");
	$my_arr = array();
	$my_arr_row = array();

	if($r) {
		while($row = $r->fetch_array()) {
			$my_arr_row['file_id'] = $row['file_id'];
			$my_arr_row['value'] = $row['tradename'];
			$my_arr_row['address'] = $row['address'];
			$my_arr_row['shipping_address'] = $row['shipping_address'];
			$my_arr_row['label'] = $row['employer'];

			array_push($my_arr,$my_arr_row);
		}
	}

	echo json_encode($my_arr);

?>