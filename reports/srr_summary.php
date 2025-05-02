<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");
	ini_set("memory_limit","1024M");
	ini_set("max_execution_time","0");

	$con = new _init;

	$mpdf=new mPDF('win-1252','folio','','',10,10,32,20,10,10);
	$mpdf->use_embeddedfonts_1252 = true;    // false is default
	$mpdf->SetProtection(array('print'));
	$mpdf->SetAuthor("PORT80 Business Solutions");
	$mpdf->SetDisplayMode(75);

	/* MYSQL QUERIES SECTION */
		$now = date("m/d/Y h:i a");
		$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");
		$searchString = '';

		if($_GET['item'] != '') { $searchString .= " and (b.item_code like '%$_GET[item]%' or b.description like '%$_GET[item]%') "; }

		$query = $con->dbquery("SELECT a.srr_no AS doc_no, DATE_FORMAT(srr_date,'%m/%d/%y') AS doc_date, a.remarks, b.item_code, b.description, b.unit, b.qty, b.cost, ROUND(qty*cost,2) as amount FROM srr_header a LEFT JOIN srr_details b ON a.srr_no = b.srr_no AND a.branch = b.branch WHERE a.branch = '$_SESSION[branchid]' AND a.srr_date BETWEEN '".$con->formatDate($_GET['dtf'])."' AND '".$con->formatDate($_GET['dt2'])."' AND a.status = 'Finalized' $searchString ORDER BY a.srr_date ASC, a.srr_no ASC;");
	/* END OF SQL QUERIES */

$html = '
<html>
<head>
<style>
body {font-family: sans-serif;
    font-size: 8pt;
}
p {    margin: 0pt;
}
td { vertical-align: top; }

table thead td {
    text-align: center;
    border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
}

.lowerHeader {
    text-align: center;
    border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
}

.items td.blanktotal {
    background-color: #FFFFFF;
    border: 0mm none #000000;
    border-top: 0.1mm solid #000000;
    border-right: 0.1mm solid #000000;
}

.items td.totals {
    text-align: right;
    border: 0.1mm solid #000000;
}

.items td.lowertotals {
	border: 0mm none #000000;
    border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
}

</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%">
	<tr>
		<td style="color:#000000; padding-top: 15px;">
			<b>'.$co['company_name'].'</b><br/><span style="font-size: 6pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'<br/>VAT REG. TIN: '.$co['tin_no'].'</span>
		</td>
		<td width="40%" align=right>
			<span style="font-weight: bold; font-size: 8pt; color: #000000;"><br/>Summary of Goods Returned/Received</span><br /><span style="font-size: 6pt; font-style: italic;">Covered Period : ' . $_GET['dtf'] . ' - ' . $_GET['dt2'] .'</span>
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
<table class="items" width="100%" align=center style="font-size: 7pt; border-collapse: collapse;" cellpadding="1">
<thead>
	<tr>
		<td width="10%" align=left><b>DOC #</b></td>
		<td width="7%" align=center><b>DATE</b></td>
		<td width="22%" align=left><b>DOC REMARKS</b></td>
		<td width="10%" align=left><b>ITEM CODE</b></td>
		<td align=left><b>DESCRIPTION</b></td>
		<td width="7%" align=center><b>UNIT</b></td>
		<td width="7%" align=right><b>QTY</b></td>
		<td width="10%" align=right><b>COST</b></td>
		<td width="10%" align=right><b>AMOUNT</b></td>
	</tr>
</thead>
<tbody>';

while($row = $query->fetch_array()) {
	if($row['doc_no'] != $o) { $dno = str_pad($row['doc_no'],6,0,STR_PAD_LEFT); $ddate = $row['doc_date']; $rem = $row['remarks']; } else { $dno = ""; $ddate = ""; $rem = ""; }

	list($ucost) = $con->getArray("select unit_cost from products_master where item_code = '$row[item_code]';");
	$amt = $row['qty'] * $ucost;
	$html = $html . '<tr>
		<td align=left>' . $dno . '</td>
		<td align=left>' . $ddate . '</td>
		<td align=left>' . $rem . '</td>
		<td align=left>' . $row['item_code'] . '</td>
		<td align=left>' . html_entity_decode($row['description']) . '</td>
		<td align=center>' . $con->identUnit($row['unit']) . '</td>
		<td align=right>' . number_format($row['qty'],2) . '</td>
		<td align=right>' . number_format($row['cost'],2) . '</td>
		<td align=right>' . number_format($row['amount'],2) . '</td>
	</tr>'; $amtGT+=$row['amount']; $o = $row['doc_no']; 
}
$html = $html . '<tr>
					<td colspan="7" style="border-top: 0.1mm solid black; border-bottom: 0.1mm solid black;"><b>GRAND TOTAL</b></td>
					<td colspan="2" align=right style="border-top: 0.1mm solid black; border-bottom: 0.1mm solid black;"><b>'.number_format($amtGT,2).'</b></td>
				</tr>
	</tbody>
</table>
</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;

mysql_close($con);
?>