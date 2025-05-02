<?php

	session_start();
	require_once("../lib/mpdf6/mpdf.php");
	require_once("../handlers/_generics.php");
	
	$con = new _init();


/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");
	$_ihead = $con->getArray("select po_no, lpad(po_no,8,0) as rr, date_format(po_date,'%m/%d/%Y') as d8, if(date_needed!='0000-00-00',date_format(date_needed,'%m/%d/%Y'),'') as nd8, delivery_address, supplier, supplier_name, supplier_addr, requested_by, amount, discount, net, remarks, mrs_no,c.description as terms FROM po_header a LEFT JOIN contact_info b ON a.supplier = b.file_id LEFT JOIN options_terms c ON b.terms = c.terms_id where po_no='$_REQUEST[po_no]' and branch = '$_SESSION[branchid]';");
	$_idetails = $con->dbquery("select item_code, description, qty, unit, cost, discount, round(qty*(cost-discount),2) as amount from po_details where po_no = '$_REQUEST[po_no]' and branch = '$_SESSION[branchid]';");
	$bcode = STR_PAD($_REQUEST['user'],2,'0',STR_PAD_LEFT)."-".$_ihead['po_no']."-".date('Ymd');

	list($dCount) = $con->getArray("select count(*) from po_details where po_no = '$_REQUEST[po_no]' and branch = '$_SESSION[branchid]';");
	if($dCount > 7) { $paper = 'Letter'; } else { $paper = 'FOLIO-H'; }

	// list($qty) = $con->getArray("select sum(qty) as qty from po_details where po_no = '$_REQUEST[po_no]' and branch = '$_SESSION[branchid]';");

	list($nos, $stin) = $con->getArray("select tel_no, tin_no from contact_info where file_id = '$_ihead[supplier]';");
	
	if($_ihead['delivery_address'] != "") {
		$daddr = "<b>Delivery Information: </b><br/>c/o ".utf8_decode($_ihead['requested_by'])."<br/>".$_ihead['delivery_address'];
	} else {
		$daddr = "<b>Delivery Information: </b><br/>";
	}
	
	$projname = $con->identCostCenter($_ihead['proj']);
	list($cperson) = $con->getArray("select cperson from contact_info where file_id = '$_ihead[supplier]';");

	$approveDiv = '';
	
	
	
/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252',$paper,'','',10,10,72,35,5,5);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->setAutoTopMargin='stretch';
$mpdf->setAutoBottomMargin='stretch';
$mpdf->use_kwt = true;
$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("PORT80 Solutions");
$mpdf->SetDisplayMode(40);

if($_REQUEST['reprint'] == 'Y') {
	$mpdf->SetWatermarkText('REPRINTED COPY');
	$mpdf->showWatermarkText = true;
}

$html = '
<html>
<head>
<style>
body { font-family: sans-serif; font-size: 9pt; }
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
    border-left: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000; /* background-color: #EEEEEE; */
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
		/* background-color: #EEEEEE; */ padding: 5px;
		text-align: left; font-weight: bold;
		border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000;
		border-top: 0.1mm solid #000000;
	}
.td-r-top { 
	text-align: right; font-weight: bold; padding: 5px;
    border-right: 0.1mm solid #000000;
	border-top: 0.1mm solid #000000;
}

.td-l-head {
	text-align: left; font-weight: bold; padding-left: 5px;vertical-align:middle;padding-right: 5px;
    border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; /* background-color: #EEEEEE; */
}

.td-r-head {
	text-align: right; font-weight: bold; padding-left: 5px;vertical-align:middle;padding-right: 5px;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000;
}
.td-l-head-bottom {
	text-align: left; font-weight: bold; padding-left: 5px;vertical-align:middle;padding-right: 5px;
    border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; /* background-color: #EEEEEE; */ border-bottom: 0.1mm solid #000000;
}

.td-r-head-bottom {
	text-align: right; font-weight: bold; padding-left: 5px;padding-right: 5px;vertical-align:middle;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
}

.billto {
	font-size: 12px; vertical-align: top; padding: 5px;
}
</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellspaing=0>
	<tr><td align=center><img src="../images/doc-header.jpg" /></td></tr>
	<tr>
		<td width="100%" align=center>
			<span style="font-weight: bold; font-size: 16pt; color: #000000;">PURCHASE ORDER</span>
		</td>
	</tr>
