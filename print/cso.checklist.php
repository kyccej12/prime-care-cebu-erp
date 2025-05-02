<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */
$now = date("m/d/Y h:i a");
$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");

$h = $con->getArray("select trace_no, customer_code, customer_name, customer_address, terms, company, location, po_no, date_format(po_date,'%m/%d/%Y') as po_date, lpad(cso_no,6,0) as csono, contact_person, contact_no, email_add, date_format(cso_date,'%m/%d/%Y') as d8, if(`from`!='0000-00-00',date_format(`from`,'%m/%d/%Y'),'') as dfrom, if(`until`!='0000-00-00',date_format(`until`,'%m/%d/%Y'),'') as duntil, if(`po_date`!='0000-00-00',date_format(`po_date`,'%m/%d/%Y'),'') as pod8, amount, created_by, left(cso_type,1) as cstype from cso_header where cso_no = '$_REQUEST[cso_no]' and branch = '$_SESSION[branchid]';");
$d = $con->dbquery("select pid, pname, b.gender, date_format(b.birthdate,'%m/%d/%Y') as birthdate, b.birthdate as bday, `code`, description, amount from cso_details a left join patient_info b on a.pid = b.patient_id WHERE trace_no = '$h[trace_no]';");	
/* END OF SQL QUERIES */
list($drows) = $con->getArray("select count(*) from cso_details where cso_no = '$_REQUEST[cso_no]' and branch = '$_SESSION[branchid]';");
list($tin,$tel_no,$bizstyle) = $con->getArray("select tin_no, tel_no, bizstyle from contact_info where file_id = '$h[customer_code]';");
list($terms) = $con->getArray("select description from options_terms where terms_id = '$h[terms]';");

if($drows > 5) { $paper = 'letter'; } else { $paper = 'LETTER-H'; }


$mpdf=new mPDF('win-1252',$paper,'','',10,10,60,35,7,7);
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
	border: 1px solid black;
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
<table width="100%" cellpadding=0 cellspaing=0>
	<tr>
		<td align=center><img src="../images/doc-header.jpg" /></td>
	</tr>
	<tr>
		<td width="100%" align=center><span style="font-weight: bold; font-size: 10pt; color: #000000;">PATIENT ATTENDANCE SHEET</span></td>
	</tr>
	<tr>
		<td width="100%" align=center><span style="font-weight: bold; font-size: 10pt; color: #000000;">(CSO NO. '.$_REQUEST['cso_no'].')</span></td>
	</tr>
	<tr><td height=20></td></tr>
</table>

<table width=100% cellspacing=0 cellpadding=0>
	<tr>
		<td width=70% align=center>
			<span style="font-size:10pt;font-weight:bold;">'.$h['company'].'</span><br/>
			<span style="font-size:9pt; font-style: italic;">'.$h['location'].'</span><br/>
			<span style="font-size:9pt;">Scheduled Date: '.$h['dfrom']. ' to ' . $h['duntil'] . '</span>
			';
			
$html .= '</td>
	</tr>
</table>

</htmlpageheader>

<htmlpagefooter name="myfooter">

</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->

<table class="items" width="100%" style="font-size: 8pt; border-collapse: collapse;" cellpadding="2">
	<thead>
		<tr>
			<td width="5%" align=center ><b>NO</b></td>
			<td width="25%" align=center ><b>PATIENT</b></td>
			<td width="5%" align=center ><b>SEX</b></td>
			<td width="10%" align=center ><b>BIRTHDATE</b></td>
			<td width="10%" align=center ><b>AGE</b></td>
			<td align=center ><b>PROCEDURE</b></td>
			<td width="20%" align=center ><b>SIGNATURE</b></td>
		</tr>
	</thead>
<tbody>';

$i = 1;
while($row = $d->fetch_array()) {

	$description = '';
	$innerQuery = $con->dbquery("SELECT d.description as incode FROM cso_header a LEFT JOIN cso_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON b.pid = c.patient_id LEFT JOIN services_master d ON b.code = d.code WHERE a.status = 'Finalized' AND d.with_subtests = 'N' AND d.category IN ('1','2') AND a.cso_no = '$_REQUEST[cso_no]' and b.code = '$row[code]' and b.pid = '$row[pid]' AND d.description NOT LIKE '%PCR%' UNION SELECT e.description AS incode FROM cso_header a LEFT JOIN cso_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON b.pid = c.patient_id LEFT JOIN services_master d ON b.code = d.code LEFT JOIN services_subtests e ON b.code = e.parent LEFT JOIN services_master f ON e.code = f.code WHERE a.status = 'Finalized' AND d.with_subtests = 'Y' AND f.category IN ('1','2') AND a.cso_no = '$_REQUEST[cso_no]' and b.code = '$row[code]' and b.pid = '$row[pid]' AND d.description NOT LIKE '%PCR%';");
	while($innerRow = $innerQuery->fetch_array()) {
		$description .= $innerRow[0] . ', ';
	}

	$html = $html . '<tr>
		<td align=center class="myitems">' . $i . '</td>
		<td align=left class="myitems">' . $row['pname'] . '</td>
		<td align=center class="myitems"> ' . $row['gender'] . '</td>
		<td align=center class="myitems"> ' . $row['birthdate'] . '</td>
		<td align=center class="myitems"> ' . $con->calculateAge($row['bday']) . '</td>
		<td align="left" class="myitems">' . substr($description,0,-2) . '</td>
		<td align="right" class="myitems"></td>
	</tr>'; $i++;
}





$html .= '</tbody>
</table>
</body>
</html>
';
$html = utf8_encode($html);
$mpdf->WriteHTML($html);
$mpdf->Output();
exit;
?>