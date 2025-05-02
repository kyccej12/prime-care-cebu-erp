<?php
	session_start();
	ini_set("max_execution_time",0);
	ini_set("memory_limit",-1);
	
	//ini_set("display_errors","On");
	require_once "../lib/mpdf6/mpdf.php";
	require_once "../handlers/_generics.php";
	
	$mydb = new _init;
	
	
	$mpdf=new mPDF('win-1252','folio-l','','',10,10,32,25,10,10);
	$mpdf->use_embeddedfonts_1252 = true;    // false is default
	$mpdf->SetProtection(array('print'));
	$mpdf->SetAuthor("PORT80 Business Solutions");
	$mpdf->SetDisplayMode(75);
	
	$co = $mydb->getArray("select * from companies where company_id = '$_SESSION[company]';");
	
	/* MYSQL QUERIES SECTION */
		$now = date("m/d/Y h:i a");
		$dtf = $mydb->formatDate($_GET['dtf']);
		$dt2 = $mydb->formatDate($_GET['dt2']);
		$searchString = '';

		if($_GET['customer'] != '') { $searchString .= " and a.customer_code = '" .  substr($_GET['customer'],1,6) . "' ";  }
		if($_GET['uid'] != '') { $f1 = " and a.created_by = '$_GET[uid]' "; } else { $f1 = ''; } 
		if($_GET['code'] != '' or $_GET['idesc'] != '') {
			$searchString .= " and (b.code = '$_GET[code]' or b.description like '%$_GET[desc]') ";
		}
	
		$query = $mydb->dbquery("SELECT * FROM (SELECT 'so' AS `type`, a.so_no, DATE_FORMAT(a.so_date,'%m/%d/%Y') AS sodate,'' AS or_no, '' AS ordate, a.customer_code, a.customer_name, a.patient_id, a.patient_name, c.description AS xterms, b.code,b.description, b.amount_due, a.amount AS charge_sales, '0' AS cash_sales, '0' AS cc_sales, '0' AS check_sales, '0' AS ad_payment FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN options_terms c ON a.terms = c.terms_id LEFT JOIN services_master d ON b.code = d.code WHERE a.status = 'Finalized' AND a.terms NOT IN ('0','100') AND a.so_date BETWEEN '$dtf' AND '$dt2' $searchString UNION ALL SELECT 'ap' AS `type`, a.so_no, DATE_FORMAT(a.so_date,'%m/%d/%Y') AS sodate,'' AS or_no, '' AS ordate, a.customer_code, a.customer_name, a.patient_id, a.patient_name, c.description AS xterms, b.code,b.description, b.amount_due, '0' AS charge_sales, '0' AS cash_sales, '0' AS cc_sales, '0' AS check_sales, a.amount AS ad_payment FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN options_terms c ON a.terms = c.terms_id WHERE a.status = 'Finalized' AND a.terms IN ('100') AND a.so_date BETWEEN '$dtf' AND '$dt2' $searchString UNION ALL SELECT 'or' AS `type`, b.so_no, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS so_date, a.or_no AS or_no, DATE_FORMAT(a.doc_date,'%m/%d/%Y') AS ordate, a.customer_code, a.customer_name, b.pid AS patient_id, b.pname AS patient_name, 'Cash' AS terms, b.code, b.description, b.amount_due, 0 AS charge_sales, a.amount_due AS cash_sales, '0' AS cc_sales, '0' AS check_sales, '0' AS ad_payment FROM or_header a LEFT JOIN or_details b ON a.trace_no = b.trace_no WHERE a.status = 'Finalized' AND a.doc_date BETWEEN '$dtf' AND '$dt2' AND a.cashtype IN ('1') $searchString UNION ALL SELECT 'cc' AS `type`, b.so_no, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS so_date, a.doc_no AS or_no, DATE_FORMAT(a.doc_date,'%m/%d/%Y') AS ordate, a.customer_code, a.customer_name, b.pid AS patient_id, b.pname AS patient_name, 'Cash' AS terms, b.code, b.description, b.amount_due, 0 AS charge_sales, '0' AS cash_sales, ROUND(a.gross-a.discount-a.sc_discount,2) AS cc_sales, '0' AS check_sales, '0' AS ad_payment FROM or_header a LEFT JOIN or_details b ON a.trace_no = b.trace_no WHERE a.status = 'Finalized' AND a.doc_date BETWEEN '$dtf' AND '$dt2' AND a.cashtype IN ('2') $searchString UNION ALL SELECT 'ck' AS `type`, b.so_no, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS so_date, a.doc_no AS or_no, DATE_FORMAT(a.doc_date,'%m/%d/%Y') AS ordate, a.customer_code, a.customer_name, b.pid AS patient_id, b.pname AS patient_name, 'Cash' AS terms, b.code, b.description, b.amount_due, 0 AS charge_sales, '0' AS cash_sales, '0' AS cc_sales, a.amount_due AS check_sales, '0' AS ad_payment FROM or_header a LEFT JOIN or_details b ON a.trace_no = b.trace_no WHERE a.status = 'Finalized' AND a.doc_date BETWEEN '$dtf' AND '$dt2' AND a.cashtype IN ('3') $searchString) a ORDER BY or_no, so_no;");

	
	/* END OF SQL QUERIES */

