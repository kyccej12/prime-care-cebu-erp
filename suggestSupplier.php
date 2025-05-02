<?php
	session_start();
	include("handlers/initDB.php");
	$con = new myDB;
	
	unset($my_arr);
	unset($my_arr_row);

	$term = trim(strip_tags($_GET['term'])); 
	$r = $con->dbquery("SELECT CONCAT('(',LPAD(file_id,6,0),') ',tradename) AS label, LPAD(file_id,6,0) AS cid,tradename,CONCAT(`address`,', ',d.brgyDesc,', ',b.citymunDesc,', ',c.provDesc) AS address,terms,price_level FROM contact_info a LEFT JOIN options_cities b ON a.city = b.citymunCode LEFT JOIN options_provinces c ON a.province = c.provCode LEFT JOIN options_brgy d ON a.brgy = d.brgyCode WHERE LOCATE('$term',tradename) > 0;");
	
	$my_arr = array();
	$my_arr_row = array();

	if($r) {
		while($row = $r->fetch_array()) {

			$patterns = array();
			$patterns[0] = '/&Ntilde;/';
			$patterns[1] = '/&ntilde;/';
			$replacements = array();
			$replacements[0] = 'Ñ';
			$replacements[1] = 'ñ';

			$label = preg_replace($patterns, $replacements, $row['label']);
			$cname = preg_replace($patterns, $replacements, $row['tradename']);

			$my_arr_row['cid'] = $row['cid'];
			$my_arr_row['cname'] = $cname;
			$my_arr_row['addr'] = $row['address'];
			$my_arr_row['terms'] = $row['terms'];
			$my_arr_row['label'] = $label;

			array_push($my_arr,$my_arr_row);
		}
	}

	echo json_encode($my_arr);
?>