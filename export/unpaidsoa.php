<?php
	session_start();
	require_once '../lib/PHPExcel/PHPExcel.php';
	include("../handlers/_generics.php");
	
	$con = new _init;

	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");
	$searchString = '';

	if($_GET['cid'] != '') { $searchString .= " and a.customer_code = '$_GET[cid]' "; $lbl = html_entity_decode($_GET['cname']); } else { $lbl = "All Customers"; }

	
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
								 ->setTitle("$co[company_name] - UNBILLED SALES ORDER")
								 ->setSubject("$co[company_name] - UNBILLED SALES ORDER")
								 ->setDescription("$co[company_name] - UNBILLED SALES ORDER")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Exported File");
	
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1",$co['company_name']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2",$co['company_address']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3",$co['tel_no']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4",$lbl);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A5","List of Unpaid Statement of Account Covering the Period $_GET[dtf] to $_GET[dt2]");

	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(24);
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(60);
	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A7","SOA #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B7","DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C7","BILLED TO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D7","REMARKS/MEMO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E7","TERMS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F7","AMOUNT DUE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G7","AMOUNT PAID");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H7","BALANCE");


	$objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('B7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('C7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('D7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('E7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('F7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('G7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('H7')->applyFromArray($headerStyle);

	$query = $con->dbquery("SELECT soa_no, LPAD(soa_no,6,0) AS soano, date_format(soa_date,'%m/%d/%Y') as sd8, customer_name, remarks, b.description AS terms_desc, amount, amount_paid AS paid, balance, `status` FROM soa_header a LEFT JOIN options_terms b ON a.terms = b.terms_id WHERE branch = '$_SESSION[branchid]' and balance > 0 and a.status = 'Finalized' $searchString;");

	$row = 8;
	while($data = $query->fetch_array()) {

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['soano']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['sd8']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,html_entity_decode($data['customer_name']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,html_entity_decode($data['remarks']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['terms_desc']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['amount']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$data['amount_paid']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data['balance']);


		for($ix=0;$ix<=7;$ix++) { $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($ix,$row)->applyFromArray($contentStyle); }
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,$row)->getNumberFormat()->setFormatCode('#,##0.00');

		$row++; $amtGT+=$data['amount']; $paidGT+=$data['amount_paid']; $balGT+=$data['balance'];
	}

	/* Total */
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$amtGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$paidGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$balGT);

	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,$row)->getNumberFormat()->setFormatCode('#,##0.00');

	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,$row)->applyFromArray($totalStyle);

	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("Unpaid SOA");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="unpaid_soa.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>