<?php
	session_start();
   // ini_set("display_errors","On");
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");

	$_ihead = $con->getArray("SELECT lpad(b.so_no,6,0) as myso, DATE_FORMAT(result_date,'%m/%d/%Y') AS rdate, b.patient_name, b.patient_address, IF(c.gender='M','Male','Female') AS gender, c.gender as xgender, FLOOR(DATEDIFF(so_date,c.birthdate)/364.25) AS age, b.physician, d.patientstatus,a.serialno,a.created_by, CONCAT(UCASE(RIGHT(b.trace_no,5)),LPAD(b.so_no,8,'0')) AS barcode FROM lab_cbcresult a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id left join options_patientstat d on b.patient_stat = d.id WHERE a.so_no = '$_REQUEST[so_no]' AND serialno = '$_REQUEST[serialno]'  AND a.branch = '$_SESSION[branchid]';");
    $b = $con->getArray("SELECT * FROM lab_cbcresult WHERE so_no = '$_ihead[myso]' AND serialno = '$_ihead[serialno]' AND branch = '$_SESSION[branchid]';");

    if($b['verified_by'] != '') {
        list($medtechSignature,$medtechFullname,$medtechLicense,$medtechRole) = $con->getArray("SELECT if(signature_file != '',concat('<img src=\"../images/signatures/',signature_file,'\" align=absmiddle />'),'<img src=\"../images/signatures/blank.png\" align=absmiddle />') as signature, fullname, license_no, role from user_info where emp_id = '$b[verified_by]';");
    }

/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','letter','','',10,10,98,30,10,10);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("PORT80 Solutions");
//$mpdf->showImageErrors = true;

if($b['verified'] != 'Y') {
	$mpdf->SetWatermarkText('FOR VALIDATION');
	$mpdf->showWatermarkText = true;
}

$mpdf->SetDisplayMode(50);

$html = '
<html>
<head>
	<style>
		body {font-family: sans-serif; font-size: 10pt; }
        .itemHeader {
            padding:5px;border:1px solid black; text-align: center; font-weight: bold;
        }

        .itemResult {
            padding:20px;border:1px solid black;text-align: center;
        }

        #items td { border: 1px solid; text-align: center; }
	</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellspaing=0>
	<tr><td align=center><img src="../images/doc-header.jpg" /></td></tr>

    <tr>
		<td width="100%" style="padding-top: 30px;" align=center>
			<span style="font-weight: bold; font-size: 12pt; color: #000000;">LABORATORY DEPARTMENT</span>
		</td>
	</tr>

</table>
<table width=100% cellpadding=2 cellspacing=0 style="font-size: 10pt;margin-top:20px;">
	<tr>
		<td width=100% colspan=4 style="background-color: #cdcdcd; border-top: 1px solid black; border-bottom: 1px solid black;" align=center><b>PATIENT INFORMATION</b></td>
	</tr>
	<tr>
		<td width=25%><b>SO NO.</b></td>
		<td width=38%>:&nbsp;&nbsp;'.$_ihead['myso'].'</td>
		<td width=20%><b>DATE</b></td>
		<td width=17%>:&nbsp;&nbsp;'.$_ihead['rdate'].'</td>
	</tr>
	<tr>
		<td><b>PATIENT NAME</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['patient_name'].'</td>
		<td><b>GENDER</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['gender'].'</td>
	</tr>
	<tr>
		<td><b>PATIENT ADDRESS</b></td>
		<td>:&nbsp;&nbsp;' . $_ihead['patient_address'] . '</td>
		<td><b>AGE</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['age'].'</td>
	</tr>
	<tr>
		<td><b>REQUESTING PHYSICIAN</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['physician'].'</td>
		<td><b>PATIENT STATUS</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['patientstatus'].'</td>
	</tr>
    <tr>
        <td width="100%" colspan=4 style="padding-top: 15px;" align=center>
         <span style="font-weight: bold; font-size: 12pt; color: #000000;">COMPLETE BLOOD COUNT (CBC)</span>
        </td>
    </tr>
</table>

</htmlpageheader>

<htmlpagefooter name="myfooter">
<table width=100% cellpadding=5>
	<tr>
		<td align=center valign=top>'.$medtechSignature.'<br/><b>'.$medtechFullname.'<br/>___________________________________________<br>'.$medtechRole.'<br/>License No. '.$medtechLicense.'</b></td>
		<td align=center valign=top><img src="../images/signatures/psa-signature.png" align=absmidddle /><br/><b>PETER S. AZNAR, M.D, F.P.S.P<br/>___________________________________________<br><b>PATHOLOGIST</b><br><span style="font-size: 7pt;">PRC LICENSE NO. 72410</span></td>
	</tr>
    <tr><td align=left><barcode size=0.8 code="'. $_ihead['barcode'] . '" type="C128A"></td><td align=right>Date & Time Printed : '.date('m/d/Y h:i:s a').'</td></tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->

