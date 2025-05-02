<?php

	session_start();
	include("../../lib/mpdf6/mpdf.php");
	include("../../handlers/initDB.php");
	ini_set("memory_limit","1024M");
	ini_set("max_execution_time",0);
	//ini_set("display_errors","On");
	
	$con = new myDB;
	
	if(isset($_REQUEST['area']) && $_REQUEST['area'] !=''){
   		$myDSG = " AND area = '$_REQUEST[area]' ";
	}

	$q = $con->dbquery("SELECT concat(lname,', ',fname,', ',mname) as emp,acct_no as `bank`,rate,annual,13thmonth as amount FROM pccpayroll.13th_month where cy = '". $_GET['year'] . "' order by `lname` asc;");

			
$mpdf=new mPDF('win-1252','Folio','','',15,15,10,10,10,10);
$mpdf -> use_kwt = true;
$mpdf->use_embeddedfonts_1252 = true;    // false is default
//$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("PORT80 Solutions");
$mpdf->SetDisplayMode(40);


	
	
$html = '
<html>
<head>
<style>
body {font-family: Arial; font-size: 8pt; }
td { vertical-align: top; }
.e_info { border-top: 0.05mm solid #000000; }
</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">

</htmlpageheader>

<sethtmlpageheader name="myheader" value="off" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="off" />
mpdf-->';
while($res = $q->fetch_array()) {
	$html = $html . '<table width="100%">
			<tr>
				<td width=40>
					<img src="../../images/logosmall.png" width=40 height=40 />
				</td>
				<td>
					<span style="font-size: 7pt;"><b>Medgruppe Polyclinics & Diagnostic Center, Inc.</b><br/>2nd Level, APM Centrale, A. Soriano Jr. Ave., NRA, Mabolo, Cebu City, 6000 Philippines<br/>Tel # (032) 232-2273/266-3245</span>
				</td>
				<td align=right>
					<span style="font-weight: bold; font-size: 11pt; color: #000000;">13<sup>th</sup> Month Payslip</span>
				</td>
			</tr>
		</table>
		<table width="100%" cellspacing=0 cellpadding=0 class=e_info>
			<tr><td colspan=6 class=e_info>&nbsp;</td>
			<tr >
				<td width=15%>EMPLOYEE NAME</td>
				<td>:</td>
				<td width=35% style="padding-left: 5px;">'. strtoupper(iconv("UTF-8", "ISO-8859-1//IGNORE", $res['emp'])). '</td>
				<td width=20%>Pay Date</td>
				<td>:</td>
				<td width=30% style="padding-left: 5px;">'.$_REQUEST['date'].'</td>
			</tr>
			<tr>
				<td width=15%>ATM ACCT #</td><td>:</td>
				<td width=35% style="padding-left: 5px;">'. $res['bank'] . '</td>
				<td width=20%></td>
				<td></td>
				<td width=30% style="padding-left: 5px;"></td>
			</tr>
			<tr>
				<td width=15%>BASIC RATE</td><td>:</td>
				<td width=35% style="padding-left: 5px;">'. number_format($res['rate'],2) . '</td>
				<td width=20%></td>
				<td></td>
				<td width=30% style="padding-left: 5px;"></td>
			</tr>
		</table>';
		
		$html .= '<table style="border-collapse:collapse;font-size:7pt; border-bottom: 1px solid black;" width=100%>
					<tr> 
						<td width=75% style="border-bottom:1.5px solid black;padding-left:5px;padding-right:5px;"> <b>&nbsp; </b> </td>
						<td width=25% style="border-bottom:1.5px solid black;padding-right:15px;padding-left:5px;padding-right:20px;" align=right><b> &nbsp; </b> </td>
					</tr>
					<tr> 
						<td width=75% style="padding-left:10px;font-size:9pt;padding-top:10px;">Annual Basic Income</td>
						<td width=25% align=right style="padding-right:10px;font-size:9pt;padding-top:10px;">'.number_format($res['annual'],2).'</td>
					</tr>
					<tr> 
						<td width=75% style="padding-left:10px;font-size:9pt;padding-top:5px;padding-bottom:5px;"> 13th Month Pay </td>
						<td width=25% align=right style="padding-right:10px;font-size:9pt;padding-top:10px;">'.number_format($res['amount'],2).'</td>
					</tr>
					';
		
		$html .= '</table>

		<table><tr><td height=90 valign=middle>&#9986;-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</td></tr></table>';

}
$html = $html.'</body>
</html>
';

$html = utf8_encode($html);
$mpdf->WriteHTML($html);
$mpdf->Output(); 
exit;