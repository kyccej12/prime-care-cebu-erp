<?php
	// ini_set("display_errors","on");
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

	/* MYSQL QUERIES SECTION */
 	$now = date("m/d/Y h:i a");
 	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");
	$_ihead = $con->getArray("SELECT a.*, LPAD(so_no,6,0) AS sono, DATE_FORMAT(so_date,'%m/%d/%Y') AS d8, DATE_FORMAT(a.updated_on, '%m/%d/%Y') AS examin_d8, b.patient_id, fname, b.lname, b.mname,FLOOR(DATEDIFF(so_date,b.birthdate)/364.25) AS age,DATE_FORMAT(b.birthdate,'%M %d') AS date1, DATE_FORMAT(b.birthdate,'%Y') AS date2, classification,examined_by,b.gender,b.brgy,b.city,b.province,b.street,c.civil_status,b.birthplace,b.employer, DATE_FORMAT(a.examined_on,'%m/%d/%Y') AS d8 FROM peme a LEFT JOIN pccmain.patient_info b ON a.pid = b.patient_id LEFT JOIN options_civilstatus c ON b.cstat = c.csid LEFT JOIN cso_details d ON a.pid = d.pid WHERE a.so_no= '$_REQUEST[so_no]' AND a.pid= '$_REQUEST[pid]';");
	$a = $con->dbquery("SELECT LPAD(so_no,6,0) AS sono, pid,CONCAT(c.fname,' ',c.lname) AS pname, pm_history FROM peme a LEFT JOIN options_medicalhistory b ON b.id = a.pid LEFT JOIN patient_info c ON a.pid = c.patient_id WHERE a.so_no= '$_REQUEST[so_no]' AND a.pid= '$_REQUEST[pid]';");
	$c = $con->getArray("SELECT pid,examined_by,DATE_FORMAT(examined_on,'%m/%d/%Y') AS examin_d8,TIME_FORMAT(examined_on,'%h:%m:%s') AS examin_tym,pre_examined_by,DATE_FORMAT(pre_examined_on,'%m/%d/%Y') AS pre_d8, TIME_FORMAT(pre_examined_on,'%h:%m:%s') AS pre_tym FROM peme WHERE so_no = '$_REQUEST[so_no]' and pid = '$_REQUEST[pid]';");
	$d = $con->getArray("select trace_no from so_header where so_no = '$_ihead[sono]';");

	if($_ihead['signature_path'] != '') {
		$patient_signature = "<img src='".$_ihead['signature_path']."' align=absmiddle width=123 height=50 />";
	} else { $patient_signature = "<img src=\"../images/signatures/blank.png\" align=absmiddle />"; }

	if($_ihead['photo_path'] != '') {
		$photo = "<img src='".$_ihead['photo_path']."' align=absmiddle width=120 />";
	} else { $photo = "<img src=\"../images/photos/peme/main.png\" align=absmiddle width=120 />"; }

	if($_ihead['examined_by'] != '') {
        list($docsignature,$docfullname,$docprefix,$docrole,$doclicenseno) = $con->getArray("SELECT IF(signature_file != '',CONCAT('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') AS signature, fullname, prefix , specialization, license_no FROM options_doctors WHERE id = '$_ihead[examined_by]';");
    }

	list($resultDate) = $con->getArray("select date_format(examined_on,'%m/%d/%Y') from peme where so_no = '$_ihead[so_no]' and pid = '$_REQUEST[pid]';");


	list($brgy) = $con->getArray("SELECT brgyDesc FROM options_brgy WHERE brgyCode = '$_ihead[brgy]';");
    list($ct) = $con->getArray("SELECT citymunDesc FROM options_cities WHERE cityMunCode = '$_ihead[city]';");
    list($prov) = $con->getArray("SELECT provDesc FROM options_provinces WHERE provCode = '$_ihead[province]';");

    if($_ihead['street'] != '') { $myaddress.=$_ihead['street'].", "; }
    if($brgy != "") { $myaddress .= $brgy.", "; }
    if($ct != "") { $myaddress .= $ct.", "; }
    if($prov != "")  { $myaddress .= $prov.", "; }
    $myaddress = substr($myaddress,0,-2);



	/* Medical History */
	if($_ihead['pm_history'] != '') {
		$pm = explode(",",$_ihead['pm_history']);
		$pmstring = '';
		foreach($pm as $pmid) {
			list($pmDescription) = $con->getArray("SELECT history FROM options_medicalhistory WHERE id = '$pmid';");
			$pmstring .= $pmDescription . ", ";
		}
		$pmstring = substr($pmstring,0,-2);

	} else { $pmstring = ''; }


	 /* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','letter','','',10,10,30,10,5,2);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
// $mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("PORT80 Solutions");

if($_REQUEST['rePrint'] == 'Y') {
	$mpdf->SetWatermarkText('Reprinted Copy');
	$mpdf->showWatermarkText = true;
}

$mpdf->SetDisplayMode(50);

$html = '
<html>
<head>
<style>
body {font-family: sans-serif; font-size: 10px; }
thead {
	height: 30%;
	border: 1px solid black;
}

.td-0 {
	border-top: 1px solid #00000;
}
.td-1 {
	border-bottom: 1px solid black;
	text-align:left;
	padding-top:5px;
}
.td-2 {
	border-top: 1px solid black;
	padding-bottom: 5px;
	border-left: 1px solid black;
	padding-left: 5px;
	text-align:left;
	padding-top:3px;
}
.td-3 {
	padding-bottom: 5px;
	border-left: 1px solid black;
	padding-left: 5px;
	text-align:left;
	padding-top:3px;
}
.td-4 {
	border-bottom: 1px solid #00000;
}
.td-5 {
	border-left: 1px solid #00000;
	padding-left:5px;
	padding-bottom:2px;
	padding-top:2px;
}
.td-6 {
	border-right: 1px solid #00000;
}
.indent-top {
	padding-top:10px;
	padding-left:5px;
}
.table-border {
	border-right: 1px solid #00000;
	border-left: 1px solid #00000;
}
tbody {
	height: 50%;
}
.side-left {
	padding-left: 10px;
}
.border-right {
	border-right: 1px solid #00000;
}
.border-left {
	border-left: 1px solid #00000;
}
.border-pe {
	padding-top:2px;
	padding-left:2px;
	border-left: 1px solid black;
	padding-bottom: 2px;
}
.padding-top {
	padding-top:5px;
}
.underlined {
	border-bottom: 1px solid black;
}
.systems {
	padding-left:10px;
	padding-top:2px;
}
</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellpadding=0>
	<tr>
		<td align=center><img src="../images/doc-header.jpg" /></td>
	</tr>
	<tr><td height=5></td></tr>
	<tr>
		<td width="100%" align=center><span style="font-weight: bold; font-size: 11pt; color: #000000;margin-top:10px;"><u>PHYSICAL EXAMINATION RECORD</u></span></td>
	</tr>
</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
<table width=100% cellpadding=5>
	<tr>
		<td width=20% align=center style="padding-top:-2px;">'.$patient_signature.'<br/>___________________________________________<br><b>SIGNATURE OF PATIENT</b></td>
		<td width=20% align=center style="padding-top:36px;"><b>'.$c['examin_d8'].'</b><br/>___________________________________________<br><b>DATE EXAMINED</b></td>
		<td width=50% align=center style="padding-top:-15px;">'.$docsignature.'<br/><b>'.$docfullname.',&nbsp;'.$docprefix.'&nbsp;- LIC No. '.$doclicenseno.'&nbsp;&nbsp;&nbsp;</b><br/>___________________________________________<br/>Medical Examiner&nbsp;</td>
	</tr>
</table>
<table width=100%>
	<tr><td align=left><barcode size=0.8 code="'.substr($d['trace_no'],0,10).'" type="C128A"></td><td align=right>Run Date: '.date('m/d/Y h:i:s a').'</td></tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" show-this-page="1" />

	mpdf-->
	<tbody>
	<table width=100% cellspacing=0 cellpadding=0>
		<tr>
			<td width=18% style="padding-top: 20px;">'.$photo.'</td>
			<td>
				<table width="100%" cellspacing=0 cellpadding=0>
					<tr><td height=10></td></tr>
					<tr>
						<td align=center style="font-size:8pt;" class="td-1"><b>'; if($_ihead['pe_type'] == 'APE') { $html .= 'Annual Physical Examination';} else { $html .= 'Pre-Employment';} $html .='</b></td>
					</tr>
				</table>
				<table width="100%" cellspacing=0 cellpadding=0>
					<tr>
						<td width=30% class="td-1">Last Name &nbsp;<b>'.$_ihead['lname'].'</b></td>
						<td width=25% class="td-1">First Name &nbsp;<b>'.$_ihead['fname'].'</b></td>
						<td width=22% class="td-1">Middle Name &nbsp;<b>'.$_ihead['mname'].'</b></td>
						<td width=23% class="td-1">Date &nbsp;<b>'.$resultDate.'</b></td>
					</tr>
				</table>
				<table width="100%" cellspacing=0 cellpadding=0>
					<tr>
						<td width=77% class="td-1">Address &nbsp;<b>'.$myaddress.'</b></td>
						<td width=23% class="td-1">Civil Status &nbsp;<b>'.$_ihead['civil_status'].'</b></td>
					</tr>
				</table>
				<table width="100%" cellspacing=0 cellpadding=0>
					<tr>
						<td width=30% class="td-1">Civil Status &nbsp;<b>'.$_ihead['civil_status'].'</b></td>
						<td width=25% class="td-1">Age &nbsp;:<b>'.$_ihead['age'].'</b></td>
						<td width=22% class="td-1">Occupation &nbsp;:<b>'.$_ihead['occupation'].'</b></td>
						<td width=23% class="td-1">Sex &nbsp;:<b>'.$_ihead['gender'].'</b></td>
					</tr>
				</table>
				<table width="100%" cellspacing=0 cellpadding=0>
					<tr>
						<td width=30% class="td-1">Place of Birth &nbsp;<b>'.$_ihead['birthplace'].'</b></td>
						<td width=25% class="td-1">Date of Birth &nbsp;:<b>'.$_ihead['date1'].', '.$_ihead['date2'].'</b></td>
						<td width=45% class="td-1">Insurance Provider &nbsp;</td>
					</tr>
				</table>
				<table width="100%" cellspacing=0 cellpadding=0>
					<tr>
						<td width=55% class="td-1">Name of Company &nbsp;<b>'.$_ihead['employer'].'</b></td>
						<td width=45% class="td-1">Tel./Mobile no. &nbsp;:<b>'.$_ihead['mobile_no'].'</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<table width="100%" cellspacing=0 cellpadding=0 style="padding-top:8px;padding-bottom:8px;">
		<tr>
			<td width="100%" align=center><span style="font-weight: bold; font-size: 11pt; color: #000000;padding-top:10px;">PHYSICAL EXAMINATION</span></td>
		</tr>
	</table>
	<table width="100%" cellspacing=0 cellpadding=0 style="border-top: 1px solid black;">
		<tr>
			<td width=17% class="border-pe">Temp <sup>o</sup>C</td>
			<td width=17% class="border-pe">Height</td>
			<td width=17% class="border-pe">Weight</td>
			<td width=17% class="border-pe">Blood Pressure</td>
			<td width=17% class="border-pe">Pulse rate</td>
			<td class="border-pe border-right">Respiratory rate</td>
		</tr>
		<tr>
			<td width=17% align=center class="border-pe td-4"><b>'.$_ihead['temp'].' <sup>o</sup>C</b></td>
			<td width=17% align=center class="border-pe td-4"><b>'.$_ihead['ht'].' cm</b></td>
			<td width=17% align=center class="border-pe td-4"><b>'.number_format($_ihead['wt']).' kg</b></td>
			<td width=17% align=center class="border-pe td-4"><b>'.$_ihead['bp'].' mmHg</b></td>
			<td width=16% align=center class="border-pe td-4"><b>'.$_ihead['pulse'].' bpm</b></td>
			<td align=center class="border-pe td-4 border-right"><b>'.$_ihead['rr'].' bpm</b></td>
		</tr>
	</table>
	<table width="100%" cellspacing=0 cellpadding=0>
		<tr>
			<td width=24% class="border-pe">VISUAL ACUITY</td>
			<td width=10% class="border-pe">With Glasses</td>
			<td class="border-pe">BMI kg/m2</td>
			<td class="border-pe">Ishihara Test</td>
			<td class="border-pe border-right">BMI CATEGORY</td>
		</tr>
		<tr>
			<td width=24% align=center class="border-pe td-4"><b>R<u>&nbsp;'.$_ihead['righteye'].'&nbsp; </u>&nbsp;&nbsp; L<u>&nbsp;'.$_ihead['lefteye'].'&nbsp;</u></b><br><i>with or without glasses</i></td>';
			if($_ihead['with_glasses'] == 'Y') {
				$html .= '<td width=10% align=center class="border-pe td-4"><b>YES</b></td>';
			}else {
				$html .= '<td width=10% align=center class="border-pe td-4"><b>&nbsp;</b></td>';
			}
				$html .= '<td width=34% align=center class="border-pe td-4"><b>'.$_ihead['bmi'].'</b></td>
			<td align=center class="border-pe td-4"><b></b></td>
			<td align=center class="border-pe td-4 border-right"><b>'.$_ihead['bmi_category'].'</b></td>
		</tr>
	</table>
	<table width="100%" cellspacing=0 cellpadding=0 style="padding-top:8px;padding-bottom:8px;">
		<tr>
			<td width="100%" align=center><span style="font-weight: bold; font-size: 11pt; color: #000000;padding-top:10px;">MEDICAL HISTORY</span></td>
		</tr>
	</table>
	<table width="100%" cellspacing=0 cellpadding=0>
		<tr>
			<td align=left class="indent-top">Past Medical History:</td>
			<td align=left width=78% style="border-bottom: 1px solid black;"><b>'.$pmstring.'&nbsp;'.$_ihead['pm_others'].'</b></td>
		</tr>
		<tr>
			<td align=left class="indent-top">Family History:</td>
			<td align=left width=78% style="border-bottom: 1px solid black;"><b>'. $_ihead['fm_history'] . '</b></td>
		</tr>
		<tr>
			<td align=left class="indent-top">Previous Hospitalization:</td>
			<td align=left width=78% style="border-bottom: 1px solid black;"><b>'. $_ihead['pv_hospitalization'] . '</b></td>
		</tr>
	</table>
	<table width="100%" cellspacing=0 cellpadding=0>
		<tr>
			<td width=15% class="border-left" align=left class="indent-top">Menstrual History:</td>
			<td width=10% align=center width=10% style="border-bottom: 1px solid black;"><b>'.$_ihead['mens_history'].'</b></td>
			<td width=10% align=right class="indent-top" style="padding-left:15px;">Parity</td>
			<td width=10% align=center width=10% style="border-bottom: 1px solid black;"><b>'.$_ihead['parity'].'</b></td>
			<td width=10% align=right class="indent-top">LMP:</td>
			<td width=10% align=center style="border-bottom: 1px solid black;"><b>'.$_ihead['lmp'].'</b></td>
			<td width=15% align=right class="indent-top">Contraceptive Use:</td>
			<td width=10% align=center width=15% style="border-bottom: 1px solid black;"><b>'.$_ihead['contraceptives'].'</b></td>
		</tr>
		<tr><td height=10></td></tr>
	</table>	
	<table width=100% align=left cellspacing=0 cellpadding=0 class="border-right">
		<tr>
			<td width=15% class="border-pe td-0" align=center><b>Review of Systems<b/></td>
			<td width=15% class="border-pe td-0" align=center><b>STATUS</b></td>
			<td width=20% class="border-pe td-0" align=center><b>FINDINGS</b></td>
			<td width=15% class="border-pe td-0" align=center><b>Review of Systems</b></td>
			<td width=15% class="border-pe td-0" align=center><b>STATUS</b></td>
			<td width=20% class="border-pe td-0" align=center><b>FINDINGS</b></td>
		</tr>
	</table>
	<table width=100% align=left cellspacing=0 cellpadding=0 class="border-right">
		<tr>
			<td width=15% align=left class="border-pe td-0 systems">Head, Scalp</td>';
			if($_ihead['hs_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['hs_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['hs_findings'].'</b></td>
			<td width=15% align=left class="border-pe td-0 systems">Lungs</td>';
			if($_ihead['lungs_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['lungs_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['lungs_findings'].'</b></td>
		</tr>
		<tr>
			<td width=15% align=left class="border-pe td-0 systems">Eyes & Ears</td>';
			if($_ihead['ee_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['ee_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['ee_findings'].'</b></td>
			<td width=15% align=left class="border-pe td-0 systems">Heart</td>';
			if($_ihead['heart_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['heart_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['heart_findings'].'</b></td>
		</tr>
		<tr>
			<td width=15% align=left class="border-pe td-0 systems">Skin / Allergy</td>';
			if($_ihead['sa_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['sa_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['sa_findings'].'</b></td>
			<td width=15% align=left class="border-pe td-0 systems">Abdomen</td>';
			if($_ihead['abdomen_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['abdomen_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['abdomen_findings'].'</b></td>
		</tr>
		<tr>
			<td width=15% align=left class="border-pe td-0 systems">Nose & Sinuses</td>';
			if($_ihead['nose_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['nose_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['nose_findings'].'</b></td>
			<td width=15% align=left class="border-pe td-0 systems">Genitals</td>';
			if($_ihead['genitals_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['genitals_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['genitals_findings'].'</b></td>
		</tr>
		<tr>
			<td width=15% align=left class="border-pe td-0 systems">Mouth/Teeth/Tongue</td>';
			if($_ihead['mouth_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['mouth_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['mouth_findings'].'</b></td>
			<td width=15% align=left class="border-pe td-0 systems">Extremities</td>';
			if($_ihead['extr_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['extr_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['extr_findings'].'</b></td>
		</tr>
		<tr>
			<td width=15% align=left class="border-pe td-0 systems">Neck / Nodes</td>';
			if($_ihead['neck_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['neck_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['neck_findings'].'</b></td>
			<td width=15% align=left class="border-pe td-0 systems">Reflexes</td>';
			if($_ihead['ref_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['ref_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['ref_findings'].'</b></td>
		</tr>
		<tr>
			<td width=15% align=left class="border-pe td-0 systems">Check / Breast</td>';
			if($_ihead['check_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['check_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['check_findings'].'</b></td>
			<td width=15% align=left class="border-pe td-0 systems">BPE</td>';
			if($_ihead['bpe_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['bpe_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['bpe_findings'].'</b></td>
		</tr>
		<tr>
			<td width=15% align=left class="border-pe td-0 systems underlined">&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td width=15% align=center class="border-pe td-0 underlined">&nbsp;</td>
			<td width=20% align=left class="border-pe td-0 underlined"></td>
			<td width=15% align=left class="border-pe td-0 systems underlined">Rectal</td>';
			if($_ihead['rect_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0 underlined"><b>NORMAL</b></td>';
			}else if($_ihead['rect_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0 underlined"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0 underlined"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0 underlined"><b>'.$_ihead['rect_findings'].'</b></td>
		</tr>
		<tr><td height=5></td></tr>
	</table>
	<table width=100% align=left cellspacing=0 cellpadding=0 class="border-right">
		<tr>
			<td width=15% class="border-pe td-0" align=center><b>LABORATORY<b/></td>
			<td width=15% class="border-pe td-0" align=center><b>STATUS</b></td>
			<td width=20% class="border-pe td-0" align=center><b>FINDINGS</b></td>
			<td width=15% class="border-pe td-0" align=center><b>Review of Systems</b></td>
			<td width=15% class="border-pe td-0" align=center><b>STATUS</b></td>
			<td width=20% class="border-pe td-0" align=center><b>FINDINGS</b></td>
		</tr>
	</table>
	<table width=100% align=left cellspacing=0 cellpadding=0 class="border-right">
		<tr>
			<td width=15% align=left class="border-pe td-0 systems">Chest X-Ray</td>';
			if($_ihead['chest_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['chest_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['chest_findings'].'</b></td>

			<td width=15% align=left class="border-pe td-0 systems">ECG</td>';
			if($_ihead['ecg_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['ecg_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['ecg_findings'].'</b></td>
		</tr>
		<tr>
			<td width=15% align=left class="border-pe td-0 systems">CBC</td>';
			if($_ihead['cbc_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['cbc_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['cbc_findings'].'</b></td>
			<td width=15% align=left class="border-pe td-0 systems">Other Procedures</td>
			<td width=15% align=center class="border-pe td-0"><b>&nbsp;</b></td>
			<td width=20% align=left class="border-pe td-0"><b>&nbsp;</b></td>
		</tr>
		<tr>
			<td width=15% align=left class="border-pe td-0 systems">Urinalysis</td>';
			if($_ihead['ua_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['ua_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['ua_findings'].'</b></td>

			<td width=15% align=left class="border-pe td-0 systems">'.$_ihead['others1_name'].'</td>';
			if($_ihead['others1_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['others1_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else if($_ihead['others1_normal'] == '') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>&nbsp;</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['others1_findings'].'</b></td>
		</tr>
		<tr>
			<td width=15% align=left class="border-pe td-0 systems">Fecalysis</td>';
			if($_ihead['se_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['se_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['se_findings'].'</b></td>

			<td width=15% align=left class="border-pe td-0 systems">'.$_ihead['others2_name'].'</td>';
			if($_ihead['others2_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>NORMAL</b></td>';
			}else if($_ihead['others2_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>With Findings</b></td>';
			}else if($_ihead['others2_normal'] == '') {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>&nbsp;</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0"><b>'.$_ihead['others2_findings'].'</b></td>
		</tr>
		<tr>
			<td width=15% align=left class="border-pe td-0 systems underlined">Drug Test</td>';
			if($_ihead['dt_normal'] == 'Y') {
$html .=	'<td width=15% align=center class="border-pe td-0 underlined"><b>POSITIVE</b></td>';
			}else if($_ihead['dt_normal'] == 'N') {
$html .=	'<td width=15% align=center class="border-pe td-0 underlined"><b>NEGATIVE</b></td>';
			}else {
$html .=	'<td width=15% align=center class="border-pe td-0 underlined"><b>N/A</b></td>';
			}
$html .=	'<td width=20% align=left class="border-pe td-0 underlined"><b>'.$_ihead['dt_findings'].'</b></td>
			<td width=15% align=left class="border-pe td-0 systems underlined">&nbsp;</td>
			<td width=15% align=center class="border-pe td-0 underlined"><b>&nbsp;</b></td>
			<td width=20% align=left class="border-pe td-0 underlined"><b>&nbsp;</b></td>
		</tr>
	</table>
	<table width="100%" cellspacing=0 cellpadding=0 style="padding-top: 10px;">
		<tr>
			<td align=left>I Hereby Certify that I have examined and found the employee to be '.$_ihead['pe_fit'].' for employment.</td>
		</tr>
		<tr>
			<td>Classification:</td>
		</tr>
		<tr><td height=5></td></tr>
	</table>
	<table width="100%" cellspacing=0 cellpadding=0>';
		if($_ihead['classification'] == 'A') {
		$html .= '<tr>
				<td style="padding-left:40px;"><b>Class "A"</b>&nbsp;&nbsp;&nbsp;&nbsp; = Physically fit for all types of work.</td>';
		$html .= '</tr>';

		} else if($_ihead['classification'] == 'B') {
		$html .= '<tr>
					<td style="padding-left:40px;"><b>Class "B" </b>&nbsp;&nbsp;&nbsp;&nbsp; = Physically fit for all types of work.<br/>
					Have minor ailments or defect.Easily curable or offers no handicap to job applied.<br/>
					Needs treatment/ correction:<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.strtoupper($_ihead['class_b_remarks1']).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u><br>
					Treatment optional for:<u> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.strtoupper($_ihead['class_b_remarks2']).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></td>';
		$html .= '</tr>';

		}else if($_ihead['classification'] == 'C') {
	$html .='<tr>
	
			 <td style="padding-left:40px;"><b>Class "C"</b>&nbsp;&nbsp;&nbsp;&nbsp; = Physically fit for less strenuous type of work. Has minor ailment/s or defect/s.<br/>
			 	 Easily curable or offers no handicap to job applied.<br/>
			 	 Needs treatment / correction: <u> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.strtoupper($_ihead['class_c_remarks1']).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u><br />
			  	 Treatment optional for:<u> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.strtoupper($_ihead['class_c_remarks2']).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></td>
			 
			 </tr>';
		} else if($_ihead['classification'] == 'D') {
			$html .= '<tr>
			<td style="padding-left:40px;"><b>Class "D"</b>&nbsp;&nbsp;&nbsp;&nbsp; = Employment at the risk and discretion of the management.</td>';
	$html .= '</tr>';


	}else if($_ihead['classification'] == 'E') {
		$html .= '<tr>
		<td style="padding-left:40px;"><b>Class "E"</b>&nbsp;&nbsp;&nbsp;&nbsp; = Unfit for Employment.</td>';
	$html .= '</tr>';
	}else if($_ihead['classification'] == 'PENDING') {
		$html .= '<tr>
		<td style="padding-left:40px;"><b>Classification: PENDING</b>&nbsp;&nbsp;&nbsp;&nbsp;<br />
		For further evaluation of:<u> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.strtoupper($_ihead['pending_remarks']).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></td>';
	$html .= '</tr>';
	}else {
		$html .= '<tr>
		<td style="padding-left:40px;">&nbsp;&nbsp;&nbsp;&nbsp;<br />';
		$html .= '</tr>';
	}
	$html .=	'</tr>

	</table>
	<table width="100%" cellspacing=0 cellpadding=0>
	<tr><td height=5></td></tr>
		<tr>
			<td align=left class="indent-top">Remarks:</td>
			<td align=left width=90% style="border-bottom: 1px solid black;margin-left:10px;"><b>'. strtoupper($_ihead['overall_remarks']) . '</b></td>
		</tr>
		<tr><td height=15></td></tr>
	</table>
	</tbody>
</body>
</html>
';

$html = html_entity_decode($html);
$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;

mysql_close($con);
?>