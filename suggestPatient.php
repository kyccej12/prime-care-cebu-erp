<?php
	session_start();
    include("handlers/initDB.php");
	$con = new myDB;

	unset($my_arr);
	unset($my_arr_row);

	//$term = trim(strip_tags($_GET['term'])); 
	
	$term = trim(htmlentities($_GET['term']));
	
	
	$r = $con->dbquery("SELECT * FROM (SELECT CONCAT('[',LPAD(patient_id,6,0),'] ',lname,', ',fname,', ',mname,' ',suffix) AS label,  CONCAT(lname,', ',fname,', ',mname,' ',suffix) AS `name`, LPAD(patient_id,6,'0') AS cid, street, brgy, city, province, pwd, DATE_FORMAT(birthdate,'%m/%d/%Y') AS bday, gender, mobile_no AS contactno, email_add, employer, hmo_provider, hmo_no, date_format(hmo_expiry,'%m/%d/%Y') as hmo_expiry FROM patient_info) a WHERE LOCATE('$term',`name`) > 0;");
	//echo "SELECT * FROM (SELECT CONCAT('[',LPAD(patient_id,6,0),'] ',lname,', ',fname,', ',mname,' ',suffix) AS label,  CONCAT(lname,', ',fname,', ',mname,' ',suffix) AS `name`, LPAD(patient_id,6,'0') AS cid, street, brgy, city, province, pwd, DATE_FORMAT(birthdate,'%m/%d/%Y') AS bday, gender, mobile_no AS contactno, email_add FROM patient_info) a WHERE LOCATE('$term',`name`) > 0";
	
	$my_arr = array();
	$my_arr_row = array();

	if($r) {
		while($row = $r->fetch_array()) {
            $myaddress = '';
            list($brgy) = $con->getArray("SELECT brgyDesc FROM options_brgy WHERE brgyCode = '$row[brgy]';");
			list($ct) = $con->getArray("SELECT citymunDesc FROM options_cities WHERE cityMunCode = '$row[city]';");
			list($prov) = $con->getArray("SELECT provDesc FROM options_provinces WHERE provCode = '$row[province]';");
		
			if($row['street'] != '') { $myaddress.=$row['street'].", "; }
			if($brgy != "") { $myaddress.=$brgy.", "; }
			if($ct != "") { $myaddress.=$ct.", "; }
			if($prov != "")  { $myaddress.=$prov.", "; }
			$myaddress = substr($myaddress,0,-2);

            $name = html_entity_decode($row['name']);
			$label = html_entity_decode($row['label']);
            $addr = html_entity_decode($myaddress);
			$er = html_entity_decode($row['employer']);

			$my_arr_row['pwd'] = $row['pwd'];
			$my_arr_row['bday'] = $row['bday'];
			$my_arr_row['gender'] = $row['gender'];
			$my_arr_row['contactno'] = $row['contactno'];
			$my_arr_row['email'] = $row['email_add'];
			$my_arr_row['hmo_provider'] = html_entity_decode($row['hmo_provider']);
			$my_arr_row['hmo_no'] = $row['hmo_no'];
			$my_arr_row['hmo_expiry'] = $row['hmo_expiry'];
			$my_arr_row['er'] = $er;
            $my_arr_row['name'] = $name;
			$my_arr_row['addr'] = $addr;
			$my_arr_row['label'] = $label;
			$my_arr_row['value'] = $row['cid'];

			array_push($my_arr,$my_arr_row);
		}
	}

	echo json_encode($my_arr);
?>