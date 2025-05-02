<?php
    session_start();
	require_once("../lib/mpdf6/mpdf.php");
	require_once("../handlers/_generics.php");
    $now = date("m/d/Y h:i a");
    $con = new _init;
    $co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");

    $h = $con->getArray("SELECT *, DATE_FORMAT(birthdate,'%m/%d/%Y') AS bday FROM patient_info WHERE patient_id = '$_REQUEST[pid]';");
  
    $myaddress = '';
    list($brgy) = $con->getArray("SELECT brgyDesc FROM options_brgy WHERE brgyCode = '$h[brgy]';");
    list($ct) = $con->getArray("SELECT citymunDesc FROM options_cities WHERE cityMunCode = '$h[city]';");
    list($prov) = $con->getArray("SELECT provDesc FROM options_provinces WHERE provCode = '$h[province]';");

    if($row['street'] != '') { $myaddress.=$h['street'].", "; }
    if($brgy != "") { $myaddress.=$brgy.", "; }
    if($ct != "") { $myaddress.=$ct.", "; }
    if($prov != "")  { $myaddress.=$prov.", "; }
    $myaddress = substr($myaddress,0,-2);

    list($cstat) = $con->getArray("select civil_status from options_civilstatus where csid = '$h[cstat]';");

    $mpdf=new mPDF('win-1252','LETTER-H','','',10,10,10,10,10,10);
    $mpdf->use_embeddedfonts_1252 = true;    // false is default
    $mpdf->setAutoTopMargin='stretch';
    $mpdf->setAutoBottomMargin='stretch';
    $mpdf->use_kwt = true;
    $mpdf->SetProtection(array('print'));
    $mpdf->SetAuthor("Opon Medical Diagnostic Corporation");
    $mpdf->SetDisplayMode(100);

    $html = '<html>
                <head>
                    <title>Specimen Barcode</title>
                    <style>
                        body {
                            font-family: sans-serif;
                            font-size: 9pt;
                        }    
                    </style>
                </head>
                <body>

                    <table width="100%" cellpadding=0 cellspaing=0 align=center>
                        <tr>
                            <td style="color:#000000; padding-top: 5px;" valign=top align=center>
                                <span style="font-size: 9pt;"><b>'.$co['company_name'].'</b><br/>'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/><br/><b>PATIENT INFORMATION SLIP<br/>PATIENT ID: '.$_REQUEST['pid'].'</b></span>
                            </td>
                        </tr>
                    </table>
                    
                    <table width=100% cellpadding=2 cellspacing=0 style="margin-top: 20px;" align=center>
                        <tr>
                            <td width=20%>Patient Name: </td>
                            <td width=25% style="border-bottom: 0.1mm solid black;">'.$h['lname'].'</td>
                            <td width=25% style="border-bottom: 0.1mm solid black;">'.$h['fname'].'</td>
                            <td width=20% style="border-bottom: 0.1mm solid black;">'.$h['mname'].'</td>
                            <td width=10% style="border-bottom: 0.1mm solid black;">'.$h['suffix'].'</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td align=left style="font-size: 8pt;">Last Name</td>
                            <td align=left style="font-size: 8pt;">First Name</td>
                            <td align=left style="font-size: 8pt;">Middle Name</td>
                            <td align=left style="font-size: 8pt;">Suffix</td>
                        </tr>
                        <tr>
                            <td>
                                Address:
                            </td>
                            <td colspan=4 style="border-bottom: 0.1mm solid black;">'.$myaddress.'</td>
                        </tr>
                    </table>
                    <table width=100% cellpadding=2 cellspacing=0  align=center> 
                        <tr>
                            <td width=20%>Place of Birth:</td>
                            <td width=40% style="border-bottom: 0.1mm solid black;">'.$h['birthplace'].'</td>
                            <td width=20%>Date of Birth:</td>
                            <td width=20% style="border-bottom: 0.1mm solid black;">'.$h['bday'].'</td>
                        </tr>
                        <tr>
                            <td>Parents/Guardian :</td>
                            <td style="border-bottom: 0.1mm solid black;">'.$h['guardian'].'</td>
                            <td>Gender:</td>
                            <td style="border-bottom: 0.1mm solid black;">'.$h['gender'].'</td>
                        </tr>
                        <tr>
                            <td>Employer :</td>
                            <td style="border-bottom: 0.1mm solid black;">'.$h['employer'].'</td>
                            <td>Age:</td>
                            <td style="border-bottom: 0.1mm solid black;">'.$con->calculateAge($h['birthdate']).'</td>
                        </tr>
                        <tr>
                            <td>Employer Address :</td>
                            <td style="border-bottom: 0.1mm solid black;">'.$h['emp_street'].'</td>
                            <td>Civil Status:</td>
                            <td style="border-bottom: 0.1mm solid black;">'.$cstat.'</td>
                        </tr>
                        <tr>
                            <td colspan=2><i>In Case of Emergency, please notify:</i></td>
                            <td>Mobile No.:</td>
                            <td style="border-bottom: 0.1mm solid black;">'.$h['mobile_no'].'</td>
                        </tr>
                        <tr>
                            <td colspan=2 style="padding-left: 20px;">Name:</td>
                            <td>Email Address:</td>
                            <td style="border-bottom: 0.1mm solid black;">'.$h['email_add'].'</td>
                        </tr>
                        <tr>
                            <td colspan=2 style="padding-left: 20px;">Address:</td>
                            <td>Occupation:</td>
                            <td style="border-bottom: 0.1mm solid black;">'.$h['occupation'].'</td>
                        </tr>
                        <tr>
                            <td colspan=4 style="padding-left: 20px;">Contact Details:</td>
                        </tr>
                        <tr><td colspan=4 height=20>&nbsp;</td></tr>
                        <tr>
                            <td colspan=4 style="padding-left: 20px;">__________________________________________________________</td>
                        </tr>
                        <tr>
                            <td colspan=4 style="padding-left: 60px;">Patient\'s Name over Signature</td>
                        </tr>
                        <tr><td colspan=4 height=20>&nbsp;</td></tr>
                        <tr>
                            <td colspan=4 style="font-size: 7pt;">Date & Time Printed: '.$now.'</td>
                        </tr>
                    </table>

                </body>
            </html>';

 $mpdf->WriteHTML($html);
 $mpdf->Output();
 exit;
?>