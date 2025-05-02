<?php
	session_start();
	require_once '../lib/PHPExcel/PHPExcel.php';
	require_once '../handlers/_generics.php';
	//ini_set("display_errors","On");
	$mydb = new _init;
	
	$now = date("m/d/Y h:i a");

	$co = $mydb->getArray("select * from companies where company_id = '$_SESSION[company]';");


    if($_REQUEST['category'] != '') { $f1 = " and c.id = '$_REQUEST[category]' "; }
	$query = $mydb->dbquery("SELECT a.code, a.procedure, c.subcategory AS subcatname, COUNT(a.code) AS testcount FROM lab_samples a LEFT JOIN services_master b ON a.code = b.code LEFT JOIN options_servicesubcat c ON b.subcategory = c.id WHERE a.extractdate BETWEEN '".$mydb->formatDate($_GET['dtf'])."' AND '".$mydb->formatDate($_GET['dt2'])."' AND a.status IN (1,3,4) $f1 GROUP BY a.code ORDER BY c.subcategory, a.procedure;");

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
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","Summary of Performed Tests Covering the Period $_GET[dtf] to $_GET[dt2]");

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A7","CODE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B7","TEST OR PROCEDURE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C7","CATEGORY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D7","TOTAL TESTS");

	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(16);
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);

	$objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('B7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('C7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('D7')->applyFromArray($headerStyle);
	$row = 8;
	while($data = $query->fetch_array(MYSQLI_BOTH)) {

		// /*OPD*/
		// list($opdStat) = $mydb->getArray("SELECT COUNT(*) FROM lab_samples a WHERE a.extractdate BETWEEN '" . $mydb->formatDate($_GET['dtf']) . "' AND '" . $mydb->formatDate($_GET['dt2']) . "' AND a.status IN (3,4) AND `code` = '$data[code]' AND hpatroom IN ('OPD');");

		// /* ER & ERADM */
		// list($erStat) = $mydb->getArray("SELECT COUNT(*) FROM lab_samples a WHERE a.extractdate BETWEEN '" . $mydb->formatDate($_GET['dtf']) . "' AND '" . $mydb->formatDate($_GET['dt2']) . "' AND a.status IN (3,4) AND `code` = '$data[code]' AND hpatroom IN ('ER');");

		// /* In Patient */
		// list($inStat) = $mydb->getArray("SELECT COUNT(*) FROM lab_samples a WHERE a.extractdate BETWEEN '" . $mydb->formatDate($_GET['dtf']) . "' AND '" . $mydb->formatDate($_GET['dt2']) . "' AND a.status IN (3,4) AND `code` = '$data[code]' AND hpatroom NOT IN ('OPD','ER');");

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['code']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['procedure']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['subcatname']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['testcount']);

		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3,$row)->applyFromArray($contentStyle);

		/* NUMBER FORMAT */
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3,$row)->getNumberFormat()->setFormatCode('#,##0.00');

		$row++;
	}
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("CENSUS SUMMARY");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="census_summary.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>