$html = '
<html>
<head>
<style>
body {
	font-family: calibri;
    font-size: 8pt;
}
p {    margin: 0pt;
}
td { vertical-align: top; }

table thead td {
	border: 0.1mm solid #000000;
	background-color: #cdcdcd;
}

.items td {
    border: 0.1mm solid #000000;
}

</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%">
	<tr>
		<td width=75><img src="../images/logo-small.png" width=64 height=64 align=absmiddle></td>
		<td style="color:#000000; padding-top: 15px;">
			<b>'.$co['company_name'].'</b><br/><span style="font-size: 6pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'<br/>VAT REG. TIN: '.$co['tin_no'].'</span>
		</td>
		<td width="40%" align=right>
			<span style="font-weight: bold; font-size: 8pt; color: #000000;">DETAILED SALES REPORT</span><br/>Date Covered ' . $_GET['dtf'] . ' - ' . $_GET['dt2'] .'</span>
		</td>
	</tr>
</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
<table style="border-top: 1px solid #000000; font-size: 7pt; width: 100%">
<tr>
<td width="50%" align="left">Page {PAGENO} of {nb}</td>
<td width="50%" align="right" style="font-size:7pt; font-color: #cdcdcd;">Run Date: ' . $now . '</td>
</tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<table class="items" width="100%" align=center style="font-size: 7pt; border-collapse: collapse;" cellpadding="3">
	<thead>
		<tr>
			<td align=center><b>SO #</b></td>
			<td align=center><b>SO DATE</b></td>
			<td align=center><b>OR #</b></td>
			<td align=center><b>OR DATE</b></td>
			<td align=center><b>BILLED TO</b></td>
			<td align=center><b>PATIENT NAME</b></td>
			<td align=center><b>EMPLOYER</b></td>
			<td align=center><b>TERMS</b></td>
			<td align=center><b>DESCRIPTION</b></td>
			<td align=center><b>AMOUNT</b></td>
			<td align=center><b>CHARGE SALES</b></td>
			<td align=center><b>CASH SALES</b></td>
			<td align=center><b>CARD SALES</b></td>
			<td align=center><b>CHECK SALES</b></td>
			<td align=center><b>ADV. PAYMENT</b></td>
		</tr>
	</thead>
<tbody>';

