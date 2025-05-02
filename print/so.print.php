<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");
	//$_ihead = $con->getArray("SELECT *, LPAD(so_no,6,0) AS sono, DATE_FORMAT(so_date,'%m/%d/%Y') AS d8, DATE_FORMAT(created_on,'%m/%d/%Y %h:%i %p') as dateCreated, IF(loa_date!='0000-00-00',DATE_FORMAT(loa_date,'%m/%d/%Y'),'') AS load8, IF(hmo_card_expiry!='0000-00-00',DATE_FORMAT(hmo_card_expiry,'%m/%d/%Y'),'') AS exd8, LPAD(patient_id,6,'0') AS pid, IF(customer_code!=0,CONCAT('[',LPAD(customer_code,6,'0'),'] ',customer_name),CONCAT(patient_name,' (Patient)')) AS cname, IF(customer_code!=0,customer_address,patient_address) AS customer_address, b.description AS terms_desc, CONCAT(UCASE(RIGHT(trace_no,5)),LPAD(so_no,8,'0')) AS barcode FROM so_header a LEFT JOIN options_terms b ON a.terms = b.terms_id where so_no = '$_REQUEST[so_no]' and branch = '$_SESSION[branchid]';");
	$_ihead = $con->getArray("SELECT *, LPAD(so_no,6,0) AS sono, FLOOR(DATEDIFF(a.so_date,c.birthdate)/365.25) AS age, DATE_FORMAT(so_date,'%m/%d/%Y') AS d8, DATE_FORMAT(a.created_on,'%m/%d/%Y %h:%i %p') AS dateCreated, a.created_by, IF(loa_date!='0000-00-00',DATE_FORMAT(loa_date,'%m/%d/%Y'),'') AS load8, IF(hmo_card_expiry!='0000-00-00',DATE_FORMAT(hmo_card_expiry,'%m/%d/%Y'),'') AS exd8, LPAD(a.patient_id,6,'0') AS pid, IF(customer_code!=0,CONCAT('[',LPAD(customer_code,6,'0'),'] ',customer_name),CONCAT(patient_name,' (Patient)')) AS cname, IF(customer_code!=0,customer_address,patient_address) AS customer_address, b.description AS terms_desc, CONCAT(UCASE(RIGHT(trace_no,5)),LPAD(so_no,8,'0')) AS barcode FROM so_header a LEFT JOIN options_terms b ON a.terms = b.terms_id LEFT JOIN patient_info c ON a.patient_id = c.patient_id  WHERE so_no = '$_REQUEST[so_no]' and branch = '$_SESSION[branchid]';");
	$_idetails = $con->dbquery("SELECT `code`,description as particulars,qty,ROUND(amount/qty,2) as unit_price, amount, discount, amount_due FROM so_details WHERE so_no = '$_REQUEST[so_no]' AND branch = '$_SESSION[branchid]';");
	$bcode = $_ihead['trace_no'];
	
	list($nos,$stin,$isVat) = $con->getArray("select tel_no, tin_no, vatable from contact_info where file_id = '$_ihead[customer_code]';");
	$_p = $con->getArray("SELECT DATE_FORMAT(birthdate,'%m/%d/%Y') AS bday, birthdate, IF(gender='M','Male','Female') AS gender, b.civil_status, mobile_no, email_add, a.pwd, a.employer FROM patient_info a LEFT JOIN pccpayroll.options_civilstatus b ON a.cstat = b.csid WHERE a.patient_id = '$_ihead[patient_id]';");
	
	$age = $con->calculateAge($_p['birthdate']);
	//list($lvisit) = $con->getArray("select date_format(so_date,'%m/%d/%Y') from so_header where patient_id = '$_ihead[patient_id]' and status = 'Finalized' and so_date < '$_ihead[so_date]' order by so_date desc limit 1;");
	list($pstat) = $con->getArray("select patientstatus from options_patientstat where id = '$_ihead[patient_stat]';");
	list($dRows) = $con->getArray("select count(*) from so_details WHERE so_no = '$_REQUEST[so_no]' AND branch = '$_SESSION[branchid]';");


	if($_ihead['referred_by'] != '') { $comp = $_ihead['referred_by']; } else { $comp = $_ihead['employer']; }
	
	/* Change to Short Bond when no. of entries exceeds 4 */
	if($dRows > 4) { $paper = "letter"; } else { $paper = "LETTER-H"; }
	/* AUDIT TRAIL PURPOSES */
			
	/* Summary of Charges */
	/*if($isVat == 'Y' || $isVat == '') {
		list($scdiscount) = $con->getArray("select sum(discount) from so_details where so_no = '$_REQUEST[so_no]' and branch = '$_SESSION[branchid]' and disctype in ('SC','PWD');");
		
		if($scdiscount > 0) { 
			$gross = $_ihead['amount'] + $scdiscount;	
		} else {
			$gross = $_ihead['amount'];
		}

		$amountDue = $_ihead['amount'];
		$vatable = ROUND($gross/1.12,2);
		$vat = ROUND($vatable * 0.12,2);

	} else { */
		list($scdiscount) = $con->getArray("select sum(discount) from so_details where so_no = '$_REQUEST[so_no]' and branch = '$_SESSION[branchid]' and disctype in ('SC','PWD');");
		$gross = $_ihead['amount'] + $scdiscount;
		$amountDue = $_ihead['amount'];
		$vatable = 0;
		$vat = 0;
		$scdiscount = 0;
	
	//}
	
