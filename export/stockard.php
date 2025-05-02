<?php
	session_start();
	require_once '../lib/PHPExcel/PHPExcel.php';
	include("../handlers/_generics.php");
	$con = new _init;

	date_default_timezone_set('Asia/Manila');
	set_time_limit(0);

	/* MYSQL QUERIES SECTION */
		
		$now = date("m/d/Y h:i a");
		$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");
		$description = $con->identItemDescription($_REQUEST['item_code']);
		/* Check Last Physical Inventory for the Item */
		list($baseD8) = $con->getArray("select posting_date from phy_header a left join phy_details b on a.doc_no = b.doc_no and a.branch = b.branch where a.branch = '$_SESSION[branchid]' and b.item_code = '$_GET[item_code]' and b.lot_no = '$_GET[lot_no]' and b.expiry = '".$con->formatDate($_GET['expiry'])."' order by posting_date desc limit 1;");

		/* Forward Balance = From Last Date of Physical Count and Before Period Start */
		$pi = $con->getArray("select ifnull(sum(b.qty),0) from phy_header a left join phy_details b on a.doc_no = b.doc_no and a.branch=b.branch where a.branch = '$_SESSION[branchid]' and b.item_code = '$_GET[item_code]' and lot_no = '$_GET[lot_no]' and expiry = '".$con->formatDate($_GET['expiry'])."' and a.status = 'Finalized' and a.posting_date = '$baseD8' GROUP BY b.item_code;");
		$run = $con->getArray("select sum(purchases+inbound-outbound-pullouts-sold) as run from ibook where doc_date >= '$baseD8' and doc_date < '" . $con->formatDate($_GET['dtf']) . "' and item_code = '$_GET[item_code]' and lot_no = '$_GET[lot_no]' and expiry = '".$con->formatDate($_GET['expiry'])."' and doc_branch = '$_SESSION[branchid]';");
		
		$query = $con->dbquery("select doc_type, lpad(doc_no,6,0) as xdoc, cname, date_format(doc_date,'%m/%d/%Y') as dd8, if((purchases+inbound-pullouts-outbound-sold) > 0,abs(purchases+inbound-pullouts-outbound-sold),0) as `in`, if((purchases+inbound-pullouts-outbound-sold) < 0,(purchases+inbound-pullouts-outbound-sold),0) as `out`, (purchases+inbound-pullouts-outbound-sold) as run from ibook where item_code = '$_GET[item_code]' and lot_no = '$_GET[lot_no]' and expiry = '".$con->formatDate($_GET['expiry'])."' and doc_branch = '$_SESSION[branchid]' and doc_date between '".$con->formatDate($_GET['dtf'])."' and '".$con->formatDate($_GET['dt2'])."';");
		
		$beg = $pi[0] + $run[0];
	
		
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
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1",$co['company_name']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2",$co['company_address']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3","$co[tel_no]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","$co[website]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","Inventory Stockcard");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A5","Item Code: $_GET[item_code]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","Description: $description");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D5","Lot No. : $_REQUEST[lot_no]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D6","Expiry : $_REQUEST[expiry]");

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A7","DOC #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B7","DOC DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C7","DOC TYPE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D7","DESTINATION/ORIGIN");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E7","IN");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F7","OUT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G7","RUNNING QTY");
	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(16);
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);

	$objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('B7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('C7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('D7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('E7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('F7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('G7')->applyFromArray($headerStyle);

	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,8,'BALANCE FORWARDED FROM PREVIOUS PERIOD >>');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,8,$beg);
	
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,8)->applyFromArray($contentStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6,8)->applyFromArray($contentStyle);
	
	$row = 9;
	while($data = $query->fetch_array()) {

		$beg += $data['run'];

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['xdoc']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['dd8']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['doc_type']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['cname']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['in']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['out']*-1);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$beg);
		
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6,$row)->applyFromArray($contentStyle);
		$row++;
	}
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("INVENTORY STOCKARD");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="stockcard.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>