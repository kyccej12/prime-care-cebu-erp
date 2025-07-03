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
		
		$query = $mydb->dbquery("SELECT 'so' AS `type`, a.so_no, DATE_FORMAT(a.so_date,'%m/%d/%Y') AS sodate,'' AS or_no, '' AS ordate, a.customer_code, a.customer_name, a.patient_id, a.patient_name, a.patient_address, c.description AS xterms, b.code,b.description, a.amount, '0' AS charge_sales, '0' AS cash_sales, '0' AS cc_sales, '0' AS check_sales FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN options_terms c ON a.terms = c.terms_id WHERE a.status = 'Finalized' AND a.so_date BETWEEN '$dtf' AND '$dt2' $searchString GROUP BY patient_id ORDER BY so_no;");

	
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
			<span style="font-weight: bold; font-size: 8pt; color: #000000;">DETAILED DAILY CENSUS REPORT</span><br/>Date Covered ' . $_GET['dtf'] . ' - ' . $_GET['dt2'] .'</span>
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
			<td align=center><b>BILLED TO</b></td>
			<td align=center><b>PATIENT NAME</b></td>
			<td align=center><b>PATIENT ADDR</b></td>
			<td align=center><b>CONTACT#</b></td>
			<td align=center><b>EMPLOYER</b></td>
			<td align=center><b>TERMS</b></td>
			<td  align=center><b>CODE</b></td>
			<td align=center><b>DESCRIPTION</b></td>
			<td align=center><b>AMOUNT</b></td>
		</tr>
	</thead>
<tbody>';

$cashGT = 0; $i = 1;
while($row = $query->fetch_array()) {


	list($employer) = $mydb->getArray("select employer from patient_info where patient_id = '$row[patient_id]';");
	list($contactnum) = $mydb->getArray("select mobile_no from patient_info where patient_id = '$row[patient_id]';");
	if($row['customer_code'] != 0) { $billedto = $row['customer_name']; } else { $billedto = 'PATIENT'; }

	if($row['type'] == 'so') {
		if($row['so_no'] != $xso) {
			$charge = number_format($row['amount'],2); $cashGT += $row['amount'];
		}
	}

	$html = $html . '<tr bgcolor="'.$mydb->initBackground($i).'">
		<td align=center>' . $row['so_no'] . '</td>
		<td align=center>' . $row['sodate'] . '</td>
		<td align=left width=15%>' . $billedto . '</td>
		<td align=left width=15%>'. $row['patient_name'] .'</td>
		<td align=left>'. $row['patient_address'] .'</td>
		<td align=left>'. $contactnum .'</td>
		<td align=left>'.  $employer . '</td>
		<td align=center>'. $row['xterms'] .'</td>
		<td align=center>'. $row['code'] .'</td>
		<td align=left width=15%>'. $row['description'] .'</td>
		<td align=right>' . number_format($row['amount'],2) . '</td>
	</tr>'; $xso = $row['so_no']; $i++;
}

$html = $html . '<tr bgcolor="'.$mydb->initBackground($i).'">
					<td colspan=10 align=left><b>GRAND TOTAL</b></td>
					<td align=right><b>' .number_format($cashGT,2) . '</b></td>
			     </tr>';
$html = $html . '</tbody></table>
</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;
?>