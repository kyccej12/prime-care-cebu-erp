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

	$query = $mydb->dbquery("SELECT 'so' AS `type`, a.so_no, DATE_FORMAT(a.so_date,'%m/%d/%Y') AS sodate,'' AS or_no, '' AS ordate, a.customer_code, a.customer_name, a.patient_id, a.patient_name, a.patient_address, c.description AS xterms, b.code,b.description, a.amount, '0' AS charge_sales, '0' AS cash_sales, '0' AS cc_sales, '0' AS check_sales FROM so_header a LEFT JOIN so_details b ON a.trace_no = b.trace_no LEFT JOIN options_terms c ON a.terms = c.terms_id WHERE a.status = 'Finalized' AND a.so_date BETWEEN '$dtf' AND '$dt2' $searchString GROUP BY patient_id ORDER BY so_no;");

	date_default_timezone_set('Asia/Manila');
	$now = date("m/d/Y h:i a");

	/* MYSQL QUERIES SECTION */
		$co = $mydb->getArray("select * from companies where company_id = '$_SESSION[company]';");
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
								 ->setTitle("$co[company_name] - Detailed Daily Census Report")
								 ->setSubject("$co[company_name] - Detailed Daily Census Report")
								 ->setDescription("$co[company_name] - Detailed Daily Census Report")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Exported File");
	

	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(16);
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(48);
	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("L")->setAutoSize(true);

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1",$co['company_name']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2",$co['company_address']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3",$co['tel_no']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","Detailed Daily Census Report Covering the Period $_GET[dtf] to $_GET[dt2]");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","NO.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B6","SO #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C6","SO DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D6","BILLED TO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E6","PATIENT NAME");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F6","PATIENT ADDR");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G6","CONTACT NO.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H6","EMPLOYER");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I6","TERMS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J6","CODE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K6","DESCRIPTION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L6","AMOUNT");


	for($colheader = 0; $colheader <= 11; $colheader++) { $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($colheader,6)->applyFromArray($headerStyle); }

	$row = 7; $cashGT = 0; $i = 1;

	while($data = $query->fetch_array(MYSQLI_BOTH)) {

		list($employer) = $mydb->getArray("select employer from patient_info where patient_id = '$data[patient_id]';");
        list($contactnum) = $mydb->getArray("select mobile_no from patient_info where patient_id = '$data[patient_id]';");
        if($data['customer_code'] != 0) { $billedto = $data['customer_name']; } else { $billedto = 'PATIENT'; }

        if($data['type'] == 'so') {
            if($data['so_no'] != $xso) {
                $charge = number_format($data['amount'],2); $cashGT += $data['amount'];
            }
        }

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$i);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['so_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['sodate']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$billedto);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,html_entity_decode($data['patient_name']));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['patient_address']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$contactnum);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$employer);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data['xterms']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$data['code']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$data['description']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$data['amount']);

		/* NUMBER FORMAT */
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		

		for($contentLoop = 0; $contentLoop <= 11; $contentLoop++) {
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contentLoop,$row)->applyFromArray($contentStyle);
		}
		$row++; $xso = $data['so_no']; $xor = $data['or_no']; $i++;
	}


	/* TOTAL */
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$cashGT);

	/* NUMBER FORMAT */

	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11,$row)->getNumberFormat()->setFormatCode('#,##0.00');

	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11,$row)->applyFromArray($totalStyle);

	// Rename worksheet
	$objPHPExcel->getActiveSheet()->freezePane('A7');
	$objPHPExcel->getActiveSheet()->setTitle("Detailed Sales Report");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="dcr.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>