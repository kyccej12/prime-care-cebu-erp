<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");

	$_ihead = $con->getArray("SELECT LPAD(b.so_no,6,0) AS myso, a.pid, trans_id, DATE_FORMAT(a.so_date,'%m/%d/%Y') AS sdate, b.patient_name, b.patient_address, IF(c.gender='M','Male','Female') AS gender, c.gender AS xgender, FLOOR(DATEDIFF(a.so_date,c.birthdate)/364.25) AS age, b.physician, d.patientstatus,a.created_by, CONCAT(UCASE(RIGHT(b.trace_no,5)),LPAD(b.so_no,8,'0')) AS barcode FROM consultation_form a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_patientstat d ON b.patient_stat = d.id WHERE a.so_no = '$_REQUEST[sono]' AND a.pid = '$_REQUEST[pid]'  AND a.trans_id = '$_REQUEST[transid]';");
     $b = $con->getArray("SELECT *, prescription1, prescription2, prescription3, prescription4, prescription5, sig FROM consultation_form WHERE so_no = '$_ihead[myso]' AND pid = '$_ihead[pid]'  AND trans_id = '$_ihead[trans_id]';"); 

/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','LEETER-H','','',0,0,90,0,0,0);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("PORT80 Solutions");

$mpdf->SetWatermarkImage ('../images/logo-small.png',0.05,'F','P');
$mpdf->showWatermarkImage = true;

$mpdf->SetDisplayMode(50);

$html = '
<html>
<head>
	<style>
		body {font-family: "Times New Roman", Times, serif; font-size: 10pt; }
        .itemHeader {
            padding:5px;border:1px solid black; text-align: center; font-weight: bold;
        }

        .itemResult {
            padding:20px;border:1px solid black;text-align: center;
        }

        #items td { border: 1px solid; text-align: center; }

        span { font-family: "Times New Roman", Times, serif; }
	</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellspacing=5>
	<tr><td align=center><img src="../images/doc-header.jpg" /></td></tr>
</table>
<table width=90% align=center cellpadding=2 cellspacing=0 style="font-size: 10pt;margin-top:20px;">
	<tr>
		<td width=20%></td>
		<td></td>
		<td width=20%><b>Date</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['sdate'].'</td>
	</tr>
	<tr>
		<td><b>Patient Name</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['patient_name'].'</td>
		<td><b>Age</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['age'].'</td>
	</tr>
	<tr>
		<td><b>Patient Address</b></td>
		<td>:&nbsp;&nbsp;' . $_ihead['patient_address'] . '</td>
		<td><b>Gender</b></td>
		<td>:&nbsp;&nbsp;'.$_ihead['gender'].'</td>
	</tr>
	<tr>
		<td style="height="15px;" colspan=4></td>
	</tr>
	<tr>
		<td style="height="15px;" colspan=4><hr></td>
	</tr>
    <tr>
        <td style="font-weight: bold; font-size: 40px;">Rx</td>
   </tr>
</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
<table width=50% cellpadding=0 cellspacing=5 align=right style="margin-top: 15px; margin-right: 60px; padding:bottom: 10px;">
	<tr>  
     <td align=center colspan=3>________________________________________________________</td>   
   </tr>
	<tr>
     <td align=left width=25%>License No.</td>   
     <td align=right>:</td>   
     <td align=right>______________________________________</td>   
   </tr>
   <tr>
     <td align=left>PTR No.</td>   
     <td align=right>:</td>   
     <td align=right>______________________________________</td>   
   </tr>  
   <tr>
     <td align=left>S2 No.</td>   
     <td align=right>:</td>   
     <td align=right>______________________________________</td>   
   </tr>  
</table>
<table width=100% cellpadding=5>
	<tr><td align=center><img src="../images/doc-footer.png" /></td></tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->

<table width=80% cellpadding=0 cellspacing=5 align=center style="font-size:10pt;">
   <tr>
        <td>'. $b['prescription1'].'</td>
   </tr>
   <tr>
		<td height=5></td>
   </tr>
    <tr>
        <td>'. $b['prescription2'].'</td>
   </tr>
   <tr>
		<td height=5></td>
   </tr>
    <tr>
        <td>'. $b['prescription3'].'</td>
   </tr>
   <tr>
		<td height=5></td>
   </tr>
    <tr>
        <td>'. $b['prescription4'].'</td>
   </tr>
   <tr>
		<td height=5></td>
   </tr>
    <tr>
        <td>'. $b['prescription5'].'</td>
   </tr>
   <tr>
		<td height=5></td>
   </tr>
    <tr>
        <td>SIG&nbsp;:&nbsp;&nbsp;'. $b['sig'].'</td>
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