</table>
<table width="100%" cellspacing=0 cellpadding=2 style = "font-size: 11pt; margin-top: 10px;" >
	<tr>
		<td class="billto" width=65% rowspan="4">
			<b>SUPPLIER :</b><br /><br /><span style="font-weight: bold; font-size: 14pt;"><b>'.$_ihead['supplier_name'].'</b></span><br /><i>'.$_ihead['supplier_addr'].'<br/><b>Contact Nos: </b>'.$nos.'
		</td>
		<td class="td-l-head"><b>PO No.</b></td>
		<td class="td-r-head"><b>' . $_ihead['rr'] . '</b></td>
	</tr>
	<tr>
		<td class="td-l-head"><b>REQUEST #</b></td>
		<td class="td-r-head"><b>' . $_ihead['mrs_no'] . '</b></td>
	</tr>
	<tr>
		<td class="td-l-head"><b>PO DATE</b></td>
		<td class="td-r-head"><b>' . $_ihead['d8'] . '</b></td>
	</tr>
	<tr>
		<td class="td-l-head-bottom"><b>TERMS</b></td>
		<td class="td-r-head-bottom"><b>'.$_ihead['terms'].'</b></td>
	</tr>
</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
	<table width=100% cellpadding=1 border=0 style = "font-size:13pt;">
		<tr><td width=70% align=right><b>NET PURCHASE AMOUNT :</b></td><td align=right><b>'. number_format($_ihead['net'],2) .' PHP</b></td></tr>
	</table>
	<table width=100% cellpadding=5>
		<tr><td width=12%><b>Remarks :</b></td><td align=left>'.$_ihead['remarks'].'</td></tr>
	</table>
	<table width=100% cellpadding=2 style="border: 1px solid #000000;">
		<tr>
			<td width=50% align=center><b>PREPARED BY:</b><br><br>Janely Mari A. Baguioso<br/>_________________________________<br/><font size=3>Purchasing & Central Supply Manager</font></td>
			<td width=50% align=center><b>APPROVED BY:</b><br><br>Tinna P. Aznar, RMT, MD<br>_________________________________<br><font size=3>Assistant Medical Director</font></td>
		</tr>
	</table>
	<table width=100%>
		<tr>
			<td width=50%>Date & Time Sent: ______________________________</td>
			<td></td>
		</tr>
		<tr>
			<td width=50%>P.O Received By: &nbsp;&nbsp;______________________________</td>
			<td align=right>Date & Time Printed: '.$now.'</td>
		</tr>
	</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->

<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="3">
	<thead>
		<tr>

			<td align=left><b>PARTICULARS</b></td>
			<td width="8%" align=center><b>QTY</b></td>
			<td width="8%" align=center><b>UoM</b></td>
			<td width="10%" align=right><b>COST</b></td>
			<td width="10%" align=right><b>DISC</b></td>
			<td width="12%" align=right><b>NET AMT</b></td>
		</tr>
	</thead>
	<tbody>';
		//$style = 'style = "font-size:8pt;border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;"';
		$i = 0;
		while($row = $_idetails->fetch_array()) {
			$html = $html . '<tr>

			<td align=left '.$style.'>' . $row['description'] . '</td>
			<td align="center" '.$style.'>' . number_format($row['qty'],2) . '</td>
			<td align="center" '.$style.'>' . $con->identUnit($row['unit']) . '</td>
			<td align="right" '.$style.'>' . number_format($row['cost'],2) . '</td>
			<td align="right" '.$style.'>' . number_format($row['discount'],4) . '</td>
			<td align="right" '.$style.'>' . number_format($row['amount'],2) . '</td>
			</tr>'; $i++;
		}
		//$html = $html . "<tr><td colspan=7 align=center ".$style."><b>*** NOTHING FOLLOWS ***</b></td></tr>";$i++;
		
	$html = $html .  '
	</tbody>
</table>
</body>
</html>
';
$html = utf8_encode($html);
$mpdf->WriteHTML($html);
$mpdf->Output();
exit;

?>