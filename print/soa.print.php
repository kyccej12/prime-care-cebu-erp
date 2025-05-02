<?php
	session_start();
	ini_set("max_execution_time",0);
	ini_set("memory_limit",-1);
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$flagged = array('1553');

	$con = new _init;

	/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$h = $con->getArray("SELECT a.trace_no, LPAD(a.soa_no,6,'0') AS soano, a.stub_no, a.customer_code AS cid, a.customer_name AS cname, a.customer_address AS caddr, a.remarks, c.description AS terms, DATE_FORMAT(soa_date,'%M %d, %Y') AS soadate, b.tel_no, b.tin_no, b.cperson, a.po_no, if(a.po_date!='0000-00-00',date_format(po_date,'%m/%d/%Y'),'') as pd8, bizstyle, a.referred_by, a.revenue_center, a.vat, a.soa_no as xso, a.created_by, a.created_on FROM soa_header a LEFT JOIN contact_info b ON a.customer_code = b.file_id LEFT JOIN options_terms c ON a.terms = c.terms_id WHERE a.soa_no = '$_REQUEST[soa_no]' AND a.branch = '$_SESSION[branchid]';");
	$d = $con->dbquery("SELECT if(source='SO',concat('SO-',LPAD(so_no,6,0)),so_no) AS sono, so_no as xso, date_format(so_date,'%m/%d/%Y') as sodate, pid, pname, sum(amount) as amount, `source`, pid, so_no as xso from soa_details where soa_no = '$_REQUEST[soa_no]' and branch = '$_SESSION[branchid]' group by so_no, pid order by sum(amount) desc, so_date asc, so_no asc;");
	
	list($billingSig,$billingInCharge,$billingRole) = $con->getArray("SELECT if(signature_file != '',concat('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') as signature, fullname, `role` from user_info where emp_id = '$h[created_by]';");

	/* END OF SQL QUERIES */

	list($isVat) = $con->getArray("select vatable from contact_info where file_id = '$h[cid]';");
	if($h['stub_no'] != '') { $soano = $h['stub_no']; } else { $soano = $h['soano']; }

	switch($h['revenue_center']) {
		case "081":	$center = 'AIRPORT'; break;
		case "050": $center = 'MOBILE'; break;
		case "150": $center = 'PPP (Province of Cebu)'; break;
		case "190": $center = 'CLINIC MANAGEMENT'; break;
		default: $center = 'Center'; break;
	}

	if(in_array($h['xso'],$flagged)) {
		$center = "Mobile";
	}

$mpdf=new mPDF('win-1252','letter','','',10,10,80,35,5,5);
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
body { font-family: sans-serif; font-size: 7pt; }
td { vertical-align: top; }

table thead td { 
	border: 0.1mm solid #000000;
    text-align: center;
	vertical-align: middle;
}

.myitems {
	border-left: 1px solid black;
	border-right: 1px solid black;
}

.borderKilid {
	border-left: 1px solid black;
	border-right: 1px solid black;
	font-size: 12px;
	/* font-weight: bold; */
	text-align: right;
}

.borderTaas {
	border-top: 1px solid black;
	font-size: 12px;
	/* font-weight: bold; */
	text-align: right;
}

.borderUbos {
	border-bottom: 1px solid black;
	font-size: 12px;
	/* font-weight: bold; */
	text-align: right;
}

.mytotals {
	border: 1px solid black;
	font-size: 12px;
	/* font-weight: bold; */
	text-align: right;
}

</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellspaing=0>';
	if($h['cid'] == '000187') { $html .= '<tr> <td align=center><img src="../images/doc-header-teletech.jpg" /></td>'; $html .= '</tr>'; }

	else { $html .= '<tr><td align=center><img src="../images/primecare-alpha.png" /></td></tr>';}

	$html .= '<tr><td height=20></td></tr>
	<tr>
		<td width="100%" align=center><span style="font-weight: bold; font-size: 12pt; color: #000000;">STATEMENT OF ACCOUNT</span></td>
	</tr>
	<tr>
		<td width="100%" align=center><span style="font-weight: bold; font-size: 9pt; color: #000000;">(NO. '.$soano.')</span></td>
	</tr>
	<tr><td height=20></td></tr>
