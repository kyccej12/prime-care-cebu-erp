<?php
	session_start();
	//ini_set("display_erors","On");
	ini_set("max_execution_time",0);
	ini_set("memory_limit","2056M");

	ini_set("display_errors","on");
	require_once '../lib/PHPExcel/PHPExcel.php';
	require_once '../handlers/_generics.php';

	$mydb = new _init;
	$dtf = $mydb->formatDate($_GET['dtf']);
	$dt2 = $mydb->formatDate($_GET['dt2']);
	$searchString = '';

	date_default_timezone_set('Asia/Manila');
	$now = date("m/d/Y h:i a");

	/* MYSQL QUERIES SECTION */
		$co = $mydb->getArray("select * from companies where company_id = '$_SESSION[company]';");

		
		if($_GET['customer'] != '') { $searchString .= " and a.customer_code = '" .  substr($_GET['customer'],1,6) . "' ";  }
		if($_GET['referredby'] != '') { $searchString .= " and a.referred_by = '" . $_GET['referredby'] . "' ";  }

		//$query = $mydb->dbquery("SELECT a.so_no, a.so_date, DATE_FORMAT(a.so_date,'%M') AS so_month, DATE_FORMAT(a.so_date,'%d') AS so_day, DATE_FORMAT(a.so_date,'%Y') AS so_year, a.customer_name, a.referred_by, d.description AS terms, a.physician, a.patient_id, a.patient_name, c.code, c.description FROM so_header a LEFT JOIN so_details b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN services_subtests c ON b.code = c.parent LEFT JOIN options_terms d ON a.terms = d.terms_id WHERE a.status = 'Finalized' AND a.so_date BETWEEN '$dtf' AND '$dt2' AND b.code LIKE 'P%' $searchString ORDER BY a.so_date asc, a.patient_name, a.customer_name, a.so_no, c.description");
		$query = $mydb->dbquery("SELECT a.so_no, a.so_date, DATE_FORMAT(a.so_date,'%M') AS so_month, DATE_FORMAT(a.so_date,'%d') AS so_day, DATE_FORMAT(a.so_date,'%Y') AS so_year, a.customer_name, a.referred_by, d.description AS terms, a.physician, a.patient_id, a.patient_name, c.code, c.description, with_subtests FROM so_header a LEFT JOIN so_details b ON a.so_no = b.so_no AND a.branch = b.branch LEFT JOIN services_subtests c ON b.code = c.parent LEFT JOIN options_terms d ON a.terms = d.terms_id LEFT JOIN services_master e ON b.code = e.code WHERE a.status = 'Finalized' AND a.so_date BETWEEN '$dtf' AND '$dt2' AND e.with_subtests = 'Y' $searchString ORDER BY a.so_date ASC, a.patient_name, a.customer_name, a.so_no, c.description;");

	/* END OF SQL QUERIES */

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
								 ->setTitle("$co[company_name] - Detailed Sales Report")
								 ->setSubject("$co[company_name] - Detailed Sales Report")
								 ->setDescription("$co[company_name] - Detailed Sales Report")
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
	$objPHPExcel->getActiveSheet()->getColumnDimension("M")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("N")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("O")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("P")->setAutoSize(true);

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1",$co['company_name']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2",$co['company_address']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3",$co['tel_no']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","Walkin APE/PEME Summary Report Covering the Period $_GET[dtf] to $_GET[dt2]");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","SO #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B6","MONTH");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C6","DAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D6","YEAR");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E6","BILLED TO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F6","REFERRED BY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G6","PATIENT NAME");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H6","GENDER");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I6","BIRTH DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J6","AGE BRACKET");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K6","REFERRING DOCTOR");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L6","TERMS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("M6","CODE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("N6","DESCRIPTION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("O6","CURRENT STATUS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("P6","SPECIMEN NO");

	for($colheader = 0; $colheader <= 15; $colheader++) { $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($colheader,6)->applyFromArray($headerStyle); }

	$row = 7;

	while($data = $query->fetch_array()) {

		list($stat,$sn) = $mydb->getArray("SELECT `status`,serialno FROM lab_samples WHERE so_no = '$data[so_no]' AND `code` = '$data[code]';");
		list($gender,$bday) = $mydb->getArray("select gender, birthdate from patient_info where patient_id = '$data[patient_id]';");

		$age = $mydb->calculateAge($data['so_date'],$bday);

		if($gender == 'F') {
			if($age < 30) {
				$ageBracket = "FEMALE BELOW 30";
			} elseif ($age >= 30 && $age <= 34) {
				$ageBracket = "FEMALE 30-34";
			} else {
				$ageBracket = "FEMALE ABOVE 35";
			}

		} else {
			if($age >= 35) {
				$ageBracket = "MALE 30 & ABOVE";
			} else {
				$ageBracket = "MALE BELOW 30";
			}
		}

		if($stat != '') {
			list($status) = $mydb->getArray("select samplestatus from options_samplestatus where id = '$stat';");
		} else {
			$status = "Pending";
		}

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['so_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['so_month']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['so_day']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['so_year']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,html_entity_decode($data['customer_name']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,html_entity_decode($data['referred_by']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,html_entity_decode($data['patient_name']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$gender);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$bday);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$ageBracket);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,html_entity_decode($data['physician']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$data['terms']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$row,$data['code']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,$row,$data['description']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14,$row,$status);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(15,$row,$sn);
		
		for($contentLoop = 0; $contentLoop <= 15; $contentLoop++) {
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contentLoop,$row)->applyFromArray($contentStyle);
		}
		$row++;
	}

	// Rename worksheet
	$objPHPExcel->getActiveSheet()->freezePane('A7');
	$objPHPExcel->getActiveSheet()->setTitle("Walkin APE Summary");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="apesummary.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>