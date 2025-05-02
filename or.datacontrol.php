<?php
	session_start();
	//ini_set("display_errors","On");
	include("handlers/_generics.php");
	ini_set("max_execution_time",0);
	
	$con = new _init();
	$bid = $_SESSION['branchid'];
	$uid = $_SESSION['userid'];
	
	function post2GL($docno) {
		global $con;

		$a = $con->getArray("SELECT doc_no, or_no, doc_date, DATE_FORMAT(doc_date,'%Y') AS cy, customer_code, sc_discount, cash_tendered-change_due as cash, cc_tendered, check_tendered, ewt, amount_due, remarks FROM or_header WHERE doc_no = '$docno' and branch = '1';");
  
        /* DEBIT SIDE */
        if($a['cash'] > 0) {
            $con->dbquery("INSERT IGNORE INTO acctg_gl (company,branch,cy,doc_no,doc_date,doc_type,contact_id,acct_branch,acct,debit,ref_no,ref_date,ref_type,cost_center,doc_remarks) VALUES ('1','1','$a[cy]','$a[doc_no]','$a[doc_date]','OR','$a[customer_code]','1','10102','$a[cash]','$a[or_no]','$a[doc_date]','OR','','".$con->escapeString($a['remarks'])."');");
        }

        if($a['check_tendered'] > 0) {
            $con->dbquery("INSERT IGNORE INTO acctg_gl (company,branch,cy,doc_no,doc_date,doc_type,contact_id,acct_branch,acct,debit,ref_no,ref_date,ref_type,cost_center,doc_remarks) VALUES ('1','1','$a[cy]','$a[doc_no]','$a[doc_date]','OR','$a[customer_code]','1','10102','$a[check_tendered]','$a[or_no]','$a[doc_date]','OR','','".$con->escapeString($a['remarks'])."');");
        }

        if($a['cc_tendered'] > 0) {
            $con->dbquery("INSERT IGNORE INTO acctg_gl (company,branch,cy,doc_no,doc_date,doc_type,contact_id,acct_branch,acct,debit,ref_no,ref_date,ref_type,cost_center,doc_remarks) VALUES ('1','1','$a[cy]','$a[doc_no]','$a[doc_date]','OR','$a[customer_code]','1','10202','$a[cc_tendered]','$a[or_no]','$a[doc_date]','OR','','".$con->escapeString($a['remarks'])."');");
        }

        if($a['ewt'] > 0) {
            $con->dbquery("INSERT IGNORE INTO acctg_gl (company,branch,cy,doc_no,doc_date,doc_type,contact_id,acct_branch,acct,debit,ref_no,ref_date,ref_type,cost_center,doc_remarks) VALUES ('1','1','$a[cy]','$a[doc_no]','$a[doc_date]','OR','$a[customer_code]','1','10403','$a[ewt]','$a[or_no]','$a[doc_date]','OR','','".$con->escapeString($a['remarks'])."');");
        }

        if($a['doc_date'] < '2022-01-17') {
            if($a['sc_discount'] > 0) {
                $con->dbquery("INSERT IGNORE INTO acctg_gl (company,branch,cy,doc_no,doc_date,doc_type,contact_id,acct_branch,acct,debit,ref_no,ref_date,ref_type,cost_center,doc_remarks) VALUES ('1','1','$a[cy]','$a[doc_no]','$a[doc_date]','OR','$a[customer_code]','1','60403','$a[sc_discount]','$a[or_no]','$a[doc_date]','OR','','".$con->escapeString($a['remarks'])."');");
            }
        }

        /* CREDIT SIDE */
        $c = $con->dbquery("SELECT a.doc_no, IF(b.rev_acct = '', '60101',b.rev_acct) AS rev_acct, SUM(amount_due) AS amount FROM or_details a LEFT JOIN services_master b ON a.code = b.code where a.doc_no = '$a[doc_no]' GROUP BY a.doc_no, b.rev_acct;");
        while($d = $c->fetch_array()) {
            $con->dbquery("INSERT IGNORE INTO acctg_gl (company,branch,cy,doc_no,doc_date,doc_type,contact_id,acct_branch,acct,credit,ref_no,ref_date,ref_type,cost_center,doc_remarks) VALUES ('1','1','$a[cy]','$a[doc_no]','$a[doc_date]','OR','$a[customer_code]','1','$d[rev_acct]','$d[amount]','$a[or_no]','$a[doc_date]','OR','','".$con->escapeString($a['remarks'])."');");
        }
	}

	function updateAmount($doc_no,$cid) {
		global $con;

		if($cid == '' || $cid == '0') { $vat = "Y"; } else { list($vat) = $con->getArray("select vatable from contact_info where file_id = '$cid';"); }

		list($taxcode) = $con->getArray("select tax_code from or_header where doc_no = '$doc_no' and branch = '$_SESSION[branchid]';");
		list($gross,$adue) = $con->getArray("select sum(amount),sum(amount_due) as adue from or_details where doc_no = '$doc_no' and branch = '$_SESSION[branchid]';");
		list($regdiscount) = $con->getArray("select sum(discount) from or_details where doc_no = '$doc_no' and branch = '$_SESSION[branchid]';");
		list($scdiscount) = $con->getArray("select sum(discount) from or_details where doc_no = '$doc_no' and branch = '$_SESSION[branchid]' and disctype in ('SC','PWD');");

		//$nov = 0;
		//$vat = 0;
		//$nonvat = $adue;

		if($vat == 'Y') {
			$nov = ROUND($adue / 1.12,2);
			$vat = ROUND($nov * 0.12,2);
			if($taxcode != '') { 
				$ewt = ROUND($nov * 0.02,2); 
				$adue = $adue - $ewt; 
			}
		} else {
			$nov = $adue;
			$vat = 0;
			if($taxcode != '') { 
				$ewt = ROUND($nov * 0.02,2); 
				$adue = $adue - $ewt; 
			}
		}

		

		$con->dbquery("update or_header set gross = 0$gross, discount = 0$regdiscount, net_of_vat = 0$nov, nonvat = 0$nonvat, vat = 0$vat, ewt = 0$ewt, sc_discount = 0$scdiscount, amount_due = 0$adue, balance = 0$adue  where doc_no = '$doc_no' and branch = '$_SESSION[branchid]';");
	}

	switch($_REQUEST['mod']) {
		
		case "retrieveSO":

			if($_POST['cid'] != '') { $cid = " and customer_code = '$_POST[cid]' "; } else { $cid = ''; }
			$docno = $_POST['docno'];

			$soString = "select *,lpad(so_no,6,0) as sono, lpad(patient_id,6,0) as pid, if(customer_code!=0,lpad(customer_code,6,'0'),'') as cid from so_header where status = 'Finalized' and so_no = '$_POST[so]' and branch = '$bid' $cid;";
			if($con->countRows($soString) > 0) {
				$so = $con->getArray($soString);
				if($so['billed'] == 'Y' or $so['paid'] == 'Y') {
					echo json_encode(array("respond"=>"used"));
				} else {

					$s = $con->getArray("select distinct a.doc_no,lpad(a.doc_no,6,'0') as docno, date_format(doc_date,'%m/%d/%Y') as docdate from or_header a left join or_details b on a.doc_no = b.doc_no and a.branch = b.branch where b.so_no = '$_POST[so]' and b.branch = '$bid' and `status` = 'Active';");
					if($s[0] != '') {
						echo json_encode(array("respond"=>"currentActive","doc_no"=>$s['doc_no'],"docno"=>$s['docno'],"date"=>$s['docdate']));
					} else {
					
						/* Automatically Insert Data from SO Header to OR Header */
						if($docno == '') {
							list($orno) = $con->getArray("select ifnull(max(or_no),0)+1 from or_header where branch = '$bid';"); 
							list($docno) = $con->getArray("select ifnull(max(doc_no),0)+1 from or_header where branch = '$bid';");
							$con->dbquery("INSERT IGNORE INTO or_header (doc_no,branch,doc_date,or_no,customer_code,customer_name,customer_address,scpwd_id,amount_due,amount_paid,balance,trace_no,created_by,created_on) VALUES ('$docno','$bid','".date('Y-m-d')."','$orno','$so[customer_code]','$so[customer_name]','$so[customer_address]','$so[scpwd_id]','$so[amount]','0','$so[amount]','$_POST[trace_no]','$uid',now());");
						} 

						/* INSERT CONTENT OF SO DETAILS TO OR DETAILS */
						$sdQuery = $con->dbquery("select a.*, b.patient_id,b.patient_name,b.patient_address,b.so_date from so_details a left join so_header b on a.so_no = b.so_no and a.branch = b.branch where  a.so_no = '$_POST[so]' and a.branch = '$bid';");
						while($sdRow = $sdQuery->fetch_array()) {
							$con->dbquery("INSERT IGNORE INTO or_details (doc_no,branch,so_no,so_date,pid,pname,paddr,`code`,`description`,unit,unit_price,qty,amount,disctype,discpercent,discount,amount_due,trace_no) VALUES ('$docno','$bid','$sdRow[so_no]','$sdRow[so_date]','$sdRow[patient_id]','".$con->escapeString($sdRow['patient_name'])."','".$con->escapeString($sdRow['patient_address'])."','$sdRow[code]','".$con->escapeString($sdRow['description'])."','$sdRow[unit]','$sdRow[unit_price]','$sdRow[qty]','$sdRow[amount]','$sdRow[disctype]','$sdRow[discpercent]','$sdRow[discount]','$sdRow[amount_due]','$_POST[trace_no]');");
							//echo "INSERT INTO or_details (doc_no,branch,so_no,so_date,pid,pname,paddr,`code`,`description`,unit,unit_price,qty,amount,disctype,discpercent,discount,amount_due,trace_no) VALUES ('$docno','$bid','$sdRow[so_no]','$sdRow[so_date]','$sdRow[patient_id]','$sdRow[patient_name]','$sdRow[patient_address]','$sdRow[code]','$sdRow[description]','$sdRow[unit]','$sdRow[unit_price]','$sdRow[qty]','$sdRow[amount]','$sdRow[disctype]','$sdRow[discpercent]','$sdRow[discount]','$sdRow[amount_due]','$_POST[trace_no]');";
						}
						
						$or = array("respond"=>"ok","or_no"=>str_pad($orno,6,0,STR_PAD_LEFT),"doc_no"=>str_pad($docno,6,0,STR_PAD_LEFT));
						$result = array_merge($so,$or);
						echo json_encode($result);

						updateAmount($docno,$so['customer_code']);
					}
				}
			} else {
				echo json_encode(array("respond"=>"notFound"));
			}

		break;

		// case "retrieveSOA":

		// 	$soa = $con->getArray("select customer_code, lpad(customer_code,6,'0') as cid, customer_name as cname, customer_address as caddr, amount, b.vatable from soa_header a left join contact_info b on a.customer_code = b.file_id where soa_no = '$_POST[soa]' and branch = '$bid';");
			
		// 	if($_POST['docno'] == '') {
		// 		list($orno) = $con->getArray("select ifnull(max(or_no),0)+1 from or_header where branch = '$bid';"); 
		// 		list($docno) = $con->getArray("select ifnull(max(doc_no),0)+1 from or_header where branch = '$bid';");
		// 		$con->dbquery("INSERT IGNORE INTO or_header (doc_no,branch,doc_date,or_no,customer_code,customer_name,customer_address,amount_due,amount_paid,balance,trace_no,created_by,created_on) VALUES ('$docno','$bid','".date('Y-m-d')."','$orno','$soa[customer_code]','$soa[cname]','$soa[caddr]','$soa[amount]','0','$soa[amount]','$_POST[trace_no]','$uid',now());");
		// 	} else {
		// 		$orno = $_POST['or_no'];
		// 		$docno = $_POST['docno'];
		// 		$con->dbquery("UPDATE IGNORE or_header SET customer_code = '$soa[customer_code]', customer_name='$soa[cname]',customer_address='$soa[caddr]',amount_due='$soa[amount]',amount_paid=0,balance='$soa[amount]',updated_by='$uid',updated_on = now() where doc_no = '$docno' and branch = '$bid';");
		// 	}
			
		// 	/* INSERT CONTENT OF SOA DETAILS TO OR DETAILS */
		// 	$sdQuery = $con->dbquery("SELECT *, unit_price as uprice, amount as amt FROM soa_details WHERE soa_no = '$_POST[soa]' AND branch = '$bid';");
		// 	while($sdRow = $sdQuery->fetch_array()) {
		// 		$con->dbquery("INSERT INTO or_details (doc_no,branch,so_no,so_date,soa_no,pid,pname,`code`,`description`,unit,unit_price,is_special,qty,amount,amount_due,trace_no) VALUES ('$docno','$bid','$sdRow[so_no]','$sdRow[so_date]','$_POST[soa]','$sdRow[pid]','$sdRow[pname]','$sdRow[code]','$sdRow[description]','$sdRow[unit]','$sdRow[uprice]','N','$sdRow[qty]','".ROUND($sdRow['uprice'] * $sdRow['qty'],2)."','$sdRow[amt]','$_POST[trace_no]');");
		// 	}

		// 	$or = array("respond"=>"ok","or_no"=>str_pad($orno,6,0,STR_PAD_LEFT),"doc_no"=>str_pad($docno,6,0,STR_PAD_LEFT));
		// 	$result = array_merge($soa,$or);
		// 	echo json_encode($result);

		// 	updateAmount($docno,$soa['customer_code']);
		// break;
		case "retrieveSOA":

			$soa = $con->getArray("select customer_code, lpad(customer_code,6,'0') as cid, customer_name as cname, customer_address as caddr, amount, b.vatable from soa_header a left join contact_info b on a.customer_code = b.file_id where soa_no = '$_POST[soa]' and branch = '$bid';");
			
			if($_POST['docno'] == '') {
				list($orno) = $con->getArray("select ifnull(max(or_no),0)+1 from or_header where branch = '$bid';"); 
				list($docno) = $con->getArray("select ifnull(max(doc_no),0)+1 from or_header where branch = '$bid';");
				$con->dbquery("INSERT IGNORE INTO or_header (doc_no,branch,doc_date,or_no,customer_code,customer_name,customer_address,amount_due,amount_paid,balance,trace_no,created_by,created_on) VALUES ('$docno','$bid','".date('Y-m-d')."','$orno','$soa[customer_code]','$soa[cname]','$soa[caddr]','$soa[amount]','0','$soa[amount]','$_POST[trace_no]','$uid',now());");
			} else {
				$orno = $_POST['or_no'];
				$docno = $_POST['docno'];
				$con->dbquery("UPDATE IGNORE or_header SET customer_code = '$soa[customer_code]', customer_name='$soa[cname]',customer_address='$soa[caddr]',amount_due='$soa[amount]',amount_paid=0,balance='$soa[amount]',updated_by='$uid',updated_on = now() where doc_no = '$docno' and branch = '$bid';");
			}
			
			/* INSERT CONTENT OF SO DETAILS TO OR DETAILS */
			$sdQuery = $con->dbquery("SELECT *, unit_price as uprice, amount as amt FROM soa_details WHERE soa_no = '$_POST[soa]' AND branch = '$bid';");
			while($sdRow = $sdQuery->fetch_array()) {
				$con->dbquery("INSERT IGNORE INTO or_details (doc_no,branch,so_no,so_date,soa_no,pid,pname,paddr,`code`,`description`,unit,unit_price,is_special,qty,amount,amount_due,trace_no) VALUES ('$docno','$bid','$sdRow[so_no]','$sdRow[so_date]','$_POST[soa]','$sdRow[pid]','$sdRow[pname]','$sdRow[paddr]','$sdRow[code]','$sdRow[description]','$sdRow[unit]','$sdRow[uprice]','N','$sdRow[qty]','".ROUND($sdRow['uprice'] * $sdRow['qty'],2)."','$sdRow[amt]','$_POST[trace_no]');");
			}

			$or = array("respond"=>"ok","or_no"=>str_pad($orno,6,0,STR_PAD_LEFT),"doc_no"=>str_pad($docno,6,0,STR_PAD_LEFT));
			$result = array_merge($soa,$or);
			echo json_encode($result);

			updateAmount($docno,$soa['customer_code'],'');
		break;

		case "browseSO":

			$s = '';

			if($_POST['cid'] != '') { $s .= " and a.customer_code = '$_POST[cid]' "; }
			if($_POST['stxt'] != '') {
				$s .= " and (a.so_no = '$_POST[stxt]' || a.customer_name like '%$_POST[stxt]%' || a.patient_name like '%$_POST[stxt]%') ";
			}
			list($cutoff) = $con->getArray("SELECT DATE_SUB('".date('Y-m-d')."', INTERVAL 3 MONTH);");

            $q = "SELECT so_no, LPAD(so_no,6,0) AS sono, DATE_FORMAT(so_date,'%m/%d/%Y') AS sdate, a.patient_name, a.customer_code, if(a.customer_code=0,'Charge to Patient',customer_name) as chargeto, amount, a.remarks FROM so_header a WHERE so_date > '$cutoff' and branch = '$bid' AND billed != 'Y' AND so_no NOT IN (SELECT so_no FROM or_details WHERE doc_no = '$_POST[doc_no]' AND branch = '$bid') AND `status` = 'Finalized' AND cstatus IN ('1') $s ORDER BY so_no DESC, so_date DESC, soa_no DESC;";
            
            if($con->countRows($q) > 0) {
                echo "
                    <form name=\"frmFetchedSO\" id=\"frmFetchedSO\">
                    <table width=100% cellspacing=0 cellpadding=0>
						<tr>
							<td colspan=7 align=right style=\"padding: 4px;\">
								<input type=\"text\" name=\"so_search\" id=\"so_search\" class=\"gridInput\" style=\"width: 200px;\" value=\"$_POST[stxt]\">
								<button type = \"button\" name = \"setPrintLab\" class=\"ui-button ui-widget ui-corner-all\" onClick=\"javascript: searchBrowseSO();\">
									<span class=\"ui-icon ui-icon-search\"></span> Search
								</button>	
							</td>
						</tr>
                        <tr>
                            <td class=gridHead width=10%>SO #</td>
                            <td class=gridHead width=10%>DATE</td>
                            <td class=gridHead width=15%>PATIENT</td>
							<td class=gridHead width=20%>CHARGE TO</td>
                            <td class=gridHead >REMARKS</td>
                            <td class=gridHead width=15% align=right>AMOUNT</td>
                            <td class=gridHead width=20>&nbsp;</td>
                        </tr>";

                $soQuery = $con->dbquery($q); $i=0;
                while($soRow = $soQuery->fetch_array()) {

                    echo "<tr bgcolor='".$con->initBackground($i)."'>
                            <td class=grid>".$soRow['sono']."</td>
                            <td class=grid>".$soRow['sdate']."</td>
                            <td class=grid>".$soRow['patient_name']."</td>
							<td class=grid>".$soRow['chargeto']."</td>
                            <td class=grid>".$soRow['remarks']."</td>
                            <td class=grid align=right>".number_format($soRow['amount'],2)."</td>
                            <td class=grid align=center><input type=radio name=\"so\" id=\"so\" value='".$soRow['so_no']."'></td>
                        </tr>
                    
                    ";
                    $i++;

                }
                 echo "</table>  
                </form>";


            }

        break;

		case "browseSOA":

			if($_POST['cid'] != '') { $fs = " and a.customer_code = '$_POST[cid]' "; }
            $q = "SELECT soa_no, LPAD(soa_no,6,0) AS soano, DATE_FORMAT(soa_date,'%m/%d/%Y') AS sdate, a.customer_code, if(a.customer_code=0,'Charge to Patient',customer_name) as chargeto, amount, a.remarks FROM soa_header a WHERE branch = '$bid' AND balance > 0 AND soa_no NOT IN (SELECT soa_no FROM or_details WHERE doc_no = '$_POST[doc_no]' AND branch = '$bid' and soa_no !='') AND `status` = 'Finalized' $fs ORDER BY soa_date DESC, soa_no DESC;";
            
            if($con->countRows($q) > 0) {
                echo "
                    <form name=\"frmFetchedSO\" id=\"frmFetchedSO\">
                    <table width=100% cellspacing=0 cellpadding=0>

                        <tr>
                            <td class=gridHead width=10%>SOA #</td>
                            <td class=gridHead width=10%>DATE</td>
							<td class=gridHead width=30%>CHARGED TO</td>
                            <td class=gridHead >REMARKS</td>
                            <td class=gridHead width=15% align=right>AMOUNT</td>
                            <td class=gridHead width=20>&nbsp;</td>
                        </tr>";

                $soQuery = $con->dbquery($q); $i=0;
                while($soRow = $soQuery->fetch_array()) {

                    echo "<tr bgcolor='".$con->initBackground($i)."'>
                            <td class=grid>".$soRow['soano']."</td>
                            <td class=grid>".$soRow['sdate']."</td>
							<td class=grid>".$soRow['chargeto']."</td>
                            <td class=grid>".$soRow['remarks']."</td>
                            <td class=grid align=right>".number_format($soRow['amount'],2)."</td>
                            <td class=grid align=center><input type=radio name=\"soa\" id=\"soa\" value='".$soRow['soa_no']."'></td>
                        </tr>
                    
                    ";
                    $i++;

                }
                 echo "</table>  
                </form>";


            }

        break;

		case "updateItem":
			$upd = $con->getArray("select * from or_details where line_id = '$_POST[lid]';");
			$con->dbquery("UPDATE IGNORE or_details set amount_due = '$_POST[amount]' where line_id = '$_POST[lid]';");

			updateAmount($_POST['doc_no'],$_POST['cid']);
		break;

		case "saveHeader":
			if($_POST['doc_no'] != '') {
				$con->dbquery("UPDATE IGNORE or_header SET doc_date = '".$con->formatDate($_POST['docdate'])."', or_no='$_POST[or_no]', customer_code='$_POST[cid]', customer_name='".$con->escapeString(htmlentities($_POST['cname']))."', customer_address='".$con->escapeString(htmlentities($_POST['caddress']))."', is_pwd = '$_POST[is_pwd]', scpwd_id='$_POST[scid]', remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."', cashtype='$_POST[cashtype]', cardtype='$_POST[cc_type]', cardprovider='$_POST[cc_bank]', cardname='$_POST[cc_name]',cardno='$_POST[cc_no]',cardexpiry='$_POST[cc_expiry]',cardapproval='$_POST[cc_approvalno]',checkbank='".$con->escapeString(htmlentities($_POST['ck_bank']))."',checkno='$_POST[ck_no]',checkdate='".$con->formatDate($_POST['ck_date'])."', updated_by='$uid', updated_on=now() WHERE doc_no = '$_POST[doc_no]' and branch = '$bid';");
			} else {
				list($orno) = $con->getArray("select ifnull(max(or_no),0)+1 from or_header where branch = '$bid';"); 
				list($docno) = $con->getArray("select ifnull(max(doc_no),0)+1 from or_header where branch = '$bid';");
				$con->dbquery("INSERT IGNORE INTO or_header (trace_no,doc_no,branch,doc_date,or_no,customer_code,customer_name,customer_address,terms,is_pwd,scpwd_id,remarks,cashtype,cardtype,cardprovider,cardname,cardno,cardexpiry,cardapproval,checkno,checkdate,checkbank,created_by,created_on) values ('$_POST[trace_no]','$docno','$bid','".$con->formatDate($_POST['docdate'])."','$orno','$_POST[cid]','".$con->escapeString(htmlentities($_POST['cname']))."','".$con->escapeString(htmlentities($_POST['caddress']))."','$_POST[terms]','$_POST[is_pwd]','$_POST[scid]','".$con->escapeString(htmlentities($_POST['remarks']))."','$_POST[cashtype]','$_POST[cc_type]','$_POST[cc_bank]','$_POST[cc_name]','$_POST[cc_no]','$_POST[cc_expiry]','$_POST[cc_approvalno]','$_POST[ck_no]','".$con->formatDate($_POST['ck_date'])."','".$con->escapeString(htmlentities($_POST['ck_bank']))."','$uid',now());");
				echo json_encode(array("docno"=>str_pad($docno,6,0,STR_PAD_LEFT),"orno"=>str_pad($orno,6,0,STR_PAD_LEFT)));
			}
		break;

		case "deleteLine":
            $con->deleteRow($table="or_details",$arg = "line_id='$_POST[lid]'");
			updateAmount($_POST['doc_no'],$_POST['cid']);
        break;

		/* case "addItem":

            $sprice = $con->formatDigit($_POST['sprice']);
            $qty = $con->formatDigit($_POST['qty']);
            $amt = $con->formatDigit($_POST['amount']);
            
            if($sprice != $uprice) { $price = $sprice; } else { $price = $uprice; }
            $con->dbquery("INSERT INTO or_details (doc_no,branch,`code`,`description`,unit,unit_price,is_special,qty,amount,discount,amount_due,trace_no) VALUES ('$_POST[doc_no]','$bid','$_POST[item]','".$con->escapeString(htmlentities($_POST['description']))."','$_POST[unit]','$sprice','$_POST[ispecial]','$qty','$amt','0','$amt','$_POST[trace_no]');");
            updateAmount($_POST['doc_no'],$_POST['cid']);
        break; */
		case "applyDiscount":
			$d = $con->getArray("select * from or_details where line_id = '$_POST[lid]';");
			$adue = ROUND($d['qty'] * ($d['unit_price'] - $con->formatDigit($_POST['discount'])),2);
			$con->dbquery("update ignore or_details set discount = '".$con->formatDigit($_POST['discount'])."', amount_due = '$adue' where line_id = '$_POST[lid]';");
			updateAmount($_POST['doc_no'],$_POST['cid'],$_POST['scid']);
		break;

		case "updatePayment":
			$con->dbquery("update ignore or_header set balance = 0, amount_paid = ".$con->formatDigit($_POST['paid']).", cash_tendered = '".$con->formatDigit($_POST['cash'])."', check_tendered = '".$con->formatDigit($_POST['check'])."', cc_tendered = '".$con->formatDigit($_POST['cc'])."', change_due = '".$con->formatDigit($_POST['changeDue'])."', updated_by = '$uid', updated_on = now() where doc_no = '$_POST[doc_no]' and branch = '$bid';");
		break;

		case "updateEWT":
			$con->dbquery("update ignore or_header set tax_code = '$_POST[ecode]' where doc_no = '$_POST[doc_no]' and branch = '$bid';");
			updateAmount($_POST['doc_no'],$_POST['cid']);
		break;

		case "retrieve":
            $data = array();
			$srrd = $con->dbquery("SELECT line_id AS id, LPAD(so_no,6,'0') AS sono, DATE_FORMAT(so_date,'%m/%d/%y') AS sodate, IF(soa_no!='',LPAD(soa_no,6,'0'),'') AS soano, pname, IF(qty > 1,CONCAT(description,' (x',qty,')'),description) AS `procedure`, amount as unit_price, discount, amount_due FROM or_details WHERE trace_no = '$_REQUEST[trace_no]';");
			while($row = $srrd->fetch_array()) {
				$data[] = array_map('utf8_encode',$row);
			}
			$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
			echo json_encode($results);	
        break;

		case "getTotals":
			echo json_encode($con->getArray("SELECT FORMAT(gross,2) as gross, FORMAT(discount,2) as discount, FORMAT(gross-discount,2) as subtotal, FORMAT(ewt,2) as ewt, FORMAT(net_of_vat,2) AS nvat, FORMAT(nonvat,2) as nonvat, FORMAT(vat,2) AS vat, FORMAT(sc_discount,2) AS sc, FORMAT(amount_due,2) AS adue, FORMAT(amount_paid,2) AS paid, FORMAT(balance,2) AS balance FROM or_header WHERE doc_no = '$_POST[doc_no]' and branch = '$bid';;"));
		break;

		case "finalize":
			list($isVat) = $con->getArray("select vatable from contact_info where file_id = '$_POST[cid]';");
			$con->dbquery("update ignore or_header set `status` = 'Finalized', updated_by = '$uid', updated_on = now() where doc_no = '$_POST[doc_no]' and branch = '$bid';");
			
			/* Charge to Patient */
			$dQuery = $con->dbquery("select distinct so_no from or_details where doc_no = '$_POST[doc_no]' and branch = '$bid' AND (soa_no IS NULL OR soa_no = '');");
			while($dRow = $dQuery->fetch_array()) {
				$con->dbquery("update ignore so_header set cstatus = '2', paid = 'Y' where so_no = '$dRow[so_no]' and branch = '$bid';");
			
				/* Send Lab Request Pending Extraction */
				$so = $con->dbquery("SELECT a.so_no AS so, b.code as parent_code, b.code, b.description AS `procedure`, a.physician, d.sample_type, d.container_type FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id LEFT JOIN services_master d ON b.code = d.code WHERE a.status = 'Finalized' AND a.cstatus IN (2,4,12) AND d.with_subtests = 'N' AND d.category IN ('1','2') AND a.so_no = '$dRow[so_no]' and d.description not like '%PCR%' UNION SELECT a.so_no AS so, e.parent as parent_code, e.code, e.description AS `procedure`, a.physician, f.sample_type, f.container_type FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id LEFT JOIN services_master d ON b.code = d.code LEFT JOIN services_subtests e ON b.code = e.parent LEFT JOIN services_master f ON e.code = f.code WHERE a.status = 'Finalized' AND a.cstatus IN (2,4,12) AND d.with_subtests = 'Y' AND f.category IN ('1','2') AND a.so_no = '$dRow[so_no]' and d.description not like '%PCR%';");
				while($eRow = $so->fetch_array()) {
					list($labCount) = $con->getArray("select count(*) from lab_samples where so_no = '$eRow[so]' and parent_code = '$eRow[parent_code]' and code = '$eRow[code]';");
					if($labCount == 0) {
						$con->dbquery("INSERT IGNORE INTO lab_samples (branch,so_no,parent_code,code,`procedure`,sampletype,samplecontainer,physician,created_by,created_on) values ('$bid','$eRow[so]','$eRow[parent_code]','$eRow[code]','$eRow[procedure]','$eRow[sample_type]','$eRow[container_type]','$eRow[physician]','$uid',now());");
					}
				}

				/* Send Request to Nursing Station for PEME */
				$gQuery = $con->dbquery("SELECT a.priority_no as prio, a.so_no, a.so_date, b.code as parentcode, b.code, b.description AS `procedure`, a.patient_id AS pid, c.birthplace, c.occupation AS occu, c. employer AS compname, c.mobile_no AS contactno FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id WHERE a.so_no = '$dRow[so_no]' AND b.code IN ('O009') AND a.status IN (2,4,12) UNION ALL SELECT a.priority_no as prio, a.so_no, a.so_date, b.code as parentcode, e.code, e.description AS `procedure`, a.patient_id AS pid, c.birthplace, c.occupation AS occu, c. employer AS compname, c.mobile_no AS contactno FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id LEFT JOIN services_master d ON b.code = d.code LEFT JOIN services_subtests e ON e.parent = d.code  WHERE a.so_no = '$dRow[so_no]' AND e.code IN ('O009') AND a.status IN (2,4,12) AND  d.with_subtests = 'Y';");
				while($hRow = $gQuery->fetch_array()) {
					$con->dbquery("INSERT IGNORE INTO peme (so_no,branch,so_date,prio,parentcode,code,`procedure`,pid,pob,occu,compname,contactno) values ('$hRow[so_no]','$bid','$hRow[so_date]','$hRow[prio]','$hRow[parentcode]','$hRow[code]','$hRow[procedure]','$hRow[pid]','" . $con->escapeString(htmlentities($hRow['birthplace'])) . "','$hRow[occu]','" . $con->escapeString(htmlentities($hRow['compname'])) . "','$hRow[contactno]');");

				}

				 /* Send Request to Consultation */
				 $xQuery = $con->dbquery("SELECT a.priority_no as prio, a.so_no, a.so_date, b.code as parentcode, b.code, b.description AS `procedure`, a.patient_id AS pid, c.birthplace, c.occupation AS occu, c. employer AS compname, c.mobile_no AS contactno, c.emp_idno, a.patient_name, c.gender, a.trace_no, c.birthdate FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id WHERE a.so_no = '$dRow[so_no]' AND b.code IN ('M001') AND a.status IN (2,4,12) UNION ALL SELECT a.priority_no as prio, a.so_no, a.so_date, b.code as parentcode, e.code, e.description AS `procedure`, a.patient_id AS pid, c.birthplace, c.occupation AS occu, c. employer AS compname, c.mobile_no AS contactno, c.emp_idno, a.patient_name, c.gender, a.trace_no, c.birthdate FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON a.patient_id = c.patient_id LEFT JOIN services_master d ON b.code = d.code LEFT JOIN services_subtests e ON e.parent = d.code  WHERE a.so_no = '$dRow[so_no]' AND e.code IN ('M001') AND a.status IN (2,4,12) AND  d.with_subtests = 'Y';");
				 while($lRow = $xQuery->fetch_array()) {
					 list($fmCount) = $con->getArray("select count(*) from consultation_form where so_no = '$lRow[so_no]' and pid = '$lRow[pid]' and code = '$lRow[code]' and trace_no = '$lRow[trace_no]';");
					 if($fmCount == 0) {
						 $con->dbquery("INSERT IGNORE INTO consultation_form (trans_time,so_no,branch,so_date,priority_no,parentcode,code,`procedure`,pid,pob,occu,compname,contactno,company_id,pname,birthdate,gender,trace_no,created_by,created_on) values (now(),'$lRow[so_no]','$bid','$lRow[so_date]','$lRow[prio]','$lRow[parentcode]','$lRow[code]','$lRow[procedure]','$lRow[pid]','" . $con->escapeString(htmlentities($lRow['birthplace'])) . "','$lRow[occu]','" . $con->escapeString(htmlentities($lRow['compname'])) . "','$lRow[contactno]', '$lRow[emp_idno]','" . $con->escapeString(htmlentities($lRow['patient_name'])) . "','$lRow[birthdate]','$lRow[gender]','$lRow[trace_no]','$_SESSION[userid]',now());");
					}
				 }
			
			}

			/* On-Account */
			$sQuery = $con->dbquery("select sum(amount_due) as paid, soa_no from or_details where doc_no = '$_POST[doc_no]' and branch = '$bid' and soa_no > 0 group by soa_no;");
			while($sRow = $sQuery->fetch_array()) {
				$con->dbquery("update ignore soa_header set balance = balance - 0$sRow[paid], amount_paid = amount_paid + 0$sRow[paid] where soa_no = '$sRow[soa_no]' and branch = '$bid';");
			}

			/* Post to GL */
			post2GL($_POST['doc_no']);

		break;

		case "reopen":
			list($isVat) = $con->getArray("select vatable from contact_info where file_id = '$_POST[cid]';");
			$con->dbquery("update or_header set `status` = 'Active', updated_by = '$uid', updated_on = now() where doc_no = '$_POST[doc_no]' and branch = '$bid';");
		
			/* Charge to Patient */
			$dQuery = $con->dbquery("select distinct so_no from or_details where doc_no = '$_POST[doc_no]' and branch = '$bid' and (soa_no is null or soa_no = '');");
			while($dRow = $dQuery->fetch_array()) {
				$con->dbquery("update so_header set cstatus = '1', paid = 'N' where so_no = '$dRow[so_no]' and branch = '$bid';");
			}

			/* On-Account */
			$sQuery = $con->dbquery("select sum(if('$isVat'='Y',ROUND(amount_due * 1.12,2),amount_due)) as paid, soa_no from or_details where doc_no = '$_POST[doc_no]' and branch = '$bid' and soa_no > 0 group by soa_no;");
			while($sRow = $sQuery->fetch_array()) {
				$con->dbquery("update ignore soa_header set balance = balance + 0$sRow[paid], amount_paid = amount_paid - 0$sRow[paid] where soa_no = '$sRow[soa_no]' and branch = '$bid';");
			}

			if($terms == 0) {
				$con->dbquery("update so_header set cstatus = '1' where so_no = '$sono' and branch = '$bid';");
			} else { $con->dbquery("update ignore so_header set cstatus = '10' where so_no = '$sono' and branch = '$bid';"); }

			/* Remove from GL */
			$con->dbquery("delete from acctg_gl where doc_no = '$_POST[doc_no]' and doc_type = 'OR' and branch = '1';");
		break;

		case "cancel":
			$con->dbquery("update or_header set `status` = 'Cancelled', updated_by = '$uid', updated_on = now() where doc_no = '$_POST[doc_no]' and branch = '$bid';");
			list($sono,$terms) = $con->getArray("select so_no, terms from or_header where doc_no = '$_POST[doc_no]' and branch = '$bid';");
			$con->dbquery("update ignore so_header set cstatus = '11' where so_no = '$sono' and branch = '$bid';");
		break;

		case "reuse":
			$con->dbquery("update or_header set `status` = 'Active', updated_by = '$uid', updated_on = now() where doc_no = '$_POST[doc_no]' and branch = '$bid';");
			list($sono,$terms) = $con->getArray("select so_no, terms from or_header where doc_no = '$_POST[doc_no]' and branch = '$bid';");
			$con->dbquery("update ignore so_header set cstatus = '1' where so_no = '$sono' and branch = '$bid';");
		break;

	}

?>