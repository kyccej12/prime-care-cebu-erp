<?php
	session_start();
	require_once '../lib/PHPExcel/PHPExcel.php';
	include("../handlers/_generics.php");

	$con = new _init();

	date_default_timezone_set('Asia/Manila');
	set_time_limit(0);

	
	/* MYSQL QUERIES SECTION */
    $now = date("m/d/Y h:i a");
    $co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");
    $searchString = '';

    if($_GET['cid'] != "") { $searchString .= " and a.supplier = '$_GET[cid]' "; } else { $cust = "All Suppliers"; }
    if($_GET['item'] != '') { $searchString .= " and (b.item_code like '%$_GET[item]%' or b.description like '%$_GET[item]%') "; }

    $query = $con->dbquery("SELECT a.rr_no, date_format(a.rr_date,'%m/%d/%y') as rd8, a.supplier, a.supplier_name, b.po_no, date_format(b.po_date,'%m/%d/%y') as pd8, b.item_code, b.description, b.unit, ROUND(SUM(b.qty),2) AS qty, cost, ROUND(sum(qty*cost),2) as amount FROM rr_header a LEFT JOIN rr_details b ON a.rr_no = b.rr_no AND a.branch = b.branch WHERE a.rr_date BETWEEN '".$con->formatDate($_GET['dtf'])."' AND '".$con->formatDate($_GET['dt2'])."' AND a.status = 'Finalized' AND a.branch='$_SESSION[branchid]' and b.amount > 0 $searchString group by a.rr_no,b.po_no,b.item_code order by a.rr_no asc;");
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
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","Summary of Goods Received From Suppliers");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A5","Date Covered $_GET[dtf] to $_GET[dt2]");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A7","RR #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B7","RR DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C7","SUPPLIER");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D7","PO #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E7","ITEM CODE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F7","DESCRIPTION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G7","UNIT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H7","QTY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I7","COST");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J7","AMOUNT");
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

	$row = 8;
	while($data = $query->fetch_array()) {

        if($data['rr_no'] != $o) { $s = $data['supplier_name']; $d = $data['rd8']; $r = STR_PAD($data['rr_no'],6,0,STR_PAD_LEFT); } else { $s = ""; $d = ""; $r = ""; }
		$amt = $data['amount'];


		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$r);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$d);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$s);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['po_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['item_code']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['description']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$data['unit']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data['qty']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data['cost']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$data['amount']);

		for($j=0;$j<=8;$j++) {
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($j,$row)->applyFromArray($contentStyle);
		}

		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$row++; $amtGT+=$amt;
	}

	/* Grand Total */
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$amtGT);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("Summary of Receiving Report");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="rr_summary.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>