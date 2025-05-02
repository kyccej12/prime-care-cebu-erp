<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");

	$_ihead = $con->getArray("SELECT LPAD(b.so_no,6,0) AS myso, a.pid, trans_id, DATE_FORMAT(a.so_date,'%m/%d/%Y') AS sdate, b.patient_name, b.patient_address, IF(c.gender='M','Male','Female') AS gender, c.gender AS xgender, FLOOR(DATEDIFF(a.so_date,c.birthdate)/364.25) AS age, b.physician, d.patientstatus,a.created_by, CONCAT(UCASE(RIGHT(b.trace_no,5)),LPAD(b.so_no,8,'0')) AS barcode FROM consultation_form a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_patientstat d ON b.patient_stat = d.id WHERE a.so_no = '$_REQUEST[sono]' AND a.pid = '$_REQUEST[pid]'  AND a.trans_id = '$_REQUEST[transid]';");
     $b = $con->getArray("SELECT * FROM consultation_form WHERE so_no = '$_REQUEST[sono]' AND pid = '$_REQUEST[pid]'  AND trans_id = '$_REQUEST[transid]';");


   // Function to convert number to ordinal format
     function getOrdinal($number) {
          $suffixes = ['th', 'st', 'nd', 'rd'];
          $mod100 = $number % 100;
     
          if ($mod100 >= 11 && $mod100 <= 13) {
          return $number . 'th';
          }
     
          $lastDigit = $number % 10;
          $suffix = $suffixes[($lastDigit < 4) ? $lastDigit : 0];
     
          return $number . $suffix;
     }
     
     // Check if sdate exists
     if (!isset($_ihead['sdate']) || empty($_ihead['sdate'])) {
          die("Error: sdate is missing from query result.");
     }
     
     $raw_date = $_ihead['sdate']; // Example: "03/29/2024"
     
     // Debugging: Check if explode() works properly
     $date_parts = explode("/", $raw_date);
     if (count($date_parts) !== 3) {
          die("Error: sdate format is invalid. Expected MM/DD/YYYY but got: " . $raw_date);
     }
     
     // Convert month, day, year
     $month = date("F", mktime(0, 0, 0, (int)$date_parts[0], 1)); // Convert month number to name
     $day = (int)$date_parts[1]; // Get day as an integer
     $year = $date_parts[2]; // Get year
     
     // Convert day to ordinal
     $ordinal_day = getOrdinal($day);
     
     // Final formatted date: "21st of April 2025"
     $formatted_date = "{$ordinal_day} of {$month} {$year}";
     
 
 

/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','letter','','',0,0,48,0,0,0);
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
		body {font-family: "Times New Roman", Times, serif; font-size: 11pt; }
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

    <tr>
		<td width="100%" style="padding-top: 10px;" align=center>
			<span style="font-weight: bold; font-size: 14pt; color: #000000;">MEDICAL CERTIFICATE</span>
		</td>
	</tr>

</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
<table width=100% cellpadding=5>
	<tr><td align=center><img src="../images/doc-footer.png" /></td></tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->

<table width=85% cellpadding=0 cellspacing=5 align=center>
   <tr>
        <td>This is to certify that Mr. / Ms. / Mrs. <u>'. $_ihead['patient_name'] .'</u>, '. $_ihead['age'] .' years old, '. $_ihead['gender'] .' was seen and examined by the undersigned for the following:</td>
   </tr>
</table>
<table width=85% cellpadding=0 cellspacing=5 align=center style="margin-top: 10px; border: 1px solid #000; height:150px;">
   <tr>
        <td align=left style="padding: 10px;">COMPLAINTS</td>
   </tr>
   <tr>
        <td align=left style="height:50px; padding-left: 10px;">'.$b['complaints'].'</td>
   </tr>
</table>
<table width=85% cellpadding=0 cellspacing=5 align=center style="margin-top: 10px; border: 1px solid #000;">
   <tr>
        <td align=left style="padding: 10px;">DIAGNOSIS</td>
   </tr>
   <tr>
        <td align=left style="height:50px; padding-left: 10px;">'.$b['diagnosis'].'</td>
   </tr>
</table>
<table width=85% cellpadding=0 cellspacing=5 align=center style="margin-top: 10px; border: 1px solid #000;">
   <tr>
        <td align=left style="padding: 10px;">TREATMENT</td>
   </tr>
   <tr>
        <td align=left style="height:50px; padding-left: 10px;">'.$b['treatment'].'</td>
   </tr>
</table>
<table width=85% cellpadding=0 cellspacing=5 align=center style="margin-top: 10px; padding:bottom: 10px; border: 1px solid #000;">
   <tr>
        <td align=left style="padding: 10px;">RECOMMENDATION</td>
   </tr>
   <tr>
        <td align=left style="height:45px; padding-left: 10px;">'.$b['recommendation'].'</td>
   </tr>
   <tr><td style="padding-top:5px;"><hr></td></tr>
   <tr>
     <td style="padding-left: 10px;">Inclusive Dates Excused&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ______________________________________________</td>
   </tr>
   <tr>
     <td style="padding-left: 10px;">Return to work on&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ______________________________________________</td>
  </tr>
</table>
<table width=85% cellpadding=0 cellspacing=5 align=center style="margin-top: 10px; padding:bottom: 10px;">
   <tr>
        <td align=left>This certification is not intended for legal purposes.</td>
   </tr>
   <tr>
     <td style="padding-top:15px;">Done this <u>&nbsp;&nbsp; ' .$formatted_date. '&nbsp;&nbsp;</u> at APM Central, A. Soriano St., Mabolo, Cebu City 6000. ________________________________, M.D.</td>
   </tr>
   
</table>
<table width=50% cellpadding=0 cellspacing=5 align=left style="margin-top: 15px; margin-left: 60px; padding:bottom: 10px;">
   <tr>
     <td align=left width=25%>License No.</td>   
     <td align=left>:</td>   
     <td align=left>___________________</td>   
   </tr>
   <tr>
     <td>PTR No.</td>   
     <td>:</td>   
     <td>___________________</td>   
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