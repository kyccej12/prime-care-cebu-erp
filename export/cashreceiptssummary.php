<?php
	session_start();
	ini_set("max_execution_time",0);
	//ini_set("display_errors","on");
	require_once '../lib/PHPExcel/PHPExcel.php';
	require_once '../handlers/_generics.php';

	$mydb = new _init;
	
	/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$dtf = $mydb->formatDate($_GET['dtf']);
	$dt2 = $mydb->formatDate($_GET['dt2']);
	$searchString = '';

	if($_GET['customer'] != '') { $searchString .= " and a.customer_code = '" .  substr($_GET['customer'],1,6) . "' ";  }
	if($_GET['uid'] != '') { $f1 = " and a.created_by = '$_GET[uid]' "; $lbl = "Cashier: " . $mydb->getUname($_GET['uid']); } else { $f1 = ''; $lbl = 'All Cashiers'; } 

	$co = $mydb->getArray("select * from companies where company_id = '$_SESSION[company]';");
	$query = $mydb->dbquery("SELECT a.doc_no, a.or_no, DATE_FORMAT(a.doc_date,'%m/%d/%Y') AS ordate, b.so_no, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS sodate, IF(a.customer_code=0,'PATIENT',a.customer_name) AS billedto, b.pname, c.employer, b.code, b.description, b.qty, b.amount, b.discount, b.amount_due, a.amount_due-cc_tendered-check_tendered AS cashsales, cc_tendered AS cardsales, check_tendered AS checksales FROM or_header a LEFT JOIN or_details b ON a.doc_no = b.doc_no AND a.branch = b.branch LEFT JOIN patient_info c ON b.pid = c.patient_id WHERE a.status = 'Finalized' AND a.doc_date BETWEEN '$dtf' AND '$dt2' $f1 $searchString ORDER BY a.doc_no, a.doc_date, b.pname;");

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
								 ->setTitle("$co[company_name] - Detailed Sales Report")
								 ->setSubject("$co[company_name] - Detailed Sales Report")
								 ->setDescription("$co[company_name] - Detailed Sales Report")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Exported File");
	

	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(16);
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(24);
	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(48);
	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("L")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("M")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("N")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("O")->setAutoSize(true);

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1",$co['company_name']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2",$co['company_address']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3",$co['tel_no']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","Cash Receipts Summary Covering The Period $_GET[dtf] to $_GET[dt2]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A5","Cashier: $lbl");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","REF #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B6","OR #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C6","OR DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D6","SO #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E6","SO DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F6","BILLED TO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G6","PATIENT NAME");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H6","CODE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I6","DESCRIPTION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J6","AMOUNT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K6","DISCOUNT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L6","AMOUNT DUE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("M6","CASH SALES");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("N6","CARD SALES");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("O6","CHECK PAYMENTS");

	for($colheader = 0; $colheader <= 14; $colheader++) { $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($colheader,6)->applyFromArray($headerStyle); }

	$row = 7; $cashGT = 0; $cardGT = 0; $checkGT = 0;
	while($data = $query->fetch_array(MYSQLI_BOTH)) {

		if($xdoc != $data['doc_no']) {
			if($data['cashsales'] > 0) { 
				$csales = $data['cashsales']; 
				$cashGT+=$data['cashsales']; 
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12,$row)->getNumberFormat()->setFormatCode('#,##0.00'); 
			} else { $csales = ''; }
			if($data['cardsales'] > 0) { 
				$ccsales = $data['cardsales']; 
				$cardGT+=$data['cardsales']; 
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13,$row)->getNumberFormat()->setFormatCode('#,##0.00'); 
			} else { $ccsales = ''; }
			if($data['checksales'] > 0) { 
				$cksales = $data['checksales'];
				$ckGT+=$data['checksales']; 
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14,$row)->getNumberFormat()->setFormatCode('#,##0.00'); 
			} else { $cksales = ''; }
		} else {
			$csales = ''; $ccsales = ''; $cksales = '';
		}

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['doc_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['or_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['ordate']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['so_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['sodate']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,html_entity_decode($data['billedto']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,html_entity_decode($data['patient_name']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data['code']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data['description']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$data['amount']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$data['discount']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$data['amount_due']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$row,$csales);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,$row,$ccsales);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14,$row,$cksales);

		/* NUMBER FORMAT */
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11,$row)->getNumberFormat()->setFormatCode('#,##0.00');

		for($contentLoop = 0; $contentLoop <= 14; $contentLoop++) {
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contentLoop,$row)->applyFromArray($contentStyle);
		}
		$row++; $xdoc = $data['doc_no']; $i++;
	}


	/* TOTAL */
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$row,$cashGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,$row,$cardGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14,$row,$checkGT);

	/* NUMBER FORMAT */
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14,$row)->applyFromArray($totalStyle);
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->freezePane('A7');
	$objPHPExcel->getActiveSheet()->setTitle("Cash Receipts Summary");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="dsr.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>