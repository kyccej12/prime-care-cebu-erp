<?php
	session_start();
	//ini_set("display_errors","On");
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");
	ini_set("memory_limit","512M");
	ini_set("max_execution_time","0");

	$con = new _init;

	$mpdf=new mPDF('win-1252','folio-l','','',10,10,32,20,10,10);
	$mpdf->use_embeddedfonts_1252 = true;    // false is default
	$mpdf->SetProtection(array('print'));
	$mpdf->SetAuthor("PORT80 Business Solutions");
	$mpdf->SetDisplayMode(75);

	/* MYSQL QUERIES SECTION */
		$now = date("m/d/Y h:i a");
		$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");
		$searchString = '';

		if($_GET['cid'] != "") { $searchString .= " and a.supplier = '$_GET[cid]' "; } else { $cust = "All Suppliers"; }
		if($_GET['item'] != '') { $searchString .= " and (b.item_code like '%$_GET[item]%' or b.description like '%$_GET[item]%') "; }
		if($_GET['status'] != '') {
			switch($_GET['status']) {
				case "1":
					$rptLabel = 'Fully Delivered';
					$searchString .= " and (b.qty = b.qty_dld or b.qty < b.qty_dld) ";
				break;
				case "2":
					$rptLabel = 'Partially Delivered';
					$searchString .= " and b.qty_dld > 0 and b.qty_dld < b.qty ";
				break;	
				case "3":
					$rptLabel = 'Undelivered';
					$searchString .= " and b.qty_dld  =0 ";
				break;	

			}
		} else {
			$rptLabel = 'All';
		}

		$query = $con->dbquery("SELECT a.po_no, date_format(a.po_date,'%m/%d/%y') as pd8, a.supplier, a.supplier_name, b.item_code, b.description, b.unit,  b.qty, cost, ROUND(qty*cost,2) as amount, b.qty_dld FROM po_header a LEFT JOIN po_details b ON a.po_no = b.po_no  WHERE a.po_date BETWEEN '" . $con->formatDate($_GET['dtf']) . "' AND '" . $con->formatDate($_GET['dt2']) . "' AND a.status = 'Finalized' and b.amount > 0 $searchString order by a.po_no asc;");
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
			<span style="font-weight: bold; font-size: 8pt; color: #000000;"><br/>Summary of Purchase Order</span><br /><span style="font-size: 6pt; font-style: italic;">Date Covered ' . $_GET['dtf'] . ' - ' . $_GET['dt2'] .'<br/><br/>PO Status: '.$rptLabel.'</span>
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
		<td width="5%" align=center><b>PO #</b></td>
		<td width="5%" align=center><b>PO DATE</b></td>
		<td width="15%" align=left><b>SUPPLIER</b></td>
		<td width="7%" align=left><b>ITEM CODE</b></td>
		<td align=left><b>DESCRIPTION</b></td>
		<td width="5%" align=center><b>UNIT</b></td>
		<td width="5%" align=right><b>QTY</b></td>
		<td width="10%" align=right><b>COST</b></td>
		<td width="10%" align=right><b>AMOUNT</b></td>
		<td width="12%" align=right><b>QTY SERVED</b></td>
	</tr>
</thead>
<tbody>';

while($row = $query->fetch_array()) {
	if($row['po_no'] != $o) { $s = $row['supplier_name']; $d = $row['pd8']; $r = STR_PAD($row['po_no'],6,0,STR_PAD_LEFT); } else { $s = ""; $d = ""; $r = ""; }
	$html = $html . '<tr>
		<td align=center></b>' . $r . '</b></td>
		<td align=center></b>' . $d . '</b></td>
		<td align=left></b>' . $s . '</b></td>
		<td align=left>' . $row['item_code'] . '</td>
		<td align=left>' . html_entity_decode($row['description']) . '</td>
		<td align=center>' . $con->identUnit($row['unit']) . '</td>
		<td align=right>' . number_format($row['qty'],2) . '</td>
		<td align=right>' . number_format($row['cost'],2) . '</td>
		<td align=right>' . number_format($row['amount'],2) . '</td>
		<td align=right>' . number_format($row['qty_dld'],2) . '</td>
	</tr>'; $o = $row['po_no']; $amtGT+=$row['amount'];
}
$html = $html . '<tr>
					<td colspan="8"></td>
					<td align=right style="border-top: 0.1mm solid black; border-bottom: 0.1mm solid black;"><b>'.number_format($amtGT,2).'</b></td>
					<td></td>
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