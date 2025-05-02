<?php
	session_start();
	require_once("../lib/PHPExcel/PHPExcel.php");
	require_once("../handlers/_generics.php");
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
	
	$totalStyle = array(
		'font' => array('bold' => true),
		'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
	);
	
	$uid = $_SESSION['userid'];	
	$now = date("m/d/Y h:i a");
	$searchString = '';
	$con = new _init;


	if($_GET['cat'] != '') { $searchString .= "and a.category = '$_GET[cat]' "; }
	if($_GET['dtf'] != '' && $_GET['dt2'] != '') {
		$searchString .= "and date_acquired between '" . $con->formatDate($_GET['dtf'])."' and '".$con->formatDate($_GET['dt2']) . "' "; 
	} else {
		if($_GET['dtf'] != '') { 
			$searchString .= "and date_acquired = '" . $con->formatDate($_GET['dtf']) . "' "; 
		} elseif ($_GET['dt2'] != '') { 
			$searchString .= "and date_acquired = '". $con->formatDate($_GET['dt2']) . "' "; 
		} else { }
	}
	if($_GET['costcenter'] != '') { $searchString .= "and a.dept_code = '$_GET[costcenter]' "; }
	
	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");
	$query = $con->dbquery("SELECT asset_no, asset_description, b.category, c.costcenter, serial_no, vendor, po_no, IF(po_date!='0000-00-00',DATE_FORMAT(po_date,'%m/%d/%Y'),'') AS podate, inv_no, IF(warranty_exp!='0000-00-00',DATE_FORMAT(warranty_exp,'%m/%d/%Y'),'') AS warranty, life_span, cost, assigned_to, DATE_FORMAT(date_assigned,'%m/%d/%Y') AS assigned, `status`, remarks FROM fa_master a LEFT JOIN fa_category b ON a.category = b.id LEFT JOIN options_costcenter c ON a.dept_code = c.unitcode WHERE 1=1  $searchString ORDER BY a.asset_no;");
	
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
	$objPHPExcel->getProperties()->setCreator("Root Admin")
								 ->setLastModifiedBy("Root Admin")
								 ->setTitle("PCC - Fixed Asset")
								 ->setSubject("PCC - Fixed Asset")
								 ->setDescription("PCC - Fixed Asset")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Exported File");
	

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1",$co['company_name']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2",$co['company_address']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3",$co['tel_no']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","FIXED ASSET SUMMARY");

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","ASSET CODE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B6","ASSET DESCRIPTION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C6","CATEGORY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D6","COST CENTER");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E6","SERIAL NO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F6","VENDOR");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G6","PO NO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H6","PO DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I6","INVOICE NO.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J6","LIFE SPAN (IN YEARS)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K6","WARRANTY EXPIRATION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L6","ACQUISITION COST");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("M6","ASSIGNED TO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("N6","DATE ASSIGNED");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("O6","ASSET STATUS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("P6","REMARKS");
	
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
	$objPHPExcel->getActiveSheet()->getStyle('L6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('M6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('N6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('O6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('P6')->applyFromArray($headerStyle);



	$row = 7;
	while($data = $query->fetch_array()) {


		$pono = ltrim($data['po_no'],'0');
		list($podate) = $con->getArray("select date_format(po_date,'%m/%d/%Y') from po_header where po_no = '$pono';");

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['asset_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['asset_description']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['category']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['costcenter']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['serial_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['vendor']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$data['po_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$podate);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data['inv_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$data['life_span']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$data['warranty']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$data['cost']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$row,$data['assigned_to']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,$row,$data['assigned']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14,$row,$data['status']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(15,$row,$data['remarks']);

		
		for($j=0;$j<=15;$j++) {
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($j,$row)->applyFromArray($contentStyle);
		}
		
		/* NUMBER FORMAT */
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11,$row)->getNumberFormat()->setFormatCode('#,##0.00');

		$row++; $podate = '';
	}
		
		
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("Fixed Asset Master File");
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="fixedasset.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>