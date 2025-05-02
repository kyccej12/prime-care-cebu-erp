<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");

	$_ihead = $con->getArray("SELECT DATE_FORMAT(result_date,'%m/%d/%Y') AS rdate, b.patient_name, b.patient_address, IF(c.gender='M','Male','Female') AS gender, FLOOR(DATEDIFF(b.so_date,c.birthdate)/365.25) AS age, a.physician, e.emp_id, d.fullname AS consultant, d.prefix, d.license_no, d.specialization, d.signature_file, a.serialno, a.procedure, a.impression, a.created_by, CONCAT(UCASE(RIGHT(b.trace_no,5)),LPAD(b.so_no,8,'0')) AS barcode, a.verified, a.verified_by, e.role, e.signature_file AS encodersignature, c.employer, a.serialno, f.patientstatus FROM lab_descriptive a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_doctors d ON a.consultant = d.id LEFT JOIN user_info e ON a.verified_by = e.emp_id LEFT JOIN options_patientstat f ON b.cstatus = f.id  WHERE a.so_no = '$_REQUEST[so_no]' AND `code` = '$_REQUEST[code]' AND serialno = '$_REQUEST[serialno]' AND a.branch = '$_SESSION[branchid]';");
	list($lotno) = $con->getArray("select lotno from lab_samples where serialno = '$_ihead[serialno]';");
	if($lotno == '') { $lotno = $_ihead['serialno']; }
	
	if($_ihead['signature_file'] != '') {
		$consultantSignature = "<img src='../images/signatures/$_ihead[signature_file]' align=absmiddle />";
	} else {
		$consultantSignature = "<img src='../images/signatures/blank.png' align=absmiddle />";	
	}

	if($_ihead['encodersignature'] != '') {
		$encoderSignature = "<img src='../images/signatures/$_ihead[encodersignature]' align=absmiddle />";
	} else {
		$encoderSignature = "<img src='../images/signatures/blank.png' align=absmiddle />";	
	}

	list($brgy) = $con->getArray("SELECT brgyDesc FROM options_brgy WHERE brgyCode = '$_ihead[brgy]';");
    list($ct) = $con->getArray("SELECT citymunDesc FROM options_cities WHERE cityMunCode = '$_ihead[city]';");
    list($prov) = $con->getArray("SELECT provDesc FROM options_provinces WHERE provCode = '$_ihead[province]';");

    if($_ihead['street'] != '') { $myaddress.=$_ihead['street'].", "; }
    if($brgy != "") { $myaddress .= $brgy.", "; }
    if($ct != "") { $myaddress .= $ct.", "; }
    if($prov != "")  { $myaddress .= $prov.", "; }
    $myaddress = substr($myaddress,0,-2);

/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','LETTER-HP','','',10,10,95,30,10,10);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("PORT80 Solutions");

if($_ihead['verified'] != 'Y') {
	$mpdf->SetWatermarkText('FOR VALIDATION');
	$mpdf->showWatermarkText = true;
}

$mpdf->SetDisplayMode(50);

$html = '
<html>
<head>
	<style>
		body {font-family: arial; font-size: 10pt; }
	</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellspaing=0>
	<tr><td align=center><img src="../images/doc-header.jpg" /></td></tr>
</table>
<table width=100% cellpadding=2 cellspacing=0 style="font-size:8pt;margin-top:20px;">
	<tr>
		<td width=100% colspan=4 style="background-color: #cdcdcd; border-top: 1px solid black; border-bottom: 1px solid black;" align=center><b>PATIENT INFORMATION</b></td>
	</tr>
	<tr>
		<td width=20%><b>X-RAY NO.</b></td>
		<td width=35%>:&nbsp;&nbsp;'.$lotno.'</td>
		<td width=20%><b>DATE</b></td>
		<td width=25%>:&nbsp;&nbsp;'.$_ihead['rdate'].'</td>
	</tr>
	<tr>
		<td><b>PATIENT NAME</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['patient_name'].'</td>
		<td><b>GENDER</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['gender'].'</td>
	</tr>
	<tr>
		<td><b>ADDRESS</b></td>
		<td>:&nbsp;&nbsp;' . $_ihead['patient_address'] . '</td>
		<td><b>AGE</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['age'].'yo</td>
	</tr>
	<tr>
		<td><b>REQUESTING PHYSICIAN</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['physician'].'</td>
		<td><b>EXAMINATION</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['procedure'].'</td>
	</tr>
	<tr>
		<td><b>EMPLOYER</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['employer'].'</td>
		<td><b>PATIENT STATUS</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['patientstatus'].'</td>
	</tr>
	<tr>
		<td height=5></td>
	</tr>
	<tr>
		<td width="100%" colspan=4 style="padding-top: 30px;" align=center>
			<span style="font-weight: bold; font-size: 14pt; color: #000000;">X-RAY REPORT</span>
		</td>
	</tr>
</table>

</htmlpageheader>

<htmlpagefooter name="myfooter">
<table width=100% cellpadding=5 style="margin-bottom: 25px;">
	<tr>';
		if($_ihead['emp_id'] != '121') {
		$html .=		'<td width=50% align=center valign=top>'.$encoderSignature.'<br/>'.$con->getUname($_ihead['verified_by']).'<br>___________________________________________<br/><b>'.$_ihead['role'].'</b></td>
						<td align=center valign=top>'.$consultantSignature.'<br/>'.$_ihead['consultant'].', '. $_ihead['prefix'] .'<br/>___________________________________________<br><b>'.$_ihead['specialization'].'<br/>License No. '.$_ihead['license_no'].'</b></td>';
		}else {
		$html .=		'<td width=50% align=center valign=top><br/><br><br/><b></b></td>
							<td align=center valign=top>'.$consultantSignature.'<br/>'.$_ihead['consultant'].', '. $_ihead['prefix'] .'<br/>___________________________________________<br><b>'.$_ihead['specialization'].'<br/>License No. '.$_ihead['license_no'].'</b></td>';
		}
	$html .= '</tr>
	</table>
<table width=100% style="font-size: 8pt;">
	<tr><td align=left><barcode size=0.6 code="'. $_ihead['barcode'] .'" type="C128A"></td><td align=right>Run Date: '.date('m/d/Y h:i:s a').'</td></tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<div id="main" style="text-align: justify;">'.$_ihead['impression'].'</div>
<div id="resultFooter" style="text-align: left; padding-top: 70px; font-style: italic;">Finding is based only on radiographic interpretation. Clinical correlation is suggested.</div>
</body>
</html>
';

$html = html_entity_decode($html);
$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;

mysql_close($con);
?>