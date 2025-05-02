<?php
    session_start();
	require_once("../lib/mpdf6/mpdf.php");
	require_once("../handlers/initDB.php");

    $con = new myDB;
    $co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");

    $_ihead = $con->getArray("SELECT *, LPAD(so_no,6,0) AS sono, DATE_FORMAT(so_date,'%m/%d/%Y') AS d8 FROM so_header a where so_no = '$_REQUEST[so_no]' and branch = '$_SESSION[branchid]';");
    $_p = $con->getArray("SELECT YEAR(CURDATE()) - YEAR(birthdate) as age, DATE_FORMAT(birthdate,'%m/%d/%Y') AS bday, IF(gender='M','Male','Female') AS gender, if(a.employer='','WALK-IN',a.employer) as employer FROM patient_info a WHERE a.patient_id = '$_ihead[patient_id]';");

    $insideOfMe = '';
    $labQuery = $con->dbquery("SELECT `procedure` FROM lab_samples WHERE so_no = '$_REQUEST[so_no]';");
    while(list($txt) = $labQuery->fetch_array()) {
        $insideOfMe .= $txt . ", ";
    }

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
                            font-size: 11pt;
                        }    
                    </style>
                </head>
                <body>

                    <table width="100%" cellpadding=0 cellspaing=0 align=center>
                        <tr>
                            <td style="color:#000000; padding-top: 5px;" valign=top align=center>
                                <span style="font-size: 11pt;"><b>'.$co['company_name'].'</b><br/>'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'</span>
                            </td>
                        </tr>
                    </table>

                    <table width=80% cellpadding=1 cellspacing=0 style="margin-top: 20px;" align=center>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>SO NO.</td>
                            <td>:&nbsp;&nbsp;&nbsp;&nbsp;'.$_ihead['sono'].'</td>
                        </tr>
                        <tr>
                            <td>NAME</td>
                            <td>:&nbsp;&nbsp;&nbsp;&nbsp;'.$_ihead['patient_name'].'</td>
                            <td>AGE</td>
                            <td>:&nbsp;&nbsp;&nbsp;&nbsp;'.$_p['age'].'</td>
                        </tr>
                        <tr>
                            <td>BIRTHDAY</td>
                            <td>:&nbsp;&nbsp;&nbsp;&nbsp;'.$_p['bday'].'</td>
                            <td>DATE</td>
                            <td>:&nbsp;&nbsp;&nbsp;&nbsp;'.$_ihead['d8'].'</td>
                        </tr>
                        <tr>
                            <td>COMPANY</td>
                            <td>:&nbsp;&nbsp;&nbsp;&nbsp;'.$_p['employer'].'</td>
                            <td>GENDER</td>
                            <td>:&nbsp;&nbsp;&nbsp;&nbsp;'.$_p['gender'].'</td>
                        </tr>
                    </table>

                    <table width=80% cellpadding=20 style="border: 1px solid #000000; font-size: 11pt; margin-top: 40px;" align=center>
                        <tr>
                            <td align=center>'.$insideOfMe.'</td>
                        </tr>
                    </table>
                </body>
            </html>';

 $mpdf->WriteHTML($html);
 $mpdf->Output();
 exit;
?>