/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252',$paper,'','',5,5,85,30,2,2);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
//$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("PORT80 Solutions");

if($_REQUEST['rePrint'] == 'Y') {
	$mpdf->SetWatermarkText('Reprinted Copy');
	$mpdf->showWatermarkText = true;
}

$mpdf->SetDisplayMode(50);

$html = '
<html>
<head>
<style>
body {font-family: sans-serif; font-size: 10px; }
td { vertical-align: top; }

table thead td { 
	border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
	/* background-color: #EEEEEE; */
    text-align: center;
}

.td-l { border-left: 0.1mm solid #000000; }
.td-r { border-right: 0.1mm solid #000000; }
.empty { border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; }

.items td.blanktotal {
    /* background-color: #FFFFFF; */
    border: 0.1mm solid #000000;
}
.items td.totals-l-top {
    text-align: right; font-weight: bold;
    border-left: 0.1mm solid #000000;
	border-top: 0.1mm solid #000000;
}
.items td.totals-r-top {
    text-align: right; font-weight: bold;
    border-right: 0.1mm solid #000000;
	border-top: 0.1mm solid #000000;
}
.items td.totals-l {
    text-align: right; font-weight: bold;
    border-left: 0.1mm solid #000000;
}
.items td.totals-r {
    text-align: right; font-weight: bold;
    border-right: 0.1mm solid #000000;
}

.items td.tdTotals-l {
    text-align: left; font-weight: bold;
    border-left: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;  /* background-color: #EEEEEE; */
}
.items td.tdTotals-r {
    text-align: right; font-weight: bold;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000; /* background-color: #EEEEEE; */
}

.items td.tdTotals-l-1 {
    text-align: left;
    border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
}
.items td.tdTotals-r-1 {
    text-align: right;
    border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
}

.td-l-top { 	
		/* background-color: #EEEEEE; */ padding: 3px;
		text-align: left; font-weight: bold;
		border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000;
		border-top: 0.1mm solid #000000;
	}
.td-r-top { 
	text-align: right; font-weight: bold; padding: 3px;
    border-right: 0.1mm solid #000000;
	border-top: 0.1mm solid #000000;
}

.td-l-head {
	text-align: left; font-weight: bold; padding: 3px;
    border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; /* background-color: #EEEEEE; */
}

.td-r-head {
	text-align: right; font-weight: bold; padding: 3px;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000;
}
.td-l-head-bottom {
	text-align: left; font-weight: bold; padding: 3px;
    border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; /* background-color: #EEEEEE; */ border-bottom: 0.1mm solid #000000;
}

.td-r-head-bottom {
	text-align: right; font-weight: bold; padding: 3px;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
}

.billto {
	vertical-align: top; padding: 3px;
}
</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellspaing=0>
	<tr>
		<td width=75><img src="../images/logo-small.png" width=64 height=64 align=absmiddle></td>
		<td style="color:#000000; padding-top: 5px;" valign=top>
			<span style="font-size: 9pt;"><b>'.$co['company_name'].'</b><br/>'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'</span>
		</td>
		<td width="40%" align=right>
			<span style="font-weight: bold; font-size: 11pt; color: #000000;">SERVICE ORDER&nbsp;&nbsp;</span><br />
			<barcode size=0.8 code="'.$_ihead['barcode'] .'" type="C128A">
		</td>
	</tr>
</table>
<table width="100%" cellspacing=0 cellpadding=0 style="font-size: 9pt;">
	<tr>
		<td class="billto" width=60% rowspan="5">
		<b><br/>BILL TO :</b><br /><br /><b>'.$_ihead['cname'].'</b><br /><i>'.$_ihead['customer_address'].'<br/>'.$nos.'<br/></i></td>
		<td class="td-l-top"><b>Priority No.</b></td>
		<td class="td-r-top"><b>'.str_pad($_ihead['priority_no'],4,'0',STR_PAD_LEFT).'</b></td>
	</tr>
	<tr>
		<td class="td-l-head"><b>SO No.</b></td>
		<td class="td-r-head"><b>' . $_ihead['sono'] . '</b></td>
	</tr>

	<tr>
		<td class="td-l-head"><b>S.O Date</b></td>
		<td class="td-r-head"><b>' . $_ihead['d8'] . '</b></td>
	</tr>
	<tr>
		<td class="td-l-head"><b>Terms</b></td>
		<td class="td-r-head"><b>' . $_ihead['terms_desc'] . '</b></td>
	</tr>
	<tr>
		<td class="td-l-head-bottom"><b>Amount Due</b></td>
		<td class="td-r-head-bottom"><b>&#8369;' . number_format($_ihead['amount'],2) . '</b></td>
	</tr>
</table>
<table width=100% cellpadding=0 cellspacing=0 style="font-size: 9pt; margin-top:5px;">
	<tr>
		<td width=100% colspan=4 style="border-top: 1px solid black; border-bottom: 1px solid black;" align=center><b>PATIENT INFORMATION</b></td>
	</tr>
	<tr>
		<td width=20%><b>PATIENT ID</b></td>
		<td width=45%>:&nbsp;&nbsp;'.$_ihead['pid'].'</td>
		<td width=15%><b>GENDER</b></td>
		<td width=20%>:&nbsp;&nbsp;'.$_p['gender'].'</td>
	</tr>
	<tr>
		<td><b>PATIENT NAME</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['patient_name'].'</td>
		<td><b>BIRTHDATE</b></td>
		<td>:&nbsp;&nbsp;'.$_p['bday'].'</td>
	</tr>
	<tr>
		<td><b>PATIENT ADDRESS</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['patient_address'].'</td>
		<td><b>AGE</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['age'].'</td>
	</tr>
	<tr>
		<td><b>MOBILE NO.</b></td>
		<td>:&nbsp;&nbsp;'.$_p['mobile_no'].'</td>
		<td><b>CIVIL STATUS</b></td>
		<td>:&nbsp;&nbsp;'.$_p['civil_status'].'</td>
	</tr>
	<tr>
		<td><b>EMAIL ADDRESS</b></td>
		<td>:&nbsp;&nbsp;'.$_p['email_add'].'</td>
		<td><b>SC/PWD ID</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['scpwd_id'].'</td>
	</tr>
	<tr>
		<td><b>REQUESTING PHYSICIAN</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['physician'].'</td>
		<td><b>HMO CARD NO.</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['hmo_card_no'].'</td>
	</tr>
	<tr>
		<td><b>COMPANY/REFERRED BY</b></td>
		<td>:&nbsp;&nbsp;'.$comp.'</td>
		<td><b>PATIENT STATUS</b></td>
		<td>:&nbsp;&nbsp;'.$pstat.'</td>
	</tr>
	<tr>
		<td><b>RESULT DELIVERY</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['delivery_type'].'</td>
		<td></td>
		<td></td>
	</tr>
</table>

</htmlpageheader>

<htmlpagefooter name="myfooter">
<table width=100% cellpadding=5 style="border: 1px solid #000000; font-size: 8pt;">
	<tr>
		<td width=33% align=center><b>PREPARED BY:</b><br><br>'.$con->getUname($_ihead['created_by']).'<br/></td>
		<td align=center><b>ACKNOWLEDGED BY:</b><br><br>_______________________<br><font size=2>Signature Over Printed Name</font></td>
		<td width=33%  align=center><b>VERFIED BY:</b><br><br>_______________________<br><font size=2>Signature Over Printed Name</font></td>
	</tr>
</table>
<table width=100%>
	<tr><td align=left>Page {PAGENO} of {nb}</td><td align=center style="font-size:6.5pt;"><i>I acknowledge that I was duly informed by Prime Care Alpha employee to pay the above mentioned tests, I have reviewed <br /> the prices listed on the (SO) and agree to the changes associated with the products and services.<i/></td><td align=right>Date Created: '.$_ihead['dateCreated'].'</td></tr>
	<tr><td colspan=3 align=center><b> **** THIS DOCUMENT IS NOT VALID FOR INPUT TAX CLAIM ****</b></td></tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<table width=100% cellpadding=0 >
	<tr>
		<td width=70%>
			<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="1">
				<thead>
					<tr>';

					if($_ihead['discount'] > 0) {
						$html .= '<td width="8%" align=left><b>CODE</b></td>
								  <td width="39%" align=left><b>PARTICULARS</b></td>
								  <td width="8%" align=center><b>QTY</b></td>
								  <td width="10%" align=center><b>PRICE</b></td>
								  <td width="10%" align=right><b>AMOUNT</b></td>
								  <td width="10%" align=right><b>DISC.</b></td>
								  <td width="15%" align=right><b>AMT DUE</b></td>
								  ';
					} else {

						$html .= '<td width="10%" align=left><b>CODE</b></td>
								  <td width="50%" align=left><b>PARTICULARS/PROCEDURE</b></td>
								  <td width="10%" align=center><b>QTY</b></td>
								  <td width="15%" align=center><b>UNIT PRICE</b></td>
								  <td width="15%" align=right><b>AMOUNT</b></td>';
					}
					$html .= '</tr>
				</thead>
				<tbody>';
					$i = 0;
					while($row = $_idetails->fetch_array()) {


						list($cat) = $con->getArray("select category from services_master where `code` = '$row[code]';");
						if($cat == 6) { list($subdescription) = $con->getArray("select concat('<br/>&raquo;',fulldescription) as subdescription from services_master where `code` = '$row[code]';"); } else { $subdescription = ''; }

						if($_ihead['discount'] > 0) {
							$html.= '<tr>
									<td align=left>'.$row['code'].'</td>
									<td align=left>' . $row['particulars'] . $subdescription . '</td>
									<td align="center">' . number_format($row['qty'],2) . '</td>
									<td align="center">' . number_format($row['unit_price'],2) . '</td>
									<td align="right">' . number_format($row['amount'],2) . '</td>
									<td align="right">' . number_format($row['discount'],2) . '</td>
									<td align="right">' . number_format($row['amount_due'],2) . '</td>
									</tr>'; $i++; $amtGT+=$row['amount_due'];

						} else {
							$html.= '<tr>
									<td align=left>'.$row['code'].'</td>
									<td align=left>' . $row['particulars'] . $subdescription . '</td>
									<td align="center">' . number_format($row['qty'],2) . '</td>
									<td align="center">' . number_format($row['unit_price'],2) . '</td>
									<td align="right">' . number_format($row['amount_due'],2) . '</td>
									</tr>'; $i++; $amtGT+=$row['amount_due'];
						
						}
					}
					$html = $html .  '
				</tbody>
			</table>
		</td>
		<td width=30%>
			<table width=100% style="font-size: 9pt; border-collapse: collapse; margin-left: 15px;" cellpadding="1">
				<tr>
					<td colspan=3 style="border-top: 1px solid black; border-bottom: 1px solid black; padding: 1px;" align=center><b>SUMMARY OF CHARGES</b></td>
				</tr>
				<tr>
					<td width=60%><b>TOTAL SALES</b></td>
					<td width=5%>:</td>
					<td align=right>'.number_format($gross,2).'</td>
				</tr>
				<tr>
					<td width=60%><b>VATABLE SALES</b></td>
					<td width=5%>:</td>
					<td align=right>'.number_format($vatable,2).'</td>
				</tr>
				<tr>
					<td width=60%><b>V-A-T</b></td>
					<td width=5%>:</td>
					<td align=right>'.number_format($vat,2).'</td>
				</tr>
				<tr>
					<td width=60%><b>SC/PWD DISCOUNT</b></td>
					<td width=5%>:</td>
					<td align=right>'.number_format($scdiscount,2).'</td>
				</tr>
				<tr>
					<td width=60%><b>AMOUNT DUE</b></td>
					<td width=5%>:</td>
					<td align=right>'.number_format($amountDue,2).'</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>
';

$html = html_entity_decode($html);
$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;

mysql_close($con);
?>