<?php
	include("handlers/initDB.php");
	session_start();

	$con = new myDB;
	unset($my_arr);
	unset($my_arr_row);

	$terms = trim(strip_tags($_GET['term'])); 
	$r = $con->dbquery("SELECT DISTINCT referred_by FROM so_header WHERE LOCATE('synchrony',referred_by) > 0 ORDER BY referred_by;");
	
	$my_arr = array();
	$my_arr_row = array();

	if($r) {
		while($row = $r->fetch_array()) {
			$my_arr_row['label'] = $row[0];
			$my_arr_row['value'] = $row[0];
			array_push($my_arr,$my_arr_row);
		}
	}

	echo json_encode($my_arr);

?>