</table>

<table width=100% cellspacing=0 cellpadding=0>
	<tr>
		<td width=70%>
			<span style="font-size:16px;font-weight:bold;">'.$h['cname'].'</span><br/>
			<span style="font-size:11px;">'.$h['caddr'].'</span><br/>';
			if($h['tel_no'] !='') {
				$html .= '<span style="font-size:11px;">Telephone No.: '.$h['tel_no'].'</span><br/>';
			}
			if($h['tin_no'] !='') {
				$html .= '<span style="font-size:11px;">Tax Identification No.: '.$h['tin_no'].'</span><br/>';
			}
			if($h['bizstyle'] !='') {
				$html .= '<span style="font-size:11px;">Business Style: '.$h['bizstyle'].'</span><br/>';
			}
			if($h['referred_by'] != '') {
				$html .= '<br/><span style="font-size:11px;">REFERRED BY: </span><br/><span style="font-size:12px;font-weight:bold;">'.$h['referred_by'].'</span><br/>';
			}
			if($h['po_no'] !='') {
				$html .= '<span style="font-size:11px;"><b>PO NO.:</b> '.$h['po_no'].'</span><br/>';
			}
$html .= '</td>
		  <td align=right valign=top>
		  	<span style="font-size:12px;font-weight:bold;">'.$h['soadate'].'</span><br/><br/>';
			$html .= '<span style="font-size:12px;"><b>LOCATION :</b> '.$center.'</span>
		</td>
	</tr>
</table>

</htmlpageheader>

<htmlpagefooter name="myfooter">

</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->

<table class="items" width="100%" style="font-size: 10px; border-collapse: collapse;" cellpadding="2">
	<thead>
		<tr><td colspan=7 style="border: 1px solid black; font-weight: bold;" align=center>PARTICULARS</td></tr>
		<tr>
			<td width="5%" align=center ><b>NO</b></td>
			<td width="30%" align=center ><b>PATIENT</b></td>
			<td width="10%" align=center ><b>SERVICE<br/>ORDER NO.</b></td>
			<td width="10%" align=center ><b>DATE AVAILED</b></td>
			<td width="30%" align=center ><b>PROCEDURE</b></td>
			<td width="15%" align=center ><b>AMOUNT</b></td>
		</tr>
	</thead>
<tbody>';

$i = 1;
while($row = $d->fetch_array()) {


	// if($row['source'] == 'CSO') {
	// 	//list($dateAvailed) = $con->getArray("select date_format(processed_on,'%m/%d/%Y') from pccmobile.cso_details where cso_no = '$row[xso]' and pid = '$row[pid]';");
	// 	list($dateAvailed) = $con->getArray("SELECT DATE_FORMAT(extractdate,'%m/%d/%Y') FROM pccmobile.lab_samples WHERE so_no = '$row[xso]' AND pid = '$row[pid]' AND extractdate != '' ORDER BY extractdate ASC;");
	// 	$row['sodate'] = $dateAvailed;

	// }

	$description = '';
	$items = $con->dbquery("SELECT description from soa_details where so_no = '$row[xso]' and soa_no = '$_REQUEST[soa_no]' and pid = '$row[pid]';");
	while($itemRow = $items->fetch_array()) {
		$description .= $itemRow[0] . ', ';
	}

	$html = $html . '<tr>
		<td align=center class="myitems">' . $i . '</td>
		<td align=left class="myitems">' . $row['pname'] . '</td>
		<td align=center class="myitems"> ' . $row['sono'] . '</td>
		<td align=center class="myitems"> ' . $row['sodate'] . '</td>
		<td align="left" class="myitems">' . substr($description,0,-2) . '</td>
		<td align="right" class="myitems">' . number_format($row['amount'],2) . '</td>
	</tr>'; $i++; $gt+=$row['amount'];
}