<table width=80% cellpadding=0 cellspacing=5 align=center>
    <tr>
        <td align="left" width=30%></td>
        <td align=center width=30%></td>
        <td align="left" width=40% style="padding-left: 15px;"><b>NORMAL VALUES</b></td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">WBC '.$con->checkCBCValues($_ihead['age'],$_ihead['xgender'],"WBC",$b['wbc'],$b['machine']).'</td>
        <td align=center style="border-bottom: 1px solid black;vertical-align: top;">'. number_format($b['wbc']) . '</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($_ihead['age'],$_ihead['xgender'],"WBC",$b['machine']).'</td>	
    </tr>

    <tr>
        <td align="left" style="padding-left: 15px;" valign=top>RBC '.$con->checkCBCValues($_ihead['age'],$_ihead['xgender'],"RBC",$b['rbc'],$b['machine']).'</td>
        <td align=center style="border-bottom: 1px solid black;" valign=top>'. $b['rbc'] . '</td>
        <td align="left" style="padding-left: 15px;" valign=top>'.$con->getCBCAttribute2($_ihead['age'],$_ihead['xgender'],"RBC",$b['machine']).'</td>	
    </tr>

    <tr>
        <td align="left" style="padding-left: 15px;">Hemoglobin '.$con->checkCBCValues($_ihead['age'],$_ihead['xgender'],"HEMOGLOBIN",$b['hemoglobin'],$b['machine']).'</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['hemoglobin'] . '</td>
        <td align="left" style="padding-left: 15px;">'. $con->getCBCAttribute2($_ihead['age'],$_ihead['xgender'],"HEMOGLOBIN",$b['machine']) . '</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">Hematocrit '.$con->checkCBCValues($_ihead['age'],$_ihead['xgender'],"HEMATOCRIT",$b['hematocrit'],$b['machine']).'</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['hematocrit'] . '</td>
        <td align="left" style="padding-left: 15px;">'. $con->getCBCAttribute2($_ihead['age'],$_ihead['xgender'],"HEMATOCRIT",$b['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">MCV '.$con->checkCBCValues($_ihead['age'],$_ihead['xgender'],"MCV",$b['mcv'],$b['machine']).'</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['mcv'] . ' </td>
        <td align="left" style="padding-left: 15px;">'. $con->getCBCAttribute2($_ihead['age'],$_ihead['xgender'],"MCV",$b['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">MCH '.$con->checkCBCValues($_ihead['age'],$_ihead['xgender'],"MCH",$b['mch'],$b['machine']).'</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['mch'] . ' </td>
        <td align="left" style="padding-left: 15px;">'. $con->getCBCAttribute2($_ihead['age'],$_ihead['xgender'],"MCH",$b['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">MCHC '.$con->checkCBCValues($_ihead['age'],$_ihead['xgender'],"MCHC",$b['mchc'],$b['machine']).'</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['mchc'] . ' </td>
        <td align="left" style="padding-left: 15px;">'. $con->getCBCAttribute2($_ihead['age'],$_ihead['xgender'],"MCHC",$b['machine']).'</td>	
    </tr>
    <tr><td height=5>&nbsp;</td></tr>
    <tr>
        <td align="left" colspan=3  style="padding-left: 15px;"><b>Differential Count&nbsp;:</b></td>
    </tr>
    <tr>
        <td align="left" style="padding-left: 35px;">Neutrophils '.$con->checkCBCValues($_ihead['age'],$_ihead['xgender'],"NEUTROPHILS",$b['neutrophils'],$b['machine']).'</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['neutrophils'] . '</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($_ihead['age'],$_ihead['xgender'],"NEUTROPHILS",$b['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 35px;">Lymphocytes '.$con->checkCBCValues($_ihead['age'],$_ihead['xgender'],"LYMPHOCYTES",$b['lymphocytes'],$b['machine']).'</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['lymphocytes'] . '</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($_ihead['age'],$_ihead['xgender'],"LYMPHOCYTES",$b['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 35px;">Monocytes '.$con->checkCBCValues($_ihead['age'],$_ihead['xgender'],"MONOCYTES",$b['monocytes'],$b['machine']).'</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['monocytes'] . '</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($_ihead['age'],$_ihead['xgender'],"MONOCYTES",$b['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 35px;">Eosinophils '.$con->checkCBCValues($_ihead['age'],$_ihead['xgender'],"EOSINOPHILS",$b['eosinophils'],$b['machine']).'</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['eosinophils'] . '</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($_ihead['age'],$_ihead['xgender'],"EOSINOPHILS",$b['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 35px;">Basophils '.$con->checkCBCValues($_ihead['age'],$_ihead['xgender'],"BASOPHILS",$b['basophils'],$b['machine']).'</td>
        <td align=center style="border-bottom: 1px solid black;">'. $b['basophils'] . '</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($_ihead['age'],$_ihead['xgender'],"BASOPHILS",$b['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;">Platelet Count '.$con->checkCBCValues($_ihead['age'],$_ihead['xgender'],"PLATELATE",$b['platelate'],$b['machine']).'</td>
        <td align=center style="border-bottom: 1px solid black;">'. number_format($b['platelate']) . '</td>
        <td align="left" style="padding-left: 15px;">'.$con->getCBCAttribute2($_ihead['age'],$_ihead['xgender'],"PLATELATE",$b['machine']).'</td>	
    </tr>
    <tr>
        <td align="left" style="padding-left: 15px;" valign=top>Remarks</td>
        <td align=left colspan=2 style="border-bottom: 1px solid black;">'. $b['remarks'] . '</td>
    </tr>
</table>

</body>
</html>
';

$html = html_entity_decode($html);
$mpdf->WriteHTML($html);
$mpdf->Output();
exit;

?>