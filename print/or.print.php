<?php
    session_start();
	require_once("../lib/mpdf6/mpdf.php");
	require_once("../handlers/initDB.php");

    $con = new myDB;
    $_ihead = $con->getArray("SELECT LPAD(doc_no,6,0) AS docno, or_no, date_format(doc_date,'%m/%d/%Y') as ddate, date_format(doc_date,'%M %d') as date1, date_format(doc_date,'%y') as date2, customer_code, customer_name, scpwd_id, gross, net_of_vat, vat, ewt, sc_discount, amount_due, cashtype, checkno, checkbank, cardtype FROM or_header WHERE doc_no = '$_REQUEST[doc_no]' and branch = '$_SESSION[branchid]';");

    if($_ihead['customer'] == 0) {
        list($pname,$paddress) = $con->getArray("SELECT DISTINCT pname, paddr FROM or_details WHERE doc_no = '$_REQUEST[doc_no]' AND branch = '$_SESSION[branchid]';");
    } else {
        $pname = $_ihead['customer_name'];
        $paddress = $_ihead['customer_address'];
        list($tin,$bizstyle) = $con->getArray("select tin_no, bizzstyle from contact_info where file_id = '$_ihead[customer_code]';");
    }



    $mpdf=new mPDF('win-1252','LETTER','','',27,15,20,10,10,10);
    $mpdf->use_embeddedfonts_1252 = true;    // false is default
    $mpdf->setAutoTopMargin='stretch';
    $mpdf->setAutoBottomMargin='stretch';
    $mpdf->use_kwt = true;
    $mpdf->SetProtection(array('print'));
    $mpdf->SetAuthor("Primecare Cebu");
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
                    <table width=100% cellpaddding=0 cellspacing=0>
                        <tr>
                            <td colspan=4 align=right>REFERENCE NO. :'.$_ihead['docno'].'</td>
                        </tr>
                        <tr><td height=40></tr>
                        <tr>
                            <td colspan=2 width=60%>&nbsp;</td>
                            <td align=left>'.$_ihead['date1'].'&nbsp;&nbsp;&nbsp;&nbsp;'.$_ihead['date2'].'</td><td></td>
                        </tr>
                        <tr><td height=12></tr>
                        <tr>
                            <td width=60% colspan=2 style="padding-left: 40px;">'.$pname.'</td>
                            <td width=20% align=center>'.$tin.'</td>
                            <td width=20% align=left>'.$paddress.'</td>
                        </tr>
                        <tr><td height=10>&nbsp;</td></tr>
                    </table>
                    <table width=100% cellspacing=0 cellpadding=0>
                        <tr>
                            <td width=70% valign=top>
                                <table with=100% cellpadding=0 cellspacing=0>
                                    <tr>
                                        <td width=22% colspan=2 align=center>'.$bizzstyle.'</td>
                                        <td width=54% align=center>'.$_ihead['scpwd_id'].'</td>
                                        <td width=24% colspan=2 align=center></td>
                                    </tr>
                                    <tr><td height=70>&nbsp;</td></tr>';

                                    list($soCount) = $con->getArray("SELECT COUNT(so_no) FROM or_details WHERE doc_no = '$_REQUEST[doc_no]' and branch = '$_SESSION[branchid]';");
                                    if($soCount > 4) {
                                        $dQuery = $con->dbquery("SELECT COUNT(`code`) AS pax, description, unit_price, SUM(amount_due) AS amt1, SUM(amount_due) AS amt2 FROM or_details WHERE doc_no = '$_REQUEST[doc_no]' AND branch = '$_SESSION[branchid]' GROUP BY `code`;");
                                        
                                        while($dRow = $dQuery->fetch_array()) {
                                            $html .= '<tr>
                                                <td width=76% colspan=3>('. $dRow['pax'] . ' Pax) ' . $dRow ['description'] . ' @ ' . number_format($dRow['unit_price'],2) . ' Per Pax</td>
                                                <td width=12% align=right>'.number_format($dRow['amt1'],2).'</td>
                                                <td width=12% align=right>'.number_format($dRow['amt2'],2).'</td>
                                            </tr>';
                                        }
                                    }   else {                             
                                
                                        $dQuery = $con->dbquery("SELECT lpad(so_no,6,0) as so, DATE_FORMAT(so_date,'%m/%d/%Y') AS sdate, description, amount_due AS si_amount, amount_due AS paid FROM or_details WHERE doc_no = '$_REQUEST[doc_no]' AND branch = '$_SESSION[branchid]';");
                                        while($dRow = $dQuery->fetch_array()) {
                                            $html .= '<tr>
                                                <td width=10%>'.$dRow['so'].'</td>
                                                <td width=12%>'.$dRow['sdate'].'</td>
                                                <td width=54%>'.$dRow['description'].'</td>
                                                <td width=12% align=right>'.number_format($dRow['si_amount'],2).'</td>
                                                <td width=12% align=right>'.number_format($dRow['paid'],2).'</td>
                                            </tr>';

                                        }
                                    }
                    $html .= '</table>
                            </td>             
                            <td width=30% align=right>
                                <table width=100% cellspacing=0 cellpadding=1 style="font-size: 8.5pt;">
                                    <tr>
                                        <td>'.number_format($_ihead['gross'],2).'</td>
                                    </tr>
                                    <tr><td>&nbsp;</td></tr>
                                    <tr>
                                        <td>'.number_format($_ihead['vat'],2).'</td>
                                    </tr>
                                    <tr>
                                        <td>'.number_format($_ihead['net_of_vat'],2).'</td>
                                    </tr>
                                    <tr>
                                        <td>'.number_format($_ihead['sc_discount'],2).'</td>
                                    </tr>
                                    <tr>
                                        <td>'.number_format($_ihead['amount_due'],2).'</td>
                                    </tr>
                                    <tr>
                                        <td>'.number_format($_ihead['ewt'],2).'</td>
                                    </tr>
                                    <tr>
                                        <td>'.number_format($_ihead['amount_due'],2).'</td>
                                    </tr>
                                    <tr>
                                        <td>'.number_format($_ihead['net_of_vat'],2).'</td>
                                    </tr>
                                    <tr>
                                        <td>'.number_format($_ihead['nonvat'],2).'</td>
                                    </tr>
                                    <tr>
                                        <td>'.number_format($_ihead['zero_rated'],2).'</td>
                                    </tr>
                                    <tr>
                                        <td>'.number_format($_ihead['vat'],2).'</td>
                                    </tr>
                                    <tr>
                                        <td>'.number_format($_ihead['amount_due'],2).'</td>
                                    </tr>
                                </table>
                            </td>
                         </tr>
                    </table>
                </body>
            </html>';

 $mpdf->WriteHTML($html);
 $mpdf->Output();
 exit;
?>