<?php
	session_start();
	require_once '../lib/PHPExcel/PHPExcel.php';
	require_once '../handlers/_generics.php';
	
	$mydb = new _init;
	
	$now = date("m/d/Y h:i a");

	$co = $mydb->getArray("select * from companies where company_id = '$_SESSION[company]';");

    if($_REQUEST['category'] != '') { $f1 = " and c.id = '$_REQUEST[category]' "; }
	$query = $mydb->dbquery("SELECT d.patient_name, a.physician, a.code, a.procedure, c.subcategory AS subcatname, extractdate, extractime, extractby FROM lab_samples a LEFT JOIN services_master b ON a.code = b.code LEFT JOIN options_servicesubcat c ON b.subcategory = c.id LEFT JOIN so_header d ON a.so_no = d.so_no WHERE a.extractdate BETWEEN '".$mydb->formatDate($_GET['dtf'])."' AND '".$mydb->formatDate($_GET['dt2'])."' AND a.status IN (1,3,4) $f1 ORDER BY c.subcategory, a.procedure, d.patient_name, a.extractdate, a.extractime;");

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
								 ->setLastModifiedBy("Root Admin")
								 ->setTitle("Medgruppe Polyclinics & Diagnostic Center, Inc. - Laboratory Census")
								 ->setSubject("Medgruppe Polyclinics & Diagnostic Center, Inc. - Laboratory Census")
								 ->setDescription("Medgruppe Polyclinics & Diagnostic Center, Inc. - Laboratory Census")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Exported File");

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1",$co['company_name']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2",$co['company_address']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3",$co['tel_no']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A5","Detailed List of Performed Tests Covering the Period $_GET[dtf] to $_GET[dt2]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A7","PATIENT NAME");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B7","REQUESTING PHYSICIAN");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C7","CODE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D7","PROCEDURE OR TEST");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E7","CATEGORY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F7","DATE EXTRACTED");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G7","TIME EXTRACTED");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H7","EXTRACTED BY");

	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);

	$objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('B7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('C7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('D7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('E7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('F7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('G7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('H7')->applyFromArray($headerStyle);

	$row = 8;
	while($data = $query->fetch_array(MYSQLI_BOTH)) {
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,utf8_encode($data['patient_name']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['physician']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['code']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['procedure']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['subcatname']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['extractdate']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$data['extractime']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data['extractby']);

		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,$row)->applyFromArray($contentStyle);
		$row++;
	}
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("DETAILED CENSUS SUMMARY");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="census_detailed.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>