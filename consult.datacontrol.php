<?php
    session_start();
	require_once 'handlers/_generics.php';
	$con = new _init;
    $bid = $_SESSION['branchid'];

    
    switch($_REQUEST['mod']) {
        case "saveRecord":

			// list($isE) = $con->getArray("SELECT COUNT(*) from consultation_form where trace_no = '$_POST[trace_no]';");

            // if($isE > 0) {
                $queryString = "UPDATE IGNORE consultation_form SET height = '$_POST[height]',weight = '$_POST[weight]',temp = '$_POST[temp]',pulse = '$_POST[pulse]',respiratory = '$_POST[respiratory]', bp = '$_POST[bp]', last_visit = '".$con->formatDate($_POST['last_visit'])."', bmi = '$_POST[bmi]', bmi_category = '$_POST[bmi_category]', physician = '".$con->escapeString(htmlentities($_POST['physician']))."', updated_by = '$_SESSION[userid]', updated_on = NOW() WHERE trace_no = '$_POST[trace_no]';";
            // } else {
            //     $queryString = "INSERT IGNORE INTO consultation_form (trans_time,trace_no,trans_date,pid,dept,company_id,pname,birthdate,age,gender,height,`weight`,`temp`,`pulse`,respiratory,bp,last_visit,bmi,physician,created_by,created_on) VALUES (NOW(),'$_POST[trace_no]','".$con->formatDate($_POST['date'])."','$_POST[pid]','$_POST[dept]','$_POST[com_id]','".$con->escapeString(htmlentities($_POST['pname']))."','".$con->formatDate($_POST['birthdate'])."','$_POST[age]','$_POST[gender]','$_POST[height]','$_POST[weight]','$_POST[temp]','$_POST[pulse]','$_POST[respiratory]','$_POST[bp]','".$con->formatDate($_POST['last_visit'])."','$_POST[bmi]','".$con->escapeString(htmlentities($_POST['physician']))."','$_SESSION[userid]',NOW());";
            // }

            $con->dbquery($queryString);
            echo $queryString;
        break;

		case "saveComplaint":
			$con->dbquery("UPDATE IGNORE consultation_form set complaints = '".$con->escapeString(htmlentities($_POST['complaint']))."' where trace_no = '$_POST[trace_no]';");
		break;

		case "saveDiagnosis":
			$con->dbquery("UPDATE IGNORE consultation_form set diagnosis = '".$con->escapeString(htmlentities($_POST['diagnosis']))."' where trace_no = '$_POST[trace_no]';");
		break;

		case "saveTreatment":
			$con->dbquery("UPDATE IGNORE consultation_form set treatment = '".$con->escapeString(htmlentities($_POST['treatment']))."' where trace_no = '$_POST[trace_no]';");
		break;

		case "saveRecommendation":
			$con->dbquery("UPDATE IGNORE consultation_form set recommendation = '".$con->escapeString(htmlentities($_POST['recommendation']))."' where trace_no = '$_POST[trace_no]';");
		break;

		case "savePrescription":
			$con->dbquery("UPDATE IGNORE consultation_form set prescription1 = '".$con->escapeString(htmlentities($_POST['prescription1']))."', prescription2 = '".$con->escapeString(htmlentities($_POST['prescription2']))."', prescription3 = '".$con->escapeString(htmlentities($_POST['prescription3']))."', prescription4 = '".$con->escapeString(htmlentities($_POST['prescription4']))."', prescription5 = '".$con->escapeString(htmlentities($_POST['prescription5']))."', sig = '".$con->escapeString(htmlentities($_POST['sig']))."' where trace_no = '$_POST[trace_no]';");
		break;

		case "assignToClinic":
			$con->dbquery("update ignore consultation_form set clinic = '$_POST[clinic]', assign_by = '$_SESSION[userid]', assign_on = now() where so_no = '$_POST[so_no]' and pid = '$_POST[pid]';");
		break;

		case "retrieveConsultForm":
            $data = array();
			$srrd = $con->dbquery("SELECT * FROM consultation_form WHERE trace_no = '$_REQUEST[trace_no]';");
			while($row = $srrd->fetch_array()) {
				$data[] = array_map('utf8_encode',$row);
			}
			$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
			echo json_encode($results);	
        break;

		/* Prescriptions */
		case "retrieveDiagnosis":
			$data = array();
			$pQuery = $con->dbquery("SELECT line_id as id, diagnosis FROM diagnosis WHERE trace_no = '$_REQUEST[trace_no]';");
			while($row = $pQuery->fetch_array()) {
				$data[] = array_map('utf8_encode',$row);
			}
			$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
			echo json_encode($results);	


		break;

        /* Prescriptions */
		case "retrievePrescriptions":
			$data = array();
			$pQuery = $con->dbquery("SELECT line_id as id, medicine, dosage, concat(date_format(dtf,'%m/%d/%y'),' - ',date_format(dt2,'%m/%d/%y')) as duration FROM prescriptions WHERE trace_no = '$_REQUEST[trace_no]';");
			while($row = $pQuery->fetch_array()) {
				$data[] = array_map('utf8_encode',$row);
			}
			$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
			echo json_encode($results);	


		break;

		 /* Lab Request */
		 case "retrieveLabrequest":
			$data = array();
			$srrd = $con->dbquery("SELECT line_id as id, `code`, description, unit FROM consult_labrequest WHERE trace_no = '$_REQUEST[trace_no]';");
			while($row = $srrd->fetch_array()) {
				$data[] = array_map('utf8_encode',$row);
			}
			$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
			echo json_encode($results);	


		break;

		case "reopen":
            $con->dbquery("update consultation_form set status = 'Active', updated_by = '$_SESSION[userid]', updated_on = now() where trans_id = '$_POST[trans_id]' and trace_no = '$_POST[trace_no]' and pid = '$_POST[pid]';");
        break;

		case "cancel":
            $con->dbquery("update ignore consultation_form set `status` = 'Cancelled', updated_by = '$_SESSION[userid]', updated_on = now() where trans_id = '$_POST[trans_id]' and trace_no = '$_POST[trace_no]' and pid = '$_POST[pid]';");
        break; 

		case "reopen":
            $con->dbquery("update consultation_form set status = 'Active', updated_by = '$_SESSION[userid]', updated_on = now() where trans_id = '$_POST[trans_id]' and trace_no = '$_POST[trace_no]' and pid = '$_POST[pid]';");
        break;

		case "check4print":
			list($a) = $con->getArray("select count(*) from consultation_form where trace_no = '$_POST[trace_no]' and pid = '$_POST[pid]';");
			list($b) = $con->getArray("select count(*) from consultation_form where trace_no = '$_POST[trace_no]' and pid = '$_POST[pid]';");
			
			if($a == 0 && $b > 0) { echo "head"; }
			if($b == 0 && $a > 0) { echo "det"; }
			if($a == 0 && $b == 0) { echo "both"; }
			if($a > 0 && $b > 0) { echo "noerror"; }
		break;

		case "finalize":
            $con->dbquery("update ignore consultation_form set `status` = 'Finalized', updated_by = '$_SESSION[userid]', updated_on = now() where trace_no = '$_POST[trace_no]' and pid = '$_POST[pid]';");
        break;
		case "addItem": 
            $con->dbquery("INSERT IGNORE INTO consult_labrequest (so_no,branch,`code`,pid,`description`,unit,trace_no) VALUES ('$_POST[so_no]','$bid','$_POST[item]','$_POST[pid]','".$con->escapeString(htmlentities($_POST['description']))."','$_POST[unit]','$_POST[trace_no]');");
        break;
		case "deleteLine":
            $con->deleteRow($table="consult_labrequest",$arg = "line_id='$_POST[lid]'");
        break;



    }

?>