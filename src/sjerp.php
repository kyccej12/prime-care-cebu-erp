<?php
	session_start();
	//ini_set("display_errors","On");
	require_once "../handlers/_generics.php";
	
	$con = new _init;
	$date = date('Y-m-d');
	$bid = $_SESSION['branchid'];
	$uid = $_SESSION['userid'];

	switch($_POST['mod']) {

		/* Accounting & Finance */
		case "getAcctCodeSeries":
			list($newCode) = $con->getArray("SELECT MAX(acct_code+1) FROM acctg_accounts WHERE parent_acct = '$_POST[parent]';");
			if($newCode == '') {
				$newCode = $_POST['pid'] + 1;	
			}
			
			echo $newCode;
			
		break;
		case "newAccount":
			list($xx) = $con->getArray("select count(*) from acctg_accounts where acct_code = '$_POST[acct_code]' and company = '1';");
			if($xx > 0) {
				echo "DUPLICATE";
			} else {
				$con->dbquery("insert ignore into acctg_accounts (company,acct_code,description,parent,parent_acct,acct_grp) values ('1','$_POST[acct_code]','".$con->escapeString($_POST['acct_desc'])."','N','$_POST[parent]','$_POST[acct_grp]');");
			}
		break;
		case "updateAccount":
			list($xx) = $con->getArray("select count(*) from acctg_accounts where acct_code = '$_POST[acct_code]' and company = '1' and record_id != $_POST[recid];");
			if($xx > 0) {
				echo "DUPLICATE";
			} else {
				$con->dbquery("update ignore acctg_accounts set acct_code = '$_POST[acct_code]', description = '".$_POST['acct_desc']."', acct_grp = '$_POST[acct_grp]' where record_id = '$_POST[recid]';");
			}
		break;
		case "getAcctDetails":
			echo json_encode($con->getArray("select * from acctg_accounts where record_id = '$_POST[recid]';"));
		break;
		case "deleteAccount":
			//$con->dbquery("delete from acctg_accounts where record_id = '$_POST[rid]';");
			$con->dbquery("update ignore acctg_accounts set file_status = 'Deleted', deleted_by = '$uid', deleted_on = now() where record_id = '$_POST[recid]';");
		break;
		case "getParentAccts":
			$paQuery = $con->dbquery("select acct_code, description from acctg_accounts where acct_grp = '$_POST[grp]' and parent = 'Y';");
			echo "<option value=''>- Select Parent Account -</option>";
			while($paRow = $paQuery->fetch_array()) {
				echo "<option value='$paRow[0]'>$paRow[1]</option>";
			}
		break;
		case "saveBank":
			if($_POST['bid'] != "") {
				$con->dbquery("update acctg_bankaccounts set bank_name = '".$con->escapeString($_POST['bname'])."', bank_address = '".$con->escapeString($_POST['badd'])."', tel_no = '".$con->escapeString($_POST['tel_no'])."', acct_type = '$_POST[acct_type]', acct_no='$_POST[acct_no]', gl_acct='$_POST[gl_acct]', check_no='$_POST[check_no]', company='1' where bank_id = '$_POST[bid]';");
			} else {
				$con->dbquery("insert into acctg_bankaccounts (company,bank_name,bank_address,tel_no,acct_type,acct_no,gl_acct,check_no) values ('1','".$con->escapeString($_POST['bname'])."','".$con->escapeString($_POST['badd'])."','$_POST[tel_no]','$_POST[acct_type]','$_POST[acct_no]','$_POST[gl_acct]','$_POST[check_no]');");
			}
		break;
		case "deleteBank":
			$con->dbquery("delete from acctg_bankaccounts where bank_id = '$_POST[bid]';");
		break;
		
		/* Branches */
		case "saveBInfo":
			if($_POST['bid'] != "") {
				$con->dbquery("update options_branches set branch_name = '".$con->escapeString(htmlentities($_POST['branchname']))."', address='".$con->escapeString(htmlentities($_POST['address']))."', city='$_POST[city]', province = '$_POST[province]', tel_no = '".$con->escapeString($_POST['telno'])."',oic='".$con->escapeString(htmlentities($_POST['oic']))."', sales_quota='".$con->formatDigit($_POST['quota'])."', office_current='$_POST[office_current]', branch_current='$_POST[branch_current]', client_code = '$_POST[client_code]', updated_by = '$uid', updated_on = now() where record_id = '$_POST[bid]' and company = '1';");
			} else {
				list($bcode) = $con->getArray("select ifnull(max(branch_code),0)+1 from options_branches where company = '1';");
				$con->dbquery("insert ignore into options_branches (branch_code,company,branch_name,address,city,province,tel_no,oic,sales_quota,office_current,branch_current,client_code,created_by,created_on) values ('$bcode','1','".$con->escapeString($_POST['branchname'])."','".$con->escapeString($_POST['address'])."','$_POST[city]','$_POST[province]','".$con->escapeString($_POST['telno'])."','".$con->escapeString($_POST['oic'])."','".$con->formatDigit($_POST['quota'])."','$_POST[office_current]','$_POST[branch_current]','$_POST[client_code]','$uid',now())");
			}
		break;
		case "deleteBranch":
			$con->dbquery("delete from options_branches where branch_code = '$_POST[bid]';");
		break;
		case "getBranchList":
			$bl = $con->dbquery("select branch_code, branch_name from options_branches where company = '$_POST[company]' order by branch_code;");
			while(list($bid,$bname) = $bl->fetch_array()) {
				echo "<option value='$bid'>$bname</option>";
			}
		break;
		case "identCompany":
			echo $_SESSION['company'];
		break;
		
		/* Items */
		case "getIcode":
			switch($_POST['mid']) { case "1": $main="RMO"; break; case "2": $main="FA"; break; case "3": $main="LAB"; break; case "4": $main="NRS"; break; case "5": $main="DTL"; break; case "6": $main="XRY"; break;}
			
			if($_POST['sgroup'] > 0) {
				list($scode) = $con->getArray("select `code` from options_sgroup where sid = '$_POST[sgroup]';");
			} else { $scode = '000'; }
			
			list($series) = $con->getArray("SELECT LPAD(IFNULL(MAX(series+1),1),4,0) FROM (SELECT TRIM(LEADING '0' FROM(SUBSTRING_INDEX(SUBSTRING_INDEX(item_code, '-', 3), '-', -1))) AS series FROM products_master WHERE SUBSTRING_INDEX(SUBSTRING_INDEX(item_code, '-', 1), '-', -1) = '$main' AND subgroup='$_POST[sgroup]') a;");		
			echo $main.'-'.$scode.'-'.$series;
		break;
		
		case "getSgroup":
			$a = $con->dbquery("select sid, sgroup from options_sgroup where mid = '$_POST[mgroup]';");
			echo "<option value='0'>- Not Applicable -</option>";
			while(list($y,$z) = $a->fetch_array()) {
				echo "<option value = '$y'>$z</option>";
			}
		break;
		
		case "getCgroup":
			$a = $con->dbquery("select id, cgrp_name from options_cgroup where sgrp_id = '$_POST[sgrp_id]';");
			echo "<option value='0'>- Not Applicable -</option>";
			while(list($y,$z) = $a->fetch_array()) {
				echo "<option value = '$y'>$z</option>";
			}
		break;

		case "copyInfo":
			$a = $con->dbquery("select * from products_master where indcode = '$_POST[stockCode]' limit 1;");
			if($a) {
				echo json_encode($a->fetch_array());
			} else {
				echo json_encode(array("noerror"=>true));
			}
		break;
		
		case "checkDupCode":
			if($_POST['rid'] != "") {
				list($isExist) = $con->getArray("select count(*) from products_master where item_code = '$_POST[item_code]' and company = '1' and record_id != '$_POST[rid]';");
			} else {
				list($isExist) = $con->getArray("select count(*) from products_master where item_code = '$_POST[item_code]' and company = '1';");
			}
			
			if($isExist == 0) { echo "NODUPLICATE"; }
		break;

		case "savePInfo":
			if(!$_POST['status']) { $stat = "Y"; } else { $stat = $_POST['status']; }
			if(isset($_POST['rid']) && $_POST['rid'] != "") {
				$con->dbquery("update ignore products_master set category = '$_POST[item_category]', subgroup='$_POST[item_sgroup]', cgroup = '$_POST[item_cgroup]', item_code='".$con->escapeString($_POST['item_code'])."', indcode = '$_POST[item_indcode]', brand = '".strtoupper($_POST['item_brand'])."', description='".$con->escapeString(htmlentities($_POST['item_description']))."',full_description='".$con->escapeString(htmlentities($_POST['item_fdescription']))."',beg_qty='".$con->formatDigit($_POST['item_beginning'])."',minimum_level='".$con->formatDigit($_POST['item_mininv'])."',reorder_pt='$_POST[item_reorder]',unit = '$_POST[item_unit]',unit_cost='".$con->formatDigit($_POST['item_unitcost'])."',srp='".$con->formatDigit($_POST['srp'])."',vat_exempt='$_POST[vat_exempt]',rev_acct='$_POST[rev_acct]',cogs_acct='$_POST[cogs_acct]',exp_acct='$_POST[exp_acct]',asset_acct='$_POST[asset_acct]',supplier='$_POST[supplier]',`active`='$stat',updated_by='$uid', updated_on=now(), with_expiration = '$_POST[with_expiration]' where record_id = '$_POST[rid]';");
				echo "update ignore products_master set category = '$_POST[item_category]', subgroup='$_POST[item_sgroup]', item_code='".$con->escapeString($_POST['item_code'])."', brand = '".strtoupper($_POST['item_brand'])."', description='".$con->escapeString(htmlentities($_POST['item_description']))."',full_description='".$con->escapeString(htmlentities($_POST['item_fdescription']))."',beg_qty='".$con->formatDigit($_POST['item_beginning'])."',minimum_level='".$con->formatDigit($_POST['item_mininv'])."',reorder_pt='$_POST[item_reorder]',unit = '$_POST[item_unit]',unit_cost='".$con->formatDigit($_POST['item_unitcost'])."',srp='".$con->formatDigit($_POST['srp'])."',vat_exempt='$_POST[vat_exempt]',rev_acct='$_POST[rev_acct]',cogs_acct='$_POST[cogs_acct]',exp_acct='$_POST[exp_acct]',asset_acct='$_POST[asset_acct]',supplier='$_POST[supplier]',`active`='$stat',updated_by='$uid', updated_on=now() where record_id = '$_POST[rid]';";
			} else {
				$con->dbquery("insert ignore into products_master (company,category,subgroup,cgroup,item_code,indcode,brand,description,full_description,unit,unit_cost,srp,beg_qty,minimum_level,reorder_pt,vat_exempt,supplier,rev_acct,cogs_acct,exp_acct,asset_acct,`active`,with_expiration,encoded_by,encoded_on) values ('$_SESSION[company]','$_POST[item_category]','$_POST[item_sgroup]','$_POST[item_cgroup]','".$con->escapeString(htmlentities($_POST['item_code']))."','$_POST[item_indcode]','".$con->escapeString(htmlentities($_POST['item_brand']))."','".$con->escapeString(htmlentities($_POST['item_description']))."','".$con->escapeString(htmlentities($_POST['item_fdescription']))."','$_POST[item_unit]','".$con->formatDigit($_POST['item_unitcost'])."','".$con->formatDigit($_POST['srp'])."','".$con->formatDigit($_POST['item_beginning'])."','".$con->formatDigit($_POST['item_mininv'])."','".$con->formatDigit($_POST['item_reorder'])."','$_POST[vat_exempt]','$_POST[supplier]','$_POST[rev_acct]','$_POST[cogs_acct]','$_POST[exp_acct]','$_POST[asset_acct]','$stat','$_POST[with_expiration]','$uid',now());");
			}
		break;
		
		case "deletePro":
			$con->dbquery("update products_master set `active` = 'N', file_status = 'Deleted', deleted_by = '$_SESSION[userid]', deleted_on = now() where record_id = '$_POST[rid]';");
		break;
		
		case "restorePro":
			$con->dbquery("update products_master set `active` = 'Y', file_status = 'Active' where record_id = '$_POST[rid]';");
		break;
		
		case "getItemTitle":
			$_h = $con->getArray("select `group`,concat(item_code, ' :: ',description) from products_master where record_id = '$_POST[fid]';");
			echo json_encode($_h);
		break;
		
		case "getGroups":
			$o = $con->dbquery("select `group`,`group_description` from options_igroup where mid = '$_POST[type]' order by `group_description` asc;");
			echo "<option value=''>- Not Applicable -</option>\n";
			while($oo = $o->fetch_array()) {
				echo "<option value='$oo[0]'>$oo[1]</option>\n";
			}
		break;
		
		case "newSGroup":
			list($isE) = $con->getArray("select count(*) from options_sgroup where code = '$_POST[code]';");
			if($isE > 0) {
				echo "DUPLICATE";
			} else {
				$con->dbquery("INSERT INTO options_sgroup (`mid`,sgroup,`code`) VALUES ('$_POST[maingrp]','".$con->escapeString(htmlentities($_POST['description']))."','$_POST[code]');");
			}
		break;
		
		case "retrieveSGroup":
			echo json_encode($con->getArray("select * from options_sgroup where sid = '$_POST[id]';"));
		break;
		
		case "updateSGroup":
			$con->dbquery("update options_sgroup set `mid` = '$_POST[maingrp]', sgroup = '" . $con->escapeString(htmlentities($_POST['description'])) . "' where sid = '$_POST[id]';");
		break;
		
		case "deleteSGroup":
			$con->dbquery("update options_sgroup set file_status = 'Deleted', deleted_by = '$uid', deleted_on = now() where sid = '$_POST[id]';");
		break;

		/* Pharmacy */
		case "deletePharma":
			$con->dbquery("update pharma_master set `active` = 'N', file_status = 'Deleted' where record_id = '$_POST[rid]';");
		break;
		
		case "getPharmaIcode":
			list($series) = $con->getArray("SELECT LPAD(IFNULL(MAX(series+1),1),6,0) FROM (SELECT TRIM(LEADING '0' FROM SUBSTR(item_code,4)) AS series FROM pharma_master) a");		
			echo 'PH'.$series;
		break;

		case "getPharmaSgroup":
			$a = $con->dbquery("select id, sgroup from pharma_sgroup where pid = '$_POST[mgroup]';");
			echo "<option value='0'>- Not Applicable -</option>";
			while(list($y,$z) = $a->fetch_array()) {
				echo "<option value = '$y'>$z</option>";
			}
		break;
		
		case "checkPharmaDupCode":
			if($_POST['rid'] != "") {
				list($isExist) = $con->getArray("select count(*) from pharma_master where item_code = '$_POST[item_code]' and company = '1' and record_id != '$_POST[rid]';");
			} else {
				list($isExist) = $con->getArray("select count(*) from pharma_master where item_code = '$_POST[item_code]' and company = '1';");
			}
			
			if($isExist == 0) { echo "NODUPLICATE"; }
		break;

		case "savePharmaInfo":
			if(isset($_POST['rid']) && $_POST['rid'] != "") {
				$con->dbquery("update ignore pharma_master set category = '$_POST[item_category]', subgroup='$_POST[item_sgroup]', item_code='".$con->escapeString($_POST['item_code'])."', brand = '".strtoupper($_POST['item_brand'])."',  generic_name='".$con->escapeString(htmlentities($_POST['item_genericname']))."', barcode = '$_POST[item_barcode]', rack_no = '".strtoupper($_POST['item_rack_no'])."', level = '".strtoupper($_POST['item_level'])."', description='".$con->escapeString(htmlentities($_POST['item_description']))."',full_description='".$con->escapeString(htmlentities($_POST['item_fdescription']))."',beg_qty='".$con->formatDigit($_POST['item_beginning'])."',minimum_level='".$con->formatDigit($_POST['item_mininv'])."',reorder_pt='$_POST[item_reorder]',unit = '$_POST[item_unit]',unit_cost='".$con->formatDigit($_POST['item_unitcost'])."',srp='".$con->formatDigit($_POST['srp'])."',vat_exempt='$_POST[vat_exempt]',rev_acct='$_POST[rev_acct]',cogs_acct='$_POST[cogs_acct]',exp_acct='$_POST[exp_acct]',asset_acct='$_POST[asset_acct]',supplier='$_POST[supplier]',begqty='".$con->formatDigit($_POST['item_beginning'])."',begdate='".$con->formatDate($_POST['item_beginning_date'])."',expiry_d8='".$con->formatDate($_POST['expiry_d8'])."',`active`='$stat',updated_by='$uid', updated_on=now() where record_id = '$_POST[rid]';");
			} else {
				$con->dbquery("insert ignore into pharma_master (company,category,subgroup,item_code,brand,generic_name,barcode,rack_no,level,description,full_description,unit,unit_cost,srp,beg_qty,minimum_level,reorder_pt,vat_exempt,supplier,rev_acct,cogs_acct,exp_acct,asset_acct,`active`,encoded_by,encoded_on) values ('$_SESSION[company]','$_POST[item_category]','$_POST[item_sgroup]','".$con->escapeString(htmlentities($_POST['item_code']))."','".$con->escapeString(htmlentities($_POST['item_brand']))."','".$con->escapeString(htmlentities($_POST['item_genericname']))."','$_POST[item_barcode]','$_POST[item_rack_no]','$_POST[item_level]','".$con->escapeString(htmlentities($_POST['item_description']))."','".$con->escapeString(htmlentities($_POST['item_fdescription']))."','$_POST[item_unit]','".$con->formatDigit($_POST['item_unitcost'])."','".$con->formatDigit($_POST['srp'])."','".$con->formatDigit($_POST['item_beginning'])."','".$con->formatDigit($_POST['item_mininv'])."','".$con->formatDigit($_POST['item_reorder'])."','$_POST[vat_exempt]','$_POST[supplier]','$_POST[rev_acct]','$_POST[cogs_acct]','$_POST[exp_acct]','$_POST[asset_acct]','$stat','$uid',now());");
				//echo "insert ignore into pharma_master (company,category,subgroup,item_code,brand,generic_name,barcode,description,full_description,unit,unit_cost,srp,beg_qty,minimum_level,reorder_pt,vat_exempt,supplier,rev_acct,cogs_acct,exp_acct,asset_acct,`active`,encoded_by,encoded_on) values ('$_SESSION[company]','$_POST[item_category]','$_POST[item_sgroup]','".$con->escapeString(htmlentities($_POST['item_code']))."','".$con->escapeString(htmlentities($_POST['item_brand']))."','".$con->escapeString(htmlentities($_POST['item_genericname']))."','$_POST[itme_barcode]','".$con->escapeString(htmlentities($_POST['item_description']))."','".$con->escapeString(htmlentities($_POST['item_fdescription']))."','$_POST[item_unit]','".$con->formatDigit($_POST['item_unitcost'])."','".$con->formatDigit($_POST['srp'])."','".$con->formatDigit($_POST['item_beginning'])."','".$con->formatDigit($_POST['item_mininv'])."','".$con->formatDigit($_POST['item_reorder'])."','$_POST[vat_exempt]','$_POST[supplier]','$_POST[rev_acct]','$_POST[cogs_acct]','$_POST[exp_acct]','$_POST[asset_acct]','$stat','$uid',now());";
			}
		break;
		
		/* Services */
		case "checkServiceDupCode":
			if($_POST['rid'] != "") { $f = " and id != '$_POST[rid]'"; }
			list($isExist) = $con->getArray("select count(*) from services_master where code = '$_POST[item_code]' $f;");
			if($isExist == 0) { echo "NODUPLICATE"; }
		break;
		
		case "getServiceCode":
			list($scode) = $con->getArray("select `code` from options_servicecat where id = '$_POST[mid]';");
			list($series) = $con->getArray("SELECT LPAD(IFNULL(MAX(series+1),1),3,0) FROM (SELECT TRIM(LEADING '0' FROM(SUBSTRING(`code`,2,3))) AS series FROM services_master WHERE category = '$_POST[mid]') a;");		
			echo $scode.$series;
		break;

		case "getServiceSubgroup":
			$a = $con->dbquery("select id, subcategory from options_servicesubcat where parent_id = '$_POST[parent]';");
			echo "<option value='0'>- Not Applicable -</option>";
			while(list($y,$z) = $a->fetch_array()) {
				echo "<option value = '$y'>$z</option>";
			}
		break;

		case "queryRequestCategory":

			if($_POST['queryType'] == 1) {
				$a = $con->dbquery("select record_id as id, request_type from options_requesttype where request_category = '$_POST[id]';");
				while(list($y,$z) = $a->fetch_array()) {
					echo "<option value = '$y'>$z</option>";
				}
			} else {
				list($cat) = $con->getArray("select request_category from options_requesttype where record_id = '$_POST[id]';");
				echo $cat;
			}
		break;

		case "newAppointment":
			$con->dbquery("INSERT INTO patient_appointment (patient_id,patient_name,`address`,birthdate,gender,contact_no,guardian,so_no,so_date,clinic,preferred_doctor,request_category,scheduled_date,scheduled_slot,memo,created_by,created_on) VALUES ('$_POST[appPatientId]','".$con->escapeString(htmlentities($_POST['appPatientName']))."','".$con->escapeString(htmlentities($_POST['appPatientAddress']))."','".$con->formatDate($_POST['appBirthdate'])."','$_POST[appGender]','$_POST[appContactNo]','".$con->escapeString($_POST['appGuardian'])."','$_POST[appSOno]','".$con->formatDate($_POST['appSOdate'])."','$_POST[appConsultType]','$_POST[appDoctor]','$_POST[appCategory]','".$con->formatDate($_POST['appDate'])."','$_POST[appSchedule]','".$con->escapeString($_POST['appRemarks'])."','$_SESSION[userid]',now());");
		break;

		case "viewAppointment":
			$ap = $con->getArray("SELECT *,DATE_FORMAT(birthdate,'%m/%d/%Y') AS bday, DATE_FORMAT(so_date,'%m/%d/%Y') AS sodate, DATE_FORMAT(scheduled_date,'%m/%d/%Y') AS sdate, if(clinic='MEDICAL','1','2') as category FROM patient_appointment WHERE record_id = '$_POST[lid]';");
			echo json_encode($ap);
		break;

		case "updateAppointment":
			$con->dbquery("UPDATE IGNORE patient_appointment set patient_id = '$_POST[appPatientId]', patient_name = '".$con->escapeString(htmlentities($_POST['appPatientName']))."', `address` = '".$con->escapeString(htmlentities($_POST['appPatientAddress']))."', birthdate = '".$con->formatDate($_POST['appBirthdate'])."', gender = '$_POST[appGender]', contact_no = '$_POST[appContactNo]',guardian = '".$con->escapeString($_POST['appGuardian'])."', so_no = '$_POST[appSOno]', so_date = '".$con->formatDate($_POST['appSOdate'])."', clinic = '$_POST[appConsultType]', preferred_doctor = '$_POST[appDoctor]', request_category = '$_POST[appCategory]', scheduled_date = '".$con->formatDate($_POST['appDate'])."', scheduled_slot = '$_POST[appSchedule]', memo = '".$con->escapeString($_POST['appRemarks'])."', updated_by = '$_SESSION[userid]',updated_on = now() where record_id = '$_POST[appRecordId]';");
		break;

		case "cancelAppointment":
			$con->dbquery("UPDATE IGNORE patient_appointment set `status` = 'Cancelled', updated_by = '$_SESSION[userid]', updated_on = now() where record_id = '$_POST[id]';");
		break;

		case "newDoctorProfile":
			
			$uploadDir = "../images/signatures/";

			$fileName = $_FILES['doctorSignature']['name'];
			$tmpName = $_FILES['doctorSignature']['tmp_name'];
			
			
			if($fileName!='') {

				/* CHANGE UNIQUE FILENAME TO PREVENT DUPLICATION */
				$ext = substr(strrchr($fileName, "."), 1);
				$randName = md5(rand() * time());
				$newFileName = $randName . "." . $ext;
				$filePath = $uploadDir . $newFileName;
				$result = move_uploaded_file($tmpName, $filePath);
			}

			if(!$_POST['doctorMon']) {	$mon = 'N'; $monSchedule = '';	} else { $mon = 'Y'; $monSchedule = $_POST['doctorMonSchedule']; } 
			if(!$_POST['doctorTue']) {	$tue = 'N'; $tueSchedule = '';	} else { $tue = 'Y'; $tueSchedule = $_POST['doctorTueSchedule']; }
			if(!$_POST['doctorWed']) {	$wed = 'N'; $wedSchedule = '';	} else { $wed = 'Y'; $wedSchedule = $_POST['doctorWedSchedule']; }
			if(!$_POST['doctorThu']) {	$thu = 'N'; $thuSchedule = '';	} else { $thu = 'Y'; $thuSchedule = $_POST['doctorThuSchedule']; }
			if(!$_POST['doctorFri']) {	$fri = 'N'; $friSchedule = '';	} else { $fri = 'Y'; $friSchedule = $_POST['doctorFriSchedule']; }
			if(!$_POST['doctorSat']) {	$sat = 'N'; $satSchedule = '';	} else { $sat = 'Y'; $satSchedule = $_POST['doctorSatSchedule']; }
			
			$con->dbquery("INSERT INTO options_doctors (fullname,prefix,specialization,license_no,contact_no,mon,mon_schedule,tue,tue_schedule,wed,wed_schedule,thu,thu_schedule,fri,fri_schedule,sat,sat_schedule,signature_file,created_by,created_on) VALUES ('".htmlentities($_POST['doctorFullname'])."','$_POST[doctorPrefix]','$_POST[doctorSpecialization]','$_POST[doctorLicenseNo]','$_POST[doctorContact]','$mon','$monSchedule','$tue','$tueSchedule','$wed','$wedSchedule','$thu','$thuSchedule','$fri','$friSchedule','$sat','$satSchedule','$newFileName','$_SESSION[userid]',NOW());");

		break;

		case "viewProviderProfile":
			$h = $con->getArray("SELECT *, if(signature_file!='',concat('<img src=\"images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"images/signatures/blank.png\" align=absmiddle />') as signature FROM options_doctors WHERE id = '$_POST[id]';");
			$i = array("xfullname"=>html_entity_decode($h['fullname']));
			$j = array_merge($h,$i);
			echo json_encode($j);
		break;

		case "saveSInfo":
			if($_POST['rid'] != '') {
				$con->dbquery("update ignore services_master set `code` = '$_POST[item_code]', barcode = '$_POST[item_barcode]', `description` = '".$con->escapeString($_POST['item_description'])."', fulldescription = '".$con->escapeString($_POST['item_fdescription'])."',category = '$_POST[item_category]',subcategory = '$_POST[item_sgroup]', sales_cat = '$_POST[item_scategory]', rev_acct = '$_POST[rev_acct]', unit = '$_POST[item_unit]', unit_cost = '".$con->formatDigit($_POST['item_unitcost'])."', unit_price = '".$con->formatDigit($_POST['item_unitprice'])."', with_specimen = '$_POST[with_specimen]', sample_type = '$_POST[sample_type]', with_subtests = '$_POST[with_subtests]', with_bom = '$_POST[with_bom]', lab_tat = '$_POST[lab_tat]', collection_tat = '".$con->formatDigit($_POST['collection_tat'])."', accession_tat = '".$con->formatDigit($_POST['accession_tat'])."', processing_tat = '".$con->formatDigit($_POST['processing_tat'])."', result_tat = '$_POST[result_tat]', container_type = '$_POST[container_type]', result_type = '$_POST[result_type]', updated_by = '$uid', updated_on = now() where id = '$_POST[rid]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO services_master (`code`,barcode,`description`,fulldescription,category,subcategory,sales_cat,rev_acct,unit,unit_cost,unit_price,with_specimen,sample_type,with_subtests,with_bom,lab_tat,collection_tat,accession_tat,processing_tat,result_tat,container_type,result_type,created_by,created_on) VALUES ('$_POST[item_code]','$_POST[item_barcode]','".$con->escapeString($_POST['item_description'])."','".$con->escapeString($_POST['item_fdescription'])."','$_POST[item_category]','$_POST[item_sgroup]','$_POST[item_scategory]','$_POST[rev_acct]','$_POST[item_unit]','".$con->formatDigit($_POST['item_unitcost'])."','".$con->formatDigit($_POST['item_unitprice'])."','$_POST[with_specimen]','$_POST[sample_type]','$_POST[with_subtests]','$_POST[with_bom]','$_POST[lab_tat]','".$con->formatDigit($_POST['collection_tat'])."','".$con->formatDigit($_POST['accession_tat'])."','".$con->formatDigit($_POST['processing_tat'])."','$_POST[result_tat]','$_POST[container_type]','$_POST[result_type]','$uid',now());");
			}
		break;

		case "deleteService":
			$con->dbquery("UPDATE IGNORE services_master set file_status = 'Deleted', deleted_by = '$_SESSION[userid]', deleted_on = NOW() where id = '$_POST[rid]';");
		break;

		case "checkifBoM":
			$e = $con->getArray("select count(*) from services_bom where `code` = '$_POST[scode]' and item_code = '$_POST[icode]';");
			if($e[0] == 0) { echo "ok"; }
		break;

		case "newBOM":
			$con->dbquery("insert ignore into services_bom (`code`,item_code,description,unit,qty,unit_cost,amount,remarks,created_by,created_on) values ('$_POST[scode]','$_POST[icode]','".$con->escapeString($_POST['description'])."','$_POST[unit]','".$con->formatDigit($_POST['qty'])."','".$con->formatDigit($_POST['cost'])."','".$con->formatDigit($_POST['amount'])."','".$con->escapeString($_POST['remarks'])."','$uid',now());");
		break;

		case "retrieveBoM":
			echo json_encode($con->getArray("select *, format(unit_cost,2) as ucost, format(amount,2) as amt from services_bom where record_id = '$_POST[rid]';"));
		break;

		case "updateBoM":
			$con->dbquery("update ignore services_bom set qty = '".$con->formatDigit($_POST['qty'])."', amount = '".$con->formatDigit($_POST['amount'])."', remarks = '".$con->escapeString($_POST['remarks'])."', updated_by = '$uid', updated_on = now() where record_id = '$_POST[rid]';");
		break;

		case "removeBoM":
			$con->dbquery("delete from services_bom where record_id = '$_POST[rid]';");
		break;

		case "addSublist":
			$con->dbquery("INSERT IGNORE INTO services_subtests (`parent`,`code`,`description`) values ('$_POST[parent]','$_POST[code]','".$con->escapeString($_POST['description'])."');");
		break;

		case "removeSublist":
			$con->dbquery("DELETE FROM services_subtests where record_id = '$_POST[lid]';");
		break;

		case "addAttribute":
			$con->dbquery("INSERT IGNORE INTO lab_testvalues (`code`,`attribute_type`,`attribute`,`unit`,`min_value`,`max_value`,`f_min_value`,`f_max_value`,`p_min_value`,`p_max_value`,`critical_low_value`,`critical_high_value`,`descriptive_value`) values ('$_POST[parent]','$_POST[attr_type]','" . $con->escapeString($_POST['attr']) . "','$_POST[unit]','$_POST[min]','$_POST[max]','$_POST[f_min]','$_POST[f_max]','$_POST[p_min]','$_POST[p_max]','$_POST[low]','$_POST[high]','". $con->escapeString($_POST['desc']) . "');");
		break;

		case "retrieveTestValues":
			echo json_encode($con->getArray("select * from lab_testvalues where record_id = '$_POST[lid]';"));
		break;

		case "updateAttribute":
			$con->dbquery("UPDATE IGNORE lab_testvalues set attribute_type = '$_POST[attr_type]', attribute = '" . $con->escapeString($_POST['attr']) . "', unit = '$_POST[unit]', min_value = '$_POST[min]', max_value = '$_POST[max]',  f_min_value = '$_POST[f_min]', f_max_value = '$_POST[f_max]', p_min_value = '$_POST[p_min]', p_max_value = '$_POST[p_max]', critical_low_value = '$_POST[low]', critical_high_value = '$_POST[high]', descriptive_value = '". $con->escapeString($_POST['desc']) . "', updated_by = '$uid', updated_on = now() where record_id = '$_POST[lid]';");
		break;

		case "removeAttribute":
			$con->dbquery("DELETE FROM lab_testvalues where record_id = '$_POST[lid]';");
		break;

		case "saveXrayTemplate":
			if($_POST['tempid'] == '') {
				$con->dbquery("INSERT IGNORE INTO xray_templates (template_category,title,template,xray_type,template_owner,created_on) VALUES ('$_POST[template_category]','".htmlentities($_POST['template_title'])."','".htmlentities($_POST['template_details'])."','$_POST[template_type]','".htmlentities($_POST['template_owner'])."',now());");
			} else {
				$con->dbquery("UPDATE IGNORE xray_templates set template_category='$_POST[template_category]', title = '".htmlentities($_POST['template_title'])."', template = '".htmlentities($_POST['template_details'])."', xray_type = '$_POST[template_type]', template_owner = '".htmlentities($_POST['template_owner'])."', updated_by = '$_SESSION[userid]', updated_on = now() where id = '$_POST[tempid]';");
			}
		break;

		case "attachLabSampleFile":
			$uploadDir = "../images/attachments/";
			$filePathUploadDir = "images/attachments/";

			$fileName = $_FILES['att_file']['name'];
			$tmpName = $_FILES['att_file']['tmp_name'];
			
			
			if($fileName!='') {

				/* CHANGE UNIQUE FILENAME TO PREVENT DUPLICATION */
				$ext = substr(strrchr($fileName, "."), 1);
				$randName = md5(rand() * time());
				$newFileName = $randName . "." . $ext;
				$filePath = $uploadDir . $newFileName;
				$result = move_uploaded_file($tmpName, $filePath);
			
				$fileUploadPath = $filePathUploadDir . $newFileName;
				
				//echo "update lab_samples set with_file = 'Y', file_title = '".$con->escapeString($_POST['att_title'])."', file_remarks = '".$con->escapeString($_POST['att_remarks'])."', file_path = '$fileUploadPath' where so_no = '$_POST[att_sono]' and `code` = '$_POST[att_code]' and serialno = '$_POST[att_serialno]';";
				$con->dbquery("update lab_samples set with_file = 'Y', file_title = '".$con->escapeString($_POST['att_title'])."', file_remarks = '".$con->escapeString($_POST['att_remarks'])."', file_path = '$fileUploadPath' where so_no = '$_POST[att_sono]' and `code` = '$_POST[att_code]' and serialno = '$_POST[att_serialno]';");
				$con->updateLabSampleStatus($_POST['att_sono'],'O001',$_POST['att_serialno'],'3',$bid,$uid);	

			}


		break;

		case "unpublishResult":
			$con->dbquery("update lab_samples set status = '1', updated_by = '$uid', updated_on = now() where record_id = '$_POST[lid]';");
		break;

		case "saveVitals":
			
			$pmh = '';
			foreach($_POST['pe_medhistory'] as $mval) {
				$mh .= $mval . ",";
			}

			list($uid) = $con->getArray("select id from options_doctors where uid = '$_SESSION[userid]';");

			switch($_SESSION['type']) {
				/* USER IS EXAMINER */
				case "2":
					$updateString = ", examined_by = '$uid', examined_on = now() ";
				break;
				default:
					$updateString = ", updated_by = '$_SESSION[userid]', updated_on = now() ";	
				break;

			}

			
			if($mh!='') { $pmh = substr($mh,0,-1); }
			
			$sqlQuery = "UPDATE IGNORE peme SET pe_type = '$_POST[pe_type]', contactno = '$_POST[pe_contact]', temp = '$_POST[pe_temp]', pulse = '$_POST[pe_pr]', rr = '$_POST[pe_rr]', bp = '$_POST[pe_bp]', ht = '$_POST[pe_ht]', wt = '$_POST[pe_wt]', lefteye = '$_POST[pe_lefteye]', righteye = '$_POST[pe_righteye]', jaegerleft = '$_POST[j_lefteye]', jaegerright = '$_POST[j_righteye]', bmi = '$_POST[pe_bmi]', with_glasses = '$_POST[pe_glasses]', bmi_category = '$_POST[pe_bmitype]', pm_history = '$pmh', pm_others = '" . htmlentities($_POST['pm_others']) . "', fm_history = '" . htmlentities($_POST['pe_famhistory']) . "', pv_hospitalization = '" . htmlentities($_POST['pe_hospitalization']) . "', current_med = '$_POST[pe_current_med]', mens_history = '$_POST[pe_menshistory]', parity = '$_POST[pe_parity]', lmp = '$_POST[pe_lmp]', contraceptives = '$_POST[pe_contra]', smoker = '$_POST[pe_smoker]', pregnant = '$_POST[pe_pregnant]', alcoholic = '$_POST[pe_alcoholic]', drugs = '$_POST[pe_drugs]', hs_normal = '$_POST[pe_hs_normal]', hs_findings = '$_POST[pe_hs_findings]', ee_normal = '$_POST[pe_ee_normal]', ee_findings = '$_POST[pe_ee_findings]', sa_normal = '$_POST[pe_sa_normal]', sa_findings = '$_POST[pe_sa_findings]', nose_normal = '$_POST[pe_nose_normal]', nose_findings = '$_POST[pe_nose_findings]', lungs_normal = '$_POST[pe_lungs_normal]', lungs_findings = '$_POST[pe_lungs_findings]', heart_normal = '$_POST[pe_heart_normal]', heart_findings = '$_POST[pe_heart_findings]', abdomen_normal = '$_POST[pe_abdomen_normal]', abdomen_findings = '$_POST[pe_abdomen_findings]', genitals_normal = '$_POST[pe_genitals_normal]', genitals_findings = '$_POST[pe_genitals_findings]', mouth_normal = '$_POST[pe_mouth_normal]', mouth_findings = '$_POST[pe_mouth_findings]', extr_normal = '$_POST[pe_extr_normal]', extr_findings = '$_POST[pe_extr_findings]', neck_normal = '$_POST[pe_neck_normal]', neck_findings = '$_POST[pe_neck_findings]', ref_normal = '$_POST[pe_ref_normal]', ref_findings = '$_POST[pe_ref_findings]', check_normal = '$_POST[pe_check_normal]', check_findings = '$_POST[pe_check_findings]', bpe_normal = '$_POST[pe_bpe_normal]', bpe_findings = '$_POST[pe_bpe_findings]', rect_normal = '$_POST[pe_rect_normal]', rect_findings = '$_POST[pe_rect_findings]', chest_normal = '$_POST[pe_chest_normal]', chest_findings = '$_POST[pe_chest_findings]', cbc_normal = '$_POST[pe_cbc_normal]', cbc_findings = '$_POST[pe_cbc_findings]', ua_normal = '$_POST[pe_ua_findings_normal]', ua_findings = '$_POST[pe_ua_findings]', se_normal = '$_POST[pe_se_normal]', se_findings = '$_POST[pe_se_findings]', dt_normal = '$_POST[pe_dt_normal]', dt_findings = '$_POST[pe_dt_findings]', ecg_normal = '$_POST[pe_ecg_normal]', ecg_findings = '$_POST[pe_ecg_findings]',pap_normal = '$_POST[pe_papsmear_normal]', pap_findings = '$_POST[pe_pap_findings]', others1_name = '$_POST[pe_others1]', others1_normal = '$_POST[pe_others1_normal]', others1_findings = '$_POST[pe_others1_findings]', others2_name = '$_POST[pe_others2]', others2_normal = '$_POST[pe_others2_normal]', others2_findings = '$_POST[pe_others2_findings]', pe_fit = '$_POST[pe_fit]', classification = '$_POST[pe_class]', class_b = '$_POST[pe_class_b]', class_b_remarks1 = '$_POST[pe_class_b_remarks1]', class_b_remarks2 = '$_POST[pe_class_b_remarks2]', class_c = '$_POST[pe_class_c]', class_c_remarks1 = '$_POST[pe_class_c_remarks1]', class_c_remarks2 = '$_POST[pe_class_c_remarks2]', pending_remarks = '$_POST[pe_eval_remarks]', overall_remarks  = '$_POST[pe_remarks]',pre_examined_by = '$_POST[pe_pre_examined_by]',pre_examined_on = now() $updateString WHERE so_no = '$_POST[pe_sono]' AND pid = '$_POST[pe_pid]';";
			$con->dbquery($sqlQuery);
			$con->dbquery("update ignore peme set status = 'Finalized' where examined_by != '' and so_no = '$_POST[pe_sono]' AND pid = '$_POST[pe_pid]';");
		
		break;

		case "saveSignature":

			list($prevPath) = $con->getArray("select signature_path from peme where so_no = '$_POST[so_no]' and pid = '$_POST[pid]';");
			if($prevPath != '') {
				unlink($prevPath);
			}

			$ranString = $con->generateRandomString(32);
			$ranString .= $_POST['so_no'] . $_POST['pid'];

			$path = "../images/signatures/peme/";
			$image_parts=explode(";base64,",$_POST['jsonSignature']);
			$image_type_aux=explode("image/",$image_parts[0]);
			$image_type=$image_type_aux[1];
			$image_base64=base64_decode($image_parts[1]);
			$file .= $path . $ranString . '.png';
			file_put_contents($file,$image_base64);

			$con->dbquery("update ignore peme set signature_path = '$file' where so_no = '$_POST[so_no]' and pid = '$_POST[pid]';");
			//echo "update ignore peme set signature_path = '$file' where so_no = '$_POST[so_no]' and pid = '$_POST[pid]';";

		break;

		case "savePhoto":

			list($prevPath) = $con->getArray("select photo_path from peme where so_no = '$_POST[so_no]' and pid = '$_POST[pid]';");
			if($prevPath != '') {
				unlink($prevPath);
			}

			$ranString = $con->generateRandomString(32);
			$ranString .= $_POST['so_no'] . $_POST['pid'];

			$path = "../images/photos/peme/";
			$image_parts=explode(";base64,",$_POST['jsonSignature']);
			$image_type_aux=explode("image/",$image_parts[0]);
			$image_type=$image_type_aux[1];
			$image_base64=base64_decode($image_parts[1]);
			$file .= $path . $ranString . '.png';
			file_put_contents($file,$image_base64);

			$con->dbquery("update ignore peme set photo_path = '$file' where so_no = '$_POST[so_no]' and pid = '$_POST[pid]';");
			//echo "update ignore peme set signature_path = '$file' where so_no = '$_POST[so_no]' and pid = '$_POST[pid]';";

		break;

		/* Laboratory Information System */
		case "grabPatient":
			$con->dbquery("INSERT IGNORE INTO queueing (priority_no,so_no,patient_name,gender,calling_station,date_queued,time_queued,queued_by) values ('$_POST[pri_no]','$_POST[so_no]','".$con->escapeString($_POST['patient'])."','$_POST[gender]','$_POST[callStation]','".date('Y-m-d')."',now(),'$_SESSION[userid]');");
		break;

		case "retrievePatientForPeme":
			echo json_encode($con->getArray("SELECT a.*, b.lname, b.fname, b.mname, d.patient_address, DATE_FORMAT(b.birthdate,'%m/%d/%Y') AS bday, b.gender, b.occupation, b.employer, b.mobile_no, c.civil_status, (YEAR(a.so_date) - YEAR(b.birthdate)) AS age FROM peme a LEFT JOIN patient_info b ON a.pid = b.patient_id left join options_civilstatus c on b.cstat = c.csid left join so_header d on a.so_no = d.so_no and a.branch = d.branch WHERE a.so_no = '$_POST[so_no]' AND a.branch = '$bid';"));
		break;

		case "retrieveOrderForSample":
			$a = $con->getArray("SELECT LPAD(a.so_no,6,0) AS so, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, LPAD(b.patient_id,6,0) AS pid, b.patient_name, b.patient_address, IF(c.gender='M','Male','Female') AS gender, DATE_FORMAT(c.birthdate,'%m/%d/%Y') AS birthdate, YEAR(b.so_date) - YEAR(c.birthdate) AS age, b.physician, a.procedure as  particulars, a.code, a.parent_code, a.sampletype AS sample_type, c.mobile_no, c.email_add, e.patientstatus, f.sample_type as xsample, d.container_type FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN services_master d ON a.code = d.code LEFT JOIN options_patientstat e ON b.patient_stat = e.id left join options_sampletype f on a.sampletype = f.id WHERE a.so_no = '$_POST[sono]' AND a.code = '$_POST[code]' and a.parent_code = '$_POST[parentcode]';");

			/* Generate Auatomatic Serial No. for Specimen */
			list($code) = $con->getArray("select `sn_code` from options_sampletype where id = '$a[sample_type]';");
			$series = $con->getArray("SELECT concat('$code',LPAD(IFNULL(MAX(series+1),1),9,0)) as series FROM (SELECT TRIM(LEADING '0' FROM SUBSTRING(`serialno`,2,9)) AS series FROM lab_samples WHERE sampletype = '$a[sample_type]') a;");

			/* Count for tests that uses similar container & sample type */
			$scount = $con->getArray("select count(*) as samplecount from lab_samples where so_no = '$_POST[sono]' and samplecontainer = '$a[container_type]' and sampletype = '$a[sample_type]' and extracted = 'N' and code != '$a[code]';");

			$result = array_merge($a,$series,$scount);

			echo json_encode($result);
		break;

		case "retrieveSameSample":
			$sQuery = $con->dbquery("select `code`, `procedure` from lab_samples where so_no = '$_POST[sono]' and sampletype = '$_POST[stype]' and samplecontainer = '$_POST[ctype]' and extracted = 'N' and code != '$_POST[code]' order by `procedure`;");
		
			$html  = '<fieldset name="sameTests" id="sameTest" style="padding:5px;">
						<legend class="bareBold" style="font-size: 9px;">Use sample for the following request: </legend>
						';
			while($sRow = $sQuery->fetch_array()) {
				$html .= '<input type="checkbox" id="othercodes[]" name="othercodes[]" value="' . $sRow['code'] . '" checked>&nbsp;<span class="bareBold">'.$sRow['procedure'].'</span><br/>';

			}
			$html .= '</fieldset>';
			echo $html;
		break;
		
		case "saveSample":
		
			$tmpdate = explode(" ",$_POST['phleb_date']);
			$extractDate = $con->formatDate(trim($tmpdate[0]));
			$extractTime = $tmpdate[1];

			$con->dbquery("UPDATE IGNORE lab_samples set extracted = 'Y', serialno = '$_POST[phleb_serialno]', physician = '".htmlentities($_POST['phleb_physician'])."', testkit = '$_POST[phleb_testkit]', lotno = '$_POST[phleb_testkit_lotno]', expiry = '".$con->formatDate($_POST['phleb_testkit_expiry'])."', extractdate = '$extractDate', extractime = '$extractTime', extractby = '".$con->escapeString(htmlentities($_POST['phleb_by']))."', `location` = '$_POST[phleb_location]', remarks = '".$con->escapeString(htmlentities($_POST['phleb_remarks']))."', updated_by = '$uid', updated_on = now() WHERE `code` = '$_POST[phleb_code]' and parent_code = '$_POST[phleb_parentcode]' and so_no = '$_POST[phleb_sono]';");

			/* Check if other samples */
			if(count($_POST['othercodes']) > 0) {
				foreach($_POST['othercodes'] as $scode) {
					$con->dbquery("UPDATE IGNORE lab_samples set extracted = 'Y', serialno = '$_POST[phleb_serialno]', physician = '".htmlentities($_POST['phleb_physician'])."', testkit = '$_POST[phleb_testkit]', lotno = '$_POST[phleb_testkit_lotno]', expiry = '".$con->formatDate($_POST['phleb_testkit_expiry'])."', extractdate = '$extractDate', extractime = '$extractTime', extractby = '".$con->escapeString(htmlentities($_POST['phleb_by']))."', `location` = '$_POST[phleb_location]', remarks = '".$con->escapeString(htmlentities($_POST['phleb_remarks']))."', updated_by = '$uid', updated_on = now() WHERE `code` = '$scode' and so_no = '$_POST[phleb_sono]';");
				}
			}

			/* Update SO Status */
			$con->updateSOStatus($_POST['phleb_sono'],$bid);
	
		break;

		case "checkSerialStatus":
			echo json_encode($con->getArray("select count(*) as mycount from lab_samples where serialno = '$_POST[serialno]';"));
		break;

		case "checkMultipleChem":
			list($cat,$scat) = $con->getArray("SELECT category, subcategory FROM services_master WHERE `code` = '$_POST[code]';");
			if($cat == 1 && ($scat == 1 || $scat == 5)) {
				echo json_encode($con->getArray("select count(*) as mycount from lab_samples where serialno = '$_POST[serialno]' and `code` in (select `code` from services_master where category = '1' and subcategory in (1,5)) and `status` != '4';"));
			} else {
				echo json_encode(array("mycount"=>1));

			}		
		break;

		case "retrieveSample":
			echo json_encode($con->getArray("SELECT b.patient_name AS pname,`code`,`procedure`,sampletype,serialno, location, DATE_FORMAT(extractdate,'%m/%d/%Y') AS exdate,  SUBSTR(extractime,1,2) AS hr, SUBSTR(extractime,4,2) AS MIN, extractby FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch WHERE a.record_id = '$_POST[lid]';"));
		break;

		case "rejectSample":
			$con->dbquery("update ignore lab_samples set status = '2', rejection_remarks = '".$con->escapeString(htmlentities())."', updated_by = '$uid', updated_on = now() where record_id = '$_POST[lid]';");
		break;

		case "resultSingle":

			$a = $con->getArray("SELECT record_id AS id, LPAD(a.so_no,6,0) AS myso,DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, LPAD(b.patient_id,6,0) AS mypid,b.patient_name AS pname, YEAR(b.so_date) - YEAR(c.birthdate) AS age,IF(c.gender='M','Male','Female') AS gender, DATE_FORMAT(c.birthdate,'%m/%d/%Y') AS bday,e.patientstatus,b.physician,a.code,a.procedure,sampletype,serialno,DATE_FORMAT(extractdate,'%m/%d/%Y') AS exday,TIME_FORMAT(extractime,'%h:%i %p') AS etime,extractby,a.location,a.testkit,a.lotno,date_format(a.expiry,'%m/%d/%Y') as expiry FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_patientstat e ON b.patient_stat = e.id WHERE a.record_id = '$_POST[lid]';");
			
			switch($_POST['submod']) {
				case "labSingle":
					list($isCount) = $con->getArray("select count(*) from lab_singleResult where so_no = '$a[myso]' and branch = '$bid' and code = '$a[code]' and serialno = '$a[serialno]';");
					if($isCount > 0) {
						$b = $con->getArray("SELECT attribute,unit,lower_value as `min_value`,upper_value as `max_value`,`value`,remarks FROM lab_singleResult WHERE so_no = '$a[myso]' and branch = '$bid' and code = '$a[code]' and serialno = '$a[serialno]';");	
					} else {
						$b = $con->getArray("SELECT attribute,unit,`min_value`,`max_value`,'' as `value`,'' as remarks FROM lab_testvalues WHERE `code` = '$a[code]';");
					}
				break;
				case "enumResult":
					$b = $con->getArray("select method,testkit,lotno,expiry,patient_stat, result, performed_by, remarks from lab_enumresult where so_no = '$a[myso]' and branch = '$bid' and serialno = '$a[serialno]' and code = '$a[code]';");
				break;
				case "bloodType":
					$b = $con->getArray("select patient_stat, result, rh, performed_by, remarks from lab_bloodtyping where so_no = '$a[myso]' and branch = '$bid' and serialno = '$a[serialno]' and code = '$a[code]';");
					if(!$b) { $b = array("rh"=>'Positive',"result"=>'A+'); }
				break;
				case "dengueResultView":
					$b = $con->getArray("select method,testkit,lotno,expiry,dengue_ag,dengue_igg,dengue_igm,remarks,performed_by from lab_dengue where so_no = '$a[myso]' and branch = '$bid' and serialno = '$a[serialno]';");
				break;
				case "bleedclotResult":
					$b = $con->getArray("select patient_stat,result_date,bleed_min,bleed_sec,clot_min,clot_sec,remarks,performed_by from lab_bleed_clot where so_no = '$a[myso]' and branch = '$bid' and serialno = '$a[serialno]';");
				break;
				case "hivResult":
					$b = $con->getArray("select method,testkit,lotno,expiry,result,remarks,performed_by from lab_hivresult where so_no = '$a[myso]' and branch = '$bid' and serialno = '$a[serialno]';");
				break;
				case "antigenResult":
					$b = $con->getArray("select result_date, result, performed_by, remarks from lab_antigenresult where so_no = '$a[myso]' and branch = '$bid' and serialno = '$a[serialno]' and code = '$a[code]';");
				break;
			}

			if(count($b) > 0) {
				echo json_encode(array_merge($a,$b));
			} else { echo json_encode($a); }
		break;

		case "saveHivResult":
			list($cnt) = $con->getArray("select count(*) from lab_hivresult where so_no = '$_POST[hiv_sono]' and branch = '$bid' and code = '$_POST[hiv_code]' and serialno = '$_POST[hiv_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE lab_hivresult set method = '$_POST[hiv_method]', testkit = '$_POST[hiv_testkit]', lotno = '$_POST[hiv_testkit_lotno]', expiry = '$_POST[hiv_testkit_expiry]', result_date = '".$con->formatDate($_POST['hiv_date'])."', patient_stat = '$_POST[hiv_patientstat]', result = '$_POST[hiv_result]', performed_by = '$_POST[hiv_result_by]', remarks = '".$con->escapeString($_POST['hiv_remarks']) . "' where so_no = '$_POST[hiv_sono]' and branch = '$bid' and code = '$_POST[hiv_code]' and serialno = '$_POST[hiv_serialno]';");
			} else {
				$con->dbquery("INSERT INTO lab_hivresult (branch,so_no,result_date,patient_stat,sampletype,method,testkit,lotno,expiry,serialno,code,result,performed_by,remarks,created_by,created_on) VALUES ('$bid','$_POST[hiv_sono]','".$con->formatDate($_POST['hiv_date'])."','$_POST[hiv_patientstat]','$_POST[hiv_spectype]','$_POST[hiv_method]','$_POST[hiv_testkit]','$_POST[hiv_testkit_lotno]','$_POST[hiv_testkit_expiry]','$_POST[hiv_serialno]','$_POST[hiv_code]','$_POST[hiv_result]','$_POST[hiv_result_by]','".$con->escapeString($_POST['hiv_remarks'])."','$uid',now());");
			}
			$con->updateLabSampleStatus($_POST['hiv_sono'],$_POST['hiv_code'],$_POST['hiv_serialno'],'3',$bid,$uid);
		break;

		case "validateHivResult":
			$con->dbquery("UPDATE lab_hivresult set method = '$_POST[hiv_method]', testkit = '$_POST[hiv_testkit]', lotno = '$_POST[hiv_testkit_lotno]', expiry = '$_POST[hiv_testkit_expiry]', result_date = '".$con->formatDate($_POST['hiv_date'])."', patient_stat = '$_POST[hiv_patientstat]', result = '$_POST[hiv_result]', performed_by = '$_POST[hiv_result_by]', remarks = '".$con->escapeString($_POST['hiv_remarks']) . "' where so_no = '$_POST[hiv_sono]' and branch = '$bid' and code = '$_POST[hiv_code]' and serialno = '$_POST[hiv_serialno]';");
			$con->validateResult("lab_hivresult",$_POST['hiv_sono'],$_POST['hiv_code'],$_POST['hiv_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['hiv_sono'],$_POST['hiv_code'],$_POST['hiv_serialno'],'4',$bid,$uid);	
			$con->dbquery("update so_details set result_available = 'Y' where so_no = '$_POST[hiv_sono]' and branch = '$bid' and code = '$_POST[hiv_code]' and sample_serialno = '$_POST[hiv_serialno]';");
		break;

		case "saveEnumResult":
			list($cnt) = $con->getArray("select count(*) from lab_enumresult where so_no = '$_POST[enum_sono]' and branch = '$bid' and code = '$_POST[enum_code]' and serialno = '$_POST[enum_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE lab_enumresult set result_date = '".$con->formatDate($_POST['enum_date'])."', patient_stat = '$_POST[enum_patientstat]', result = '$_POST[enum_result]', performed_by = '$_POST[enum_result_by]', remarks = '".$con->escapeString($_POST['enum_remarks']) . "' where so_no = '$_POST[enum_sono]' and branch = '$bid' and code = '$_POST[enum_code]' and serialno = '$_POST[enum_serialno]';");
			} else {
				$con->dbquery("INSERT INTO lab_enumresult (branch,so_no,result_date,patient_stat,sampletype,serialno,code,result,performed_by,remarks,created_by,created_on) VALUES ('$bid','$_POST[enum_sono]','".$con->formatDate($_POST['enum_date'])."','$_POST[enum_patientstat]','$_POST[enum_spectype]','$_POST[enum_serialno]','$_POST[enum_code]','$_POST[enum_result]','$_POST[enum_result_by]','".$con->escapeString($_POST['enum_remarks'])."','$uid',now());");
			}
			$con->updateLabSampleStatus($_POST['enum_sono'],$_POST['enum_code'],$_POST['enum_serialno'],'3',$bid,$uid);
		break;

		case "validateEnumResult":
			$con->dbquery("UPDATE lab_enumresult set result_date = '".$con->formatDate($_POST['enum_date'])."', patient_stat = '$_POST[enum_patientstat]', result = '$_POST[enum_result]', performed_by = '$_POST[enum_result_by]', remarks = '".$con->escapeString($_POST['enum_remarks']) . "' where so_no = '$_POST[enum_sono]' and branch = '$bid' and code = '$_POST[enum_code]' and serialno = '$_POST[enum_serialno]';");
			$con->validateResult("lab_enumresult",$_POST['enum_sono'],$_POST['enum_code'],$_POST['enum_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['enum_sono'],$_POST['enum_code'],$_POST['enum_serialno'],'4',$bid,$uid);	
			$con->dbquery("update so_details set result_available = 'Y' where so_no = '$_POST[enum_sono]' and branch = '$bid' and code = '$_POST[enum_code]' and sample_serialno = '$_POST[enum_serialno]';");
		break;

		case "saveAntigenResult":
			list($cnt) = $con->getArray("select count(*) from lab_antigenresult where so_no = '$_POST[antigen_sono]' and branch = '$bid' and code = '$_POST[antigen_code]' and serialno = '$_POST[antigen_serialno]';");
			if($cnt > 0) {
				$con->dbquery("update ignore lab_antigenresult set result = '$_POST[antigen_result]', remarks = '".$con->escapeString($_POST['antigen_remarks']) . "' where so_no = '$_POST[antigen_sono]' and branch = '$bid' and code = '$_POST[antigen_code]' and serialno = '$_POST[antigen_serialno]';");
			} else {
				$con->dbquery("INSERT INTO lab_antigenresult (branch,so_no,pid,patient_stat,physician,result_date,sampletype,serialno,code,result,remarks,performed_by,created_by,created_on) VALUES ('$bid','$_POST[antigen_sono]','$_POST[antigen_pid]','$_POST[antigen_patientstat]','$_POST[antigen_physician]','".$con->formatDate($_POST['antigen_date'])."','$_POST[antigen_spectype]','$_POST[antigen_serialno]','$_POST[antigen_code]','$_POST[antigen_result]','".$con->escapeString($_POST['antigen_remarks'])."','$_POST[antigen_result_by]','$uid',now());");
			}
			$con->updateLabSampleStatus($_POST['antigen_sono'],$_POST['antigen_code'],$_POST['antigen_serialno'],'3',$bid,$uid);
		break;

		case "validateAntigenResult":
			$con->dbquery("update ignore lab_antigenresult set result = '$_POST[antigen_result]', remarks = '".$con->escapeString($_POST['antigen_remarks']) . "' where so_no = '$_POST[antigen_sono]' and branch = '$bid' and code = '$_POST[antigen_code]' and serialno = '$_POST[antigen_serialno]';");
			$con->validateResult("lab_antigenresult",$_POST['antigen_sono'],$_POST['antigen_code'],$_POST['antigen_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['antigen_sono'],$_POST['antigen_code'],$_POST['antigen_serialno'],'4',$bid,$uid);	
			$con->dbquery("update so_details set result_available = 'Y' where so_no = '$_POST[antigen_sono]' and branch = '$bid' and code = '$_POST[antigen_code]' and sample_serialno = '$_POST[antigen_serialno]';");
		break;

		case "saveBloodType":
			list($cnt) = $con->getArray("select count(*) from lab_bloodtyping where so_no = '$_POST[btype_sono]' and branch = '$bid' and code = '$_POST[btype_code]' and serialno = '$_POST[btype_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE IGNORE lab_bloodtyping set patient_stat = '$_POST[btype_patientstat]', result = '$_POST[btype_result]', rh = '$_POST[btype_rh]', performed_by = '$_POST[btype_result_by]', result_date = '".$con->formatDate($_POST['btype_date'])."', remarks = '".$con->escapeString($_POST['btype_remarks']) . "' where so_no = '$_POST[btype_sono]' and branch = '$bid' and code = '$_POST[btype_code]' and serialno = '$_POST[btype_serialno]';");
				echo "UPDATE lab_bloodtyping set patient_stat = '$_POST[btype_patientstat]', result = '$_POST[btype_result]', rh = '$_POST[btype_rh]', performed_by = '$_POST[btype_result_by]', result_date = '".$con->formatDate($_POST['btype_date'])."', remarks = '".$con->escapeString($_POST['btype_remarks']) . "' where so_no = '$_POST[btype_sono]' and branch = '$bid' and code = '$_POST[btype_code]' and serialno = '$_POST[btype_serialno]';";
			} else {
				$con->dbquery("INSERT INTO lab_bloodtyping (branch,so_no,result_date,patient_stat,sampletype,serialno,code,result,rh,performed_by,remarks,created_by,created_on) VALUES ('$bid','$_POST[btype_sono]','".$con->formatDate($_POST['btype_date'])."','$_POST[btype_patientstat]','$_POST[btype_spectype]','$_POST[btype_serialno]','$_POST[btype_code]','$_POST[btype_result]','$_POST[btype_rh]','$_POST[btype_result_by]','".$con->escapeString($_POST['btype_remarks'])."','$uid',now());");
			}
			$con->updateLabSampleStatus($_POST['btype_sono'],$_POST['btype_code'],$_POST['btype_serialno'],'3',$bid,$uid);
		break;

		case "validateBloodType":
			$con->dbquery("UPDATE IGNORE lab_bloodtyping set patient_stat = '$_POST[btype_patientstat]', result = '$_POST[btype_result]', rh = '$_POST[btype_rh]', performed_by = '$_POST[btype_result_by]', remarks = '".$con->escapeString($_POST['btype_remarks']) . "' where so_no = '$_POST[btype_sono]' and branch = '$bid' and code = '$_POST[btype_code]' and serialno = '$_POST[btype_serialno]';");
			$con->validateResult("lab_bloodtyping",$_POST['btype_sono'],$_POST['btype_code'],$_POST['btype_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['btype_sono'],$_POST['btype_code'],$_POST['btype_serialno'],'4',$bid,$uid);	
			$con->dbquery("update so_details set result_available = 'Y' where so_no = '$_POST[enum_sono]' and branch = '$bid' and code = '$_POST[btype_code]' and sample_serialno = '$_POST[btype_serialno]';");
		break;

		case "saveBleedClot":
			list($cnt) = $con->getArray("select count(*) from lab_bleed_clot where so_no = '$_POST[bleed_clot_sono]' and branch = '$bid' and code = '$_POST[bleed_clot_code]' and serialno = '$_POST[bleed_clot_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE IGNORE lab_bleed_clot set result_date = '".$con->formatDate($_POST['bleed_clot_date'])."',patient_stat = '$_POST[bleed_clot_patientstat]', bleed_min = '$_POST[bleed_mins]', bleed_sec = '$_POST[bleed_secs]', clot_min = '$_POST[clot_mins]', clot_sec = '$_POST[clot_secs]', remarks = '".$con->escapeString($_POST['bleed_clot_remarks'])."', performed_by = '$_POST[bleed_clot_result_by]' where so_no = '$_POST[bleed_clot_sono]' and branch = '$bid' and code = '$_POST[bleed_clot_code]' and serialno = '$_POST[bleed_clot_serialno]';");
			} else {
				$con->dbquery("INSERT INTO lab_bleed_clot (branch,so_no,patient_stat,result_date,sampletype,serialno,code,bleed_min,bleed_sec,clot_min,clot_sec,remarks,performed_by,created_by,created_on) VALUES ('$bid','$_POST[bleed_clot_sono]','$_POST[bleed_clot_patientstat]','".$con->formatDate($_POST['bleed_clot_date'])."','$_POST[bleed_clot_spectype]','$_POST[bleed_clot_serialno]','$_POST[bleed_clot_code]','$_POST[bleed_mins]','$_POST[bleed_secs]','$_POST[clot_mins]','$_POST[clot_secs]','".$con->escapeString($_POST['bleed_clot_remarks'])."','$_POST[bleed_clot_result_by]','$uid',now());");
			}
			$con->updateLabSampleStatus($_POST['bleed_clot_sono'],$_POST['bleed_clot_code'],$_POST['bleed_clot_serialno'],'3',$bid,$uid);
		break;

		case "validateBleedClot":
			$con->dbquery("UPDATE IGNORE lab_bleed_clot set result_date = '".$con->formatDate($_POST['bleed_clot_date'])."',patient_stat = '$_POST[bleed_clot_patientstat]', bleed_min = '$_POST[bleed_mins]', bleed_sec = '$_POST[bleed_secs]', clot_min = '$_POST[clot_mins]', clot_sec = '$_POST[clot_secs]', remarks = '".$con->escapeString($_POST['bleed_clot_remarks'])."', performed_by = '$_POST[bleed_clot_result_by]' where so_no = '$_POST[bleed_clot_sono]' and branch = '$bid' and code = '$_POST[bleed_clot_code]' and serialno = '$_POST[bleed_clot_serialno]';");
			$con->validateResult("lab_bleed_clot",$_POST['bleed_clot_sono'],$_POST['bleed_clot_code'],$_POST['bleed_clot_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['bleed_clot_sono'],$_POST['bleed_clot_code'],$_POST['bleed_clot_serialno'],'4',$bid,$uid);	
			$con->dbquery("update so_details set result_available = 'Y' where so_no = '$_POST[bleed_clot_sono]' and branch = '$bid' and code = '$_POST[bleed_clot_code]' and sample_serialno = '$_POST[bleed_clot_serialno]';");
		break;

		case "savePregnancyResult":
			list($cnt) = $con->getArray("select count(*) from lab_enumresult where so_no = '$_POST[pt_sono]' and branch = '$bid' and code = '$_POST[pt_code]' and serialno = '$_POST[pt_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE lab_enumresult set result = '$_POST[pt_result]', result_date = '".$con->formatDate($_POST['pt_date'])."', remarks = '".$con->escapeString($_POST['pt_remarks']) . "' where so_no = '$_POST[pt_sono]' and branch = '$bid' and code = '$_POST[pt_code]' and serialno = '$_POST[pt_serialno]';");
			} else {
				$con->dbquery("INSERT INTO lab_enumresult (branch,so_no,result_date,sampletype,serialno,code,result,remarks,created_by,created_on) VALUES ('$bid','$_POST[pt_sono]','".$con->formatDate($_POST['pt_date'])."','$_POST[pt_spectype]','$_POST[pt_serialno]','$_POST[pt_code]','$_POST[pt_result]','".$con->escapeString($_POST['pt_remarks'])."','$uid',now());");
			}
			$con->updateLabSampleStatus($_POST['pt_sono'],$_POST['pt_code'],$_POST['pt_serialno'],'3',$bid,$uid);
		break;

		case "validatePregnancyResult":
			$con->dbquery("UPDATE lab_enumresult set result = '$_POST[pt_result]', result_date = '".$con->formatDate($_POST['pt_date'])."', remarks = '".$con->escapeString($_POST['pt_remarks']) . "' where so_no = '$_POST[pt_sono]' and branch = '$bid' and code = '$_POST[pt_code]' and serialno = '$_POST[pt_serialno]';");
			$con->validateResult("lab_enumresult",$_POST['pt_sono'],$_POST['pt_code'],$_POST['pt_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['pt_sono'],$_POST['pt_code'],$_POST['pt_serialno'],'4',$bid,$uid);	
			$con->dbquery("update so_details set result_available = 'Y' where so_no = '$_POST[pt_sono]' and branch = '$bid' and code = '$_POST[pt_code]' and sample_serialno = '$_POST[pt_serialno]';");
		
		break;

		case "saveSingleValueResult":
			list($cnt) = $con->getArray("select count(*) from lab_singleresult where so_no = '$_POST[sresult_sono]' and branch = '$bid' and code = '$_POST[sresult_code]' and serialno = '$_POST[sresult_serialno]';");
			if($cnt>0) {
				$con->dbquery("UPDATE IGNORE lab_singleresult SET result_date = '".$con->formatDate($_POST['sresult_date'])."', `attribute`='$_POST[sresult_attribute]',`value`='$_POST[sresult_value]',lower_value='$_POST[sresult_lowerlimit]',upper_value='$_POST[sresult_upperlimit]',remarks='".$con->escapeString(htmlentities($_POST['sresult_remarks']))."', updated_by='$uid',updated_on = now() where so_no = '$_POST[sresult_sono]' and branch = '$bid' and code = '$_POST[sresult_code]' and serialno = '$_POST[sresult_serialno]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_singleresult (branch,so_no,result_date,sampletype,serialno,`code`,`procedure`,`attribute`,unit,`value`,lower_value,upper_value,remarks,created_by,created_on) values ('$bid','$_POST[sresult_sono]','".$con->formatDate($_POST['sresult_date'])."','$_POST[sresult_spectype]','$_POST[sresult_serialno]','$_POST[sresult_code]','".$con->escapeString(htmlentities($_POST['sresult_procedure']))."','$_POST[sresult_attribute]','$_POST[sresult_unit]','$_POST[sresult_value]','$_POST[sresult_lowerlimit]','$_POST[sresult_upperlimit]','".$con->escapeString(htmlentities($_POST['sresult_remarks']))."','$uid',now());");
			}
			$con->updateLabSampleStatus($_POST['sresult_sono'],$_POST['sresult_code'],$_POST['sresult_serialno'],'3',$bid,$uid);
		break;

		case "validateSingleValueResult":
			$con->dbquery("UPDATE IGNORE lab_singleresult SET result_date = '".$con->formatDate($_POST['sresult_date'])."', `attribute`='$_POST[sresult_attribute]',`value`='$_POST[sresult_value]',lower_value='$_POST[sresult_lowerlimit]',upper_value='$_POST[sresult_upperlimit]',remarks='".$con->escapeString(htmlentities($_POST['sresult_remarks']))."', updated_by='$uid',updated_on = now() where so_no = '$_POST[sresult_sono]' and branch = '$bid' and code = '$_POST[sresult_code]' and serialno = '$_POST[sresult_serialno]';");
			$con->validateResult("lab_singleresult",$_POST['sresult_sono'],$_POST['sresult_code'],$_POST['sresult_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['sresult_sono'],$_POST['sresult_code'],$_POST['sresult_serialno'],'4',$bid,$uid);	
			$con->dbquery("update so_details set result_available = 'Y' where so_no = '$_POST[sresult_sono]' and branch = '$bid' and code = '$_POST[sresult_code]' and sample_serialno = '$_POST[sresult_serialno]';");	
		break;

		case "saveDescResult":
			list($cnt) = $con->getArray("select count(*) from lab_descriptive where so_no = '$_POST[desc_sono]' and branch = '$bid' and code = '$_POST[desc_code]' and serialno = '$_POST[desc_serialno]';");
			if($cnt>0) {
				$con->dbquery("UPDATE IGNORE lab_descriptive SET impression = '".$con->escapeString(htmlentities($_POST['desc_impression']))."', physician = '".htmlentities($_POST['desc_physician'])."', consultant = '".htmlentities($_POST['desc_consultant'])."', result_type = '$_POST[desc_resultstat]', result_date = '".$con->formatDate($_POST['desc_date'])."', updated_by='$uid',updated_on = now() where so_no = '$_POST[desc_sono]' and branch = '$bid' and serialno = '$_POST[desc_serialno]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_descriptive (branch,so_no,result_date,sampletype,serialno,`code`,`procedure`,impression,physician,consultant,result_type,created_by,created_on) values ('$bid','$_POST[desc_sono]','".$con->formatDate($_POST['desc_date'])."','$_POST[desc_spectype]','$_POST[desc_serialno]','$_POST[desc_code]','".$con->escapeString(htmlentities($_POST['desc_procedure']))."','".$con->escapeString(htmlentities($_POST['desc_impression']))."','".htmlentities($_POST['desc_physician'])."','".htmlentities($_POST['desc_consultant'])."','$_POST[desc_resultstat]','$uid',now());");
				$con->updateLabSampleStatus($_POST['desc_sono'],$_POST['desc_code'],$_POST['desc_serialno'],'3',$bid,$uid);	
			}
		break;

		case "validateDescResult":
			
			$con->validateResult("lab_descriptive",$_POST['desc_sono'],$_POST['desc_code'],$_POST['desc_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['desc_sono'],$_POST['desc_code'],$_POST['desc_serialno'],'4',$bid,$uid);	
			$con->dbquery("update ignore so_details set result_available = 'Y' where so_no = '$_POST[desc_sono]' and branch = '$bid' and code = '$_POST[desc_code]' and sample_serialno = '$_POST[desc_serialno]';");
		
		break;

		case "invalidateDescResult":
			$con->dbquery("update ignore lab_descriptive set verified = 'N', verified_by = '', verified_on = '' where so_no = '$_POST[desc_sono]' and branch = '$bid' and code = '$_POST[desc_code]' and serialno = '$_POST[desc_serialno]';");
			$con->dbquery("update ignore so_details set result_available = 'N' where so_no = '$_POST[desc_sono]' and branch = '$bid' and code = '$_POST[desc_code]' and sample_serialno = '$_POST[desc_serialno]';");
			$con->dbquery("update ignore lab_samples set status = '3', updated_by = '$uid', updated_on = now() where so_no = '$_POST[desc_sono]' and branch = '$bid' and code = '$_POST[desc_code]' and serialno = '$_POST[desc_serialno]';");
		break;

		case "saveECGResult":
			list($cnt) = $con->getArray("select count(*) from lab_ecgresult where so_no = '$_POST[ecg_sono]' and branch = '$bid' and code = '$_POST[ecg_code]' and serialno = '$_POST[ecg_serialno]';");
			if($cnt>0) {
				$con->dbquery("UPDATE IGNORE lab_ecgresult SET occupation = '".$con->escapeString(htmlentities($_POST['ecg_occupation']))."', stat = '$_POST[ecg_stat]', prev_ecg_date = '".$con->formatDate($_POST['prev_ecg_date'])."', diagnosis = '".htmlentities($_POST['diagnosis'])."', medication = '".htmlentities($_POST['medication'])."', rate= '$_POST[ecg_rate]', rhythm = '$_POST[ecg_rhythm]', printerval = '$_POST[ecg_interval]', qrscomplex = '$_POST[ecg_qrscomplex]', qtc = '$_POST[ecg_qtc]', qti = '$_POST[ecg_qti]', axis= '$_POST[ecg_axis]', impression = '".$con->escapeString(htmlentities($_POST['ecg_impression']))."', consultant = '".htmlentities($_POST['ecg_consultant'])."', result_date = '".$con->formatDate($_POST['ecg_date'])."', updated_by='$uid',updated_on = now() where so_no = '$_POST[ecg_sono]' and branch = '$bid' and serialno = '$_POST[ecg_serialno]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_ecgresult (branch,so_no,pid,result_date,patient_stat,consultant,serialno,`code`,`procedure`,occupation,stat,prev_ecg_date,diagnosis,medication,rate,rhythm,printerval,qrscomplex,qtc,qti,axis,impression,created_by,created_on) values ('$bid','$_POST[ecg_sono]','$_POST[ecg_pid]','".$con->formatDate($_POST['ecg_date'])."','$_POST[ecg_patientstat]','".htmlentities($_POST['ecg_consultant'])."','$_POST[ecg_serialno]','$_POST[ecg_code]','".$con->escapeString(htmlentities($_POST['ecg_procedure']))."','".$con->escapeString(htmlentities($_POST['ecg_occupation']))."','$_POST[ecg_stat]','".$con->formatDate($_POST['prev_ecg_date'])."','".htmlentities($_POST['diagnosis'])."','".htmlentities($_POST['medication'])."','$_POST[ecg_rate]','$_POST[ecg_rhythm]','$_POST[ecg_interval]','$_POST[ecg_qrscomplex]','$_POST[ecg_qtc]','$_POST[ecg_qti]','$_POST[ecg_axis]','".$con->escapeString(htmlentities($_POST['ecg_impression']))."','$uid',now());");
			}
		break;

		case "validateECGResult":
			
			$con->validateResult("lab_ecgresult",$_POST['ecg_sono'],$_POST['ecg_code'],$_POST['ecg_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['ecg_sono'],$_POST['ecg_code'],$_POST['ecg_serialno'],'4',$bid,$uid);	
			$con->dbquery("update ignore so_details set result_available = 'Y' where so_no = '$_POST[ecg_sono]' and branch = '$bid' and serialno = '$_POST[ecg_serialno]';");
		
		break;

		case "invalidateECGResult":
			$con->dbquery("update ignore lab_ecgresult set verified = 'N', verified_by = '', verified_on = '' where so_no = '$_POST[ecg_sono]' and branch = '$bid' and code = '$_POST[ecg_code]' and serialno = '$_POST[ecg_serialno]';");
			$con->dbquery("update ignore so_details set result_available = 'N' where so_no = '$_POST[ecg_sono]' and branch = '$bid' and code = '$_POST[ecg_code]' and sample_serialno = '$_POST[ecg_serialno]';");
			$con->dbquery("update ignore lab_samples set status = '3', updated_by = '$uid', updated_on = now() where so_no = '$_POST[ecg_sono]' and branch = '$bid' and code = '$_POST[ecg_code]' and serialno = '$_POST[ecg_serialno]';");
		break;

		case "changeCbcMachine":
			$con->dbquery("UPDATE IGNORE lab_samples set machine = '$_POST[machine]' where serialno = '$_POST[serialno]' and so_no = '$_POST[so_no]';");
			echo "UPDATE IGNORE lab_samples set machine = '$_POST[machine]' where serialno = '$_POST[serialno]' and so_no = '$_POST[so_no]';";
			/* Check if result is available */
			list($isCount) = $con->getArray("select count(*) from lab_cbcresult where serialno = '$_POST[serialno]' and so_no = '$_POST[so_no]';");
			if($isCount > 0) {
				$con->dbquery("UPDATE IGNORE lab_cbcresult set machine = '$_POST[machine]' where serialno = '$_POST[serialno]' and so_no = '$_POST[so_no]';");
			}

		break;
		
		case "saveCBCResult":
			list($cnt) = $con->getArray("select count(*) from lab_cbcresult where so_no = '$_POST[cbc_sono]' and branch = '$bid' and serialno = '$_POST[cbc_serialno]';");
			if($cnt > 0) {
				$con->dbquery("update ignore lab_cbcresult set machine = '$_POST[cbc_machine]', result_date = '".$con->formatDate($_POST['cbc_date']) ."', wbc = '".$con->formatDigit($_POST['wbc'])."',rbc = '".$con->formatDigit($_POST['rbc'])."',hemoglobin = '".$con->formatDigit($_POST['hemoglobin'])."', hematocrit = '".$con->formatDigit($_POST['hematocrit'])."', mcv = '".$con->formatDigit($_POST['mcv'])."', mch = '".$con->formatDigit($_POST['mch'])."', mchc = '".$con->formatDigit($_POST['mchc'])."', neutrophils = '".$con->formatDigit($_POST['neutrophils'])."', lymphocytes = '".$con->formatDigit($_POST['lymphocytes'])."', monocytes = '".$con->formatDigit($_POST['monocytes'])."',eosinophils = '".$con->formatDigit($_POST['eosinophils'])."', basophils = '".$con->formatDigit($_POST['basophils'])."', platelate = '".$con->formatDigit($_POST['platelate'])."', antihbs = '$_POST[antihbs]', remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."', updated_by = '$uid', updated_on = now() where so_no = '$_POST[cbc_sono]' and branch = '$bid' and serialno = '$_POST[cbc_serialno]';");
			} else {
				$con->dbquery("insert ignore into lab_cbcresult (so_no,branch,result_date,sampletype,machine,serialno,wbc,rbc,hemoglobin,hematocrit,mcv,mch,mchc,neutrophils,lymphocytes,monocytes,eosinophils,basophils,platelate,remarks,created_by,created_on) values ('$_POST[cbc_sono]','$bid','".$con->formatDate($_POST['cbc_date'])."','$_POST[cbc_spectype]','$_POST[cbc_machine]','$_POST[cbc_serialno]','".$con->formatDigit($_POST['wbc'])."','".$con->formatDigit($_POST['rbc'])."','".$con->formatDigit($_POST['hemoglobin'])."','".$con->formatDigit($_POST['hematocrit'])."', '".$con->formatDigit($_POST['mcv'])."', '".$con->formatDigit($_POST['mch'])."', '".$con->formatDigit($_POST['mchc'])."', '".$con->formatDigit($_POST['neutrophils'])."','".$con->formatDigit($_POST['lymphocytes'])."','".$con->formatDigit($_POST['monocytes'])."','".$con->formatDigit($_POST['eosinophils'])."','".$con->formatDigit($_POST['basophils'])."','".$con->formatDigit($_POST['platelate'])."','".$con->escapeString(htmlentities($_POST['remarks']))."','$uid',now());");
				//echo "insert into lab_cbcresult (so_no,branch,result_date,sampletype,serialno,wbc,rbc,hemoglobin,hematocrit,neutrophils,lymphocytes,monocytes,eosinophils,basophils,platelate,remarks,created_by,created_on) values ('$_POST[cbc_sono]','$bid','".$con->formatDate($_POST['cbc_date'])."','$_POST[cbc_spectype]','$_POST[cbc_serialno]','".$con->formatDigit($_POST['wbc'])."','".$con->formatDigit($_POST['rbc'])."','".$con->formatDigit($_POST['hemoglobin'])."','".$con->formatDigit($_POST['hematocrit'])."','".$con->formatDigit($_POST['neutrophils'])."','".$con->formatDigit($_POST['lymphocytes'])."','".$con->formatDigit($_POST['monocytes'])."','".$con->formatDigit($_POST['eosinophils'])."','".$con->formatDigit($_POST['basophils'])."','".$con->formatDigit($_POST['platelate'])."','".$con->escapeString(htmlentities($_POST['remarks']))."','$uid',now());";
			}

			$con->updateLabSampleStatus($_POST['cbc_sono'],$_POST['cbc_code'],$_POST['cbc_serialno'],'3',$bid,$uid);	
	
		break;

		case "validateCBCResult":
			/* Update Status of Lab Sample */
			$con->dbquery("update ignore lab_cbcresult set machine = '$_POST[cbc_machine]', result_date = '".$con->formatDate($_POST['cbc_date']) ."', wbc = '".$con->formatDigit($_POST['wbc'])."',rbc = '".$con->formatDigit($_POST['rbc'])."',hemoglobin = '".$con->formatDigit($_POST['hemoglobin'])."', hematocrit = '".$con->formatDigit($_POST['hematocrit'])."', mcv = '".$con->formatDigit($_POST['mcv'])."', mch = '".$con->formatDigit($_POST['mch'])."', mchc = '".$con->formatDigit($_POST['mchc'])."', neutrophils = '".$con->formatDigit($_POST['neutrophils'])."', lymphocytes = '".$con->formatDigit($_POST['lymphocytes'])."', monocytes = '".$con->formatDigit($_POST['monocytes'])."',eosinophils = '".$con->formatDigit($_POST['eosinophils'])."', basophils = '".$con->formatDigit($_POST['basophils'])."', platelate = '".$con->formatDigit($_POST['platelate'])."', antihbs = '$_POST[antihbs]', remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."', updated_by = '$uid', updated_on = now() where so_no = '$_POST[cbc_sono]' and branch = '$bid' and serialno = '$_POST[cbc_serialno]';");
			$con->validateResult("lab_cbcresult",$_POST['cbc_sono'],$_POST['cbc_code'],$_POST['cbc_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['cbc_sono'],$_POST['cbc_code'],$_POST['cbc_serialno'],'4',$bid,$uid);	
		break;

		case "saveBloodChem":
			list($cnt) = $con->getArray("select count(*) from lab_bloodchem where so_no = '$_POST[bloodchem_sono]' and branch = '$bid' and serialno = '$_POST[bloodchem_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE IGNORE lab_bloodchem SET result_date = '".$con->formatDate($_POST['bloodchem_date'])."',glucose='".$con->formatDigit($_POST['glucose'])."',uric = '".$con->formatDigit($_POST['uric'])."',bun = '".$con->formatDigit($_POST['bun'])."',creatinine = '".$con->formatDigit($_POST['creatinine'])."', cholesterol = '".$con->formatDigit($_POST['cholesterol'])."',triglycerides = '".$con->formatDigit($_POST['triglycerides'])."',hdl = '".$con->formatDigit($_POST['hdl'])."',ldl = '".$con->formatDigit($_POST['ldl'])."',vldl = '".$con->formatDigit($_POST['vldl'])."',sgot = '".$con->formatDigit($_POST['sgot'])."',sgpt = '".$con->formatDigit($_POST['sgpt'])."',alkaline = '".$con->formatDigit($_POST['alkaline'])."',bilirubin = '".$con->formatDigit($_POST['bilirubin'])."',bilirubin_direct = '".$con->formatDigit($_POST['bilirubin_direct'])."',bilirubin_indirect = '".$con->formatDigit($_POST['bilirubin_indirect'])."',protein = '".$con->formatDigit($_POST['protein'])."',albumin = '".$con->formatDigit($_POST['albumin'])."',globulin = '".$con->formatDigit($_POST['globulin'])."',agratio = '".$con->formatDigit($_POST['agratio'])."',electrolytes_na = '".$con->formatDigit($_POST['electrolytes_na'])."',electrolytes_k = '".$con->formatDigit($_POST['electrolytes_k'])."',electrolytes_ci = '".$con->formatDigit($_POST['electrolytes_ci'])."',calcium = '".$con->formatDigit($_POST['calcium'])."',phosphorus = '".$con->formatDigit($_POST['phosphorus'])."',ggt = '".$con->formatDigit($_POST['ggt'])."',remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."',updated_by = '$uid',updated_on = NOW() where so_no = '$_POST[bloodchem_sono]' and branch = '$uid' and serialno = '$_POST[bloodchem_serialno]';");
			} else {
				$con->dbquery("INSERT INTO lab_bloodchem (so_no,branch,result_date,sampletype,serialno,glucose,uric,bun,creatinine,cholesterol,triglycerides,hdl,ldl,vldl,sgot,sgpt,alkaline,bilirubin,bilirubin_direct,bilirubin_indirect,protein,albumin,globulin,agratio,electrolytes_na,electrolytes_k,electrolytes_ci,calcium,phosphorus,ggt,remarks,created_by,created_on) VALUES ('$_POST[bloodchem_sono]','$bid','".$con->formatDate($_POST['bloodchem_date'])."','$_POST[bloodchem_spectype]','$_POST[bloodchem_serialno]','".$con->formatDigit($_POST['glucose'])."','".$con->formatDigit($_POST['uric'])."','".$con->formatDigit($_POST['bun'])."','".$con->formatDigit($_POST['creatinine'])."','".$con->formatDigit($_POST['cholesterol'])."','".$con->formatDigit($_POST['triglycerides'])."','".$con->formatDigit($_POST['hdl'])."','".$con->formatDigit($_POST['ldl'])."','".$con->formatDigit($_POST['vldl'])."','".$con->formatDigit($_POST['sgot'])."','".$con->formatDigit($_POST['sgpt'])."','".$con->formatDigit($_POST['alkaline'])."','".$con->formatDigit($_POST['bilirubin'])."','".$con->formatDigit($_POST['bilirubin_direct'])."','".$con->formatDigit($_POST['bilirubin_indirect'])."','".$con->formatDigit($_POST['protein'])."','".$con->formatDigit($_POST['albumin'])."','".$con->formatDigit($_POST['globulin'])."','".$con->formatDigit($_POST['agratio'])."','".$con->formatDigit($_POST['electrolytes_na'])."','".$con->formatDigit($_POST['electrolytes_k'])."','".$con->formatDigit($_POST['electrolytes_ci'])."','".$con->formatDigit($_POST['calcium'])."','".$con->formatDigit($_POST['phosphorus'])."','".$con->formatDigit($_POST['ggt'])."','".$con->escapeString(htmlentities($_POST['remarks']))."','$uid',NOW());");
			}
			
			/* Update Status of Lab Sample */
			$con->updateLabSampleStatus($_POST['bloodchem_sono'],$_POST['bloodchem_code'],$_POST['bloodchem_serialno'],'3',$bid,$uid);	
		break;

		case "validateBloodChem":
			/* Update Status of Lab Sample */
			$con->dbquery("UPDATE IGNORE lab_bloodchem SET result_date = '".$con->formatDate($_POST['bloodchem_date'])."',glucose='".$con->formatDigit($_POST['glucose'])."',uric = '".$con->formatDigit($_POST['uric'])."',bun = '".$con->formatDigit($_POST['bun'])."',creatinine = '".$con->formatDigit($_POST['creatinine'])."', cholesterol = '".$con->formatDigit($_POST['cholesterol'])."',triglycerides = '".$con->formatDigit($_POST['triglycerides'])."',hdl = '".$con->formatDigit($_POST['hdl'])."',ldl = '".$con->formatDigit($_POST['ldl'])."',vldl = '".$con->formatDigit($_POST['vldl'])."',sgot = '".$con->formatDigit($_POST['sgot'])."',sgpt = '".$con->formatDigit($_POST['sgpt'])."',alkaline = '".$con->formatDigit($_POST['alkaline'])."',bilirubin = '".$con->formatDigit($_POST['bilirubin'])."',bilirubin_direct = '".$con->formatDigit($_POST['bilirubin_direct'])."',bilirubin_indirect = '".$con->formatDigit($_POST['bilirubin_indirect'])."',protein = '".$con->formatDigit($_POST['protein'])."',albumin = '".$con->formatDigit($_POST['albumin'])."',globulin = '".$con->formatDigit($_POST['globulin'])."',agratio = '".$con->formatDigit($_POST['agratio'])."',electrolytes_na = '".$con->formatDigit($_POST['electrolytes_na'])."',electrolytes_k = '".$con->formatDigit($_POST['electrolytes_k'])."',electrolytes_ci = '".$con->formatDigit($_POST['electrolytes_ci'])."',calcium = '".$con->formatDigit($_POST['calcium'])."',phosphorus = '".$con->formatDigit($_POST['phosphorus'])."',ggt = '".$con->formatDigit($_POST['ggt'])."',remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."',updated_by = '$uid',updated_on = NOW() where so_no = '$_POST[bloodchem_sono]' and branch = '$uid' and serialno = '$_POST[bloodchem_serialno]';");
			$con->validateResult("lab_bloodchem",$_POST['bloodchem_sono'],$_POST['bloodchem_code'],$_POST['bloodchem_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['bloodchem_sono'],$_POST['bloodchem_code'],$_POST['bloodchem_serialno'],'4',$bid,$uid);	
		break;

		// dengue result
		case "saveDengueResult":
			list($cnt) = $con->getArray("select count(*) from lab_dengue where so_no = '$_POST[dengue_sono]' and branch = '$bid' and serialno = '$_POST[dengue_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE IGNORE lab_dengue set method = '$_POST[dengue_method]', testkit = '$_POST[dengue_testkit]', lotno= '$_POST[dengue_testkit_lotno]', expiry = '$_POST[dengue_testkit_expiry]',dengue_ag = '$_POST[dengue_ag]', dengue_igg = '$_POST[dengue_igg]', dengue_igm = '$_POST[dengue_igm]', remarks = '$_POST[dengue_remarks]', performed_by = '$_POST[dengue_result_by]', updated_by = '$_SESSION[userid]', updated_on = now() where so_no = '$_POST[dengue_sono]' and serialno = '$_POST[dengue_serialno]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO lab_dengue (so_no,branch,result_date,sampletype,serialno,method,testkit,lotno,expiry,dengue_ag,dengue_igg,dengue_igm,remarks,performed_by,created_by,created_on) VALUES ('$_POST[dengue_sono]', '$bid', '".$con->formatDate($_POST['dengue_date'])."', '$_POST[dengue_spectype]','$_POST[dengue_serialno]','$_POST[dengue_method]','$_POST[dengue_testkit]','$_POST[dengue_testkit_lotno]','$_POST[dengue_testkit_expiry]','$_POST[dengue_ag]', '$_POST[dengue_igg]', '$_POST[dengue_igm]','$_POST[dengue_remarks]','$_POST[dengue_result_by]','$uid',NOW());");
			}
			$con->updateLabSampleStatus($_POST['dengue_sono'],$_POST['dengue_code'],$_POST['dengue_serialno'],'3',$bid,$uid);
		break;
		// dengue validate
		case "validateDengueResult":
			$con->dbquery("UPDATE IGNORE lab_dengue set method = '$_POST[dengue_method]', testkit = '$_POST[dengue_testkit]', lotno= '$_POST[dengue_testkit_lotno]', expiry = '$_POST[dengue_testkit_expiry]',dengue_ag = '$_POST[dengue_ag]', dengue_igg = '$_POST[dengue_igg]', dengue_igm = '$_POST[dengue_igm]', remarks = '$_POST[dengue_remarks]', performed_by = '$_POST[dengue_result_by]', updated_by = '$_SESSION[userid]', updated_on = now() where so_no = '$_POST[dengue_sono]' and serialno = '$_POST[dengue_serialno]';");
			$con->validateResult("lab_dengue",$_POST['dengue_sono'],$_POST['dengue_code'],$_POST['dengue_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['dengue_sono'],$_POST['dengue_code'],$_POST['dengue_serialno'],'4',$bid,$uid);
			$con->dbquery("UPDATE so_details set result_available = 'Y' where so_no = '$_POST[dengue_sono]' and branch = '$bid' and code = '$_POST[dengue_code]';");
		break;

		case "saveUAReport":
			list($cnt) = $con->getArray("select count(*) from lab_uaresult where so_no = '$_POST[ua_sono]' and branch = '$bid' and serialno = '$_POST[ua_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE lab_uaresult SET result_date = '".$con->formatDate($_POST['ua_date'])."', color = '$_POST[color]', appearance = '$_POST[appearance]', leukocytes = '$_POST[leukocytes]', nitrite = '$_POST[nitrite]', urobilinogen = '$_POST[urobilinogen]', blood = '$_POST[blood]', ketone = '$_POST[ketone]', bilirubin = '$_POST[bilirubin]', ph = '$_POST[ph]',gravity = '$_POST[gravity]', glucose = '$_POST[glucose]', protein = '$_POST[protein]', rbc_hpf = '$_POST[rbc_hpf]', wbc_hpf = '$_POST[wbc_hpf]', epith_hpf = '$_POST[epith_hpf]', casts = '$_POST[casts]',mucus_thread = '$_POST[mucus_thread]',bacteria = '$_POST[bacteria]',crystals = '$_POST[crystals]',amorphus_urates = '$_POST[amorphous_urates]', amorphus_po4 = '$_POST[amorphous_po4]',remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."',others = '".$con->escapeString(htmlentities($_POST['others']))."',updated_by = '$uid',updated_on = now() where so_no = '$_POST[ua_sono]' and branch = '$uid' and serialno = '$_POST[ua_serialno]';");
			} else {
				$con->dbquery("INSERT INTO lab_uaresult (so_no,branch,result_date,sampletype,serialno,pid,color,appearance,leukocytes,nitrite,urobilinogen,blood,ketone,bilirubin,ph,gravity,glucose,protein,rbc_hpf,wbc_hpf,epith_hpf,casts,mucus_thread,bacteria,crystals,amorphus_urates,amorphus_po4,pregnancy,remarks,others,created_by,created_on) values ('$_POST[ua_sono]','$bid','".$con->formatDate($_POST['ua_date'])."','$_POST[ua_spectype]','$_POST[ua_serialno]','$_POST[ua_pid]','$_POST[color]','$_POST[appearance]','$_POST[leukocytes]','$_POST[nitrite]','$_POST[urobilinogen]','$_POST[blood]','$_POST[ketone]','$_POST[bilirubin]','$_POST[ph]','$_POST[gravity]','$_POST[glucose]','$_POST[protein]','$_POST[rbc_hpf]','$_POST[wbc_hpf]','$_POST[epith_hpf]','$_POST[casts]','$_POST[mucus_thread]','$_POST[bacteria]','$_POST[crystals]','$_POST[amorphous_urates]','$_POST[amorphous_po4]','$_POST[pregnancy]','".$con->escapeString(htmlentities($_POST['remarks']))."','".$con->escapeString(htmlentities($_POST['others']))."','$uid',now());");
			}

			/* Update Status of Lab Sample */
			$con->updateLabSampleStatus($_POST['ua_sono'],$_POST['ua_code'],$_POST['ua_serialno'],'3',$bid,$uid);	
		break;

		case "validateUAReport":
			/* Update Status of Lab Sample */
			$con->dbquery("UPDATE lab_uaresult SET result_date = '".$con->formatDate($_POST['ua_date'])."', color = '$_POST[color]', appearance = '$_POST[appearance]', leukocytes = '$_POST[leukocytes]', nitrite = '$_POST[nitrite]', urobilinogen = '$_POST[urobilinogen]', blood = '$_POST[blood]', ketone = '$_POST[ketone]', bilirubin = '$_POST[bilirubin]', ph = '$_POST[ph]', gravity = '$_POST[gravity]', glucose = '$_POST[glucose]', protein = '$_POST[protein]', rbc_hpf = '$_POST[rbc_hpf]', wbc_hpf = '$_POST[wbc_hpf]', epith_hpf = '$_POST[epith_hpf]', casts = '$_POST[casts]', mucus_thread = '$_POST[mucus_thread]', bacteria = '$_POST[bacteria]', crystals = '$_POST[crystals]', amorphus_urates = '$_POST[amorphous_urates]', amorphus_po4 = '$_POST[amorphous_po4]',remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."',others = '".$con->escapeString(htmlentities($_POST['others']))."',updated_by = '$uid',updated_on = now() where so_no = '$_POST[ua_sono]' and branch = '$uid' and serialno = '$_POST[ua_serialno]';");
			echo "UPDATE lab_uaresult SET result_date = '".$con->formatDate($_POST['ua_date'])."', color = '$_POST[color]', appearance = '$_POST[appearance]', leukocytes = '$_POST[leukocytes]', nitrite = '$_POST[nitrite]', urobilinogen = '$_POST[urobilinogen]', blood = '$_POST[blood]', ketone = '$_POST[ketone]', bilirubin = '$_POST[bilirubin]', ph = '$_POST[ph]',gravity = '$_POST[gravity]', glucose = '$_POST[glucose]', protein = '$_POST[protein]', rbc_hpf = '$_POST[rbc_hpf]', wbc_hpf = '$_POST[wbc_hpf]', epith_hpf = '$_POST[epith_hpf]', casts = '$_POST[casts]',mucus_thread = '$_POST[mucus_thread]',bacteria = '$_POST[bacteria]',crystals = '$_POST[crystals]',amorphus_urates = '$_POST[amorphous_urates]', amorphus_po4 = '$_POST[amorphous_po4]',remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."',others = '".$con->escapeString(htmlentities($_POST['others']))."',updated_by = '$uid',updated_on = now() where so_no = '$_POST[ua_sono]' and branch = '$uid' and serialno = '$_POST[ua_serialno]';";
			$con->validateResult("lab_uaresult",$_POST['ua_sono'],$_POST['ua_code'],$_POST['ua_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['ua_sono'],$_POST['ua_code'],$_POST['ua_serialno'],'4',$bid,$uid);	
		break;

		case "saveStoolExam":
			list($cnt) = $con->getArray("select count(*) from lab_stoolexam where so_no = '$_POST[stool_sono]' and branch = '$bid' and serialno = '$_POST[stool_serialno]';");
			if($cnt > 0) {
				$con->dbquery("UPDATE lab_stoolexam SET result_date = '".$con->formatDate($_POST['stool_date'])."', color = '$_POST[color]', consistency = '$_POST[consistency]', blood = '$_POST[blood]', mucus = '$_POST[mucus]',parasites = '$_POST[parasites]', rbc = '$_POST[rbc_hpf]', wbc = '$_POST[wbc_hpf]', ova_parasites = '$_POST[ova_parasites]',bacteria = '$_POST[bacteria]', globules = '$_POST[globules]', yeast_cells = '$_POST[yeast_cells]', occult_blood = '$_POST[occult_blood]', remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."',updated_by = '$uid', updated_on = now() where so_no = '$_POST[stool_sono]' and branch  = '$bid' and serialno = '$_POST[stool_serialno]';");
			} else {
				$con->dbquery("INSERT INTO lab_stoolexam (so_no,branch,result_date,sampletype,serialno,color,consistency,blood,mucus,parasites,rbc,wbc,ova_parasites,bacteria,globules,yeast_cells,occult_blood,remarks,created_by,created_on) VALUES ('$_POST[stool_sono]','$bid','".$con->formatDate($_POST['stool_date'])."','$_POST[stool_spectype]','$_POST[stool_serialno]','$_POST[color]','$_POST[consistency]','$_POST[blood]','$_POST[mucus]','$_POST[parasites]','$_POST[rbc_hpf]','$_POST[wbc_hpf]','$_POST[ova_parasites]','$_POST[bacteria]','$_POST[globules]','$_POST[yeast_cells]','$_POST[occult_blood]','".$con->escapeString(htmlentities($_POST['remarks']))."','$uid',now());");
			
				/* Update Status of Lab Sample */
				$con->updateLabSampleStatus($_POST['stool_sono'],'L013',$_POST['stool_serialno'],'3',$bid,$uid);
			}	
		break;

		case "validateStoolExam":
			$con->dbquery("UPDATE lab_stoolexam SET result_date = '".$con->formatDate($_POST['stool_date'])."', color = '$_POST[color]', consistency = '$_POST[consistency]', blood = '$_POST[blood]', mucus = '$_POST[mucus]',parasites = '$_POST[parasites]', rbc = '$_POST[rbc_hpf]', wbc = '$_POST[wbc_hpf]', ova_parasites = '$_POST[ova_parasites]',bacteria = '$_POST[bacteria]', globules = '$_POST[globules]', yeast_cells = '$_POST[yeast_cells]', occult_blood = '$_POST[occult_blood]', remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."',updated_by = '$uid', updated_on = now() where so_no = '$_POST[stool_sono]' and branch  = '$bid' and serialno = '$_POST[stool_serialno]';");
			$con->validateResult("lab_stoolexam",$_POST['stool_sono'],'L013',$_POST['stool_serialno'],$bid,$uid);
			$con->updateLabSampleStatus($_POST['stool_sono'],'L013',$_POST['stool_serialno'],'4',$bid,$uid);	
		break;

		case "checkOtherResultsForRelease":
			list($isE) = $con->getArray("select count(*) from lab_samples where so_no = '$_POST[so_no]' and `status` = '4' and `code` != '$_POST[code]' and released != 'Y';");
			echo $isE;
		break;

		case "batchReleaseResult":
			$con->dbquery("update lab_samples set released = 'Y', released_by = '$uid', release_date = '" . $con->formatDate($_POST['date']) . "', release_mode = '$_POST[mode]', release_remarks = '" . $con->escapeString($_POST['remarks']) . "', released_to = '" . $con->escapeString(htmlentities($_POST['remarks'])) . "' where so_no = '$_POST[so_no]' and `status` = '4';");
		break;

		case "releaseResult":
			$con->dbquery("update lab_samples set released = 'Y', released_by = '$uid', release_date = '" . $con->formatDate($_POST['date']) . "', release_mode = '$_POST[mode]', release_remarks = '" . $con->escapeString($_POST['remarks']) . "', released_to = '" . $con->escapeString(htmlentities($_POST['remarks'])) . "' where record_id = '$_POST[id]';");
		break;

		/* Patient Archive */
		case "savePatientInfo":
			if($_POST['pid'] != '') {
				$con->dbquery("UPDATE IGNORE patient_info SET lname = '".$con->escapeString(htmlentities($_POST['p_lname']))."',fname = '".$con->escapeString(htmlentities($_POST['p_fname']))."',mname = '".$con->escapeString(htmlentities($_POST['p_mname']))."',suffix = '$_POST[p_suffix]',gender = '$_POST[p_gender]', pwd = '$_POST[p_pwd]', birthdate = '".$con->formatDate($_POST['p_bday'])."', birthplace = '".$con->escapeString(htmlentities($_POST['p_birthplace']))."', nationality = '$_POST[nation]',cstat = '$_POST[p_cstat]',spouse_lname = '".$con->escapeString(htmlentities($_POST['s_lname']))."',spouse_fname = '".$con->escapeString(htmlentities($_POST['s_fname']))."',spouse_mname = '".$con->escapeString(htmlentities($_POST['s_mname']))."',spouse_suffix = '$_POST[s_suffix]',spouse_birthdate = '".$con->formatDate($_POST['s_bday'])."',mobile_no = '$_POST[p_mobileno]',tel_no = '$_POST[p_telephone]',email_add = '$_POST[p_email]',guardian = '".$con->escapeString(htmlentities($_POST['p_guardian']))."',street = '".$con->escapeString(htmlentities($_POST['p_street']))."',brgy = '$_POST[p_brgy]',city = '$_POST[p_city]',province = '$_POST[p_province]',phic_no = '$_POST[p_phic]',occupation = '$_POST[p_occupation]',employer = '".$con->escapeString(htmlentities($_POST['p_employer']))."',emp_street = '".$con->escapeString(htmlentities($_POST['e_street']))."',emp_brgy = '$_POST[e_brgy]',emp_city = '$_POST[e_city]',emp_province = '$_POST[e_province]', emp_telno = '$_POST[e_telno]', cabinet_no = '$_POST[cabinet_no]', drawer_no = '$_POST[drawer_no]', folder_no = '$_POST[folder_no]', hmo_provider = '" . htmlentities($_POST['p_hmo']) . "', hmo_no = '$_POST[p_hmo_no]', hmo_expiry = '".$con->formatDate($_POST['p_hmo_expiry'])."', emp_idno = '$_POST[p_idno]', updated_by = '$uid',updated_on = now() where patient_id = '$_POST[pid]';");
				
				/* Update Sales Order, Official Receipt & SOA Patient Name */
				$myaddress = '';
				$patient = $con->getArray("SELECT CONCAT(lname,', ',fname,', ',mname,' ',suffix) AS `name`, LPAD(patient_id,6,'0') AS cid, street, brgy, city, province FROM patient_info WHERE patient_id = '$_POST[pid]';");
			
				list($brgy) = $con->getArray("SELECT brgyDesc FROM options_brgy WHERE brgyCode = '$row[brgy]';");
				list($ct) = $con->getArray("SELECT citymunDesc FROM options_cities WHERE cityMunCode = '$row[city]';");
				list($prov) = $con->getArray("SELECT provDesc FROM options_provinces WHERE provCode = '$row[province]';");
			
				if($patient['street'] != '') { $myaddress.=$patient['street'].", "; }
				if($brgy != "") { $myaddress.=$brgy.", "; }
				if($ct != "") { $myaddress.=$ct.", "; }
				if($prov != "")  { $myaddress.=$prov.", "; }
				$myaddress = substr($myaddress,0,-2);

				echo "update ignore so_header set patient_name = '$patient[name]', patient_address = '$myaddress' where patient_id = '$_POST[pid]';";
				$con->dbquery("update ignore so_header set patient_name = '$patient[name]', patient_address = '$myaddress' where patient_id = '$_POST[pid]';");
				$con->dbquery("update ignore or_details set pname = '$patient[name]', paddr = '$myaddress' where pid = '$_POST[pid]';");
				$con->dbquery("update ignore soa_details set pname = '$patient[name]' where pid = '$_POST[pid]';");
			} else {
				$con->dbquery("INSERT IGNORE patient_info (lname,fname,mname,suffix,gender,birthdate,birthplace,pwd,nationality,cstat,spouse_lname,spouse_fname,spouse_mname,spouse_suffix,spouse_birthdate,mobile_no,tel_no,email_add,guardian,street,brgy,city,province,phic_no,occupation,employer,emp_street,emp_brgy,emp_city,emp_province,emp_telno,cabinet_no,drawer_no,folder_no,hmo_provider,hmo_no,hmo_expiry,emp_idno,created_by,created_on) VALUES ('".$con->escapeString(htmlentities($_POST['p_lname']))."','".$con->escapeString(htmlentities($_POST['p_fname']))."','".$con->escapeString(htmlentities($_POST['p_mname']))."','$_POST[p_suffix]','$_POST[p_gender]','".$con->formatDate($_POST['p_bday'])."','".$con->escapeString(htmlentities($_POST['p_birthplace']))."','$_POST[p_pwd]','$_POST[p_naation]','$_POST[p_cstat]','".$con->escapeString(htmlentities($_POST['s_lname']))."','".$con->escapeString(htmlentities($_POST['s_fname']))."','".$con->escapeString(htmlentities($_POST['s_mname']))."','$_POST[s_suffix]','".$con->formatDate($_POST['s_bday'])."','$_POST[p_mobileno]','$_POST[p_telephone]','$_POST[p_email]','".$con->escapeString(htmlentities($_POST['p_guardian']))."','".$con->escapeString(htmlentities($_POST['p_street']))."','$_POST[p_brgy]','$_POST[p_city]','$_POST[p_province]','$_POST[p_phic]','$_POST[p_occupation]','".$con->escapeString(htmlentities($_POST['p_employer']))."','".$con->escapeString(htmlentities($_POST['e_street']))."','$_POST[e_brgy]','$_POST[e_city]','$_POST[e_province]','$_POST[e_telno]','$_POST[cabinet_no]','$_POST[drawer_no]','$_POST[folder_no]','$_POST[p_hmo]','$_POST[p_hmo_no]','".$con->formatDate($_POST['p_hmo_expiry'])."','$_POST[p_idno]','$uid',now());");
			}	
			//echo $queryString;
			//$con->dbquery($queryString);
		break;

		case "deletePatient":
			if($_POST['pid'] != '') {
				$con->dbquery("UPDATE IGNORE patient_info SET file_status = 'Deleted', deleted_by = '$uid',deleted_on = NOW() WHERE patient_id = '$_POST[pid]';");
			}
		break;
		
		/* Asset Management */
		case "saveAsset":
			if($_POST['fid'] != "") {
				$con->dbquery("update ignore fa_master set asset_no='$_POST[asset_no]', date_acquired = '".$con->formatDate($_POST['acquired_date'])."', asset_description='".$con->escapeString(htmlentities($_POST['asset_description']))."', category='$_POST[category]', serial_no='$_POST[serial_no]', vendor='$_POST[vendor]', po_no='$_POST[po_no]', po_date='".$con->formatDate($_POST['po_date'])."', inv_no='$_POST[inv_no]', cv_no='$_POST[cv_no]', cv_date='".$con->formatDate($_POST['check_date'])."', check_no='$_POST[check_no]', warranty_exp='".$con->formatDate($_POST['warranty_exp'])."', life_span='$_POST[lifespan]', asset_acct='$_POST[asset_acct]', adeprn_acct='$_POST[adepn_acct]', deprn_acct='$_POST[depn_acct]', cost='".$con->formatDigit($_POST['cost'])."', assigned_to='".$con->escapeString(htmlentities($_POST['assigned_to']))."', date_assigned='".$con->formatDate($_POST['date_assigned'])."', dept_code = '$_POST[dept_code]', `status`='$_POST[status]', remarks='".$con->escapeString(htmlentities($_POST['remarks']))."', updated_by='$uid', updated_on = now() where fid = '$_POST[fid]';");
				//echo "update fa_master set asset_no='$_POST[asset_no]', date_acquired = '".$con->formatDate($_POST['acquired_date'])."', asset_description='".$con->escapeString(htmlentities($_POST['asset_description']))."', category='$_POST[category]', serial_no='$_POST[serial_no]', vendor='$_POST[vendor]', po_no='$_POST[po_no]', po_date='".$con->formatDate($_POST['po_date'])."', inv_no='$_POST[inv_no]', cv_no='$_POST[cv_no]', cv_date='".$con->formatDate($_POST['check_date'])."', check_no='$_POST[check_no]', warranty_exp='".$con->formatDate($_POST['warranty_exp'])."', life_span='$_POST[lifespan]', asset_acct='$_POST[asset_acct]', adeprn_acct='$_POST[adepn_acct]', deprn_acct='$_POST[depn_acct]', cost='".$con->formatDigit($_POST['cost'])."', assigned_to='".$con->escapeString(htmlentities($_POST['assigned_to']))."', date_assigned='".$con->formatDate($_POST['date_assigned'])."', dept_code = '$_POST[dept_code]', `status`='$_POST[status]', remarks='".$con->escapeString(htmlentities($_POST['remarks']))."', updated_by='$uid', updated_on = now() where fid = '$_POST[fid]';";
			} else {
				$con->dbquery("insert ignore into fa_master (asset_no,asset_description,category,serial_no,vendor,po_no,date_acquired,inv_no,cv_no,cv_date,check_no,warranty_exp,life_span,asset_acct,adeprn_acct,deprn_acct,cost,assigned_to,date_assigned,dept_code,`status`,remarks,created_by,created_on) values ('$_POST[asset_no]','".$con->escapeString(htmlentities($_POST['asset_description']))."','$_POST[category]','$_POST[serial_no]','".$con->escapeString(htmlentities($_POST['vendor']))."','$_POST[po_no]','".$con->formatDate($_POST['acquired_date'])."','$_POST[inv_no]','$_POST[cv_no]','".$con->formatDate($_POST['check_date'])."','$_POST[check_no]','".$con->formatDate($_POST['warranty_exp'])."','$_POST[lifespan]','$_POST[asset_acct]','$_POST[adepn_acct]','$_POST[depn_acct]','".$con->formatDigit($_POST['cost'])."','".$con->escapeString(htmlentities($_POST['assigned_to']))."','".$con->formatDate($_POST['date_assigned'])."','$_POST[dept_code]','$_POST[status]','".$con->escapeString(htmlentities($_POST['remarks']))."','$uid',now());");
			}
		break;
		
		case "checkDupAssetNo":
			if($_POST['fid'] != '') { $f1 = " and fid!='$_POST[fid]' "; }
			list($isE) = $con->getArray("select count(*) from fa_master where asset_no = '$_POST[asset_no]' $f1;");
			if($isE > 0) { echo "DUPLICATE"; } else { echo "NODUPLICATE"; }
		break;
		
		/* Customers & Suppliers */
		case "saveCInfo":
			if($_POST['type'] == 'SUPPLIER') { $company = '0'; } else { $company = $_SESSION['company']; }
			if(isset($_POST['fid']) && $_POST['fid'] != "") {
				$con->dbquery("update ignore contact_info set company='$company', type='$_POST[type]',tradename='".$con->escapeString(htmlentities($_POST['tradename']))."',address='".$con->escapeString(htmlentities($_POST['address']))."',billing_address='".$con->escapeString(htmlentities($_POST['billing_address']))."',shipping_address='".$con->escapeString(htmlentities($_POST['shipping_address']))."',bizstyle='".$con->escapeString($_POST['bizstyle'])."',brgy='$_POST[brgy]', city='$_POST[city]',province='$_POST[province]',country='$_POST[country]',tel_no='".$con->escapeString($_POST['telno'])."',cperson='".$con->escapeString($_POST['cperson'])."',price_level='$_POST[price_level]',terms='$_POST[terms]',credit_limit='".$con->formatDigit($_POST['climit'])."',email='".$con->escapeString($_POST['email'])."',srep='$_POST[srep]',tin_no='$_POST[tin_no]',bank_acct='$_POST[bank_acct]',vatable='$_POST[vatable]', acct_validity = '".$con->formatDate($_POST['acctValid'])."', delivery_type= '$_POST[delivery_type]', updated_by='$uid', updated_on=now() where file_id='$_POST[fid]';");
			} else {
				$con->dbquery("insert ignore into contact_info (company,`type`,tradename,address,brgy,city,province,country,bizstyle,billing_address,shipping_address,tel_no,email,cperson,srep,price_level,terms,credit_limit,vatable,tin_no,bank_acct,acct_validity,delivery_type,created_by,created_on) values ('$company','$_POST[type]','".$con->escapeString(htmlentities($_POST['tradename']))."','".$con->escapeString(htmlentities($_POST['address']))."','$_POST[brgy]','$_POST[city]','$_POST[province]','$_POST[country]','".$con->escapeString($_POST['bizstyle'])."','".$con->escapeString(htmlentities($_POST['billing_address']))."','".$con->escapeString(htmlentities($_POST['shipping_address']))."','".$con->escapeString($_POST['telno'])."','".$con->escapeString($_POST['email'])."','$_POST[cperson]','$_POST[srep]','$_POST[price_level]','$_POST[terms]','".$con->formatDigit($_POST['climit'])."','$_POST[vatable]','$_POST[tin_no]','$_POST[bank_acct]','".$con->formatDate($_POST['acctValid'])."','$_POST[delivery_type]','$uid',now());");
			}
		break;
		case "deleteCust":
			$con->dbquery("update contact_info set record_status = 'Deleted', deleted_by='$uid', deleted_on=now() where file_id='$_POST[fid]';");
		break;
		case "verifyCID":
			list($iCount) = $con->getArray("select count(*) from contact_info where file_id = '$_POST[cid]';");
			if($iCount > 0) { echo "Ok"; } else { echo "notOk"; }
		break;

		case "newSpecialPrice":
			$con->dbquery("insert IGNORE into contact_sprice (contact_id,`code`,description,unit,unit_price,special_price,with_validity,valid_until,remarks,created_by,created_on) values ('$_POST[cid]','$_POST[code]','".$con->escapeString($_POST['description'])."','$_POST[unit]','".$con->formatDigit($_POST['walkinprice'])."','".$con->formatDigit($_POST['spprice'])."','$_POST[isValid]','".$con->formatDate($_POST['validUntil'])."','".$con->escapeString($_POST['remarks'])."','$uid',now());");
		break;

		case "retrieveSpecialPrice":
			$sp = $con->getArray("select *, format(unit_price,2) as uprice, format(special_price,2) as sprice from contact_sprice where record_id = '$_POST[rid]';");
			echo json_encode($sp);
		break;

		case "checkifSP":
			$e = $con->getArray("select count(*) from contact_sprice where `code` = '$_POST[code]' and contact_id = '$_POST[cid]';");
			if($e[0] == 0) { echo "ok"; }
		break;

		case "updateSpecialPrice":
			list($currentPrice) = $con->getArray("select unit_price from services_master where `code` = '$_POST[code]';");
			list($presentSpecialPrice) = $con->getArray("select special_price from contact_sprice where record_id = '$_POST[rid]';");

			if($con->formatDigit($_POST['spprice']) != $presentSpecialPrice) { $previousSpecialPrice = $presentSpecialPrice; }

			$con->dbquery("update ignore contact_sprice set unit_price = '$currentPrice', special_price = '".$con->formatDigit($_POST['spprice'])."', previous_price = '$previousSpecialPrice', with_validity = '$_POST[isValid]', valid_until = '".$con->formatDate($_POST['validUntil'])."', remarks = '".$con->escapeString($_POST['remarks'])."', updated_by = '$uid', updated_on = now() where record_id = '$_POST[rid]';");
		break;

		case "removeSpecialPrice":
			$con->dbquery("delete from contact_sprice where record_id = '$_POST[rid]';");
		break;

		/* USERS DATA */
		case "getUinfo":
			list($uname) = $con->getArray("select fullname from user_info where emp_id = '$_POST[uid]';");
			echo $uname;
		break;
		
		case "checkUname":
			list($count) = $con->getArray("select count(*) from user_info where username = '$_POST[uname]';"); echo $count;
		break;
		
		case "checkUnameUID":
			list($count) = $con->getArray("select count(*) from user_info where username = '$_POST[uname]' and emp_id!='$_POST[uid]';"); echo $count;
		break;

		case "getUserDetails":
			$u1 = $con->getArray("select *,if(signature_file!='',concat('<img src=\"images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"images/signatures/blank.png\" align=absmiddle />') as signaturefile from user_info where emp_id = '$_POST[uid]';");
			$u2 = array("xfullname"=>html_entity_decode($u1['fullname']));
			$u3 = array_merge($u1,$u2);
			echo json_encode($u3);
		break;

		case "updateUser":
			$uploadDir = "../images/signatures/";

			$fileName = $_FILES['signatureFile']['name'];
			$tmpName = $_FILES['signatureFile']['tmp_name'];
			
			
			if($fileName!='') {

				/* CHANGE UNIQUE FILENAME TO PREVENT DUPLICATION */
				$ext = substr(strrchr($fileName, "."), 1);
				$randName = md5(rand() * time());
				$newFileName = $randName . "." . $ext;
				$filePath = $uploadDir . $newFileName;
				$result = move_uploaded_file($tmpName, $filePath);
			
				$signatureFile = ",signature_file = '$newFileName' ";

			}

			$con->dbquery("UPDATE IGNORE user_info SET username = '$_POST[uname]', fullname = '".htmlentities($_POST['fname'])."', user_type = '$_POST[utype]', r_type = '$_POST[rtype]', role = '".$con->escapeString($_POST['urole'])."', license_no = '$_POST[license_no]', email = '$_POST[uemail]' $signatureFile WHERE emp_id = '$_POST[uid]';");
			
		break;

		case "newUser":
			$uploadDir = "../images/signatures/";

			$fileName = $_FILES['new_signatureFile']['name'];
			$tmpName = $_FILES['new_signatureFile']['tmp_name'];
			
			
			if($fileName!='') {

				/* CHANGE UNIQUE FILENAME TO PREVENT DUPLICATION */
				$ext = substr(strrchr($fileName, "."), 1);
				$randName = md5(rand() * time());
				$newFileName = $randName . "." . $ext;
				$filePath = $uploadDir . $newFileName;
				$result = move_uploaded_file($tmpName, $filePath);
			}

			$con->dbquery("INSERT IGNORE INTO user_info (username,`password`,fullname,user_type,r_type,email,`role`,license_no,signature_file) value ('$_POST[new_uname]',md5('$_POST[new_pass1]'),'".$con->escapeString(htmlentities($_POST['new_fname']))."','$_POST[new_utype]','$_POST[new_rtype]','$_POST[new_uemail]','".$con->escapeString($_POST['new_urole'])."','$_POST[new_license_no]','$newFileName');");

		break;

		/* case "addUser":
			$con->dbquery("insert ignore into user_info (username,password,fullname,user_type,r_type,require_change_pass,email) value ('$_POST[uname]',md5('$_POST[pass]'),'".$con->escapeString(htmlentities($_POST['fname']))."','$_POST[utype]','$_POST[rtype]','$_POST[changePass]','$_POST[email]');");
			$con->trailer("USER MANAGEMENT","SYSTEM USER ADDED, username: $_POST[uname], user type: $_POST[utype], fullname: $_POST[fname]");
		break;
		
		case "updateUInfo":
			$con->dbquery("update ignore user_info set username = '$_POST[uname]', fullname='".$con->escapeString(htmlentities($_POST['fname']))."', user_type='$_POST[utype]', r_type='$_POST[rtype]', email='$_POST[email]' where emp_id='$_POST[uid]';");
			$con->trailer("USER MANAGEMENT","USER INFO UPDATED, username: $_POST[uname], user type: $_POST[utype] :: $_POST[rtype], fullname: $_POST[fname]");
		break; */

		case "deleteUser":
			$h = $con->getArray("select username, fullname from user_info where emp_id = '$_POST[uid]';");
			$con->trailer("USER MANAGEMENT","USER INFO DELETED, User ID: $_POST[uid], Username: $h[username], Full Name: $h[fullname]");
			$con->dbquery("delete from user_info where emp_id = '$_POST[uid]';");
			$con->dbquery("delete from user_rights where UID = '$_POST[uid]';");
		break;
		case "checkOldPass":
			list($count) = $con->getArray("select count(*) from user_info where emp_id='$_POST[uid]' and password=md5('$_POST[old_pass]');");	
			if($count>0) { echo "Ok"; } else { echo "noOk"; }
		break;
		case "changePassword":
			$con->dbquery("update ignore user_info set password=md5('$_POST[pass]'), require_change_pass = 'N' where emp_id = '$_POST[uid]';");
			//$con->trailer("USER MANAGEMENT","PASSWORD FOR UID $_POST[uid] was updated");
		break;
		case "resetPassword":
			$con->dbquery("update ignore user_info set password=md5('123456'), require_change_pass='Y' where emp_id='$_POST[uid]';");
			//$con->trailer("USER MANAGEMENT","PASSWORD FOR UID $_POST[uid] was reset");
		break;
		case "insertRights":
			list($module,$id) = explode("|",$_REQUEST['val']);
			if($_REQUEST['push'] == "N") { 
				$xfind = $con->getArray("select count(*) from user_rights where UID='$_POST[uid]' and MENU_MODULE='$module' and MENU_ID='$id';");
				if($xfind[0] > 0) { 
					$con->dbquery("delete from user_rights where UID='$_POST[uid]' and MENU_MODULE='$module' and MENU_ID='$id';"); 
					$con->trailer("USER MANAGEMENT","RIGHTS REMOVED FOR UID $_POST[uid] -> SUBMENU ID # $id");
				}
			} else {
				$xfind = $con->getArray("select count(*) from user_rights where UID='$_POST[uid]' and MENU_MODULE='$module' and MENU_ID='$id';");
				if($xfind[0] == 0) { 
					$con->dbquery("insert ignore into user_rights (UID,MENU_MODULE,MENU_ID) values ('$_REQUEST[uid]','$module','$id');"); 
					$con->trailer("USER MANAGEMENT","RIGHTS ADDED TO UID $_POST[uid] -> SUBMENU ID # $id");
				}
			}
		break;
		case "tagCompany":
			$con->dbquery("update user_info set `$_POST[val]` = '$_POST[push]' where emp_id = '$_POST[uid]';");
			echo "update user_info set `$_POST[val]` = '$_POST[push]' where emp_id = '$_POST[uid]';";
		break;

		case "checkSPass":
			if($_POST['pass'] == 'e10adc3949ba59abbe56e057f20f883e') { echo "ok"; }
		break;

		/* Miscellaneous */
		case "getCities":
			$cq = $con->dbquery("select citymunCode, citymunDesc from options_cities where provCode = '$_POST[pid]';");
			while(list($cid,$ctname) = $cq->fetch_array()) {
				echo "<option value='$cid'>$ctname</option>\n";
			}
		break;
		case "getBrgy":
			$cq = $con->dbquery("select brgyCode, brgyDesc from options_brgy where citymunCode = '$_POST[city]';");
			echo "<option value='0'>- Not Applicable -</option>\n";
			while(list($cid,$ctname) = $cq->fetch_array()) {
				echo "<option value='$cid'>$ctname</option>\n";
			}
		break;
		case "getSections":
			$vg = $con->dbquery("select section_code, section_name from options_sections where parent_dept = '$_POST[dept]';");
			echo "<option value=''>-N/A-</option>\n";
			while(list($scode,$sname) = $vg->fetch_array()) {
				echo "<option value='$scode'>$sname</option>\n";
			}
		break;
		
		/* Apply to Other Documents */
		case "checkForDoc":
			switch($_POST['doctype']) {
				case "SI":
					if($_POST['acct'] == '10106') {
						$sql = $con->dbquery("SELECT '' as lid, doc_no, DATE_FORMAT(invoice_date,'%m/%d/%Y') AS doc_date, format(amount,2) as amount, balance, 'CR' as side FROM invoice_header WHERE customer = trim(leading '0' from '$_POST[cid]') AND `status` = 'Finalized' AND balance > 0;");
					} else {
						$sql = $con->dbquery("SELECT record_id as lid, doc_no, DATE_FORMAT(doc_date,'%m/%d/%Y') AS doc_date, FORMAT(ABS(debit-credit),2) AS amount, ABS(debit-credit) - applied_amount AS balance, IF((debit-credit) > 0,'CR','DB') AS side FROM acctg_gl WHERE contact_id = trim(leading '0' from '$_POST[cid]') AND acct = '$_POST[acct]' and doc_type = 'SI' and (ABS(debit-credit) - applied_amount) > 0;");
					}
				break;
				case "APV": case "AP":
					if($_POST['acct'] == '20201') {
						$sql = $con->dbquery("SELECT '' as lid, apv_no AS doc_no, DATE_FORMAT(apv_date,'%m/%d/%Y') AS doc_date, FORMAT(amount,2) AS amount, balance, 'DB' AS side FROM apv_header WHERE supplier = TRIM(LEADING '0' FROM '$_POST[cid]') AND `status` = 'Posted' AND balance > 0");
					} else {
						$sql = $con->dbquery("SELECT a.record_id as lid, b.apv_no AS doc_no, DATE_FORMAT(b.apv_date,'%m/%d/%Y') AS doc_date, FORMAT(ABS(debit-credit),2) AS amount, a.balance, IF((debit-credit) > 0,'CR','DB') AS side FROM apv_details a INNER JOIN apv_header b ON a.apv_no = b.apv_no AND a.branch = b.branch WHERE a.acct = '$_POST[acct]' AND a.balance > 0 AND b.branch = '$bid' AND b.supplier = TRIM(LEADING '0' FROM '$_POST[cid]') AND b.status = 'Posted';");
					}
				break;
				default:
					$sql = $con->dbquery("SELECT record_id as lid, doc_no, DATE_FORMAT(doc_date,'%m/%d/%Y') AS doc_date, FORMAT(ABS(debit-credit),2) AS amount, ABS(debit-credit) - applied_amount AS balance, IF((debit-credit) > 0,'CR','DB') AS side FROM acctg_gl WHERE contact_id = TRIM(LEADING '0' FROM '$_POST[cid]') AND acct = '$_POST[acct]' AND doc_type = '$_POST[doctype]' AND (ABS(debit-credit) - applied_amount) > 0;");
				break;
			}

			if($sql) {
				echo "<table width=100% cellpadding=0 cellspacing=0>";
				while($b = $sql->fetch_array(MYSQLI_BOTH)) {
					echo "<tr bgcolor=".$con->initBackground($i++)." style='cursor: pointer;' title='Click to Apply this Document' onclick=\"javascript: selectDocument('$b[doc_no]','$b[doc_date]','$b[amount]','$b[balance]','$b[side]','$b[lid]');\">
						<td class = grid width = '20%'>$_POST[doctype]-$b[doc_no]</td>
						<td class = grid width = '20%' align=center>$b[doc_date]</td>
						<td class = grid width = '34%' align=right style='padding-right: 15px;'>".$b['amount']."</td>
						<td class = grid align=right style='padding-right: 10px;'>".number_format($b['balance'],2)."</td>
					</tr>
				";
				}
				if($i < 10) { for($i; $i <= 10; $i++) { echo "<tr bgcolor=".$con->initBackground($i)."><td class=grid colspan=4>&nbsp;</td>"; }}
				echo "</table>";
			}
		break;
		
		/* End to Apply Documents */
		
		case "verifyACCT":
			list($i) = $con->getArray("select count(*) from acctg_accounts where acct_code = '$_POST[acct]';");
			if($i > 0) { echo "NoError"; } else { echo "NotFound"; }
		break;
		
		
		case "checkLockStatus":
			//list($isOk) = $con->getArray("select count(*) from closingtime where `month` = '$_POST[month]' and `year` = '$_POST[year]';");
			//if($isOk == 0) { echo "Ok"; } else { echo "NotOK"; }
			
			echo "Ok";
		break;
		
		case "lockStatusOk":
			$con->dbquery("insert into $dbase.closingtime (`month`,`year`,`closing_memo`,closed_by,closed_on) values ('$_POST[month]','$_POST[year]','".$con->escapeString($_POST['memo'])."','$uid',now());");
		
			list($dtf,$dt2) = $con->getArray("select '$_POST[year]-$_POST[month]-01', last_day('$_POST[year]-$_POST[month]-01');");
			$con->dbquery("UPDATE ignore $dbase.apv_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE apv_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.cr_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE cr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.cv_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE cv_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.dr_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE dr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.invoice_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE invoice_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.journal_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE j_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.phy_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE doc_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.po_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE po_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.rr_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE rr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.so_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE so_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.str_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE str_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.srr_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE srr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.sw_header SET locked='Y',locked_on=now(),locked_by=$uid WHERE sw_date BETWEEN '$dtf' AND '$dt2';");
			
			/* CREATING SUMMARY OF ACCOUNTS FOR PERIODIC TRIAL BALANCE */
			$itapok = $con->dbquery("SELECT branch, acct, ROUND(SUM(debit-credit),2) AS amt, cost_center FROM $dbase.acctg_gl WHERE doc_date BETWEEN '$dtf' AND '$dt2' GROUP BY branch, acct, cost_center order by branch, acct, cost_center;");
			while($gitapok = $itapok->fetch_array(MYSQLI_BOTH)) {
				if($gitapok[amt] > 0) { $db = $gitapok[amt]; $cr = 0; } else { $db = 0; $cr = abs($gitapok[amt]); }
				$con->dbquery("insert ignore into $dbase.acctg_mo_tbalance (branch,`month`,`year`,`acct`,`debit`,`credit`,cost_center,monthend) values ('$gitapok[branch]','$_POST[month]','$_POST[year]','$gitapok[acct]','$db','$cr','$gitapok[cost_center]','$dt2');");
				$db = 0; $cr = 0;
			}
			
			/* CREATE INVENTORY JOURNAL */
			$iihap = $con->dbquery("SELECT a.branch, 'SI' AS `type`, item_code AS `code`, ROUND(SUM(qty),2) AS sold, 0 AS `in`, 0 AS `out`, ROUND(SUM(qty*cost),2) AS amount FROM $dbase.invoice_header a INNER JOIN $dbase.invoice_details b ON a.invoice_no = b.invoice_no AND a.branch = b.branch AND a.company = b.company WHERE a.status = 'Finalized' AND a.invoice_date BETWEEN '$dtf' AND '$dt2' GROUP BY a.branch, b.item_code UNION ALL SELECT a.branch, 'RR' AS `type`, item_code AS `code`, 0 AS sold, ROUND(SUM(qty),2) AS `in`, 0 AS `out`, ROUND(SUM(qty*cost),2) AS amount FROM $dbase.rr_header a INNER JOIN $dbase.rr_details b ON a.rr_no = b.rr_no AND a.branch = b.branch AND a.company = b.company WHERE a.status = 'Finalized' AND a.rr_date BETWEEN '$dtf' AND '$dt2' GROUP BY a.branch, b.item_code UNION ALL SELECT a.branch, 'STR' AS `type`, item_code AS `code`, 0 AS sold, 0 AS `in`, ROUND(SUM(qty),2) AS `out`, ROUND(SUM(qty*cost),2) AS amount FROM $dbase.str_header a INNER JOIN $dbase.str_details b ON a.str_no = b.str_no AND a.branch = b.branch AND a.company = b.company WHERE a.status = 'Finalized' AND a.str_date BETWEEN '$dtf' AND '$dt2' GROUP BY a.branch, b.item_code UNION ALL SELECT a.branch, 'SRR' AS `type`, item_code AS `code`, 0 AS sold, ROUND(SUM(qty),2) AS `in`, 0 AS `out`, ROUND(SUM(qty*cost),2) AS amount FROM $dbase.srr_header a INNER JOIN $dbase.srr_details b ON a.srr_no = b.srr_no AND a.branch = b.branch AND a.company = b.company WHERE a.status = 'Finalized' AND a.srr_date BETWEEN '$dtf' AND '$dt2' GROUP BY a.branch, b.item_code UNION ALL SELECT a.branch, 'SW' AS `type`, item_code AS `code`, 0 AS sold, 0 AS `in`, ROUND(SUM(qty),2) AS `out`, ROUND(SUM(qty*cost),2) AS amount FROM $dbase.sw_header a INNER JOIN $dbase.sw_details b ON a.sw_no = b.sw_no AND a.branch = b.branch AND a.company = b.company WHERE a.status = 'Finalized' AND a.sw_date BETWEEN '$dtf' AND '$dt2' GROUP BY a.branch, b.item_code UNION ALL SELECT a.branch, 'POS' AS `type`, item_code AS `code`, ROUND(SUM(qty),2) AS sold, 0 AS `in`, 0 AS `out`, ROUND(SUM(qty*price),2) AS amount FROM $dbase.pos_header a INNER JOIN $dbase.pos_details b ON a.tmpfileid = b.tmpfileid WHERE a.status = 'Finalized' AND a.trans_date BETWEEN '$dtf' AND '$dt2' GROUP BY a.branch, b.item_code");
			while($giihap = $iihap->fetch_array(MYSQLI_BOTH)) {
				$con->dbquery("insert ignore into $dbase.ijournal (branch,`month`,`year`,`code`,`type`,`sold`,`inbound`,`outbound`,`amount`,`monthend`) values ('$giihap[branch]','$_POST[month]','$_POST[year]','$giihap[code]','$giihap[type]','$giihap[sold]','$giihap[in]','$giihap[out]','$giihap[amount]','$dt2');");
			}
			
			/* Audit Trail */
			$con->dbquery("insert into traillog (company,branch,user_id,`timestamp`,ipaddress,module,`action`) values ('1','$bid','$uid',now(),'$_SERVER[REMOTE_ADDR]','DOC LOCKING','PERIOD $_POST[month]-$_POST[year] was marked as LOCKED :: Posted Remarks >> ".$con->escapeString($_POST['memo'])."');");
			
		break;
		case "unLock":
			$con->dbquery("delete from $dbase.closingtime where `month` = '$_POST[month]' and `year` = '$_POST[year]';");
			list($dtf,$dt2) = $con->getArray("select '$_POST[year]-$_POST[month]-01', last_day('$_POST[year]-$_POST[month]-01');");
			$con->dbquery("UPDATE ignore $dbase.apv_header SET locked='N',locked_on='',locked_by='' WHERE apv_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.cr_header SET locked='N',locked_on='',locked_by='' WHERE cr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.cv_header SET locked='N',locked_on='',locked_by='' WHERE cv_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.dr_header SET locked='N',locked_on='',locked_by='' WHERE dr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.invoice_header SET locked='N',locked_on='',locked_by='' WHERE invoice_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.journal_header SET locked='N',locked_on='',locked_by='' WHERE j_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.phy_header SET locked='N',locked_on='',locked_by='' WHERE doc_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.po_header SET locked='N',locked_on='',locked_by='' WHERE po_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.rr_header SET locked='N',locked_on='',locked_by='' WHERE rr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.so_header SET locked='N',locked_on='',locked_by='' WHERE so_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.str_header SET locked='N',locked_on='',locked_by='' WHERE str_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.srr_header SET locked='N',locked_on='',locked_by='' WHERE srr_date BETWEEN '$dtf' AND '$dt2';");
			$con->dbquery("UPDATE ignore $dbase.sw_header SET locked='N',locked_on='',locked_by='' WHERE sw_date BETWEEN '$dtf' AND '$dt2';");
			
			/* Delete from Monthly Trial Balance */
			$con->dbquery("delete from $dbase.acctg_mo_tbalance where `month` = '$_POST[month]' and `year` = '$_POST[year]';");
			
			/* Delete from Inventory Journal */
			$con->dbquery("delete from $dbase.ijournal where `month` = '$_POST[month]' and `year` = '$_POST[year]`;");
			
			/* Audit Trail */
			$con->dbquery("insert into traillog (company,branch,user_id,`timestamp`,ipaddress,module,`action`) values ('1','$bid','$uid',now(),'$_SERVER[REMOTE_ADDR]','DOC LOCKING','PERIOD $_POST[month]-$_POST[year] was removed from the list of locked periods.');");
		break;
		case "checkDateLock":
			list($belowOct) = $con->getArray("select '".$con->formatDate($_POST['myDate'])."' <= '2017-10-31';");
			if($belowOct != 1) {
				$i = explode("/",$_POST['myDate']);
				list($isOk) = $con->getArray("select count(*) from $dbase.closingtime where `month` = '$i[0]' and `year` = '$i[2]';");
				if($isOk == 0) { echo "Ok"; }
			}
		break;
		case "getSuppliers":
			echo "<option value=''>- All Suppliers -</option>";
			$yt = $con->dbquery("SELECT DISTINCT payee AS supplier, payee_name AS supplier_name FROM cv_header UNION SELECT DISTINCT supplier, supplier_name FROM apv_header ORDER BY supplier_name");
			while($ytt = $yt->fetch_array(MYSQLI_BOTH)) {
				echo "<option value='$ytt[0]'>$ytt[1]</option>";
			}
		break;
		case "modifyBudget":
			list($isE) = $con->getArray("select count(*) from budgets where `acct` = '$_POST[acct]' and `year` = '$_POST[year]';");
			if($isE > 0) {
				$con->dbquery("update budgets set `budget` = '".$con->formatDigit($_POST['budget'])."',updatedBy='$uid',updatedOn=now() where acct = '$_POST[acct]' and `year` = '$_POST[year]';");
			} else { $con->dbquery("INSERT INTO budgets (`year`,`acct`,`budget`,createdBy,createdOn) VALUES ('$_POST[year]','$_POST[acct]','".$con->formatDigit($_POST['budget'])."','$uid',now());"); }
		break;
		case "checkIdentProjCode":
			if($_POST['proj_id'] != '') {
				echo "ok";
			} else {
				list($isE) = $con->getArray("select count(*) from options_project where proj_code = '$_POST[proj_code]';");
				if($isE > 0) { echo "notOk"; } else { echo "ok"; } 
			}
		break;
		case "saveProj":
			if($_POST['proj_id'] != ""){
				$con->dbquery("UPDATE ignore options_project a SET a.proj_code = '$_POST[proj_code]' , a.proj_name = '".$con->escapeString(htmlentities($_POST['proj_name']))."',proj_description='".$con->escapeString(htmlentities($_POST['proj_description']))."', proj_address='".$con->escapeString(htmlentities($_POST['proj_address']))."', proj_type='$_POST[proj_type]', proj_cost='".$con->formatDigit($_POST['proj_cost'])."', proj_duration='$_POST[proj_scale]',proj_date='".$con->formatDate($_POST['proj_date'])."', proj_client='".$con->escapeString(htmlentities($_POST['client']))."', client_address='".$con->escapeString(htmlentities($_POST['client_address']))."', archived = '$_POST[archived_val]', parent='$_POST[is_Parent]',parent_id='$_POST[parent_id]', updated_by = '$uid', updated_on = now() where a.proj_id = '$_POST[proj_id]';");
			}else{
				$con->dbquery("INSERT ignore INTO options_project (proj_code,proj_name,proj_type,proj_description,proj_address,proj_cost,proj_date,proj_client,client_address,proj_duration,parent,parent_id) VALUES ('".$con->escapeString($_POST['proj_code'])."','".$con->escapeString(htmlentities($_POST['proj_name']))."','$_POST[proj_type]','".$con->escapeString(htmlentities($_POST['proj_description']))."','".$con->escapeString(htmlentities($_POST['proj_address']))."','".$con->formatDigit($_POST['proj_cost'])."','".$con->formatDate($_POST['proj_date'])."','".$con->escapeString(htmlentities($_POST['client']))."','".$con->escapeString(htmlentities($_POST['client_address']))."','$_POST[proj_scale]','$_POST[is_Parent]','$_POST[parent_id]');");
			}
		break;
		case "checkSession":
			$uid = $_SESSION['userid'];
			if($uid == 0 || $uid == '') {
				echo "NotOk";
			}
		break;
	}
?>