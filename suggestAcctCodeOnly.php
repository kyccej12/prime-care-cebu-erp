<?php
	session_start();
	include("handlers/initDB.php");
	$con = new myDB;

	$my_arr = array();
	$my_arr_row = array();
	
	$term = trim(strip_tags($_GET['term'])); 
	$r = $con->dbquery("select acct_code, concat(description, ' [',acct_code,']') as description from acctg_accounts where file_status != 'Deleted' and parent != 'Y' and description like '%$term%' LIMIT 25");
	
	
	if($r) {
		while($row = $r->fetch_array()) {
			$my_arr_row['value'] = $row['acct_code'];
			$my_arr_row['label'] = $row['description'];
			array_push($my_arr,$my_arr_row);
		}
	}

	echo json_encode($my_arr);

?>