$cashGT = 0; $chargeGT = 0; $ccGT = 0; $checkGT= 0; $adPGT = 0; $i = 1;
while($row = $query->fetch_array()) {
	// list($employer) = $mydb->getArray("select employer from patient_info where patient_id = '$row[patient_id]';");
	// if($row['customer_code'] != 0) { $billedto = $row['customer_name']; } else { $billedto = 'PATIENT'; }

	// if($row['type'] == 'so') {
	// 	if($row['so_no'] != $xso) {
	// 		$charge = number_format($row['charge_sales'],2); $cash = ''; $cc = ''; $chargeGT += $row['charge_sales']; $check = '';
	// 	}
	// } else {
	// 	if($xor != $row['or_no']) {
	// 		$charge = '';
	// 		if($row['cash_sales'] > 0) { $cash = number_format($row['cash_sales'],2); $cashGT+=$row['cash_sales']; } else { $cash = ''; }
	// 		if($row['cc_sales'] > 0) { $cc = number_format($row['cc_sales'],2); $ccGT+=$row['cc_sales']; } else { $cc = ''; }
	// 		if($row['check_sales'] > 0) { $check = number_format($row['check_sales'],2); $checkGT+=$row['check_sales']; } else { $check = ''; }
	// 	} else {
	// 		$charge = ''; $cash = ''; $cc = ''; $check = '';
	// 	}

	// }

	list($employer) = $mydb->getArray("select employer from patient_info where patient_id = '$row[patient_id]';");
		$charge = ''; $cash  = ''; $cc = ''; $check = ''; $ap = '';

		switch($row['type']) {
			case "so": if($row['so_no'] != $xso) {	$charge = $row['charge_sales']; $cash = ''; $cc = ''; $check = ''; $chargeGT += $row['charge_sales']; }	break;
			case "or": if($row['or_no'] != $xor) {	$cash = $row['cash_sales']; $charge = ''; $cc = ''; $cc = ''; $cashGT += $row['cash_sales'];	} break;
			case "cc":	if($row['or_no'] != $xor) {	$cc = $row['cc_sales']; $charge = ''; $cash = ''; $ap = ''; $ccGT += $row['cc_sales']; }	break;
			case "ck":	if($row['or_no'] != $xor) {	$check = $row['check_sales']; $charge = ''; $cash = ''; $ap = ''; $checkGT += $row['check_sales']; }	break;
			case "ap":	if($row['so_no'] != $xso) {	$ap = $row['ad_payment']; $charge = ''; $cash = ''; $cc = ''; $check = ''; $adPGT += $row['ad_payment']; }	break;
		}

		if($row['customer_code'] != 0) { $billedto = html_entity_decode($row['customer_name']); } else { $billedto = 'PATIENT'; }

	$html = $html . '<tr bgcolor="'.$mydb->initBackground($i).'">
		<td align=center>' . $row['so_no'] . '</td>
		<td align=center>' . $row['sodate'] . '</td>
		<td align=center>' . $row['or_no'] . '</td>
		<td align=center>' . $row['ordate'] . '</td>
		<td align=left width=15%>' . $billedto . '</td>
		<td align=left width=15%>'. $row['patient_name'] .'</td>
		<td align=left>'.  $employer . '</td>
		<td align=center>'. $row['xterms'] .'</td>
		<td align=left width=15%>'. $row['description'] .'</td>
		<td align=right>' . number_format($row['amount_due'],2) . '</td>
		<td align=right>' . number_format($charge,2) . '</td>
		<td align=right>' . number_format($cash,2) . '</td>
		<td align=right>' . number_format($cc,2) . '</td>
		<td align=right>' . number_format($check,2) . '</td>
		<td align=right>' . number_format($ap,2) . '</td>
	</tr>'; $xso = $row['so_no']; $xor = $row['or_no']; $i++;
}

$html = $html . '<tr bgcolor="'.$mydb->initBackground($i).'">
					<td colspan=10 align=left><b>GRAND TOTAL</b></td>
					<td align=right><b>' . number_format($chargeGT,2) . '</b></td>
					<td align=right><b>' .number_format($cashGT,2) . '</b></td>
					<td align=right><b>' . number_format($ccGT,2) . '</b></td>
					<td align=right><b>' . number_format($checkGT,2) . '</b></td>
					<td align=right><b>' . number_format($adPGT,2) . '</b></td>
			     </tr>';
$html = $html . '</tbody></table>
</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;
?>