<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");

	$_ihead = $con->getArray("SELECT LPAD(b.so_no,6,0) AS myso, a.pid, trans_id, DATE_FORMAT(a.so_date,'%m/%d/%Y') AS sdate, b.patient_name, b.patient_address, IF(c.gender='M','Male','Female') AS gender, c.gender AS xgender, FLOOR(DATEDIFF(a.so_date,c.birthdate)/364.25) AS age, b.physician, d.patientstatus,a.created_by, CONCAT(UCASE(RIGHT(b.trace_no,5)),LPAD(b.so_no,8,'0')) AS barcode FROM consultation_form a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_patientstat d ON b.patient_stat = d.id WHERE a.so_no = '$_REQUEST[sono]' AND a.pid = '$_REQUEST[pid]'  AND a.trans_id = '$_REQUEST[transid]';");
    $_idetails = $con->dbquery("SELECT so_no, pid, branch, code, `description`, unit FROM consult_labrequest where so_no = '$_REQUEST[sono]' AND pid = '$_REQUEST[pid]';");

/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','FOLIO-N','','',0,0,75,0,0,0);
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
		body {font-family: "Times New Roman", Times, serif; font-size: 8pt; }
        td { vertical-align: top; }

        table thead td { 
            border-top: 0.1mm solid #000000;
            border-bottom: 0.1mm solid #000000;
            /* background-color: #EEEEEE; */
            text-align: center;
        }

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

        span { font-family: "Times New Roman", Times, serif; }
	</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellspacing=5>
	<tr><td align=center><img src="../images/doc-header.jpg" /></td></tr>
</table>
<table width=90% align=center cellpadding=2 cellspacing=0 style="font-size: 8pt;margin-top:20px;">
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
<table width=90% align=center cellpadding=2 cellspacing=0 style="margin-top:20px;">
    <tr>
	    <td align=center style="font-size: 15px; font-weight:bold;">LAB REQUEST FORM</td>
    </tr>
</table>

</htmlpageheader>

<htmlpagefooter name="myfooter">
<table width=50% cellpadding=0 cellspacing=5 align=right style="margin-top: 15px; margin-right: 60px; padding:bottom: 10px;">
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

<table align=center class="items" width=90% style="font-size: 8pt; border-collapse: collapse;" cellpadding="2">
    <thead>
            <tr>
                <td width="10%" align=left><b>CODE</b></td>
                <td width="50%" align=left><b>PARTICULARS/PROCEDURE</b></td>
                <td width="15%" align=center><b>UNIT</b></td>
            </tr>
	</thead>
    <tbody>';
        $i = 0;
        while($row = $_idetails->fetch_array()) {


            list($cat) = $con->getArray("select category from services_master where `code` = '$row[code]';");
            if($cat == 6) { list($subdescription) = $con->getArray("select concat('<br/>&raquo;',fulldescription) as subdescription from services_master where `code` = '$row[code]';"); } else { $subdescription = ''; }

            $html.= '<tr>
                    <td align=left>'.$row['code'].'</td>
                    <td align=left>' . $row['description'] . $subdescription . '</td>
                    <td align="center">' . $row['unit'] . '</td>
                    </tr>'; $i++;
            
            }
        $html = $html .  '
    </tbody>
</table>
</body>
</html>
';

$html = html_entity_decode($html);
$mpdf->WriteHTML($html);
$mpdf->Output();
exit;

?>