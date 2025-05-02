<?php
	session_start();
	ini_set("max_execution_time",-1);
	require_once '../lib/PHPExcel/PHPExcel.php';
	require_once '../handlers/_generics.php';

	$mydb = new _init;
	
	/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$dtf = $mydb->formatDate($_GET['dtf']);
	$dt2 = $mydb->formatDate($_GET['dt2']);
	$searchString = ''; 

	if($_GET['category'] != '') {
		$searchString .= " and d.sales_id = '$_GET[category]' ";
	}

	$co = $mydb->getArray("select * from companies where company_id = '$_SESSION[company]';");
	$query = $mydb->dbquery("SELECT * FROM (SELECT 'SO' AS `type`, a.so_no, d.sales_category AS salescat, DATE_FORMAT(so_date,'%Y') AS `year`, DATE_FORMAT(so_date,'%b') AS `month`, DATE_FORMAT(so_date,'%d') AS `day`, a.physician, IF(a.customer_code='0','',customer_name) AS company, IF(a.customer_code=0,'Private Account (Individual)',f.contacttype) AS channel, a.customer_code, a.patient_name, 'Cash' AS terms, b.code, b.description, b.amount, g.subcategory FROM so_header a LEFT JOIN so_details b ON a.so_no = b.so_no LEFT JOIN services_master c ON b.code = c.code LEFT JOIN options_salescategory d ON c.sales_cat = d.sales_id LEFT JOIN contact_info e ON a.customer_code = e.file_id LEFT JOIN options_ctype f ON e.type = f.id left join options_servicesubcat g on c.subcategory = g.id WHERE a.so_date BETWEEN '$dtf' AND '$dt2' AND a.status = 'Finalized' AND paid = 'Y' AND a.terms = '0' $searchString UNION ALL SELECT 'SOA' AS `type`, a.soa_no, d.sales_category AS salescat, DATE_FORMAT(soa_date,'%Y') AS `year`, DATE_FORMAT(soa_date,'%b') AS `month`, DATE_FORMAT(soa_date,'%d') AS `day`, g.physician, IF(a.customer_code='0','',a.customer_name) AS company, IF(a.customer_code=0,'Private Account (Individual)',f.contacttype) AS channel, a.customer_code, b.pname AS patient_name, h.description AS terms, b.code, b.description, b.amount, i.subcategory FROM soa_header a LEFT JOIN soa_details b ON a.soa_no = b.soa_no LEFT JOIN services_master c ON b.code = c.code LEFT JOIN options_salescategory d ON c.sales_cat = d.sales_id LEFT JOIN contact_info e ON a.customer_code = e.file_id LEFT JOIN options_ctype f ON e.type = f.id left join options_servicesubcat i on c.subcategory = i.id LEFT JOIN so_header g ON b.so_no = g.so_no LEFT JOIN options_terms h ON a.terms = h.terms_id WHERE a.soa_date BETWEEN '$dtf' AND '$dt2' AND a.status = 'Finalized' $searchString) a ORDER BY patient_name;");

/* END OF SQL QUERIES */

	date_default_timezone_set('Asia/Manila');
	$now = date("m/d/Y h:i a");

	$headerStyle = array(
		'font' => array('bold' => true),
		'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
		'borders' => array('outline' => array('style' =>PHPExcel_Style_Border::BORDER_THIN)),
	);

	$totalStyle = array(
		'font' => array('bold' => true),
		'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
	);
	
	$contentStyle = array(
		'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
	);
	
	$totalStyle = array(
		'font' => array('bold' => true),
		'borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),'bottom' => array('style' => PHPExcel_Style_Border::BORDER_DOUBLE))
	);
	
	
	
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
	$objPHPExcel->getProperties()->setCreator("Root Admin")
								 ->setLastModifiedBy("Root Admin")
								 ->setTitle("$co[company_name] - Sales Per Category")
								 ->setSubject("$co[company_name] - Sales Per Category")
								 ->setDescription("$co[company_name] - Sales Per Category")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Exported File");
	

	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(16);
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("L")->setAutoSize(true);

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1",$co['company_name']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2",$co['company_address']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3",$co['tel_no']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","Sales Per Category Covering The Period $_GET[dtf] to $_GET[dt2]");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","SALES CATEGORY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B6","SALES CHANNEL");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C6","SERVICES");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D6","YEAR");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E6","MONTH");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F6","DAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G6","REFERING DOCTOR");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H6","NAME OF COMPANY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I6","NAME OF PATIENT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J6","LABORATORY/DIAGNOSTIC TEST");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K6","TERMS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L6","SALES");

	for($colheader = 0; $colheader <= 11; $colheader++) { $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($colheader,6)->applyFromArray($headerStyle); }

	$row = 7; $cashGT = 0; $cardGT = 0; $checkGT = 0;
	while($data = $query->fetch_array(MYSQLI_BOTH)) {

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['salescat']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['channel']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['subcategory']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['year']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['month']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['day']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,html_entity_decode($data['physician']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,html_entity_decode($data['company']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,html_entity_decode($data['patient_name']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$data['description']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$data['terms']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$data['amount']);

		/* NUMBER FORMAT */
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11,$row)->getNumberFormat()->setFormatCode('#,##0.00');


		for($contentLoop = 0; $contentLoop <= 11; $contentLoop++) {
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contentLoop,$row)->applyFromArray($contentStyle);
		}
		$row++; $amtGT+=$data['amount'];
	}

	/* TOTAL */
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$amtGT);

	/* NUMBER FORMAT */
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11,$row)->applyFromArray($totalStyle);
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->freezePane('A7');
	$objPHPExcel->getActiveSheet()->setTitle("Sales PER CATEGORY");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="salespercategory.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>