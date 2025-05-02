<?php
	session_start();
	//ini_set("display_errors","On");
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	ini_set('max_execution_time',0);
	ini_set('memory_limit',-1);


	$con = new _init;

/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$h = $con->getArray("SELECT  a.trace_no, LPAD(a.soa_no,6,'0') AS soano, a.customer_code AS cid, a.customer_name AS cname, a.customer_address AS caddr, a.remarks, c.description AS terms, DATE_FORMAT(soa_date,'%M %d, %Y') AS soadate, b.tel_no, b.tin_no, b.cperson, a.created_by, a.created_on FROM soa_header a LEFT JOIN contact_info b ON a.customer_code = b.file_id LEFT JOIN options_terms c ON a.terms = c.terms_id WHERE a.soa_no = '$_REQUEST[soa_no]' AND a.branch = '$_SESSION[branchid]';");
	
	$d = $con->dbquery("SELECT * FROM soa_details WHERE soa_no = '$_REQUEST[soa_no]' AND branch = '$_SESSION[branchid]';");

	list($billingSig,$billingInCharge,$billingRole) = $con->getArray("SELECT if(signature_file != '',concat('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') as signature, fullname, `role` from user_info where emp_id = '$h[created_by]';");

	switch($h['revenue_center']) {
		case "081":	$center = 'AIRPORT'; break;
		case "050": $center = 'MOBILE'; break;
		case "150": $center = 'PPP (Province of Cebu)'; break;
		case "190": $center = 'CLINIC MANAGEMENT'; break;
		default: $center = 'Center'; break;
	}
	/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','letter','','',15,15,80,15,10,15);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->setAutoTopMargin='stretch';
$mpdf->setAutoBottomMargin='stretch';
$mpdf->use_kwt = true;
$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("Opon Medical Diagnostic Inc.");
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

.mytotals {
	border: 1px solid black;
	font-size: 12px;
	font-weight: bold;
	text-align: right;
}

</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellspading=0>
	<tr>
		<td align=center><img src="../images/doc-header.jpg" /></td> 
	</tr>
	<tr>
		<td width="100%" align=center><span style="font-weight: bold; font-size: 14pt; color: #000000;">STATEMENT OF ACCOUNT - PHARMACY</span></td>
	</tr>
	<tr>
		<td width="100%" align=center><span style="font-weight: bold; font-size: 11pt; color: #000000;">(NO. '.$h['soano'].')</span></td>
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
$html .= '</td>
		  <td align=right valign=top>
		  	<span style="font-size:12px;font-weight:bold;">'.$h['soadate'].'</span><br/><br/>';
			  if($h['po_no'] !='') {
				$html .= '<span style="font-size:12px;"><b>PO NO.:</b> '.$h['po_no'].'</span><br/>
				          <span style="font-size:12px;"><b>PO Date:</b> '.$h['pd8'].'</span><br/>';
				}
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
		<tr><td colspan=10 style="border: 1px solid black; font-weight: bold;" align=center>PARTICULARS</td></tr>
		<tr>
			<td width="5%" align=center ><b>NO</b></td>
			<td width="6%" align=center ><b>SO NO.</b></td>
			<td width="8%" align=center ><b>SO DATE</b></td>
			<td width="15%" align=center ><b>CUSTOMER</b></td>
			<td width="25%" align=center ><b>ITEM DESCRIPTION</b></td>
			<td width="5%" align=center ><b>UNIT</b></td>
			<td width="5%" align=center ><b>QTY</b></td>
			<td width="7%" align=center ><b>PRICE</b></td>
            <td width="5%" align=center ><b>DISC.</b></td>
			<td width="10%" align=center ><b>AMOUNT</b></td>
		</tr>
	</thead>
<tbody>';

$i = 1;
while($row = $d->fetch_array()) {
	$html = $html . '<tr>
		<td align=center class="myitems">' . $i . '</td>
		<td align=center class="myitems"> ' . $row['so_no'] . '</td>
		<td align=center class="myitems"> ' . $row['so_date'] . '</td>
		<td align=center class="myitems"> ' . $row['pname'] . '</td>
		<td align="left" class="myitems">' . $row['description'] . '</td>
        <td align=center class="myitems"> ' . $row['unit'] . '</td>
        <td align=center class="myitems"> ' . $row['qty'] . '</td>
        <td align=center class="myitems"> ' . $row['unit_price'] . '</td>
        <td align=center class="myitems">' . $row['disc'] . '</td>
		<td align="right" class="myitems">' . number_format($row['amount'],2) . '</td>
	</tr>'; $i++; $gt+=$row['amount'];
}

$html .= '<tr>
			<td colspan=9 class="mytotals" style="padding-right: 20px;">GRAND TOTAL</td>
			<td class="mytotals">'.number_format($gt,2).'</td>
		  </tr>
		  <tr><td colspan=10 height=20>&nbsp;</td></tr>
		  <tr><td colspan=10 align=center style="font-size: 14px; font-weight: bold; text-decoration: underline;">* All payments should be made in '.$h['terms'].'</td></tr>
		  <tr><td colspan=10 height=20>&nbsp;</td></tr>
		  <tr><td colspan=10 class="mytotals" style="text-align: center;">NOTE:  PLEASE ISSUE CHECK PAYABLE TO MEDGRUPPE POLYCLINICS & DIAGNOSTIC CENTER, INC.</td></tr>
		  <tr><td colspan=10 height=20>&nbsp;</td></tr>
		  <tr>
		  <td colspan=4 align=left><b>PREPARED BY:</b><br/><br/>_________________________________<br/><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$billingInCharge.'</b><br/><span style="font-size: 8px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$billingRole.'</span></td>
		  <td colspan=4 align=left style="padding-left: 150px;"><b>NOTED BY:</b><br/><br/>___________________________________<br/><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MS. Janna M. Ragas</b><br/><span style="font-size:8px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Finance Supervisor</span></td>
		  </tr>
		  <tr><td colspan=10 height=20>&nbsp;</td></tr>
		  <tr><td colspan=10 align=center style="padding:20px; font-weight: bold;">ANY BILLING QUESTIONS OR CORRECTIONS MUST BE BROUGHT TO OUR ATTENTION WITHIN SEVEN (7) DAYS FROM RECEIPT OF OTHERWISE TOTAL BILL REFLECTED IS DEEMED CORRECT AND DUE.</td></tr>
	</tbody>
</table>

<table width=100% style="margin-top: 40px;">
	<tr><td>RECEIVED BY:<br/><br/>________________________________________<br/>&nbsp;&nbsp;&nbsp;&nbsp;SIGNATURE OVER PRINTED NAME</td></tr>
	<tr><td><br/>Date : ______________________________</td></tr>
	<tr><td>Time : ______________________________</td></tr>
	<tr><td height=10>&nbsp;</td></tr>
	<tr><td><barcode size=0.8 code="'.substr($h['trace_no'],0,10).'" type="C128A"></td></tr>
</table>

</body>
</html>
';
$html = utf8_encode($html);
$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;
?>