<?php
	session_start();
	require_once '../lib/PHPExcel/PHPExcel.php';
	include("../handlers/_generics.php");


	ini_set("max_execution_time",0);
	ini_set("memory_limit",-1);

	$con = new _init;

	/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");	
	$h = $con->getArray("SELECT a.trace_no, LPAD(a.soa_no,6,'0') AS soano, a.stub_no, a.customer_code AS cid, a.customer_name AS cname, a.customer_address AS caddr, a.remarks, c.description AS terms, DATE_FORMAT(soa_date,'%M %d, %Y') AS soadate, b.tel_no, b.tin_no, b.cperson, a.po_no, if(a.po_date!='0000-00-00',date_format(po_date,'%m/%d/%Y'),'') as pd8, bizstyle, a.referred_by, a.revenue_center, a.vat FROM soa_header a LEFT JOIN contact_info b ON a.customer_code = b.file_id LEFT JOIN options_terms c ON a.terms = c.terms_id WHERE a.soa_no = '$_REQUEST[soa_no]' AND a.branch = '$_SESSION[branchid]';");
	$d = $con->dbquery("SELECT if(source='SO',concat('SO-',LPAD(so_no,6,0)),so_no) AS sono, so_no as xso, date_format(so_date,'%m/%d/%Y') as sodate, pname, b.gender, date_format(b.birthdate,'%m/%d/%Y') as bday, sum(amount) as amount, `source`, `pid` from soa_details a left join patient_info b on a.pid = b.patient_id where soa_no = '$_REQUEST[soa_no]' and branch = '$_SESSION[branchid]' group by so_no, pid order by sum(amount) desc, so_date asc, so_no asc;");	
	/* END OF SQL QUERIES */

	list($isVat) = $con->getArray("select vatable from contact_info where file_id = '$h[cid]';");
	if($h['stub_no'] != '') { $soano = $h['stub_no']; } else { $soano = $h['soano']; }

	$filename = "soa_" . $_REQUEST['soa_no'] . ".xlsx";

	switch($h['revenue_center']) {
		case "081":	$center = 'AIRPORT'; break;
		case "050": $center = 'MOBILE'; break;
		case "150": $center = 'PPP (Province of Cebu)'; break;
		case "190": $center = 'CLINIC MANAGEMENT'; break;
		default: $center = 'Center'; break;
	}

	$headerStyle = array(
		'font' => array('bold' => true),
		'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
		'borders' => array('outline' => array('style' =>PHPExcel_Style_Border::BORDER_THIN)),
	);

	$totalStyle = array(
		'font' => array('bold' => true),
		'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),
		'borders' => array('outline' => array('style' =>PHPExcel_Style_Border::BORDER_THIN)),
	);
	
	$contentStyle = array(
		'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
	);
	
	$totalStyle = array(
		'font' => array('bold' => true),
		'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
	);
	
	
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
	$objPHPExcel->getProperties()->setCreator("Root Admin")
								 ->setLastModifiedBy("CGAP System")
								 ->setTitle("Medgruppe Polyclinics & Diagnostic Center, Inc. - Statement Of Account")
								 ->setSubject("Medgruppe Polyclinics & Diagnostic Center, Inc. - Statement of Account")
								 ->setDescription("Medgruppe Polyclinics & Diagnostic Center, Inc. - Statement of Account")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Exported File");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1",$co['company_name']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2",$co['company_address']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3",$co['tel_no']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A5","SOA No.:");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B5",$_REQUEST['soa_no']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","Statement Date:");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B6",$h['soadate']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A7","Customer:");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B7",html_entity_decode($h['cname']));

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A8","Billing Address:");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B8",html_entity_decode($h['caddr']));

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A9","Tel. No.:");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B9",html_entity_decode($h['tel_no']));

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A10","Company Serviced :");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B10",html_entity_decode($h['referred_by']));

	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A12","NO.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B12","PATIENT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C12","GENDER");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D12","BIRTHDATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E12","SOA NO.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F12","DATE AVAILED");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G12","PROCEDURE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H12","AMOUNT");


	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(60);
	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);


	
	$objPHPExcel->getActiveSheet()->getStyle('A12')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('B12')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('C12')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('D12')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('E12')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('F12')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('G12')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('H12')->applyFromArray($headerStyle);


	$row = 13; $i = 1;
	while($data = $d->fetch_array()) {

		$description = '';
		$items = $con->dbquery("SELECT description from soa_details where so_no = '$data[xso]' and soa_no = '$_REQUEST[soa_no]';");
		while($itemRow = $items->fetch_array()) {
			$description .= $itemRow[0] . ', ';
		}

		if($data['source'] == 'CSO') {
			list($dateAvailed) = $con->getArray("select date_format(processed_on,'%m/%d/%Y') from pccmobile.cso_details where cso_no = '$data[xso]' and pid = '$data[pid]';");
			$data['sodate'] = $dateAvailed;
	
		}

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$i);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,html_entity_decode($data['pname']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['gender']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['bday']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['sono']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['sodate']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,substr($description,0,-2));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data['amount']);

		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,$row)->applyFromArray($contentStyle);

		/* NUMBER FORMAT */
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$row++; $i++; $amtGT+=$data['amount'];
	}


	/* TOTAL */
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$amtGT);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("STATEMENT OF ACCOUNT");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="'.$filename.'"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>