<?php
	session_start();
	include("../handlers/initDB.php");
	$con = new myDB;

	$data = array();

	$datares = $con->dbquery("SELECT doc_no, LPAD(doc_no,6,'0') AS dno, or_no, '' as so, DATE_FORMAT(doc_date,'%m/%d/%Y') AS d8, IF(customer_code = '0', 'CHARGED TO PATIENT', customer_name) AS cname, b.cashtype, remarks, amount_due, amount_paid, `status`, '' as `tag` FROM or_header a LEFT JOIN options_cashtype b ON a.cashtype = b.id WHERE branch = '$_SESSION[branchid]';");
	while($row = $datares->fetch_array()){
		$tag = ''; $so = '';
		$dQuery = $con->dbquery("SELECT DISTINCT so_no, soa_no, pname FROM or_details WHERE doc_no = '$row[doc_no]';");	
		while($dRow = $dQuery->fetch_array()) {
			$tag .= $dRow['so_no'] . "," . $dRow['pname'] . ",";
			if($dRow['soa_no'] != '') { $tag .= $dRow['soa_no'] . ","; }
			
			if($dRow['so_no'] != '') {
				$so .= "<a href=\"#\" onclick=\"javascript: parent.viewSO('$dRow[so_no]');\" style=\"text-decoration: none; color: black;\" title=\"Click to View Sales Order Details\">$dRow[so_no]</a><br>";
			}
		
		}
		
		$row['tag'] = $tag;
		$row['so'] = $so;

		$data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
?>