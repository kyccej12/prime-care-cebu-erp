<?php
session_start();
include("../lib/mpdf6/mpdf.php");
include("../handlers/_generics.php");

$con = new _init;

$cv_no = $_GET['cv_no'];
$payee = $_GET['payee'];
$cross = $_GET['cross'];
$amount = $con->formatDigit($_GET['amount']);

list($check_date, $numDate, $source) = $con->getArray("select date_format(check_date,'%M %d, %Y'), DATE_FORMAT(check_date,'%m/%d/%Y') AS numdate, source from cv_header where cv_no = '$cv_no' and branch = '$_SESSION[branchid]';");

$cv_date = $con->formatDate($numDate);
$cv_date = explode("-",$cv_date);
$day = $cv_date[2];
$month = $cv_date[1];
$year = $cv_date[0];

//if($source == '1005')  { $top = 8; } else { $top = 5; } 
$top = 6;

$mpdf=new mPDF('win-1252','CHECKE','','',5,7,$top,0,0,0);
$mpdf->use_embeddedfonts_1252 = true; 
$mpdf->SetProtection(array('print'));
$mpdf->SetTitle("Print Check");
$mpdf->SetAuthor("PORT80 Solutions");
$mpdf->SetDisplayMode(50);

$html = '
<html>
<head>
<style>
body {font-family: sans-serif;
    font-size: 10pt;
}
p {    margin: 0pt;
}
td { vertical-align: top; }
.items td {
    border-left: 0.1mm solid #000000;
    border-right: 0.1mm solid #000000;
}
table thead td { background-color: #EEEEEE;
    text-align: center;
    border: 0.1mm solid #000000;
}
.items td.blanktotal {
    background-color: #FFFFFF;
    border: 0mm none #000000;
    border-top: 0.1mm solid #000000;
    border-right: 0.1mm solid #000000;
}
.items td.totals {
    text-align: right;
    border: 0.1mm solid #000000;
}

.items td.lowerHeads {
	padding-left: 10px;
    text-align: left;
	border: 0mm none #000000;
    border-left: 0.1mm solid #000000;
	border-right: 0.1mm solid #000000;
}

.item td.lowerContent {
	padding-left: 20px;
    text-align: left;
	border: 0mm none #000000;
    border-left: 0.1mm solid #000000;
	border-right: 0.1mm solid #000000;
}
</style>
</head>
<body>';



list($digs,$fracs) = explode(".",$amount);
if($fracs != '00') { $xfracs = " & $fracs/100"; }
$word = $con->inWords($digs) . $xfracs ." PESOS ONLY";

if($_GET['cross'] == "Y") {
	$img = "FOR PAYEE'S ACCOUNT ONLY";
} else { $img = "&nbsp;"; }

$html = $html . '
<table width=100% style="font-size:8pt; font-family: arial;" cellpadding=0 cellspacing=0>
	<tr>
		<td width=100% align=left style="padding-top: 8px; padding-right" colspan=2><b>' .$img . '</b></td>
	</tr>
	<tr>
		<td width=100% align=right style="padding-right: 60px; font-size: 10pt;" colspan=2><b>' .$month . '&nbsp;&nbsp;&nbsp;&nbsp;' .$day. '&nbsp;&nbsp;&nbsp;&nbsp;' .$year. '</b></td>
	</tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<tr>
		<td width=70% align=left style="padding-left: 120px; padding-top: 1px; font-size: 10pt;"><b>&nbsp; ' . $payee . ' &nbsp;<b></td>
		<td width=30% align=right style="padding-right: 60px; padding-top: 1px; font-size: 10pt;"><b>&nbsp; ' . number_format($amount,2) . ' &nbsp;</b></td>
	</tr>
	<tr><td height=10 colspan=2></td></tr>
	<tr>
		<td width=100% align=left style="padding-left: 80px; padding-top: 1px; font-size: 10pt;" colspan=2><b>' . $word . '<b></td>
	</tr>
</table>
</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;

exit;			
?>