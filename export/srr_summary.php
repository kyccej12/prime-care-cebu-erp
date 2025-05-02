<?php
	session_start();
	//ini_set("display_errors","On");
	require_once '../lib/PHPExcel/PHPExcel.php';
	include("../handlers/_generics.php");

	$con = new _init();

	date_default_timezone_set('Asia/Manila');
	set_time_limit(0);

	
	/* MYSQL QUERIES SECTION */
		$now = date("m/d/Y h:i a");
		$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");
		$searchString = '';

		if($_GET['item'] != '') { $searchString .= " and (b.item_code like '%$_GET[item]%' or b.description like '%$_GET[item]%') "; }

		$query = $con->dbquery("SELECT a.srr_no AS doc_no, DATE_FORMAT(srr_date,'%m/%d/%y') AS doc_date, a.remarks, b.item_code, b.description, b.unit, b.qty, b.cost, ROUND(qty*cost,2) as amount FROM srr_header a LEFT JOIN srr_details b ON a.srr_no = b.srr_no AND a.branch = b.branch WHERE a.branch = '$_SESSION[branchid]' AND a.srr_date BETWEEN '".$con->formatDate($_GET['dtf'])."' AND '".$con->formatDate($_GET['dt2'])."' AND a.status = 'Finalized' $searchString ORDER BY a.srr_date ASC, a.srr_no ASC;");
		//echo "SELECT a.sw_no AS doc_no, ppp_id, clinic_id, DATE_FORMAT(sw_date,'%m/%d/%y') AS doc_date, a.remarks, b.item_code, b.description, lot_no, if(expiry!='0000-00-00',date_format(expiry,'%m/%d/%Y'),'') as expiry, b.unit,b.qty, b.cost, ROUND(b.qty * b.cost) as amount FROM sw_header a LEFT JOIN sw_details b ON a.sw_no = b.sw_no AND a.branch = b.branch WHERE a.branch = '$_SESSION[branchid]' AND a.sw_date BETWEEN '".$con->formatDate($_GET['dtf'])."' AND '".$con->formatDate($_GET['dt2'])."' AND a.status = 'Finalized' $searchString ORDER BY a.sw_date ASC, a.sw_no ASC;";
	/* END OF SQL QUERIES */


	$headerStyle = array(
		'font' => array('bold' => true),
		'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
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
								 ->setTitle("$co[company_name] - STOCKCARD")
								 ->setSubject("$co[company_name] - STOCKCARD")
								 ->setDescription("$co[company_name] - STOCKCARD")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Exported File");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1","$co[company_name]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2",$co['company_address']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3","$co[tel_no]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","$co[website]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","Summary of Goods Received Covering the Period $_GET[dtf] to $_GET[dt2]");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A7","DOC #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B7","DOC DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C7","REMARKS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D7","ITEM CODE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E7","DESCRIPTION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F7","LOT NO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G7","EXPIRY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H7","UNIT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I7","QTY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J7","UNIT COST");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K7","AMOUNT");
	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(16);
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(40);
	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setAutoSize(true);

	$objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('B7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('C7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('D7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('E7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('F7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('G7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('H7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('I7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('J7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('K7')->applyFromArray($headerStyle);

	$row = 8;
	while($data = $query->fetch_array()) {
		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['doc_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['doc_date']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['remarks']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['item_code']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['description']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['lot_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$data['expiry']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data['unit']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data['qty']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$data['cost']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$data['amount']);

		for($j=0;$j<=10;$j++) {
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($j,$row)->applyFromArray($contentStyle);
		}

		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$row++; $amtGT+=$data['amount'];
	}

	/* Grand Total */
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$amtGT);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("Summary of Goods Received");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="srr_summary.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>