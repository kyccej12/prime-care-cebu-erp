<?php
	session_start();
	ini_set("max_execution_time",0);
	ini_set("display_errors","on");
	require_once '../lib/PHPExcel/PHPExcel.php';
	require_once '../handlers/_generics.php';

	$mydb = new _init;

	$query = $mydb->dbquery("SELECT LPAD(a.id,6,0) AS id, a.`code`, a.description, a.fulldescription, b.category, c.subcategory, a.unit, d.sample_type, e.type AS container, a.result_type, a.unit_cost, a.unit_price FROM services_master a LEFT JOIN options_servicecat b ON a.category = b.id LEFT JOIN options_servicesubcat c ON a.subcategory = c.parent_id LEFT JOIN options_sampletype d ON a.sample_type = a.id LEFT JOIN options_containers e ON a.container_type = e.id WHERE a.file_status = 'Active' GROUP BY a.`code` ORDER BY a.id ASC;");

    
	date_default_timezone_set('Asia/Manila');
	$now = date("m/d/Y h:i a");

	/* MYSQL QUERIES SECTION */
		$co = $mydb->getArray("select * from companies where company_id = '1';");
	/* END OF SQL QUERIES */

	$headerStyle = array(
		'font' => array('bold' => true,'color' => array('rgb' => 'FFFFFF')),
		'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
		'borders' => array('outline' => array('style' =>PHPExcel_Style_Border::BORDER_THIN)),
		'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => '00b33c'))
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
								 ->setTitle("$co[company_name] - SERVICES LIST")
								 ->setSubject("$co[company_name] - SERVICES LIST")
								 ->setDescription("$co[company_name] - SERVICES LIST")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Exported File");

	$objDrawing = new PHPExcel_Worksheet_Drawing();
	$objDrawing->setName('test_img');
	$objDrawing->setDescription('test_img');
	$objDrawing->setPath('../images/prime-care-medgruppe.png');
	$objDrawing->setCoordinates('A1');                      
	//setOffsetX works properly
	$objDrawing->setOffsetX(5); 
	$objDrawing->setOffsetY(5);                
	//set width, height
	$objDrawing->setWidth(72); 
	$objDrawing->setHeight(72); 
	$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
	

	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
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

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A7","Services List Summary");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A9","RECORD NO.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B9","CODE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C9","DESCRIPTION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D9","FULL DESCRIPTION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E9","CATEGORY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F9","SUB-CATEGORY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G9","UNIT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H9","CONTAINER");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I9","RESULT TYPE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J9","UNIT COST");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K9","UNIT PRICE");

	for($colheader = 0; $colheader <= 10; $colheader++) { $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($colheader,9)->applyFromArray($headerStyle); }

	$row = 10;
	while($data = $query->fetch_array()) {

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['id']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['code']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,html_entity_decode($data['description']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,html_entity_decode($data['fulldescription']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['category']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['subcategory']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$data['unit']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data['container']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data['result_type']);		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$data['unit_cost']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$data['unit_price']);

        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->getNumberFormat()->setFormatCode('#,##0.00');

		for($contentLoop = 0; $contentLoop <= 10; $contentLoop++) {
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($contentLoop,$row)->applyFromArray($contentStyle);
		}
		$row++;
	}

    /* NUMBER FORMAT */
	
	// Rename worksheet
	// $objPHPExcel->getActiveSheet()->freezePane('A10');
	$objPHPExcel->getActiveSheet()->setTitle("Services List");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="serviceslist.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>