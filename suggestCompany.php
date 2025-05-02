<?php
	include("handlers/initDB.php");
	session_start();

	$con = new myDB;
	unset($my_arr);
	unset($my_arr_row);

	$terms = trim(strip_tags($_GET['term'])); 
	$r = $con->dbquery("SELECT tradename, `address`, brgy, city, province, type  FROM contact_info WHERE LOCATE('$terms',tradename) > 0;");
	
	$my_arr = array();
	$my_arr_row = array();

	if($r) {
		while($row = $r->fetch_array()) {
	
			$myaddress = "";
			if($row['type'] != "7") {
				list($brgy) = $con->getArray("SELECT brgyDesc FROM options_brgy WHERE brgyCode = '$row[brgy]';");
				list($ct) = $con->getArray("SELECT citymunDesc FROM options_cities WHERE cityMunCode = '$row[city]';");
				list($prov) = $con->getArray("SELECT provDesc FROM options_provinces WHERE provCode = '$row[province]';");
			
				if($row['address'] != '') { $myaddress.=$row['address'].", "; }
				if($brgy != "") { $myaddress.=$brgy.", "; }
				if($ct != "") { $myaddress.=$ct.", "; }
				if($prov != "")  { $myaddress.=$prov.", "; }
				$myaddress = substr($myaddress,0,-2);
			} else {
				$myaddress = $row['billing_address'];
			}

			$cname = html_entity_decode($row['tradename']);
			$label = html_entity_decode($row['tradename']);
			$address = html_entity_decode($myaddress);
			
			$my_arr_row['address'] = $address;
			$my_arr_row['label'] = $label;
			$my_arr_row['value'] = $cname;
			array_push($my_arr,$my_arr_row);
		}
	}

	echo json_encode($my_arr);

?>