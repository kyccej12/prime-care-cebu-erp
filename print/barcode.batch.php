<?php
	session_start();
	ini_set("max_execution_time",-1);
	ini_set("memory_limit",-1);
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");

	$con = new _init;

/* MYSQL QUERIES SECTION */
$now = date("m/d/Y h:i a");
$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");
$mpdf=new mPDF('win-1252','BARCODE','','',0,0,0,0,0,0);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->setAutoTopMargin='stretch';
$mpdf->setAutoBottomMargin='stretch';
$mpdf->use_kwt = true;
$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("Prime Care Cebu");
$mpdf->SetDisplayMode(100);

$outerQuery = $con->dbquery("select pname, b.gender, date_format(b.birthdate,'%m/%d/%Y') as birthdate, b.birthdate as bday, `code` from cso_details a left join patient_info b on a.pid = b.patient_id WHERE cso_no = '$_REQUEST[cso_no]';");
while($outerRow = $outerQuery->fetch_array()) {

	$innerQuery = $con->dbquery("SELECT a.cso_no AS so, b.code AS parent_code, b.code, b.description AS `procedure`,d.sample_type, d.container_type, d.description as incode FROM cso_header a LEFT JOIN cso_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON b.pid = c.patient_id LEFT JOIN services_master d ON b.code = d.code WHERE a.status = 'Finalized' AND d.with_subtests = 'N' AND d.category IN ('1','2') AND a.cso_no = '$_REQUEST[cso_no]' AND d.description NOT LIKE '%PCR%' UNION SELECT a.cso_no AS so, e.parent AS parent_code, e.code, e.description AS `procedure`, f.sample_type, f.container_type, e.description AS incode FROM cso_header a LEFT JOIN cso_details b ON a.trace_no = b.trace_no LEFT JOIN patient_info c ON b.pid = c.patient_id LEFT JOIN services_master d ON b.code = d.code LEFT JOIN services_subtests e ON b.code = e.parent LEFT JOIN services_master f ON e.code = f.code WHERE a.status = 'Finalized' AND d.with_subtests = 'Y' AND f.category IN ('1','2') AND a.cso_no = '$_REQUEST[cso_no]' AND d.description NOT LIKE '%PCR%';");
	while($innerRow = $innerQuery->fetch_array()) {

		if($innerRow['sample_type'] != 9) {
			list($code) = $con->getArray("select `sn_code` from options_sampletype where id = '$innerRow[sample_type]';");
			list($serialno) = $con->getArray("SELECT concat('$code',LPAD(IFNULL(MAX(series+1),1),9,0)) as series FROM (SELECT TRIM(LEADING '0' FROM SUBSTRING(`serialno`,2,9)) AS series FROM lab_samples WHERE sampletype = '$innerRow[sample_type]') a;");


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
							<td align=left colspan=2>&nbsp;&nbsp;&nbsp;'.substr($outerRow['pname'],0,30).'^' . $outerRow['gender'] . '^'.$con->calculateAge($outerRow['bday']).'</td>
						</tr>
						<tr><td colspan=2 align=center><barcode code="'.$serialno.'" type="C128A" height="0.3" size="0.85"></td></tr>
						<tr>
							<td align=left>&nbsp;&nbsp;&nbsp;'.substr($innerRow['incode'],0,30).'</td>
							<td width=30% align=right>'. $serialno . '&nbsp;&nbsp;&nbsp;</td>
						</tr>    
					</table>
				</body>
			</html>';

			$endOfPage = $mpdf->page + 1;
			$html = html_entity_decode($html);
			$mpdf->WriteHTML($html);
			$mpdf->AddPage();
		}

	}
}

$mpdf->DeletePages($endOfPage);
$mpdf->Output();
exit;

?>