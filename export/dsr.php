<?php
	session_start();
	ini_set("max_execution_time",0);
	ini_set("memory_limit","2056M");

	//ini_set("display_errors","on");
	require_once '../lib/PHPExcel/PHPExcel.php';
	require_once '../handlers/_generics.php';

	$mydb = new _init;
	$dtf = $mydb->formatDate($_GET['dtf']);
	$dt2 = $mydb->formatDate($_GET['dt2']);
	$searchString = '';

	if($_GET['customer'] != '') { $searchString .= " and a.customer_code = '" .  substr($_GET['customer'],1,6) . "' ";  }
	if($_GET['code'] != '' or $_GET['idesc'] != '') {
		$searchString .= " and (b.code = '$_GET[code]' or b.description like '%$_GET[desc]') ";
	}
	$query = $mydb->dbquery("SELECT * FROM (SELECT 'so' AS `type`, a.so_no, DATE_FORMAT(a.so_date,'%m/%d/%Y') AS sodate,'' AS or_no, '' AS ordate, a.customer_code, a.customer_name, a.patient_id, a.patient_name, c.description AS xterms, b.code,b.description, b.amount_due, a.amount AS charge_sales, '0' AS cash_sales, '0' AS cc_sales, '0' AS check_sales, '0' AS ad_payment, a.physician, a.referred_by FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN options_terms c ON a.terms = c.terms_id LEFT JOIN services_master d ON b.code = d.code WHERE a.status = 'Finalized' AND a.terms NOT IN ('0','100') AND a.so_date BETWEEN '$dtf' AND '$dt2' $searchString UNION ALL SELECT 'ap' AS `type`, a.so_no, DATE_FORMAT(a.so_date,'%m/%d/%Y') AS sodate,'' AS or_no, '' AS ordate, a.customer_code, a.customer_name, a.patient_id, a.patient_name, c.description AS xterms, b.code,b.description, b.amount_due, '0' AS charge_sales, '0' AS cash_sales, '0' AS cc_sales, '0' AS check_sales, a.amount AS ad_payment, a.physician, a.referred_by FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN options_terms c ON a.terms = c.terms_id WHERE a.status = 'Finalized' AND a.terms IN ('100') AND a.so_date BETWEEN '$dtf' AND '$dt2' $searchString UNION ALL SELECT 'or' AS `type`, b.so_no, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS so_date, a.or_no AS or_no, DATE_FORMAT(a.doc_date,'%m/%d/%Y') AS ordate, a.customer_code, a.customer_name, b.pid AS patient_id, b.pname AS patient_name, 'Cash' AS terms, b.code, b.description, b.amount_due, 0 AS charge_sales, a.amount_due AS cash_sales, '0' AS cc_sales, '0' AS check_sales, '0' AS ad_payment, '' as physician, '' as referred_by FROM or_header a LEFT JOIN or_details b ON a.doc_no = b.doc_no WHERE a.status = 'Finalized' AND a.doc_date BETWEEN '$dtf' AND '$dt2' AND a.cashtype IN ('1') $searchString UNION ALL SELECT 'cc' AS `type`, b.so_no, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS so_date, a.doc_no AS or_no, DATE_FORMAT(a.doc_date,'%m/%d/%Y') AS ordate, a.customer_code, a.customer_name, b.pid AS patient_id, b.pname AS patient_name, 'Cash' AS terms, b.code, b.description, b.amount_due, 0 AS charge_sales, cash_tendered AS cash_sales, cc_tendered AS cc_sales, '0' AS check_sales, '0' AS ad_payment, '' as physician, '' as referred_by FROM or_header a LEFT JOIN or_details b ON a.trace_no = b.trace_no WHERE a.status = 'Finalized' AND a.doc_date BETWEEN '$dtf' AND '$dt2' AND a.cashtype IN ('2') $searchString UNION ALL SELECT 'ck' AS `type`, b.so_no, DATE_FORMAT(b.so_date,'%m/%d/%Y') AS so_date, a.doc_no AS or_no, DATE_FORMAT(a.doc_date,'%m/%d/%Y') AS ordate, a.customer_code, a.customer_name, b.pid AS patient_id, b.pname AS patient_name, 'Cash' AS terms, b.code, b.description, b.amount_due, 0 AS charge_sales, '0' AS cash_sales, '0' AS cc_sales, a.amount_due AS check_sales, '0' AS ad_payment,'' as physician, '' as referred_by FROM or_header a LEFT JOIN or_details b ON a.doc_no = b.doc_no WHERE a.status = 'Finalized' AND a.doc_date BETWEEN '$dtf' AND '$dt2' AND a.cashtype IN ('3') $searchString) a ORDER BY or_no, so_no;");

	date_default_timezone_set('Asia/Manila');
	$now = date("m/d/Y h:i a");

	/* MYSQL QUERIES SECTION */
		$co = $mydb->getArray("select * from companies where company_id = '$_SESSION[company]';");
		if($_GET['acct'] != '') { $f2 = " and a.acct = '$_GET[acct]' "; }
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
	$objPHPExcel->getActiveSheet()->getColumnDimension("P")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("R")->setAutoSize(true);

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1",$co['company_name']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2",$co['company_address']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3",$co['tel_no']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","Detailed Sales Report Covering the Period $_GET[dtf] to $_GET[dt2]");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","SO #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B6","SO DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C6","OR #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D6","OR DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E6","BILLED TO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F6","PATIENT NAME");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G6","EMPLOYER");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H6","REFERRED BY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I6","REFERRING DOCTOR");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J6","TERMS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K6","CODE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L6","DESCRIPTION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("M6","AMOUNT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("N6","CHARGE SALES");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("O6","CASH SALES");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("P6","CARD SALES");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Q6","CHECK SALES");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("R6","ADV. PAYMENT");

	for($colheader = 0; $colheader <= 17; $colheader++) { $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($colheader,6)->applyFromArray($headerStyle); }

	$row = 7; $cashGT = 0; $chargeGT = 0; $ccGT = 0; $checkGT= 0; $adPGT = 0; $i = 1;

	while($data = $query->fetch_array(MYSQLI_BOTH)) {

		list($employer) = $mydb->getArray("select employer from patient_info where patient_id = '$row[patient_id]';");
		$charge = ''; $cash  = ''; $cc = ''; $check = ''; $ap = '';

		switch($data['type']) {
			case "so": if($data['so_no'] != $xso) {	$charge = $data['charge_sales']; $cash = ''; $cc = ''; $chargeGT += $data['charge_sales']; }	break;
			case "or": if($data['or_no'] != $xor) {	$cash = $data['cash_sales']; $charge = ''; $cc = ''; $cashGT += $data['cash_sales'];	} break;
			case "cc":	if($data['or_no'] != $xor) {$cc = $data['cc_sales']; $charge = ''; $cash = $data['cash_sales']; $ccGT += $data['cc_sales'];  $cashGT += $data['cash_sales']; }	break;
			case "ck":	if($data['or_no'] != $xor) {$check = $data['check_sales']; $charge = ''; $cash = ''; $ap = ''; $checkGT += $data['check_sales']; }	break;
			case "ap":	if($data['so_no'] != $xso) {$ap = $data['ad_payment']; $charge = ''; $cash = ''; $cc = ''; $check = ''; $adPGT += $data['ad_payment']; }	break;

		}

		if($data['customer_code'] != 0) { $billedto = html_entity_decode($data['customer_name']); } else { $billedto = 'PATIENT'; }

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['so_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['sodate']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['or_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['ordate']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$billedto);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,html_entity_decode($data['patient_name']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$employer);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,html_entity_decode($data['referred_by']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,html_entity_decode($data['physician']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$data['xterms']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$data['code']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$data['description']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$row,$data['amount_due']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,$row,$charge);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14,$row,$cash);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(15,$row,$cc);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(16,$row,$check);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(17,$row,$ap);
		/* NUMBER FORMAT */
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(17,$row)->getNumberFormat()->setFormatCode('#,##0.00');

		for($contentLoop = 0; $contentLoop <= 17; $contentLoop++) {
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contentLoop,$row)->applyFromArray($contentStyle);
		}
		$row++; $xso = $data['so_no']; $xor = $data['or_no'];
	}


	/* TOTAL */
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,$row,$chargeGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14,$row,$cashGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(15,$row,$ccGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(16,$row,$checkGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(17,$row,$adPGT);
	/* NUMBER FORMAT */

	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(17,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(17,$row)->applyFromArray($totalStyle);
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->freezePane('A7');
	$objPHPExcel->getActiveSheet()->setTitle("Detailed Sales Report");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="dsr.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>