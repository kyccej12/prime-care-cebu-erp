<?php
    session_start();
	require_once("../lib/mpdf6/mpdf.php");
	require_once("../handlers/initDB.php");

    $con = new myDB;
    $serialno = $_GET['id'];
    //$a = $con->getArray("SELECT b.patient_name AS pname, date_format(c.birthdate,'%m/%d/%y') as bday, YEAR(b.so_date) - YEAR(c.birthdate) AS age,c.gender,CONCAT(DATE_FORMAT(extractdate,'%m/%d/%Y'),' ',TIME_FORMAT(extractime,'%h:%i %p')) AS tstamp, e.sample_type FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_sampletype e ON a.sampletype = e.id WHERE a.serialno = '$serialno';");
    $a = $con->getArray("SELECT b.patient_name AS pname, DATE_FORMAT(c.birthdate,'%m/%d/%y') AS bday, YEAR(b.so_date) - YEAR(c.birthdate) AS age,c.gender,CONCAT(DATE_FORMAT(extractdate,'%m/%d/%Y'),' ',TIME_FORMAT(extractime,'%h:%i %p')) AS tstamp, e.sample_type, a.`code`, `procedure`, g.subcategory FROM lab_samples a LEFT JOIN so_header b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.patient_id = c.patient_id LEFT JOIN options_sampletype e ON a.sampletype = e.id  LEFT JOIN services_master f ON a.code = f.code LEFT JOIN options_servicesubcat g ON f.subcategory = g.id WHERE a.serialno = '$serialno';");

    $mpdf=new mPDF('win-1252','BARCODE','','',0,0,2.5,0,0,0);
    $mpdf->use_embeddedfonts_1252 = true;    // false is default
    $mpdf->setAutoTopMargin='stretch';
    $mpdf->setAutoBottomMargin='stretch';
    $mpdf->use_kwt = true;
    $mpdf->SetProtection(array('print'));
    $mpdf->SetAuthor("Opon Medical Diagnostic Corporation");
    $mpdf->SetDisplayMode(100);

    $useDescription = array('L012','L013');

    if(in_array($a['code'],$useDescription)) {
        $procedure = $a['procedure']; 
    } else {
        $procedure = $a['subcategory'];
    }

    $html = '<html>
                <head>
                    <title>Specimen Barcode</title>
                    <style>
                        body {
                            font-family: "Times New Roman";
                            font-size: 5.5pt;
                        }    
                    </style>
                </head>
                <body>
                    <table width=100% cellpadding=0 cellspacing=0  style="font-weight: bold;">
                        <tr>
                            <td align=left colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.substr($a['pname'],0,30).'^' . $a['gender'] . '^'.$a['age'].'</td>
                        </tr>
                        <tr><td colspan=2 align=center><barcode code="'.$serialno.'" type="C128A" height="1.2" size="0.85"></td></tr>
                        <tr>
                            <td colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'. strtoupper($procedure) . '</td>
                        </tr>
                        <tr>
                            <td colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date/Time: '.$a['tstamp'].'</td>
                        </tr>
                        <tr>
                            <td colspan=2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sample No.: '.$serialno.'</td>
                        </tr>   
                    </table>
                </body>
            </html>';

 $mpdf->WriteHTML($html);
 $mpdf->Output();
 exit;
?>