if($isVat == 'Y') {
	$vatExemptSales = 0;
	$vatableSales = ROUND($gt / 1.12,2);
	$vat = ROUND($vatableSales * 0.12,2);
} else {
	$vatExemptSales = $gt;
	$vatableSales = 0;
	$vat = 0;
}

if($h['vat'] == 'Y') {

	$html .= '<tr>
					<td colspan=5 class="borderKilid borderTaas" style="padding-right: 20px;">Grand Total</td>
					<td class="borderKilid borderTaas">'.number_format($gt,2).'</td>
				</tr>
				<tr>
					<td colspan=5 class="borderKilid" style="padding-right: 20px;">Vatable Sales</td>
					<td class="borderKilid">'.number_format($vatableSales,2).'</td>
				</tr>
				<tr>
					<td colspan=5 class="borderKilid" style="padding-right: 20px;">VAT (12%)</td>
					<td class="borderKilid">'.number_format($vat,2).'</td>
				</tr>
				<tr>
					<td colspan=5 class="borderKilid" style="padding-right: 20px;">VAT Exempt Sales</td>
					<td class="borderKilid">'.number_format($vatExemptSales,2).'</td>
				</tr>
				<tr>
					<td colspan=5 class="borderKilid borderUbos" style="padding-right: 20px;">Amount Due</td>
					<td class="borderKilid borderUbos">'.number_format($gt,2).'</td>
				</tr>';

} else {
	$html .= '<tr>
			<td colspan=5 class="mytotals" style="padding-right: 20px; font-weight:bold;">Grand Total</td>
			<td class="mytotals" style="font-weight:bold;">'.number_format($gt,2).'</td>
		</tr>';
}



$html .= '<tr><td colspan=7 height=20>&nbsp;</td></tr>
		  <tr><td colspan=7 align=center style="font-size: 14px; font-weight: bold; text-decoration: underline;">* All payments should be made within '.$h['terms'].'</td></tr>
		  <tr><td colspan=7 height=20>&nbsp;</td></tr>
		  <tr><td colspan=7 class="mytotals" style="text-align: center;">NOTE:  PLEASE ISSUE CHECK PAYABLE TO MEDGRUPPE POLYCLINICS & DIAGNOSTIC CENTER, INC.</td></tr>
		  <tr><td colspan=7 height=20>&nbsp;</td></tr>
		  <tr>
		  <td colspan=3 align=left><b>PREPARED BY:</b><br/><br/>_________________________________<br/><b>&nbsp;&nbsp;&nbsp;&nbsp;Ms.&nbsp;'.$billingInCharge.'</b><br/><span style="font-size: 8px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$billingRole.'</span></td>
		    <td colspan=3 align=left style="padding-left: 100px;"><b>NOTED BY:</b><br/><br/>___________________________________<br/><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ms. Janna M. Ragas</b><br/><span style="font-size:8px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Finance Supervisor</span></td>
		  </tr>
		  <tr><td colspan=7 height=20>&nbsp;</td></tr>
		  <tr><td colspan=7 align=center style="padding:20px; font-weight: bold;">ANY BILLING QUESTIONS OR CORRECTIONS MUST BE BROUGHT TO OUR ATTENTION WITHIN SEVEN (7) DAYS FROM RECEIPT OF OTHERWISE TOTAL BILL REFLECTED IS DEEMED CORRECT AND DUE.</td></tr>
	</tbody>
</table>

<table width=100% style="margin-top: 40px;">
	<tr><td>RECEIVED BY:<br/><br/>________________________________________<br/>&nbsp;&nbsp;&nbsp;&nbsp;SIGNATURE OVER PRINTED NAME</td></tr>
	<tr><td><br/>Date/Time : ______________________________</td></tr>
	<tr>
		<td align=right style="padding-top:-35px;"><barcode size=0.8 code="'.substr($h['trace_no'],0,10).'" type="C128A"></td>
	</tr>
</table>

</body>
</html>
';
$html = utf8_encode($html);
$mpdf->WriteHTML($html);
$mpdf->Output();
exit;
?>