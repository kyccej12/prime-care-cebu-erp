<?php
    session_start();
	require_once 'handlers/_generics.php';
	$con = new _init;
    $bid = $_SESSION['branchid'];

    $discount = 0;
    $adue = 0;
    $disctype = '';
    $discpercent = 0;


	function updateAmount($so_no) {
		global $con;
		list($gross,$discount,$adue) = $con->getArray("select ifnull(sum(amount),0),ifnull(sum(discount),0),ifnull(sum(amount_due),0) from so_details where so_no = '$so_no' and branch = '$_SESSION[branchid]';");
		$con->dbquery("update so_header set gross = '$gross', discount = '$discount', amount = '$adue' where so_no = '$so_no' and branch = '$_SESSION[branchid]';");
	}

    function checkSeniorDiscount($pid,$id,$ispwd,$sodate,$amount) {
        global $con;
        global $discount;
        global $disctype;
        global $discpercent;

        if($id != '') {
            if($ispwd == 'Y') {
                $discount = ROUND(($amount/1.12) * 0.20,2);
                $disctype = 'PWD';
                $discpercent = 20;
            } else {
                list($age) = $con->getArray("SELECT FLOOR(ROUND(DATEDIFF('".$con->formatDate($sodate)."',birthdate) / 364.25,2)) from patient_info where patient_id = '$pid';");
                if($age >= 60) {
                    $discount = ROUND(($amount/1.12) * 0.20,2);
                     $disctype = 'SC';
                     $discpercent = 20;
                }
            }
        }
    }

    switch($_REQUEST['mod']) {
        case "saveHeader":

            if($_POST['terms'] != 0) { $sostat = '10'; } else { $sostat = '1'; }

            if($_POST['so_no'] != '') {
                $queryString = "UPDATE IGNORE so_header set so_date = '".$con->formatDate($_POST['so_date'])."',patient_id = '$_POST[pid]',patient_name = '".$con->escapeString(htmlentities($_POST['pname']))."',patient_address = '".$con->escapeString(htmlentities($_POST['paddr']))."',customer_code = '$_POST[cid]',customer_name = '".$con->escapeString(htmlentities($_POST['cname']))."',customer_address = '".$con->escapeString(htmlentities($_POST['caddr']))."',terms = '$_POST[terms]', referred_by = '".$con->escapeString(htmlentities($_POST['referred_by']))."', hmo_provider = '" . htmlentities($_POST['hmo_provider']) . "', hmo_card_no = '$_POST[hmo_no]',hmo_card_expiry = '".$con->formatDate($_POST['card_expiry'])."', is_pwd='$_POST[is_pwd]', scpwd_id = '$_POST[sc_id]',with_loa = '$_POST[with_loa]',loa_date = '".$con->formatDate($_POST['loa_date'])."', patient_stat = '$_POST[pstat]', physician = '".$con->escapeString(htmlentities($_POST['physician']))."',cstatus = '$sostat', delivery_type = '$_POST[dtype]', email_add = '$_POST[email]', remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."', updated_by = '$_SESSION[userid]' , updated_on = now() where so_no = '$_POST[so_no]';";
                $sono = $_POST['so_no'];
            } else {
                list($sono) = $con->getArray("select ifnull(max(so_no),0)+1 from so_header where branch = '$bid';"); 
                $queryString = "INSERT IGNORE INTO so_header (so_no,branch,so_date,priority_no,patient_id,patient_name,patient_address,customer_code,customer_name,customer_address,terms,referred_by,hmo_provider,hmo_card_no,hmo_card_expiry,is_pwd,scpwd_id,with_loa,loa_date,patient_stat,physician,cstatus,delivery_type,email_add,remarks,trace_no,created_by,created_on) VALUES ('$sono','$bid','".$con->formatDate($_POST['so_date'])."','$_POST[pri_no]','$_POST[pid]','".$con->escapeString(htmlentities($_POST['pname']))."','".$con->escapeString(htmlentities($_POST['paddr']))."','$_POST[cid]','".$con->escapeString(htmlentities($_POST['cname']))."','".$con->escapeString(htmlentities($_POST['caddr']))."','$_POST[terms]','".$con->escapeString(htmlentities($_POST['referred_by']))."','" . htmlentities($_POST['hmo_provider']) . "','$_POST[hmo_no]','".$con->formatDate($_POST['card_expiry'])."','$_POST[is_pwd]','$_POST[sc_id]','$_POST[with_loa]','".$con->formatDate($_POST['loa_date'])."','$_POST[pstat]','".$con->escapeString(htmlentities($_POST['physician']))."','$sostat','$_POST[dtype]','$_POST[email]','".$con->escapeString(htmlentities($_POST['remarks']))."','$_POST[trace_no]','$_SESSION[userid]',now());";
            }
            $con->dbquery($queryString);
            echo str_pad($sono,6,'0',STR_PAD_LEFT);
        break;

        case "saveHeader2":
            list($count) = $con->getArray("select count(*) from so_header where branch = '$bid' and so_no = '$_POST[so_no]';");
            if($count > 0) {
                $queryString = "UPDATE IGNORE so_header set so_date = '".$con->formatDate($_POST['so_date'])."',patient_id = '$_POST[pid]',patient_name = '".$con->escapeString(htmlentities($_POST['pname']))."',patient_address = '".$con->escapeString(htmlentities($_POST['paddr']))."',customer_code = '$_POST[cid]',customer_name = '".$con->escapeString(htmlentities($_POST['cname']))."',customer_address = '".$con->escapeString(htmlentities($_POST['caddr']))."',terms = '$_POST[terms]', referred_by = '".$con->escapeString(htmlentities($_POST['referred_by']))."', hmo_card_no = '$_POST[hmo_no]',hmo_card_expiry = '".$con->formatDate($_POST['card_expiry'])."', is_pwd='$_POST[is_pwd]', scpwd_id = '$_POST[sc_id]',with_loa = '$_POST[with_loa]',loa_date = '".$con->formatDate($_POST['loa_date'])."', patient_stat = '$_POST[pstat]', physician = '".$con->escapeString(htmlentities($_POST['physician']))."',cstatus = '$sostat', delivery_type = '$_POST[dtype]', email_add = '$_POST[email]', remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."', updated_by = '$_SESSION[userid]' , updated_on = now() where so_no = '$_POST[so_no]';";
                $sono = $_POST['so_no'];
            } else {
                $sono = $_POST['so_no'];
                $queryString = "INSERT IGNORE INTO so_header (so_no,branch,so_date,priority_no,patient_id,patient_name,patient_address,customer_code,customer_name,customer_address,terms,referred_by,hmo_card_no,hmo_card_expiry,is_pwd,scpwd_id,with_loa,loa_date,patient_stat,physician,cstatus,delivery_type,email_add,remarks,trace_no,created_by,created_on) VALUES ('$sono','$bid','".$con->formatDate($_POST['so_date'])."','$_POST[pri_no]','$_POST[pid]','".$con->escapeString(htmlentities($_POST['pname']))."','".$con->escapeString(htmlentities($_POST['paddr']))."','$_POST[cid]','".$con->escapeString(htmlentities($_POST['cname']))."','".$con->escapeString(htmlentities($_POST['caddr']))."','$_POST[terms]','".$con->escapeString(htmlentities($_POST['referred_by']))."','$_POST[hmo_no]','".$con->formatDate($_POST['card_expiry'])."','$_POST[is_pwd]','$_POST[sc_id]','$_POST[with_loa]','".$con->formatDate($_POST['loa_date'])."','$_POST[pstat]','".$con->escapeString(htmlentities($_POST['physician']))."','$sostat','$_POST[dtype]','$_POST[email]','".$con->escapeString(htmlentities($_POST['remarks']))."','$_POST[trace_no]','$_SESSION[userid]',now());";
            }
            $con->dbquery($queryString);
        break;

        case "addItem": 
            
            $sprice = $con->formatDigit($_POST['sprice']);
            $qty = $con->formatDigit($_POST['qty']);
            $amt = $con->formatDigit($_POST['amount']);
           
            checkSeniorDiscount($_POST['pid'],$_POST['scpwd_id'],$_POST['is_pwd'],$_POST['so_date'],$amt);
            
            $adue = $amt - $discount;
            $con->dbquery("INSERT IGNORE INTO so_details (so_no,branch,`code`,`description`,unit,unit_price,qty,amount,disctype,discpercent,discount,amount_due,trace_no) VALUES ('$_POST[so_no]','$bid','$_POST[item]','".$con->escapeString(htmlentities($_POST['description']))."','$_POST[unit]','$sprice','$qty','$amt','$disctype','$discpercent','$discount','$adue','$_POST[trace_no]');");
             updateAmount($_POST['so_no']);
        break;

        case "deleteLine":
            $con->deleteRow($table="so_details",$arg = "line_id='$_POST[lid]'");
            updateAmount($_POST['so_no']);
        break;

        case "applyDiscount":
			$d = $con->getArray("select * from so_details where line_id = '$_POST[lid]';");
			
            $discount = ROUND($d['amount'] * ($_POST['discPercent']/100),2);
            $adue = $d['amount'] - $discount;      
           
            $con->dbquery("update ignore so_details set discount = '$discount', disctype='$_POST[discType]', discpercent = '$_POST[discPercent]', amount_due = '$adue' where line_id = '$_POST[lid]';");
			updateAmount($_POST['so_no']);
		break;

        case "check4print":
			list($a) = $con->getArray("select count(*) from so_header where so_no = '$_POST[so_no]' and branch = '$bid';");
			list($b) = $con->getArray("select count(*) from so_details where so_no = '$_POST[so_no]' and branch = '$bid';");
			
			if($a == 0 && $b > 0) { echo "head"; }
			if($b == 0 && $a > 0) { echo "det"; }
			if($a == 0 && $b == 0) { echo "both"; }
			if($a > 0 && $b > 0) { echo "noerror"; }
		break;

        case "finalize":
            $con->dbquery("update ignore so_header set `status` = 'Finalized', updated_by = '$_SESSION[userid]', updated_on = now() where so_no = '$_POST[so_no]' and branch = '$bid';");
            updateAmount($_POST['so_no']);

        break;

        case "checkBilled":
            if($con->countRows("select so_no from so_header where so_no = '$_POST[so_no]' and branch = '$bid' and (billed = 'Y' or paid ='Y');") > 0) {
                echo "processed";
            }
        break;

        case "reopen":
            $con->dbquery("update so_header set status = 'Active', updated_by = '$_SESSION[userid]', updated_on = now() where so_no = '$_POST[so_no]' and branch = '$bid';");
        break;
        
        case "cancel":
            $con->dbquery("update ignore so_header set `status` = 'Cancelled', updated_by = '$_SESSION[userid]', updated_on = now() where so_no = '$_POST[so_no]' and branch = '$bid';");
        break; 

        case "retrieve":
            $data = array();
			$srrd = $con->dbquery("SELECT line_id as id, `code`, description, unit, qty, ROUND(unit_price/qty,2) as unit_price, unit_price as amount, discount, amount_due FROM so_details WHERE trace_no = '$_REQUEST[trace_no]';");
			while($row = $srrd->fetch_array()) {
				$data[] = array_map('utf8_encode',$row);
			}
			$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
			echo json_encode($results);	
        break;

        case "verify":
            $con->dbquery("update so_header set cstatus = '12', verified = 'Y', verified_by = '$_SESSION[userid]', verified_on = now() where so_no = '$_POST[so_no]' and branch = '$bid';");
        
            /* Send Lab Request Pending Extraction */
            $so = $con->dbquery("SELECT a.so_no AS so, b.code as parent_code, b.code, b.description AS `procedure`, a.physician, d.sample_type, d.container_type FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id LEFT JOIN services_master d ON b.code = d.code WHERE a.status = 'Finalized' AND d.with_subtests = 'N' AND d.category IN ('1','2') AND a.so_no = '$_POST[so_no]' and d.description not like '%PCR%' UNION SELECT a.so_no AS so, e.parent as parent_code, e.code, e.description AS `procedure`, a.physician, f.sample_type, f.container_type FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id LEFT JOIN services_master d ON b.code = d.code LEFT JOIN services_subtests e ON b.code = e.parent LEFT JOIN services_master f ON e.code = f.code WHERE a.status = 'Finalized' AND d.with_subtests = 'Y' AND f.category IN ('1','2') AND a.so_no = '$_POST[so_no]' and d.description not like '%PCR%';");
            while($eRow = $so->fetch_array()) {
                list($labCount) = $con->getArray("select count(*) from lab_samples where so_no = '$eRow[so]' and parent_code = '$eRow[parent_code]' and code = '$eRow[code]';");
                if($labCount == 0) {
                    $con->dbquery("INSERT IGNORE INTO lab_samples (branch,so_no,parent_code,code,`procedure`,sampletype,samplecontainer,physician,created_by,created_on) values ('$bid','$eRow[so]','$eRow[parent_code]','$eRow[code]','$eRow[procedure]','$eRow[sample_type]','$eRow[container_type]','$eRow[physician]','$uid',now());");
                }
            }

            /* Send Request to Nursing Station for PEME */
            $gQuery = $con->dbquery("SELECT a.priority_no as prio, a.so_no, a.so_date, b.code as parentcode, b.code, b.description AS `procedure`, a.patient_id AS pid, c.birthplace, c.occupation AS occu, c. employer AS compname, c.mobile_no AS contactno FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id WHERE a.so_no = '$_POST[so_no]' AND b.code IN ('O009') AND a.status IN (2,4,12) UNION ALL SELECT a.priority_no as prio, a.so_no, a.so_date, b.code as parentcode, e.code, e.description AS `procedure`, a.patient_id AS pid, c.birthplace, c.occupation AS occu, c. employer AS compname, c.mobile_no AS contactno FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id LEFT JOIN services_master d ON b.code = d.code LEFT JOIN services_subtests e ON e.parent = d.code  WHERE a.so_no = '$_POST[so_no]' AND e.code IN ('O009') AND a.status IN (2,4,12) AND  d.with_subtests = 'Y';");
            while($hRow = $gQuery->fetch_array()) {
                $con->dbquery("INSERT IGNORE INTO peme (so_no,branch,so_date,prio,parentcode,code,`procedure`,pid,pob,occu,compname,contactno) values ('$hRow[so_no]','$bid','$hRow[so_date]','$hRow[prio]','$hRow[parentcode]','$hRow[code]','$hRow[procedure]','$hRow[pid]','" . $con->escapeString(htmlentities($hRow['birthplace'])) . "','$hRow[occu]','" . $con->escapeString(htmlentities($hRow['compname'])) . "','$hRow[contactno]');");

            }

             /* Send Request to Consultation */
             $xQuery = $con->dbquery("SELECT a.priority_no as prio, a.so_no, a.so_date, b.code as parentcode, b.code, b.description AS `procedure`, a.patient_id AS pid, c.birthplace, c.occupation AS occu, c. employer AS compname, c.mobile_no AS contactno, c.emp_idno, a.patient_name, c.gender, a.trace_no, c.birthdate FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id WHERE a.so_no = '$_POST[so_no]' AND b.code IN ('M001') AND a.status IN (2,4,12) UNION ALL SELECT a.priority_no as prio, a.so_no, a.so_date, b.code as parentcode, e.code, e.description AS `procedure`, a.patient_id AS pid, c.birthplace, c.occupation AS occu, c. employer AS compname, c.mobile_no AS contactno, c.emp_idno, a.patient_name, c.gender, a.trace_no, c.birthdate FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id LEFT JOIN services_master d ON b.code = d.code LEFT JOIN services_subtests e ON e.parent = d.code  WHERE a.so_no = '$_POST[so_no]' AND e.code IN ('M001') AND a.status IN (2,4,12) AND  d.with_subtests = 'Y';");
             while($lRow = $xQuery->fetch_array()) {
                 list($fmCount) = $con->getArray("select count(*) from consultation_form where so_no = '$lRow[so_no]' and pid = '$lRow[pid]' and code = '$lRow[code]' and trace_no = '$lRow[trace_no]';");
                 if($fmCount == 0) {
                     $con->dbquery("INSERT IGNORE INTO consultation_form (trans_time,so_no,branch,so_date,priority_no,parentcode,code,`procedure`,pid,pob,occu,compname,contactno,company_id,pname,birthdate,gender,trace_no,created_by,created_on) values (now(),'$lRow[so_no]','$bid','$lRow[so_date]','$lRow[prio]','$lRow[parentcode]','$lRow[code]','$lRow[procedure]','$lRow[pid]','" . $con->escapeString(htmlentities($lRow['birthplace'])) . "','$lRow[occu]','" . $con->escapeString(htmlentities($lRow['compname'])) . "','$lRow[contactno]', '$lRow[emp_idno]','" . $con->escapeString(htmlentities($lRow['patient_name'])) . "','$lRow[birthdate]','$lRow[gender]','$lRow[trace_no]','$_SESSION[userid]',now());");
                 }
             }
        
        break;

        case "getTotals":
            list($gross,$discount,$adue) = $con->getArray("select ifnull(sum(amount),0),ifnull(sum(discount),0),ifnull(sum(amount_due),0) from so_details where so_no = '$_POST[so_no]' and branch = '$bid';");
            echo json_encode(array("gross"=>number_format($gross,2),"discount"=>number_format($discount,2),"amountdue"=>number_format($adue,2)));
        break;

    }



?>