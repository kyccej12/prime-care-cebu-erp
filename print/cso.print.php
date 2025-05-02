<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

	/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");

	$h = $con->getArray("select trace_no, customer_code, customer_name, customer_address, terms, company, location, po_no, date_format(po_date,'%m/%d/%Y') as po_date, lpad(cso_no,6,0) as csono, contact_person, contact_no, email_add, date_format(cso_date,'%m/%d/%Y') as d8, if(`from`!='0000-00-00',date_format(`from`,'%m/%d/%Y'),'') as dfrom, if(`until`!='0000-00-00',date_format(`until`,'%m/%d/%Y'),'') as duntil, if(`po_date`!='0000-00-00',date_format(`po_date`,'%m/%d/%Y'),'') as pod8, amount, created_by, left(cso_type,1) as cstype from cso_header where cso_no = '$_REQUEST[cso_no]' and branch = '$_SESSION[branchid]';");
	$d = $con->dbquery("select lpad(pid,6,0) as pid, pname, b.gender, date_format(b.birthdate,'%m/%d/%Y') as birthdate, b.birthdate as bday, `code`, description, amount from cso_details a left join patient_info b on a.pid = b.patient_id WHERE trace_no = '$h[trace_no]';");	
	/* END OF SQL QUERIES */
	list($drows) = $con->getArray("select count(*) from cso_details where cso_no = '$_REQUEST[cso_no]' and branch = '$_SESSION[branchid]';");
	list($tin,$tel_no,$bizstyle) = $con->getArray("select tin_no, tel_no, bizstyle from contact_info where file_id = '$h[customer_code]';");
	list($terms) = $con->getArray("select description from options_terms where terms_id = '$h[terms]';");

	if($drows > 5) { $paper = 'letter'; } else { $paper = 'LETTER-H'; }


$mpdf=new mPDF('win-1252',$paper,'','',10,10,80,35,10,10);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->setAutoTopMargin='stretch';
$mpdf->setAutoBottomMargin='stretch';
$mpdf->use_kwt = true;
$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("Prime Care Cebu");
$mpdf->SetDisplayMode(40);

if($_REQUEST['reprint'] == 'Y') {
	$mpdf->SetWatermarkText('REPRINTED COPY');
	$mpdf->showWatermarkText = true;
}



$html = '
<html>
<head>
<style>
body {font-family: sans-serif; font-size: 10px; }
td { vertical-align: top; }

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

.myitems {
	border-left: 1px solid black;
	border-right: 1px solid black;
}

.mytotals {
	border: 1px solid black;
	font-size: 12px;
	/* font-weight: bold; */
	text-align: right;
}

table thead td { 
	border: 0.1mm solid #000000;
    text-align: center;
	vertical-align: middle;
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
			<span style="font-weight: bold; font-size: 11pt; color: #000000;">CORPORATE SERVICE ORDER&nbsp;&nbsp;</span><br />
			<barcode size=0.8 code="'.$h['trace_no'].'" type="C128A">
		</td>
	</tr>
</table>
<table width="100%" cellspacing=0 cellpadding=0 style="font-size: 9pt;">
	<tr>
		<td class="billto" width=60% rowspan="7">
			<b><br/>BILL TO :</b><br /><br /><b>'.$h['customer_name'].'</b><br /><i>'.$h['customer_address'].'<br/>'.$tel_no.'<br/><br/></i>
			<b>Company :</b><br /><br /><b>'.$h['company'].'</b><br /><i>'.$h['location'].'<br/>'.$tel_no.'<br/></i>
		</td>
		<td class="td-l-top"><b>CSO No.</b></td>
		<td class="td-r-top"><b>'.$h['csono'].'-'.$h['cstype'].'</b></td>
	</tr>
	<tr>
		<td class="td-l-head"><b>Date</b></td>
		<td class="td-r-head"><b>' . $h['d8'] . '</b></td>
	</tr>

	<tr>
		<td class="td-l-head"><b>PO No.</b></td>
		<td class="td-r-head"><b>' . $h['po_no'] . '</b></td>
	</tr>
	<tr>
		<td class="td-l-head"><b>PO Date</b></td>
		<td class="td-r-head"><b>' . $h['po_date'] . '</b></td>
	</tr>
	<tr>
		<td class="td-l-head"><b>Scheduled Date</b></td>
		<td class="td-r-head"><b>' . $h['dfrom'] . ' to ' . $h['duntil'] . '</b></td>
	</tr>
	<tr>
		<td class="td-l-head"><b>Credit Terms</b></td>
		<td class="td-r-head"><b>' . $terms . '</b></td>
	</tr>
	<tr>
		<td class="td-l-head-bottom"><b>Amount Due</b></td>
		<td class="td-r-head-bottom"><b>&#8369;' . number_format($h['amount'],2) . '</b></td>
	</tr>
</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
	<table width=100% cellpadding=5 style="border: 1px solid #000000; font-size: 8pt;">
		<tr>
			<td width=33% align=center><b>PREPARED BY:</b><br><br>'.$con->getUname($h['created_by']).'<br/></td>
			<td align=center><b>CHECKED & VERIFIED BY:</b><br><br>_______________________<br><font size=2>Signature Over Printed Name</font></td>
			<td width=33%  align=center><b>APPROVED BY:</b><br><br>_______________________<br><font size=2>Signature Over Printed Name</font></td>
		</tr>
	</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->

<table class="items" width="100%" style="font-size: 10px; border-collapse: collapse;" cellpadding="2">
	<thead>
		<tr><td colspan=7 style="border: 1px solid black; font-weight: bold;" align=center>PARTICULARS</td></tr>
		<tr>
			<td width="5%" align=center ><b>NO</b></td>
			<td width="25%" align=center ><b>PATIENT</b></td>
			<td width="10%" align=center ><b>GENDER</b></td>
			<td width="10%" align=center ><b>BIRTHDATE</b></td>
			<td width="10%" align=center ><b>CODE</b></td>
			<td align=center ><b>PROCEDURE</b></td>
			<td width="10%" align=center ><b>AMOUNT</b></td>
		</tr>
	</thead>
<tbody>';

$i = 1;
while($row = $d->fetch_array()) {

	$html = $html . '<tr>
		<td align=center class="myitems">' . $i . '</td>
		<td align=left class="myitems">' . $row['pname'] . '</td>
		<td align=center class="myitems"> ' . $row['gender'] . '</td>
		<td align=center class="myitems"> ' . $row['birthdate'] . '</td>
		<td align=center class="myitems"> ' . $row['code'] . '</td>
		<td align="left" class="myitems">' . $row['description'] . '</td>
		<td align="right" class="myitems">' . number_format($row['amount'],2) . '</td>
	</tr>'; $i++; $gt+=$row['amount'];
}



$html .= '<tr>
		<td colspan=6 class="mytotals" style="padding-right: 20px;">Grand Total</td>
		<td class="mytotals">'.number_format($gt,2).'</td>
	</tr>';


$html .= '
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