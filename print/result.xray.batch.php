<?php
	session_start();
	ini_set("max_execution_time",-1);
	ini_set("memory_limit",-1);
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");


	$mpdf=new mPDF('win-1252','LETTER','','',10,10,80,30,10,10);
	$mpdf->use_embeddedfonts_1252 = true;    // false is default
	$mpdf->SetProtection(array('print'));
	$mpdf->SetAuthor("PORT80 Solutions");
	$searchString = '';

	if($_GET['consultant'] != '') { $searchString .= " and a.consultant = '$_GET[consultant]' "; }
	if($_GET['encoder'] != '') { $searchString .= " and a.created_by = '$_GET[encoder]' "; }
	if($_GET['type'] != '') { $searchString .= " and a.result_type = '$_GET[type]' "; } 

	switch($_REQUEST['sort']) {
		case "1": default:
			$order = " ORDER BY c.lname,c.fname,c.mname";
		break;
		case "2":
			$order = " ORDER BY d.lotno asc";
		break;
		case "3":
			$order = " ORDER BY c.employer";
		break;
	}

	$outerQuery = $con->dbquery("SELECT DISTINCT a.so_no, a.serialno, a.code FROM lab_descriptive a LEFT JOIN so_header b ON a.so_no = b.so_no LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN lab_samples d ON a.so_no = d.so_no AND a.serialno = d.serialno WHERE a.result_date between '".$con->formatDate($_GET['dtf'])."' AND '".$con->formatDate($_GET['dt2'])."' $searchString AND a.verified = 'Y' $order;");
	while($outerRow = $outerQuery->fetch_array()) {


		$_ihead = $con->getArray("SELECT DATE_FORMAT(result_date,'%m/%d/%Y') AS rdate, b.patient_name, b.patient_address, IF(c.gender='M','Male','Female') AS gender, FLOOR(DATEDIFF(b.so_date,c.birthdate)/365.25) AS age, a.physician,  e.emp_id, d.fullname as consultant, d.prefix, d.license_no, d.specialization, d.signature_file, a.serialno, a.procedure, a.impression, a.created_by, b.trace_no, a.verified, e.role, e.signature_file as encodersignature, c.employer, a.serialno FROM lab_descriptive a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id left join options_doctors d on a.consultant = d.id left join user_info e on a.created_by = e.emp_id WHERE a.so_no = '$outerRow[so_no]' AND `code` = '$outerRow[code]' AND serialno = '$outerRow[serialno]' AND a.branch = '$_SESSION[branchid]';");
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

	/* END OF SQL QUERIES */

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
		<td></td>
		<td></td>
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
		$html .=		'<td width=50% align=center valign=top>'.$encoderSignature.'<br/>'.$con->getUname($_ihead['created_by']).'<br>___________________________________________<br/><b>'.$_ihead['role'].'</b></td>
						<td align=center valign=top>'.$consultantSignature.'<br/>'.$_ihead['consultant'].', '. $_ihead['prefix'] .'<br/>___________________________________________<br><b>'.$_ihead['specialization'].'<br/>License No. '.$_ihead['license_no'].'</b></td>';
		}else {
		$html .=		'<td width=50% align=center valign=top><br/><br><br/><b></b></td>
							<td align=center valign=top>'.$consultantSignature.'<br/>'.$_ihead['consultant'].', '. $_ihead['prefix'] .'<br/>___________________________________________<br><b>'.$_ihead['specialization'].'<br/>License No. '.$_ihead['license_no'].'</b></td>';
		}
	$html .= '</tr>
</table>
<table width=100% style="font-size: 8pt;">
	<tr><td align=left><barcode size=0.6 code="'.substr($_ihead['trace_no'],0,25).'" type="C128A"></td><td align=right>Run Date: '.date('m/d/Y h:i:s a').'</td></tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<div id="main" style="text-align: justify;">'.$_ihead['impression'].'</div>
<div id="resultFooter" style="text-align: left; padding-top: 40px; font-style: italic;">Finding is based only on radiographic interpretation. Clinical correlation is suggested.</div>
</body>
</html>
';

	$endOfPage = $mpdf->page + 1;
	$html = html_entity_decode($html);
	$mpdf->WriteHTML($html);
	$mpdf->AddPage();
}

$mpdf->DeletePages($endOfPage);
$mpdf->Output();
exit;



?>