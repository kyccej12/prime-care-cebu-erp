<?php

	include("../../handlers/_generics.php");
	require_once '../../lib/PHPExcel/PHPExcel.php';
	date_default_timezone_set('Asia/Manila');
	//ini_set("display_errors","On");
	session_start();
		
	$con = new _init();	
		
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
	
	$searchString = '';
	if($_REQUEST['fstatus'] != '') { $searchString .= " and a.file_status = '$_REQUEST[fstatus]' "; }
	if($_REQUEST['class'] != '') { $searchString .= " and c.eTypeID = '$_REQUEST[class]' "; }
	if($_REQUEST['dept'] != '') { $searchString .= " and a.dept = '$_REQUEST[dept]' "; }
	

	switch($_REQUEST['estatus']) {
		case "97":
		break;
		case "98":
			$searchString .= " and a.employment_status not in (7,8,9) ";
		break;
		case "99":
			$searchString .= " and a.employment_status in (7,8,9) ";
		break;
		default:
			$searchString .= " and a.employment_status = '$_REQUEST[estatus]' ";
		break;
	}

	if($_REQUEST['dtf'] != '' && $_REQUEST['dt2'] != '') {
		$searchString .= " and a.date_hired between '" . $con->formatDate($_GET['dtf']) . "' and '" . $con->formatDate($_GET['dt2']) . "' ";
	}




	$queryString = "SELECT a.emp_id, b.eType, a.lname, a.fname, a.mname, '' AS suffix, gender, date_format(birthdate,'%m/%d/%Y') as birthdate, e.civil_status, date_format(date_hired,'%m/%d/%Y') as date_hired, date_format(date_regularized,'%d/%m/%Y') as date_regularized, date_ret, desg, f.dept_name, c.emp_status, IF(a.payroll_type=1,'Semi-Monthly','Dailies') AS pay_type, basic_rate, allowance, nontax_allowance, transpo_allowance, meal_allowance, ms_allowance AS rank_allowance, housing_allowance, hazard_allowance, comms_allowance, coop_premium, acct_no, vl_credit AS sil_credit, sss_no, hdmf_no, phealth_no, tin_no, w_sss, W_PHILHEALTH, W_HDMF, d.fullname AS lastupdated_by, DATE_FORMAT(updated_on,'%m/%d/%Y %r') AS lastupdated_on FROM pccpayroll.emp_masterfile a LEFT JOIN pccpayroll.option_emptype b ON a.emp_type = b.eTypeID LEFT JOIN pccpayroll.emp_status c ON a.employment_status = c.id LEFT JOIN pccmain.user_info d ON a.updated_by = d.emp_id LEFT JOIN pccpayroll.options_civilstatus e ON a.CIVIL_STATUS = e.csid LEFT JOIN pccpayroll.options_dept f ON a.dept = f.id WHERE 1=1 $searchString";
	$query = $con->dbquery($queryString);
	
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
	$objPHPExcel->getProperties()->setCreator("Root Admin")
								 ->setLastModifiedBy("Root Admin")
								 ->setTitle("JMC - Employee List")
								 ->setSubject("JMC - Employee List")
								 ->setDescription("JMC - Employee List")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Exported File");
	
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1","Medgruppe Polyclinics & Diagnostic Center Incorporated");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2","2nd Level, APM Centrale, A. Soriano Ave., Mabolo, NRA, Cebu City");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3","(032) 232-2273/266-3245");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","List of Employees");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","ID NO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B6","TYPE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C6","LAST NAME");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D6","FIRST NAME");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E6","MIDDLE NAME");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F6","SUFFIX");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G6","GENDER");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H6","BIRTHDATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I6","CIVIL STATUS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J6","DATE HIRED");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K6","DATE REGULARIZED");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L6","DATE OF EXIT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("M6","POSITION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("N6","CURRENT EMPLOYMENT STATUS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("O6","YEARS IN SERVICE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("P6","DEPARTMENT ASSIGNED");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Q6","PAYROLL CLASS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("R6","PAYROLL ACCT #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("S6","BASIC RATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("T6","ALLOWANCE (TAXABLE)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("U6","ALLOWANCE (NON-TAXABLE)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("V6","TRANSPO. ALLOWANCE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("W6","MEAL ALLOWANCE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("X6","RANK ALLOWANCE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Y6","COMMS ALLOWANCE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Z6","HOUSING/RELOCATION ALLOWANCE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AA6","HAZARD ALLOWANCE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AB6","SIL LEAVE CREDITS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AC6","SSS NO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AD6","HDMF ID NO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AE6","PHIC ID NO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AF6","T-I-N");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AG6","FILE STATUS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AH6","FILE LAST UPDATED BY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AI6","FILE LAST UPDATED ON");
	
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
	$objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("R")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("S")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("T")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("U")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("V")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("W")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("X")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("Y")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("Z")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AA")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AB")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AC")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AD")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AE")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AF")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AG")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AH")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AI")->setAutoSize(true);

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
	$objPHPExcel->getActiveSheet()->getStyle('Q6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('R6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('S6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('T6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('U6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('V6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('W6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('X6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('Y6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('Z6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AA6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AB6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AC6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AD6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AE6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AF6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AG6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AH6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AI6')->applyFromArray($headerStyle);
	
	$row = 7; $total_db = 0; $total_cr = 0;
	while($data = $query->fetch_array()) {

		if($data['date_ret'] != '0000-00-00' || $data['date_ret'] != '') {
			$serviceLength = $con->calculateAge($data['date_hired'],$data['date_ret']);
		} else {
			if($data['emp_status'] == 'REGULAR' || $data['PROBATIONARY']) {
				$serviceLength = $con->calculateAge($data['date_hired'],date('Y-m-d'));
			}
		}

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['emp_id']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['eType']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['lname']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['fname']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['mname']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['suffix']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$data['gender']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data['birthdate']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data['civil_status']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$data['date_hired']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$data['date_regularized']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$data['date_ret']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$row,$data['desg']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,$row,$data['emp_status']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14,$row,$serviceLength);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(15,$row,$data['dept_name']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(16,$row,$data['pay_type']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(17,$row,$data['acct_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(18,$row,$data['basic_rate']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(19,$row,$data['allowance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(20,$row,$data['nontax_allowance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(21,$row,$data['transpo_allowance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(22,$row,$data['meal_allowance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(23,$row,$data['rank_allowance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(24,$row,$data['comms_allowance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(25,$row,$data['housing_allowance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(26,$row,$data['hazard_allowance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(27,$row,$data['sil_credit']);

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(28,$row,$data['sss_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(29,$row,$data['hdmf_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(30,$row,$data['phealth_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(31,$row,$data['tin_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(32,$row,$data['file_status']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(33,$row,$data['lastupdated_by']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(34,$row,$data['lastupdated_on']);
		for($t = 0; $t <= 34; $t++) {
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($t,$row)->applyFromArray($contentStyle);
			if($t >= 18 && $t <= 27) {
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($t,$row)->getNumberFormat()->setFormatCode('#,##0.00');
			}
			if($t > 27) {
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($t,$row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			}
		}		
		
		$row++;
	}
	
	$row++;
	$etQuery = $con->dbquery("select count(*) as etcount, eType from ($queryString) a group by eType;");
	while($etRow = $etQuery->fetch_array()) {
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$etRow[1]);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$etRow[0]);
		$row++;
	}
	
	$row++;
	$ptQuery = $con->dbquery("select count(*) as ptcount, pay_type from ($queryString) a group by pay_type;");
	while($ptRow = $ptQuery->fetch_array()) {
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$ptRow[1]);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$ptRow[0]);
		$row++;
	}
	
	$row++;
	$estatQuery = $con->dbquery("select count(*) as estatCount, emp_status from ($queryString) a group by emp_status;");
	while($estatRow = $estatQuery->fetch_array()) {
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$estatRow[1]);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$estatRow[0]);
		$row++;
	}
	
	$row++;
	$projQuery = $con->dbquery("select count(*) as deptCount, dept_name from ($queryString) a group by dept_name;");
	while($projRow = $projQuery->fetch_array()) {
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$projRow[1]);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$projRow[0]);
		$row++;
	}
	
	
		
		
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("Employee Master File");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="employee.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>