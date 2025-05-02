<?php	
	session_start();
	include("includes/dbUSE.php");
	$date = date('Y-m-d');
	
	function trailer($module,$action) {
		dbquery("insert into traillog (user_id,`timestamp`,ipaddress,module,`action`) values ('$_SESSION[userid]',now(),'$_SERVER[REMOTE_ADDR]','$module','".mysql_real_escape_string($action)."');");
	}
	
	function displayCRecord($eid) {
		print '<table border="0" cellpadding="0" cellspacing="1" width="100%">';
			print "<tr>";
			print "<td valign=\"top\">";
			print "<table width=100% cellpadding=0 cellspacing=1 >";
			print "<tr>";
			print "<td width=\"15%\" class=dgridHead align=center ><strong>First Name</strong></td>";
			print "<td width=\"15%\" class=dgridHead align=center ><strong>Middle Name</strong></td>";
			print "<td width=\"15%\" class=dgridHead align=center ><strong>Last Name</strong></td>";
			print "<td width=\"15%\" class=dgridHead align=center ><strong>Birth Date</strong></td>";
			print "<td width=\"10%\" class=dgridHead align=center ><strong>Gender</strong></td>";
			print "<td width=\"10%\" class=dgridHead align=center ><strong>Civil Status</strong></td>";
			print "<td width=\"20%\" class=dgridHead align=center ><strong>Occupation</strong></td>";
			print "</tr>";
			print "<tr bgcolor=\"#000000\" height=1><td colspan=14></td></tr>";
			print "</table>";
			print "</td></tr>";
			print "<tr>";
			print "<td width=100% valign=top style=\"border: thin solid #ccc;\">";
			print "<table width=100% cellspacing='0' cellpadding='0' onMouseOut=\"javascript:highlightTableRowVersionA(0);\">";
			$crecords = mysql_query("select record_id, fname, mname, lname, date_format(bday,'%m/%d/%Y') as bday, bday as bd8, gender, status, occupation from pccpayroll.emp_crecord where emp_id = '$eid' order by bd8 asc;");
			$x = 1;
			while($_crow = mysql_fetch_array($crecords)) {
				if ($color == "#ffffff") { $mycolor = "#e6e6e6"; } else { $mycolor = "#ffffff"; }
				print "<tr bgcolor=\"$mycolor\" onMouseOver=\"javascript:highlightTableRowVersionA(this, '#95f0e8');\" title=\"Click to view or edit this record.\" onclick=\"view_crecord(".$_crow['record_id'].");\">";
				print "<td class=\"grid2\" width=3% align=center>" . $x++ . ".</td>";
				print "<td class=\"grid2\" width=\"12%\" align=left>$_crow[fname]</td>";
				print "<td class=\"grid2\" align=left width=\"15%\" style=\"padding-left: 10px;\">$_crow[mname]</td>";
				print "<td class=\"grid2\" align=left width=\"15%\" style=\"padding-left: 10px;\">$_crow[lname]</td>";
				print "<td align=center class=\"grid2\" width=\"15%\">$_crow[bday]</td>";
				print "<td align=center class=\"grid2\" width=\"10%\">$_crow[gender]</td>";
				print "<td align=left class=\"grid2\" width=\"10%\" style=\"padding-left: 10px;\">$_crow[status]</td>";
				print "<td align=left class=\"grid2\" width=\"20%\" style=\"padding-left: 10px;\">$_crow[occupation]&nbsp;</td>";
				print "</tr>";
				$color = $mycolor;
			}
			print "</table></td></tr></table>";
	}

	function displayBRecord($eid) {
		print '<table border="0" cellpadding="0" cellspacing="1" width="100%">';
			print "<tr>";
			print "<td valign=\"top\">";
			print "<table width=100% cellpadding=0 cellspacing=1 >";
			print "<tr>";
			print "<td width=\"15%\" class=dgridHead align=center ><strong>First Name</strong></td>";
			print "<td width=\"15%\" class=dgridHead align=center ><strong>Middle Name</strong></td>";
			print "<td width=\"15%\" class=dgridHead align=center ><strong>Last Name</strong></td>";
			print "<td width=\"15%\" class=dgridHead align=center ><strong>Birth Date</strong></td>";
			print "<td width=\"10%\" class=dgridHead align=center ><strong>Gender</strong></td>";
			print "<td width=\"10%\" class=dgridHead align=center ><strong>Civil Status</strong></td>";
			print "<td width=\"20%\" class=dgridHead align=center ><strong>Occupation</strong></td>";
			print "</tr>";
			print "<tr bgcolor=\"#000000\" height=1><td colspan=14></td></tr>";
			print "</table>";
			print "</td></tr>";
			print "<tr>";
			print "<td width=100% valign=top style=\"border: thin solid #ccc;\">";
			print "<table width=100% cellspacing='0' cellpadding='0' onMouseOut=\"javascript:highlightTableRowVersionA(0);\">";
			$crecords = mysql_query("select record_id, fname, mname, lname, date_format(bday,'%m/%d/%Y') as bday, bday as bd8, gender, status, occupation from pccpayroll.emp_brecord where emp_id = '$eid' order by bd8 asc;");
			$x = 1;
			while($_crow = mysql_fetch_array($crecords)) {
				if ($color == "#ffffff") { $mycolor = "#e6e6e6"; } else { $mycolor = "#ffffff"; }
				print "<tr bgcolor=\"$mycolor\" onMouseOver=\"javascript:highlightTableRowVersionA(this, '#95f0e8');\" title=\"Click to view or edit this record.\" onclick=\"view_brecord(".$_crow['record_id'].");\">";
				print "<td class=\"grid2\" width=3% align=center>" . $x++ . ".</td>";
				print "<td class=\"grid2\" width=\"12%\" align=left>$_crow[fname]</td>";
				print "<td class=\"grid2\" align=left width=\"15%\" style=\"padding-left: 10px;\">$_crow[mname]</td>";
				print "<td class=\"grid2\" align=left width=\"15%\" style=\"padding-left: 10px;\">$_crow[lname]</td>";
				print "<td align=center class=\"grid2\" width=\"15%\">$_crow[bday]</td>";
				print "<td align=center class=\"grid2\" width=\"10%\">$_crow[gender]</td>";
				print "<td align=left class=\"grid2\" width=\"10%\" style=\"padding-left: 10px;\">$_crow[status]</td>";
				print "<td align=left class=\"grid2\" width=\"20%\" style=\"padding-left: 10px;\">$_crow[occupation]&nbsp;</td>";
				print "</tr>";
				$color = $mycolor;
			}
			print "</table></td></tr></table>";
	}

	function retrieveExperiences($eid) {
		echo '<table width=100% cellspacing=0 cellpadding=0 onMouseOut="javascript:highlightTableRowVersionA(0);">';
					$i = 0;
					$getRec = dbquery("select record_id,company,address,job_title,job_responsibility,concat(date_format(datefrom,'%m/%d/%Y'),' - ',date_format(dateto,'%m/%d/%Y')) as covered_period from pccpayroll.emp_emphistory where emp_id='$eid';");
					while($row = mysql_fetch_array($getRec)) {
						if($i%2==0){ $bgC = "#f5f5f5"; } else { $bgC = "#ffffff"; }
						$jresp = explode(";",$row['job_responsibility']);
						echo "<tr bgcolor=\"$bgC\" onmouseover=\"highlightTableRowVersionA(this, '#95f0e8');\" id='obj_$row[record_id]' onclick='viewRecord(".$row['record_id'].");'>
								<td class=dgridbox align=left width=\"25%\">$row[company]</td>
								<td class=dgridbox align=left width=\"20%\">$row[covered_period]</td>
								<td class=dgridbox align=left width=\"15%\">$row[job_title]</td>
								<td class=dgridbox align=left>";
									$z = 1;
									foreach($jresp as $responsibilities) {
										print "&bull; " . $responsibilities . "<br>";
									}
							echo "</td>
							</tr>"; $i++; 
						}
					if($i < 20) {
						for($i; $i <= 20; $i++) {
							if($i%2==0){ $bgC = "#f5f5f5"; } else { $bgC = "#ffffff"; }
							echo "<tr bgcolor='$bgC'><td colspan='6'>&nbsp;</td></tr>";
						}
					}
				echo '</table>';
	}
	
		function retrieveExperiencesInternal($eid) {

		echo '<table width=100% cellspacing=0 cellpadding=0 onMouseOut="javascript:highlightTableRowVersionA(0);">';
					$i = 0;
					$getRec = dbquery("select record_id,previous_title,concat(date_format(previous_start,'%m/%d/%Y'),' ',date_format(previous_end,'%m/%d/%Y')) as covered_period, previous_responsibilities, new_title, date_format(new_start,'%m/%d/%Y') as xstart, new_responsibilities from pccpayroll.emp_internalhistory where emp_id='$eid';");
					while($row = mysql_fetch_array($getRec)) {
						if($i%2==0){ $bgC = "#f5f5f5"; } else { $bgC = "#ffffff"; }
						$jresp = explode(";",$row['previous_responsibilities']);
						$kresp = explode(";",$row['new_responsibilities']);
						echo "<tr bgcolor=\"$bgC\" onmouseover=\"highlightTableRowVersionA(this, '#95f0e8');\" onclick='javascript: viewRecord(".$row['record_id'].");'>
								<td class=dgridbox align=left width=\"10%\" valign=top>$row[previous_title]</td>
								<td class=dgridbox align=center width=\"16%\" valign=top>$row[covered_period]</td>
								<td class=dgridbox align=left width=\"25%\" valign=top>";
									$z = 1;
									foreach($jresp as $responsibilities) {
										print "&bull; " . $responsibilities . "<br>";
									}
						  echo "</td>
								<td class=dgridbox align=center width=\"10%\" valign=top>$row[new_title]</td>
								<td class=dgridbox align=center width=\"10%\" valign=top>$row[xstart]</td>
								<td class=dgridbox align=left>";
									$z = 1;
									foreach($kresp as $responsibilities2) {
										print "&bull; " . $responsibilities2 . "<br>";
									}
						  echo "</td>
							</tr>"; $i++; 
						}
					if($i < 20) {
						for($i; $i <= 20; $i++) {
							if($i%2==0){ $bgC = "#f5f5f5"; } else { $bgC = "#ffffff"; }
							echo "<tr bgcolor='$bgC'><td colspan='7'>&nbsp;</td></tr>";
						}
					}
				echo '</table>';

	}
	
	function retrieveCertificates($eid) {
		echo '<table width=100% cellspacing=0 cellpadding=0>';
			$i = 0;	
			$getRec = dbquery("select record_id, doc_title, date_format(doc_date,'%m/%d/%Y') as doc_date, doc_description, date_format(date_uploaded,'%m/%d/%Y %r') as uploaded from pccpayroll.emp_certificates where emp_id='$eid';");
				while($row = mysql_fetch_array($getRec)) {
						if($i%2==0){ $bgC = "#f5f5f5"; } else { $bgC = "#ffffff"; }

						echo "<tr bgcolor=\"$bgC\">
								<td class=dgridbox align=left width=\"150\">$row[doc_title]</td>
								<td class=dgridbox align=center width=\"120\">$row[doc_date]</td>
								<td class=dgridbox align=left width=\"340\">$row[doc_description]</td>
								<td class=dgridbox align=center width=\"140\">$row[uploaded]</td>
								<td class=dgridbox align=center><a href=\"#\" onclick=\"delFile($row[record_id]);\" title='Delete Record'><img src='images/icons/bin.png' width=18 height=18 align=absmiddle /></a>&nbsp;&nbsp;<a href=\"#\" onclick=\"viewFile($row[record_id]);\" title='Download File'><img src='images/icons/xdownload.png' width=18 height=18 align=absmiddle /></a>&nbsp;&nbsp;<a href=\"#\" onclick=\"editFile($row[record_id]);\" title='Edit Record'><img src='images/icons/edit.png' width=18 height=18 align=absmiddle /></a></td>
							</tr>"; $i++; 
				}
				if($i < 20) {
					for($i; $i <= 20; $i++) {
						if($i%2==0){ $bgC = "#f5f5f5"; } else { $bgC = "#ffffff"; }
						echo "<tr bgcolor='$bgC'><td colspan='7'>&nbsp;</td></tr>";
					}
				}
			echo '</table>';
	}

	switch($_POST['mod']) {
		case "checkDupID":
			if($_POST['eid'] != "") {
				list($isExist) = getArray("select count(*) from pccpayroll.emp_masterfile where EMP_ID = '$_POST[id_no]' and record_id != '$_POST[eid]';");
			} else {
				list($isExist) = getArray("select count(*) from pccpayroll.emp_masterfile where EMP_ID = '$_POST[id_no]';");
			}
			if($isExist == 0) { echo "NODUPLICATE"; }
		break;
		case "saveEInfo":
			if($_POST['eid'] == "") {
				$i_string = "insert ignore into pccpayroll.emp_masterfile (company,branch,etype,EMP_ID,bio_id,bank_acct,status,pay_type,lname,fname,mname,gender,address,birthdate,email,cstatus,bloodtype,emergency_contact,hired_date,date_regularized,exit_status,date_exited,tin_no,sss_id,pagibig_id,philhealth_id,tax_bracket,designation,department,section,reports_to,ojt_from,ojt_to,probi_from,probi_to,contract_from,contract_to,daily_rate,monthly_rate,rice_subsidy,clothing,laundry,insurance,other_non_tax,total_salary,paysched,".
							"encoded_by,encoded_on,hmo,wtax) values ('$_SESSION[company]','$_POST[ee_branch]','$_POST[ee_type]','$_POST[ee_id]','$_POST[bio_id]','$_POST[bank_acct]','$_POST[ee_status]','$_POST[ee_paytype]','".strtoupper($_POST['ee_lname'])."','".strtoupper($_POST['ee_fname'])."','".strtoupper($_POST['ee_mname'])."','$_POST[ee_gender]','".strtoupper(mysql_real_escape_string($_POST['ee_address']))."','".formatDate($_POST['ee_bday'])."',".
							"'$_POST[ee_email]','$_POST[ee_cstatus]','$_POST[ee_bloodtype]','".mysql_real_escape_string(htmlentities($_POST['emergency_contact']))."','".formatDate($_POST['ee_hired'])."','".formatDate($_POST['date_regularized'])."','$_POST[ee_exit_status]','".formatDate($_POST['date_exited'])."','$_POST[ee_tin]','$_POST[ee_sss]','$_POST[ee_pagibig]','$_POST[ee_philhealth]','$_POST[ee_taxcode]','".strtoupper($_POST['ee_desg'])."','$_POST[ee_dept]','$_POST[ee_section]','".mysql_real_escape_string(htmlentities($_POST['reports_to']))."','".formatDate($_POST['ojt_from'])."'," .
							"'".formatDate($_POST['ojt_to'])."','".formatDate($_POST['probi_from'])."','".formatDate($_POST['probi_to'])."','".formatDate($_POST['contract_from'])."','".formatDate($_POST['contract_to'])."','".formatDigit($_POST['ee_salary_daily'])."','".formatDigit($_POST['ee_salary_monthly'])."','".formatDigit($_POST['rice_subsidy'])."','".formatDigit($_POST['clothing'])."','".formatDigit($_POST['laundry'])."','".formatDigit($_POST['insurance'])."','".formatDigit($_POST['other_non_tax'])."','".formatDigit($_POST['ee_salary_total'])."','$_POST[ee_paysched]','$_SESSION[userid]',now(),'$_POST[ee_hmow]','$_POST[ee_wtax]');";
					echo $i_string;
					dbquery($i_string);
					
				trailer("PAYROLL","NEW EMPLOYEE RECORD ADDED: EMP NAME => $_POST[ee_fname] $_POST[ee_mname] $_POST[ee_fname], SALARY => $_POST[ee_salary], ALLOWANCE => $_POST[ee_allowance], PAYROLL TYPE => $_POST[ee_paytype], STATUS => $_POST[ee_status]");
			} else {
				$u_string = "update ignore pccpayroll.emp_masterfile set company = '$_POST[ee_company]', branch = '$_POST[ee_branch]', EMP_ID='$_POST[ee_id]', bio_id='$_POST[bio_id]', bank_acct = '$_POST[bank_acct]', status='$_POST[ee_status]',pay_type='$_POST[ee_paytype]',lname='".strtoupper($_POST['ee_lname'])."',fname='".strtoupper($_POST['ee_fname'])."', mname='".strtoupper($_POST['ee_mname'])."',address='".strtoupper(mysql_real_escape_string($_POST['ee_address']))."',gender='$_POST[ee_gender]',".
						    "birthdate='".formatDate($_POST['ee_bday'])."',email='$_POST[ee_email]',cstatus='$_POST[ee_cstatus]',bloodtype='$_POST[ee_bloodtype]',emergency_contact='".mysql_real_escape_string(htmlentities($_POST['emergency_contact']))."', hired_date='".formatDate($_POST['ee_hired'])."', date_regularized='".formatDate($_POST['date_regularized'])."', exit_status='$_POST[ee_exit_status]', date_exited='".formatDate($_POST['date_exited'])."', tin_no='$_POST[ee_tin]',sss_id='$_POST[ee_sss]',pagibig_id='$_POST[ee_pagibig]',philhealth_id='$_POST[ee_philhealth]',".
						    "tax_bracket='$_POST[ee_taxcode]',designation='".strtoupper($_POST['ee_desg'])."',department='$_POST[ee_dept]',section='$_POST[ee_section]',reports_to='".mysql_real_escape_string(htmlentities($_POST['reports_to']))."',ojt_from='".formatDate($_POST['ojt_from'])."', ojt_to='".formatDate($_POST['ojt_to'])."', probi_from='".formatDate($_POST['probi_from'])."', probi_to='".formatDate($_POST['probi_to'])."', contract_from='".formatDate($_POST['contract_from'])."', contract_to='".formatDate($_POST['contract_to'])."', daily_rate='".formatDigit($_POST['ee_salary_daily'])."'," .
						    " monthly_rate='".formatDigit($_POST['ee_salary_monthly'])."', rice_subsidy='".formatDigit($_POST['rice_subsidy'])."', clothing='".formatDigit($_POST['clothing'])."',laundry='".formatDigit($_POST['laundry'])."',insurance='".formatDigit($_POST['insurance'])."', other_non_tax='".formatDigit($_POST['other_non_tax'])."', total_salary='".formatDigit($_POST['ee_salary_total'])."', paysched='$_POST[ee_paysched]', updated_by=$_SESSION[userid], updated_on=now(),hmo='$_POST[ee_hmo]',wtax = '$_POST[ee_wtax]' where record_id='$_POST[eid]';";
				//echo $u_string;
				dbquery($u_string);
				trailer("PAYROLL","EMPLOYEE RECORD UPDATED: EMP NAME => $_POST[ee_fname] $_POST[ee_mname] $_POST[ee_fname], SALARY => $_POST[ee_salary], ALLOWANCE => $_POST[ee_allowance], PAYROLL TYPE => $_POST[ee_paytype], STATUS => $_POST[ee_status]");
			}
		break;
		case "deleteEE":
			dbquery("update pccpayroll.emp_masterfile set filestatus = 'Archive', updated_by='$_SESSION[userid]', updated_on=now() where record_id='$_POST[eid]';");
		break;
		case "restoreEE":
			dbquery("update pccpayroll.emp_masterfile set filestatus = 'Active', updated_by='$_SESSION[userid]', updated_on=now() where record_id='$_POST[eid]';");
		break;
		case "saveLoan":
			$amt = formatDigit($_POST['amount']);
			if(isset($_POST['fid']) && $_POST['fid'] != "") {
				dbquery("update pccpayroll.emp_loans set EMP_ID='$_POST[id_no]', date_availed='".formatDate($_POST['loan_date'])."', loan_type='$_POST[loan_type]', terms='$_POST[terms]', amount='$amt', dedu_amount='".formatDigit($_POST['dedu_amount'])."', balance=$amt-applied_amount, remarks='".mysql_real_escape_string($_POST['remarks'])."' where file_id='$_POST[fid]';");
			} else {
				dbquery("insert into pccpayroll.emp_loans (EMP_ID,date_availed,loan_type,terms,amount,dedu_amount,balance,remarks) values ('$_POST[id_no]','".formatDate($_POST['loan_date'])."','$_POST[loan_type]','$_POST[terms]','$amt','".formatDigit($_POST['dedu_amount'])."','$amt','".mysql_real_escape_string($_POST['remarks'])."');");
			}
		break;
		case "viewLoanFile":
			echo json_encode(getArray("select *, date_format(date_availed,'%m/%d/%Y') as availed from pccpayroll.emp_loans where file_id = '$_POST[lid]';"));
		break;
		case "deleteLoan":
			list($isExist) = getArray("select count(*) from pccpayroll.emp_loanposted where loan_id = '$_POST[fid]';");
			if($isExist > 0) { echo "error"; } else { dbquery("delete from pccpayroll.emp_loans where file_id='$_POST[fid]';"); }
		break;
		case "getEmployees":
			$a = dbquery("select EMP_ID, concat(lname,', ',fname) as emp from pccpayroll.emp_masterfile where (EMP_ID != '' or EMP_ID is not null)  and filestatus = 'Active' order by lname;");
				echo "<option value=''>SELECT</option>";
			while(list($bid,$name) = mysql_fetch_array($a)) {
				echo "<option value='$bid'>$name</option>";
			}
		break;
		case "getEmpName":
			list($ename) = getArray("select concat(lname,', ',fname) as emp from pccpayroll.emp_masterfile where EMP_ID = '$_POST[record_id]';");
			echo $ename;
		break;
		case "getEmpNameById":
			list($ename) = getArray("select concat(lname,', ',fname) as emp from pccpayroll.emp_masterfile where EMP_ID = '$_POST[id_no]';");
			echo $ename;
		break;
		case "viewCRecord":
			$a = getArray("select *, if(bday!='0000-00-00',date_format(bday,'%m/%d/%Y'),'') as xbday from pccpayroll.emp_crecord where record_id = '$_POST[rid]';");
			echo json_encode($a);
		break;
		case "saveCRecord":
			if($_POST['rid'] != "") {
				$txt = "update pccpayroll.emp_crecord set fname='".strtoupper(mysql_real_escape_string(htmlentities($_POST['fname'])))."', mname='".strtoupper(mysql_real_escape_string(htmlentities($_POST['mname'])))."', lname='".strtoupper(mysql_real_escape_string(htmlentities($_POST['lname'])))."', bday='".formatDate($_POST['bday'])."', gender='$_POST[gender]', `status`='$_POST[cstat]', occupation='".mysql_real_escape_string($_POST['occupation'])."' where record_id = '$_POST[rid]';";
			} else {
				$txt = "insert ignore into pccpayroll.emp_crecord (emp_id,fname,mname,lname,bday,gender,`status`,occupation) values ('$_POST[eid]','".strtoupper(mysql_real_escape_string(htmlentities($_POST['fname'])))."','".strtoupper(mysql_real_escape_string(htmlentities($_POST['mname'])))."','".strtoupper(mysql_real_escape_string(htmlentities($_POST['lname'])))."','".formatDate($_POST['bday'])."','$_POST[gender]','$_POST[cstat]','$_POST[occupation]');";
			}
			dbquery($txt);
			displayCRecord($_POST['eid']);
		break;
		case "deleteCRecord":
			dbquery("delete from pccpayroll.emp_crecord where record_id = '$_POST[rid]';");
			displayCRecord($_POST['eid']);
		break;
		case "viewBRecord":
			$a = getArray("select *, if(bday!='0000-00-00',date_format(bday,'%m/%d/%Y'),'') as xbday from pccpayroll.emp_brecord where record_id = '$_POST[rid]';");
			echo json_encode($a);
		break;
		case "saveBRecord":
			if($_POST['rid'] != "") {
				$txt = "update pccpayroll.emp_brecord set fname='".mysql_real_escape_string(htmlentities($_POST['fname']))."', mname='".mysql_real_escape_string(htmlentities($_POST['mname']))."', lname='".mysql_real_escape_string(htmlentities($_POST['lname']))."', bday='".formatDate($_POST['bday'])."', gender='$_POST[gender]', `status`='$_POST[cstat]', occupation='".mysql_real_escape_string($_POST['occupation'])."' where record_id = '$_POST[rid]';";
			} else {
				$txt = "insert ignore into pccpayroll.emp_brecord (emp_id,fname,mname,lname,bday,gender,`status`,occupation) values ('$_POST[eid]','".mysql_real_escape_string(htmlentities($_POST['fname']))."','".mysql_real_escape_string(htmlentities($_POST['mname']))."','".mysql_real_escape_string(htmlentities($_POST['lname']))."','".formatDate($_POST['bday'])."','$_POST[gender]','$_POST[cstat]','$_POST[occupation]');";
			}
			dbquery($txt);
			displayBRecord($_POST['eid']);
		break;
		case "deleteBRecord":
			dbquery("delete from pccpayroll.emp_brecord where record_id = '$_POST[rid]';");
			displayBRecord($_POST['eid']);
		break;
		case "saveFBackground":
			/* Spouse */
			list($isE) = getArray("select count(*) from pccpayroll.emp_srecord where emp_id = '$_POST[eid]';");
			if($isE > 0) {
				dbquery("update pccpayroll.emp_srecord set fname='".mysql_real_escape_string(htmlentities($_POST['s_fname']))."',mname='".mysql_real_escape_string(htmlentities($_POST['s_mname']))."',lname='".mysql_real_escape_string(htmlentities($_POST['s_lname']))."',bday='".formatDate($_POST['s_bday'])."',occupation='$_POST[s_occupation]',address='".mysql_real_escape_string(htmlentities($_POST['s_address']))."' where emp_id = '$_POST[eid]';");
			} else {
				dbquery("insert ignore into pccpayroll.emp_srecord (emp_id,fname,mname,lname,bday,occupation,address) values ('$_POST[eid]','".mysql_real_escape_string(htmlentities($_POST['s_fname']))."','".mysql_real_escape_string(htmlentities($_POST['s_mname']))."','".mysql_real_escape_string(htmlentities($_POST['s_lname']))."','".formatDate($_POST['s_bday'])."','$_POST[occupation]','".mysql_real_escape_string(htmlentities($_POST['s_address']))."');");
			}

			/* Parents */
			list($isE) = getArray("select count(*) from pccpayroll.emp_precord where emp_id = '$_POST[eid]';");
			if($isE > 0) {
				dbquery("update pccpayroll.emp_precord set mom_fname='".mysql_real_escape_string(htmlentities($_POST['m_fname']))."',mom_mname='".mysql_real_escape_string(htmlentities($_POST['m_mname']))."',mom_lname='".mysql_real_escape_string(htmlentities($_POST['m_lname']))."',mom_bday='".formatDate($_POST['m_bday'])."',mom_occupation='$_POST[m_occupation]',dad_fname='".mysql_real_escape_string(htmlentities($_POST['d_fname']))."',dad_mname='".mysql_real_escape_string(htmlentities($_POST['d_mname']))."',dad_lname='".mysql_real_escape_string(htmlentities($_POST['d_lname']))."',dad_bday='".formatDate($_POST['d_bday'])."',dad_occupation='$_POST[d_occupation]',address='".mysql_real_escape_string(htmlentities($_POST['mdaddress']))."' where emp_id = '$_POST[eid]';");
			} else {
				dbquery("insert ignore into pccpayroll.emp_precord (emp_id,mom_fname,mom_mname,mom_lname,mom_bday,mom_occupation,dad_fname,dad_mname,dad_lname,dad_bday,dad_occupation,address) values ('$_POST[eid]','".mysql_real_escape_string(htmlentities($_POST['m_fname']))."','".mysql_real_escape_string(htmlentities($_POST['m_mname']))."','".mysql_real_escape_string(htmlentities($_POST['m_lname']))."','".formatDate($_POST['m_bday'])."','$_POST[m_occupation]','".mysql_real_escape_string(htmlentities($_POST['d_fname']))."','".mysql_real_escape_string(htmlentities($_POST['d_mname']))."','".mysql_real_escape_string(htmlentities($_POST['d_lname']))."','".formatDate($_POST['d_bday'])."','$_POST[occupation]','".mysql_real_escape_string(htmlentities($_POST['mdaddress']))."');");
			}
		break;
		case "clearFBackground":
			dbquery("delete from pccpayroll.emp_brecord where emp_id = '$_POST[eid]';");
			dbquery("delete from pccpayroll.emp_crecord where emp_id = '$_POST[eid]';");
			dbquery("delete from pccpayroll.emp_precord where emp_id = '$_POST[eid]';");
			dbquery("delete from pccpayroll.emp_srecord where emp_id = '$_POST[eid]';");
		break;
		case "saveEdu":
			list($isE) = getArray("select count(*) from pccpayroll.emp_edubackground where emp_id = '$_POST[emp_idno]';");
			if($isE > 0) {
				dbquery("update ignore pccpayroll.emp_edubackground set pg_specialization='$_POST[pg_specialization]', pg_major='$_POST[pg_major]', pg_school='".mysql_real_escape_string(htmlentities($_POST['pg_school']))."', pg_address='".mysql_real_escape_string(htmlentities($_POST['pg_address']))."', pg_years='".mysql_real_escape_string($_POST['pg_years'])."', pg_graduated='$_POST[pg_graduated]', pg_awards='".mysql_real_escape_string(htmlentities($_POST['pg_awards']))."', co_specialization='$_POST[co_specialization]', co_major='$_POST[co_major]', co_school='".mysql_real_escape_string(htmlentities($_POST['co_school']))."', co_address='".mysql_real_escape_string(htmlentities($_POST['co_address']))."', co_years='".mysql_real_escape_string($_POST['co_years'])."', co_graduated='$_POST[co_graduated]', co_awards='".mysql_real_escape_string(htmlentities($_POST['co_awards']))."', hs_school='".mysql_real_escape_string(htmlentities($_POST['hs_school']))."', hs_address='".mysql_real_escape_string(htmlentities($_POST['hs_address']))."', hs_years='".mysql_real_escape_string($_POST['hs_years'])."', hs_graduated='$_POST[hs_graduated]', hs_awards='".mysql_real_escape_string(htmlentities($_POST['hs_awards']))."', elem_school='".mysql_real_escape_string(htmlentities($_POST['elem_school']))."', elem_address='".mysql_real_escape_string(htmlentities($_POST['elem_address']))."', elem_years='".mysql_real_escape_string($_POST['elem_years'])."', elem_graduated='$_POST[elem_graduated]', elem_awards='".mysql_real_escape_string(htmlentities($_POST['elem_awards']))."' where emp_id = '$_POST[emp_idno]';");
				
				echo "update ignore pccpayroll.emp_edubackground set pg_specialization='$_POST[pg_specialization]', pg_major='$_POST[pg_major]', pg_school='".mysql_real_escape_string(htmlentities($_POST['pg_school']))."', pg_address='".mysql_real_escape_string(htmlentities($_POST['pg_address']))."', pg_years='".mysql_real_escape_string($_POST['pg_years'])."', pg_graduated='$_POST[pg_graduated]', pg_awards='".mysql_real_escape_string(htmlentities($_POST['pg_awards']))."', co_specialization='$_POST[co_specialization]', co_major='$_POST[co_major]', co_school='".mysql_real_escape_string(htmlentities($_POST['co_school']))."', co_address='".mysql_real_escape_string(htmlentities($_POST['co_address']))."', co_years='".mysql_real_escape_string($_POST['co_years'])."', co_graduated='$_POST[co_graduated]', co_awards='".mysql_real_escape_string(htmlentities($_POST['co_awards']))."', hs_school='".mysql_real_escape_string(htmlentities($_POST['hs_school']))."', hs_address='".mysql_real_escape_string(htmlentities($_POST['hs_address']))."', hs_years='".mysql_real_escape_string($_POST['hs_years'])."', hs_graduated='$_POST[hs_graduated]', hs_awards='".mysql_real_escape_string(htmlentities($_POST['hs_awards']))."', elem_school='".mysql_real_escape_string(htmlentities($_POST['elem_school']))."', elem_address='".mysql_real_escape_string(htmlentities($_POST['elem_address']))."', elem_years='".mysql_real_escape_string($_POST['elem_years'])."', elem_graduated='$_POST[elem_graduated]', elem_awards='".mysql_real_escape_string(htmlentities($_POST['elem_awards']))."' where emp_id = '$_POST[emp_idno]';";
			} else {
				dbquery("insert ignore into pccpayroll.emp_edubackground (emp_id,pg_specialization,pg_major,pg_school,pg_address,pg_graduated,pg_awards,co_specialization,co_major,co_school,co_address,co_graduated,co_awards,hs_school,hs_address,hs_graduated,hs_awards,elem_school,elem_address,elem_graduated,elem_awards) values ('$_POST[emp_idno]','$_POST[pg_specialization]','$_POST[pg_major]','".mysql_real_escape_string(htmlentities($_POST['pg_school']))."','".mysql_real_escape_string(htmlentities($_POST['pg_address']))."','$_POST[pg_graduated]','".mysql_real_escape_string(htmlentities($_POST['pg_awards']))."','$_POST[co_specialization]','$_POST[co_major]','".mysql_real_escape_string(htmlentities($_POST['co_school']))."','".mysql_real_escape_string(htmlentities($_POST['co_address']))."','$_POST[co_graduated]','".mysql_real_escape_string(htmlentities($_POST['co_awards']))."','".mysql_real_escape_string(htmlentities($_POST['hs_school']))."','".mysql_real_escape_string(htmlentities($_POST['hs_address']))."','$_POST[hs_graduated]','".mysql_real_escape_string(htmlentities($_POST['hs_awards']))."','".mysql_real_escape_string(htmlentities($_POST['elem_school']))."','".mysql_real_escape_string(htmlentities($_POST['elem_address']))."','$_POST[elem_graduated]','".mysql_real_escape_string(htmlentities($_POST['elem_awards']))."');");
			}
		break;
		case "clearEHistory":
			dbquery("delete from pccpayroll.emp_edubackground where emp_id = '$_POST[eid]';");
		break;
		case "saveWorkExp":
			if($_POST['rid'] != '') {
				dbquery("update pccpayroll.emp_emphistory set company='".mysql_real_escape_string(htmlentities($_POST['company']))."',address='".mysql_real_escape_string(htmlentities($_POST['address']))."',tel_no='$_POST[telno]',job_title='$_POST[title]',job_responsibility='".mysql_real_escape_string(htmlentities($_POST['responsibility']))."',datefrom='".formatDate($_POST['from'])."',dateto='".formatDate($_POST['to'])."' where record_id = '$_POST[rid]';");
			} else {
				dbquery("insert ignore into pccpayroll.emp_emphistory (emp_id,company,address,tel_no,job_title,job_responsibility,datefrom,dateto) values ('$_POST[emp_idno]','".mysql_real_escape_string(htmlentities($_POST['company']))."','".mysql_real_escape_string(htmlentities($_POST['address']))."','$_POST[telno]','$_POST[title]','".mysql_real_escape_string(htmlentities($_POST['responsibility']))."','".formatDate($_POST['from'])."','".formatDate($_POST['to'])."');");
			}
			retrieveExperiences($_POST['emp_idno']);
		break;
		case "viewExpRecord":
			echo json_encode(getArray("select *, date_format(datefrom,'%m/%d/%Y') as dtf, date_format(dateto,'%m/%d/%Y') as dt2 from pccpayroll.emp_emphistory where record_id = '$_POST[rid]';"));
		break;
		case "deleteExpRecord":
			dbquery("delete from pccpayroll.emp_emphistory where record_id = '$_POST[rid]';");
			retrieveExperiences($_POST['emp_idno']);
		break;
		case "saveWorkExpInternal":
			if($_POST['rid'] != '') {
				dbquery("update pccpayroll.emp_internalhistory set previous_title='$_POST[previous_title]', previous_start='".formatDate($_POST['previous_start'])."', previous_end='".formatDate($_POST['previous_end'])."', previous_responsibilities='".mysql_real_escape_string($_POST['previous_responsibilities'])."',previous_rate='".formatDigit($_POST['previous_rate'])."', new_title='$_POST[new_title]',new_start='".formatDate($_POST['new_start'])."', new_responsibilities='".mysql_real_escape_string($_POST['new_responsibilities'])."', new_rate='".formatDigit($_POST['new_rate'])."' where record_id = '$_POST[rid]';");
			} else {
				dbquery("insert ignore into pccpayroll.emp_internalhistory (emp_id, previous_title,previous_start,previous_end,previous_responsibilities,new_title,new_start,new_responsibilities,previous_rate,new_rate) values ('$_POST[emp_idno]','$_POST[previous_title]','".formatDate($_POST['previous_start'])."','".formatDate($_POST['previous_end'])."','".mysql_real_escape_string($_POST['previous_responsibilities'])."','$_POST[new_title]','".formatDate($_POST['new_start'])."','".mysql_real_escape_string($_POST['new_responsibilities'])."','".formatDigit($_POST['previous_rate'])."','".formatDigit($_POST['new_rate'])."');");
			}

			retrieveExperiencesInternal($_POST['emp_idno']);
		break;
		case "viewExpRecord2":
			echo json_encode(getArray("select *, date_format(previous_start,'%m/%d/%Y') as pstart, date_format(previous_end,'%m/%d/%Y') as pend, date_format(new_start,'%m/%d/%Y') as nstart from pccpayroll.emp_internalhistory where record_id = '$_POST[rid]';"));
		break;
		case "deleteExpRecordInternal":
			dbquery("delete from pccpayroll.emp_internalhistory where record_id = '$_POST[rid]';");
			retrieveExperiencesInternal($_POST['emp_idno']);
		break;
		case "deleteCertFile":
			$file = getArray("select filepath from pccpayroll.emp_certificates where record_id = '$_POST[rid]';");
			unlink($file[0]);
			dbquery("delete from pccpayroll.emp_certificates where record_id = '$_POST[rid]';");
			retrieveCertificates($_POST['emp_idno']);
		break;
		case "viewCertificate":
			echo json_encode(getArray("select *, date_format(doc_date,'%m/%d/%Y') as xd8 from pccpayroll.emp_certificates where record_id = '$_POST[rid]';"));
		break;
		case "updateCertificate":
			dbquery("update pccpayroll.emp_certificates set doc_title='$_POST[xdoc_title]', doc_date='".formatDate($_POST['xdoc_date'])."', doc_description='".mysql_real_escape_string($_POST['xdoc_title'])."' where record_id = '$_POST[xrid]';");
			retrieveCertificates($_POST['xemp_idno']);
		break;
		case "saveAdjustment":
			if($_POST['fid'] != '') {
				dbquery("update pccpayroll.emp_adjustments set EMP_ID = '$_POST[id_no]', `date`='".formatDate($_POST['date'])."', amount='".formatDigit($_POST['amount'])."', remarks='".mysql_real_escape_string($_POST['remarks'])."' where record_id = '$_POST[fid]';");
			} else {
				dbquery("insert ignore into pccpayroll.emp_adjustments (EMP_ID,`date`,amount,remarks) values ('$_POST[id_no]','".formatDate($_POST['date'])."','".formatDigit($_POST['amount'])."','".mysql_real_escape_string($_POST['remarks'])."');");
			}
		break;
		case "viewAdjustment":
			echo json_encode(getArray("select *, date_format(`date`,'%m/%d/%Y') as d8, format(amount,2) as amt from pccpayroll.emp_adjustments where record_id = '$_POST[fid]';"));
		break;
		case "deleteAdjust":
			dbquery("delete from pccpayroll.emp_adjustments where record_id = '$_POST[rid]';");
		break;
		case "saveGraveyard":
			if($_POST['fid'] == "") {
				dbquery("insert ignore into pccpayroll.graveyard (EMP_ID,date_from,date_to,remarks,encoded_by,encoded_on) values ('$_POST[id_no]','".formatDate($_POST['from'])."','".formatDate($_POST['to'])."','".mysql_real_escape_string($_POST['remarks'])."','$_SESSION[userid]',now());");
				echo "insert ignore into pccpayroll.graveyard (EMP_ID,date_from,date_to,remarks,encoded_by,encoded_on) values ('$_POST[id_no]','".formatDate($_POST['from'])."','".formatDate($_POST['to'])."','".mysql_real_escape_string($_POST['remarks'])."','$_SESSION[userid]',now());";
			} else {
				dbquery("update pccpayroll.graveyard set EMP_ID = '$_POST[id_no]', date_from='".formatDate($_POST['from'])."', date_to='".formatDate($_POST['to'])."', remarks='".mysql_real_escape_string($_POST['remarks'])."' where file_id = '$_POST[fid]';");
			}
		break;
		case "deleteGrave":
			dbquery("delete from pccpayroll.graveyard where file_id = '$_POST[fid]';");
		break;
		case "insertAOE":
			if($_POST['push']=='Y'){
				dbquery("INSERT INTO pccpayroll.holiday_aoe (trace_no,hol_fileid,company,branch) VALUES ('$_POST[trace_no]','$_POST[fid]','$_POST[cid]','$_POST[bid]');");
			}else{
				dbquery("DELETE FROM pccpayroll.holiday_aoe WHERE hol_fileid = '$_POST[fid]' AND company = '$_POST[cid]' AND branch = '$_POST[bid]';");
			}
		break;
	}
	
	mysql_close($con);
?>