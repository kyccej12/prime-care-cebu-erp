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
		if($_GET['uid'] != '') { $f1 = " and a.created_by = '$_GET[uid]' "; $lbl = "Cashier: " . $mydb->getUname($_GET['uid']); } else { $f1 = ''; $lbl = 'All Cashiers'; } 


		$query = $mydb->dbquery("SELECT a.doc_no, a.or_no, DATE_FORMAT(a.doc_date,'%m/%d/%Y') AS ordate, b.so_no, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, IF(a.customer_code=0,'PATIENT',a.customer_name) AS billedto, b.pname, c.employer, b.code, b.description, b.qty, b.amount, b.discount, b.amount_due, a.amount_due-cc_tendered-check_tendered AS cashsales, cc_tendered AS cardsales, check_tendered-ewt AS checksales FROM or_header a LEFT JOIN or_details b ON a.doc_no = b.doc_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.pid = c.patient_id WHERE a.status = 'Finalized' AND a.doc_date BETWEEN '$dtf' AND '$dt2' $f1 $searchString ORDER BY a.or_no asc;");
	
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
			<span style="font-weight: bold; font-size: 8pt; color: #000000;">CASH RECEIPTS SUMMARY</span><br/>Date Covered ' . $_GET['dtf'] . ' - ' . $_GET['dt2'] .'<br><b>'. $lbl .'</b></span>
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
			<td align=center width=5%><b>REF #</b></td>
			<td align=center width=6%><b>OR #</b></td>
			<td align=center width=6%><b>OR DATE</b></td>
			<td align=center width=5%><b>SO #</b></td>
			<td align=center><b>SO DATE</b></td>
			<td align=center><b>BILLED TO</b></td>
			<td align=center width=12%><b>PATIENT NAME</b></td>
			<td  align=center><b>CODE</b></td>
			<td align=center><b>DESCRIPTION</b></td>
			<td align=center><b>AMOUNT</b></td>
			<td align=center><b>DISCOUNT</b></td>
			<td align=center><b>AMOUNT DUE</b></td>
			<td align=center><b>CASH SALES</b></td>
			<td align=center><b>CARD SALES</b></td>
			<td align=center><b>CHECK PAYMENTS</b></td>
		</tr>
	</thead>
<tbody>';

$cashGT = 0; $cardGT = 0; $checkGT = 0; $i = 1;
while($row = $query->fetch_array()) {
	
	if($xdoc != $row['doc_no']) {
		if($row['cashsales'] > 0) { $csales = number_format($row['cashsales'],2); $cashGT+=$row['cashsales']; } else { $csales = ''; }
		if($row['cardsales'] > 0) { $ccsales = number_format($row['cardsales'],2); $cardGT+=$row['cardsales']; } else { $ccsales = ''; }
		if($row['checksales'] > 0) { $cksales = number_format($row['checksales'],2); $ckGT+=$row['checksales']; } else { $cksales = ''; }
	} else {
		$csales = ''; $ccsales = ''; $cksales = '';
	}



	$html = $html . '<tr bgcolor="'.$mydb->initBackground($i).'">
		<td align=center>' . $row['doc_no'] . '</td>
		<td align=center>' . $row['or_no'] . '</td>
		<td align=center>' . $row['ordate'] . '</td>
		<td align=center>' . $row['so_no'] . '</td>
		<td align=center>' . $row['sodate'] . '</td>
		<td align=left>' . $row['billedto'] . '</td>
		<td align=left>'. $row['pname'] .'</td>
		<td align=center>'. $row['code'] .'</td>
		<td align=left>'. $row['description'] .'</td>
		<td align=right>' . number_format($row['amount'],2) . '</td>
		<td align=right>' . number_format($row['discount'],2) . '</td>
		<td align=right>' . number_format($row['amount_due'],2) . '</td>
		<td align=right>' .$csales . '</td>
		<td align=right>' . $ccsales . '</td>
		<td align=right>' . $cksales . '</td>
	</tr>'; $xdoc = $row['doc_no']; $i++;
}

$html = $html . '<tr bgcolor="'.$mydb->initBackground($i).'">
					<td colspan=12 align=left><b>GRAND TOTAL</b></td>
					<td align=right><b>' .number_format($cashGT,2) . '</b></td>
					<td align=right><b>' . number_format($cardGT,2) . '</b></td>
					<td align=right><b>' . number_format($ckGT,2) . '</b></td>
			     </tr>';
$html = $html . '</tbody></table>
</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); 
exit;
?>