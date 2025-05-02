<?php
    session_start();
	require_once 'handlers/_generics.php';
	$con = new _init;
    $bid = $_SESSION['branchid'];

    $discount = 0;
    $adue = 0;
    $disctype = '';
    $discpercent = 0;


	function updateAmount($cso_no) {
		global $con;
		list($gross) = $con->getArray("select ifnull(sum(amount),0) from cso_details where cso_no = '$cso_no' and branch = '$_SESSION[branchid]';");
		$con->dbquery("update cso_header set amount = '$gross', balance = '$gross' where cso_no = '$cso_no' and branch = '$_SESSION[branchid]';");
	}

    switch($_REQUEST['mod']) {
        case "saveHeader":

            if($_POST['cso_no'] != '') {
                $queryString = "UPDATE IGNORE cso_header set cso_date = '".$con->formatDate($_POST['cso_date'])."', cso_type = '$_POST[cso_type]', customer_code = '$_POST[cid]', customer_name = '".$con->escapeString(htmlentities($_POST['cname']))."', customer_address = '".$con->escapeString(htmlentities($_POST['caddr']))."',location = '".$con->escapeString(htmlentities($_POST['location']))."',company = '".$con->escapeString(htmlentities($_POST['company']))."',contact_person = '".$con->escapeString(htmlentities($_POST['contact_person']))."',contact_no = '$_POST[contact_no]',email_add = '$_POST[email_add]',remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."',terms = '$_POST[terms]',po_no = '$_POST[po_no]',po_date = '".$con->formatDate($_POST['po_date'])."', `from` = '".$con->formatDate($_POST['from'])."',`until` = '".$con->formatDate($_POST['until'])."', updated_by = '$_SESSION[userid]', updated_on = NOW() where cso_no = '$_POST[cso_no]' and branch = '$bid';";
                $csono = $_POST['cso_no'];
            } else {
                list($csono) = $con->getArray("select ifnull(max(cso_no),0)+1 from cso_header where branch = '$bid';"); 
                $queryString = "INSERT IGNORE INTO cso_header (cso_no,branch,cso_date,cso_type,customer_code,customer_name,customer_address,location,company,contact_person,contact_no,email_add,remarks,terms,po_no,po_date,`from`,`until`,trace_no,created_by,created_on) VALUES ('$csono','$bid','".$con->formatDate($_POST['cso_date'])."','$_POST[cso_type]','$_POST[cid]','".$con->escapeString(htmlentities($_POST['cname']))."','".$con->escapeString(htmlentities($_POST['caddr']))."','".$con->escapeString(htmlentities($_POST['location']))."','".$con->escapeString(htmlentities($_POST['company']))."','".$con->escapeString(htmlentities($_POST['contact_person']))."','$_POST[contact_no]','$_POST[email_add]','".$con->escapeString(htmlentities($_POST['remarks']))."','$_POST[terms]','$_POST[po_no]','".$con->formatDate($_POST['po_date'])."','".$con->formatDate($_POST['from'])."','".$con->formatDate($_POST['until'])."','$_POST[trace_no]','$_SESSION[userid]',NOW());";
            }


            $con->dbquery($queryString);
            echo str_pad($csono,6,'0',STR_PAD_LEFT);
        break;

        case "addItem":
            $con->dbquery("INSERT IGNORE INTO cso_details (cso_no,branch,pid,pname,with_loa,hmo_card_no,`code`,`description`,unit_price,amount,trace_no) VALUES ('$_POST[cso_no]','$bid','$_POST[pid]','".$con->escapeString(htmlentities($_POST['pname']))."','$_POST[loa]','$_POST[hmo]','$_POST[item]','".$con->escapeString(htmlentities($_POST['description']))."','".$con->formatDigit($_POST['price'])."','".$con->formatDigit($_POST['price'])."','$_POST[trace_no]');");
            updateAmount($_POST['cso_no']);
        break;

        case "retrieveLine":
            $e = $con->getArray("select *, lpad(pid,6,0) as pd from cso_details where line_id = '$_POST[lid]';");
            echo json_encode(array("lid"=>$e['line_id'],"pid"=>$e['pd'],"pname"=>html_entity_decode($e['pname']),"code"=>$e['code'],"description"=>$e['description'],"unit_price"=>number_format($e['unit_price'],2),"loa"=>$e['with_loa'],"hml"=>"hmo_card_no"));
        break;

        case "updateItem":
            $con->dbquery("UPDATE IGNORE cso_details set pid = '$_POST[pid]', pname = '".$con->escapeString(htmlentities($_POST['pname']))."', with_loa = '$_POST[loa]', hmo_card_no = '$_POST[hmo]',`code` = '$_POST[item]',`description` = '".$con->escapeString(htmlentities($_POST['description']))."',unit_price = '".$con->formatDigit($_POST['price'])."',amount = '".$con->formatDigit($_POST['price'])."' where line_id = '$_POST[lid]';");
            updateAmount($_POST['cso_no']);
        break;

        case "deleteLine":
            $con->deleteRow($table="cso_details",$arg = "line_id='$_POST[lid]'");
            updateAmount($_POST['cso_no']);
        break;

        case "check4print":
			list($a) = $con->getArray("select count(*) from cso_header where cso_no = '$_POST[cso_no]' and branch = '$bid';");
			list($b) = $con->getArray("select count(*) from cso_details where cso_no = '$_POST[cso_no]' and branch = '$bid';");
			
			if($a == 0 && $b > 0) { echo "head"; }
			if($b == 0 && $a > 0) { echo "det"; }
			if($a == 0 && $b == 0) { echo "both"; }
			if($a > 0 && $b > 0) { echo "noerror"; }
		break;

        case "finalize":
            $con->dbquery("update ignore cso_header set `status` = 'Finalized', updated_by = '$_SESSION[userid]', updated_on = now() where cso_no = '$_POST[cso_no]' and branch = '$bid';");
            updateAmount($_POST['cso_no']);

        break;

        case "checkBilled":
            if($con->countRows("select cso_no from cso_header where cso_no = '$_POST[cso_no]' and branch = '$bid' and (billed = 'Y' or paid ='Y');") > 0) {
                echo "processed";
            }
        break;

        case "reopen":
            $con->dbquery("update cso_header set status = 'Active', updated_by = '$_SESSION[userid]', updated_on = now() where cso_no = '$_POST[cso_no]' and branch = '$bid';");
        break;
        
        case "cancel":
            $con->dbquery("update ignore cso_header set `status` = 'Cancelled', updated_by = '$_SESSION[userid]', updated_on = now() where cso_no = '$_POST[cso_no]' and branch = '$bid';");
        break; 

        case "retrieve":
            $data = array();
			$srrd = $con->dbquery("select line_id as id, lpad(pid,6,0) as pid, pname, b.gender, date_format(b.birthdate,'%m/%d/%Y') as birthdate, `code`, description, amount from cso_details a left join patient_info b on a.pid = b.patient_id WHERE trace_no = '$_REQUEST[trace_no]';");
			while($row = $srrd->fetch_array()) {
				$data[] = array_map('utf8_encode',$row);
			}
			$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
			echo json_encode($results);	
        break;

        case "verify":
            $con->dbquery("update cso_header set cstatus = '12', verified = 'Y', verified_by = '$_SESSION[userid]', verified_on = now() where cso_no = '$_POST[cso_no]' and branch = '$bid';");
        
            /* Send Lab Request Pending Extraction */
            $so = $con->dbquery("SELECT a.cso_no AS so, b.code as parent_code, b.code, b.description AS `procedure`, a.physician, d.sample_type, d.container_type FROM cso_header a LEFT JOIN cso_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id LEFT JOIN services_master d ON b.code = d.code WHERE a.status = 'Finalized' AND d.with_subtests = 'N' AND d.category IN ('1','2') AND a.cso_no = '$_POST[cso_no]' and d.description not like '%PCR%' UNION SELECT a.cso_no AS so, e.parent as parent_code, e.code, e.description AS `procedure`, a.physician, f.sample_type, f.container_type FROM cso_header a LEFT JOIN cso_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id LEFT JOIN services_master d ON b.code = d.code LEFT JOIN services_subtests e ON b.code = e.parent LEFT JOIN services_master f ON e.code = f.code WHERE a.status = 'Finalized' AND d.with_subtests = 'Y' AND f.category IN ('1','2') AND a.cso_no = '$_POST[cso_no]' and d.description not like '%PCR%';");
            while($eRow = $so->fetch_array()) {
                list($labCount) = $con->getArray("select count(*) from lab_samples where cso_no = '$eRow[so]' and parent_code = '$eRow[parent_code]' and code = '$eRow[code]';");
                if($labCount == 0) {
                    $con->dbquery("INSERT IGNORE INTO lab_samples (branch,cso_no,parent_code,code,`procedure`,sampletype,samplecontainer,physician,created_by,created_on) values ('$bid','$eRow[so]','$eRow[parent_code]','$eRow[code]','$eRow[procedure]','$eRow[sample_type]','$eRow[container_type]','$eRow[physician]','$uid',now());");
                }
            }

            /* Send Request to Nursing Station for PEME */
            $gQuery = $con->dbquery("SELECT a.priority_no as prio, a.cso_no, a.so_date, b.code as parentcode, b.code, b.description AS `procedure`, a.patient_id AS pid, c.birthplace, c.occupation AS occu, c. employer AS compname, c.mobile_no AS contactno FROM cso_header a LEFT JOIN cso_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id WHERE a.cso_no = '$_POST[cso_no]' AND b.code IN ('O009') AND a.status IN (2,4,12) UNION ALL SELECT a.priority_no as prio, a.cso_no, a.so_date, b.code as parentcode, e.code, e.description AS `procedure`, a.patient_id AS pid, c.birthplace, c.occupation AS occu, c. employer AS compname, c.mobile_no AS contactno FROM cso_header a LEFT JOIN cso_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id LEFT JOIN services_master d ON b.code = d.code LEFT JOIN services_subtests e ON e.parent = d.code  WHERE a.cso_no = '$_POST[cso_no]' AND e.code IN ('O009') AND a.status IN (2,4,12) AND  d.with_subtests = 'Y';");
            while($hRow = $gQuery->fetch_array()) {
                $con->dbquery("INSERT IGNORE INTO peme (cso_no,branch,so_date,prio,parentcode,code,`procedure`,pid,pob,occu,compname,contactno) values ('$hRow[cso_no]','$bid','$hRow[so_date]','$hRow[prio]','$hRow[parentcode]','$hRow[code]','$hRow[procedure]','$hRow[pid]','" . $con->escapeString(htmlentities($hRow['birthplace'])) . "','$hRow[occu]','" . $con->escapeString(htmlentities($hRow['compname'])) . "','$hRow[contactno]');");

            }
        
        break;

        case "getTotals":
            list($gross) = $con->getArray("select ifnull(sum(amount),0) from cso_details where cso_no = '$_POST[cso_no]' and branch = '$bid';");
            echo json_encode(array("gross"=>number_format($gross,2)));
        break;

    }



?>