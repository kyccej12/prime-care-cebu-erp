<?php
	session_start();
	ini_set("memory_limit","1024M");
	ini_set("max_execution_time",0);

	require_once "../../handlers/initDB.php";
	$pay = new myDB;
	$fs1 = ''; 
	/* MYSQL QUERY */

		$co = $pay->getArray("select * from companies where company_id = '1';");

		$now = date("m/d/Y h:i a");
		if($_REQUEST['emp_id'] != '') {
			$fs1 .= " and a.emp_id = '$_REQUEST[eid]' ";
		}

		if($_REQUEST['type'] != '') {
			$fs1 .= " and a.loan_type = '$_REQUEST[type]' ";

		}

		list($asof) = $pay->getArray("select period_end from pccpayroll.pay_periods where period_id = '$_REQUEST[cutoff]';");
		$mainQuery = $pay->dbquery("SELECT a.record_id, a.emp_id, CONCAT(b.lname,', ',b.fname,' ',LEFT(b.mname,1),'.') AS emp_name, c.loan_type, DATE_FORMAT(date_loan, '%m/%d/%Y') AS deyt, loan_amt AS gross_amt, loan_terms, monthly_amrtz, amount_offsetted AS OFFSET, '' AS balance FROM pccpayroll.emp_loanmasterfile a LEFT JOIN pccpayroll.emp_masterfile b ON a.emp_id=b.emp_id LEFT JOIN pccpayroll.option_loantype c ON a.loan_type = c.id WHERE a.file_status != 'Deleted' AND a.active != 'N' and date_loan <= '$asof' AND b.file_status != 'DELETED' $fs1 GROUP BY a.record_id ORDER BY b.lname, b.fname, a.date_loan DESC;");
		
	/* END OF MYSQL */
		
	include("../../lib/PHPExcel/PHPExcel.php");
	date_default_timezone_set('Asia/Manila');
	set_time_limit(0);
		
	$headerStyle = array(
		'font' => array('bold' => true),
		'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
		'borders' => array('outline' => array('style' =>PHPExcel_Style_Border::BORDER_THIN)),
	);
	
	$contentStyle = array(
		'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
	);
	
	$signSpace = array(
		'borders' => array('bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
	);
	
	$totalStyle = array(
		'font' => array('bold' => true),
		'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
	);
	
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
	$objPHPExcel->getProperties()->setCreator("Payroll Master")
								 ->setLastModifiedBy("Payroll Master")
								 ->setTitle("$co[company_name] - STATUTORY")
								 ->setSubject("$co[company_name] - STATUTORY")
								 ->setDescription("$co[company_name] - STATUTORY")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Exported File");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1","$co[company_name]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2","$co[company_address]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3","$co[tel_no]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","Loan Balances Summary as Of $asof");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","ID NO.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B6","EMPLOYEE NAME");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C6","LOAN ID");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D6","LOAN TYPE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E6","DATE AVAILED");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F6","LOAN TERMS (MO.)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G6","GROSS LOAN AMOUNT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H6","MONTHLY AMORTIZATION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I6","AMOUNT OFFSETTED");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J6","AMOUNT PAID");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K6","CURRENT BALANCE");
	
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
	
	$objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('B6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('C6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('D6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('E6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('F6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('G6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('H6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('I6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('J6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('K6')->applyFromArray($headerStyle);
	
	$row = 7;

	while($data = $mainQuery->fetch_array()) {

		$dedu = 0;
		list($dedu) = $pay->getArray("select ifnull(sum(amount),0) from pccpayroll.emp_deductionmaster where ref_id = '$data[record_id]' and `type` = 'L';");
			
		$balance = $data['gross_amt'] - $dedu;

	
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['emp_id']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['emp_name']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['record_id']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['loan_type']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['deyt']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['loan_terms']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$data['gross_amt']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data['monthly_amrtz']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data['offset']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$dedu);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$balance);
		
		for($y = 0; $y <= 10; $y++) { 
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($y,$row)->applyFromArray($contentStyle); 
			if($y > 5) {
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($y,$row)->getNumberFormat()->setFormatCode('#,##0.00');
			}
		}
		
		$row++; $grossGT+=$data['gross_amt']; $balGT+=$balance; $dedGT+=$dedu; $offsetGT+=$data['offset'];
			
	}

	/* GRAND TOTAL */
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$grossGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$offsetGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$dedGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$balGT);

	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("Loan Balances");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="loanbalances.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>