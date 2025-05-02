<?php
	session_start();
	ini_set("memory_limit",-1);
	ini_set("max_execution_time",0);
	//ini_set("display_errors","On");
	
	require_once "../../handlers/initDB.php";
	$pay = new myDB;
	/* MYSQL QUERY */
		
		$year = $_GET['year'];
		$now = date("m/d/Y h:i a");
		$co = $pay->getArray("select * from companies where company_id = '$_SESSION[company]';");

		if($_GET['dept'] != '') {
			$dept = " and a.dept = '$_GET[dept]' ";
		}

		if($_GET['eid'] != '') {
			$eid = " and a.emp_id = '$_GET[eid]' ";
		}


		$_ih = $pay->dbquery("SELECT EMP_ID AS id_no,ACCT_NO as acct, lname,fname,mname,'' AS prefix,a.DESG AS position,DATE_FORMAT(a.DATE_HIRED,'%m/%d/%Y') AS hired_date,BASIC_RATE AS `rate`, ROUND(BASIC_RATE/2,2) as semirate, a.PAYROLL_TYPE AS pay_type, a.DATE_RET FROM pccpayroll.emp_masterfile a WHERE 1=1 AND a.lname!='' AND a.lname !='' AND a.mname != '' AND a.EMPLOYMENT_STATUS NOT IN (7,8,9,10) $dept $eid ORDER BY a.lname,a.fname,a.mname;");
	
	
	/* END OF MYSQL */
		
	include("../../lib/PHPExcel/PHPExcel.php");
	date_default_timezone_set('Asia/Manila');
	set_time_limit(0);
		
	$headerStyle = array(
		'font' => array('bold' => true,'color' => array('rgb' => 'FFFFFF')),
		'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER),
		'borders' => array('outline' => array('style' =>PHPExcel_Style_Border::BORDER_THIN)),
		'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => '00b33c'))
	);
	
	$contentStyle = array(
		'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
	);

	$contentStyle2 = array(
		'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
		'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
	);
	
	$totalStyle = array(
		'font' => array('bold' => true),
		'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
	);

	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
	$objPHPExcel->getProperties()->setCreator("Payroll Master")
								 ->setLastModifiedBy("Payroll Master")
								 ->setTitle("$co[company_name] - PAYROLL SUMMARY")
								 ->setSubject("$co[company_name] - PAYROLL SUMMARY")
								 ->setDescription("$co[company_name] - PAYROLL SUMMARY")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Test result file");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1","$co[company_name]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2","$co[company_address]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3","$co[tel_no]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","13TH MONTH PAY REGISTER FOR CY $year");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","No.");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A6:A7');

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B6","ACCT #");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('B6:B7');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C6","Last Name");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('C6:C7');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D6","First Name");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('D6:D7');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E6","Middle Name");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('E6:E7');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F6","Suffix");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('F6:F7');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G6","Position");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('G6:G7');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H6","Date Hired");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('H6:H7');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I6","Salary Rate");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('I6:I7');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J6","Annual Basic Pay");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('J6:J7');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K6","13th Month Pay");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('K6:K7');
	
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('L6:M6');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L6","JANUARY");
	$objPHPExcel->getActiveSheet()->getColumnDimension("L6")->setWidth(300);
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('N6:O6');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("N6","FEBRUARY");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('P6:Q6');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("P6","MARCH");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('R6:S6');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("R6","APRIL");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('T6:U6');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("T6","MAY");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('V6:W6');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("V6","JUNE");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('X6:Y6');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("X6","JULY");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('Z6:AA6');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Z6","AUGUST");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('AB6:AC6');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AA6","SEPTEMBER");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('AD6:AE6');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AD6","OCTOBER");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('AF6:AG6');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AF6","NOVEMBER");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('AH6:AI6');
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AH6","DECEMBER");

	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L7","15th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("M7","30th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("N7","15th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("O7","30th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("P7","15th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Q7","30th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("R7","15th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("S7","30th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("T7","15th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("U7","30th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("V7","15th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("W7","30th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("X7","15th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Y7","30th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Z7","15th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AA7","30th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AB7","15th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AC7","30th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AD7","15th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AE7","30th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AF7","15th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AG7","30th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AH7","15th");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AI7","30th");

	for($zz = 0; $zz <= 34; $zz++) {
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($zz,6)->applyFromArray($headerStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($zz,7)->applyFromArray($headerStyle);
		
		if($zz == 0) {
			$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($zz)->setWidth(8);
		} else {
			$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($zz)->setAutoSize(true);
		}
	}
	
	$row = 8; $ctr=1;
	while($data = $_ih->fetch_array()) {
		$annual = 0;
		$annualPay = 0;
		
		//list($annual) = $pay->getArray("SELECT SUM(amount) AS amount FROM (SELECT IFNULL(SUM(basic_pay+sick_leave+vacation_leave+legal_holiday+special_holiday),0) AS amount FROM pccpayroll.emp_payslip WHERE period_id IN (SELECT period_id FROM pccpayroll.pay_periods WHERE reportingYear = '$year' and reportingMonth < 12 and period_id > 15) AND emp_id = '$data[id_no]' UNION ALL SELECT IFNULL(SUM(IF(adjustment_type='DB',amount,(amount*-1))),0) AS amount FROM pccpayroll.emp_adjustments WHERE emp_id = '$data[id_no]' AND nature=1 AND adjustment_date >= (SELECT period_start FROM pccpayroll.pay_periods WHERE reportingYear = '$year' and reportingMonth < 12 and period_id > 15 ORDER BY period_start ASC LIMIT 1) AND adjustment_date <= (SELECT period_end FROM pccpayroll.pay_periods WHERE reportingYear = '$year' and reportingMonth < 12 and period_id > 15 ORDER BY period_end DESC LIMIT 1) UNION ALL SELECT '$data[rate]' as amount) a;");
		//list($annual) = $pay->getArray("SELECT SUM(amount) AS amount FROM (SELECT IFNULL(SUM(basic_pay+sick_leave+vacation_leave+legal_holiday+special_holiday),0) AS amount FROM pccpayroll.emp_payslip WHERE period_id IN (SELECT period_id FROM pccpayroll.pay_periods WHERE reportingYear = '$year' and reportingMonth < 12 and period_id > 15) AND emp_id = '$data[id_no]' UNION ALL SELECT IFNULL(SUM(IF(adjustment_type='DB',amount,(amount*-1))),0) AS amount FROM pccpayroll.emp_adjustments WHERE emp_id = '$data[id_no]' AND nature=1 AND adjustment_date >= (SELECT period_start FROM pccpayroll.pay_periods WHERE reportingYear = '$year' and reportingMonth < 12 and period_id > 15 ORDER BY period_start ASC LIMIT 1) AND adjustment_date <= (SELECT period_end FROM pccpayroll.pay_periods WHERE reportingYear = '$year' and reportingMonth < 12 ORDER BY period_end DESC LIMIT 1)) a;");
		//$yearBasic = $annual+$data['rate'];
		//$annualPay = ROUND($yearBasic/12);

		if($year == '2022') {
			list($eagleAmount) = $pay->getArray("select amount from pccpayroll.thirteenth_beg where emp_id = '$data[id_no]';");
			$annualPay += $eagleAmount;
		}

	
		//if($annual > 0) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$ctr);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['acct']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['lname']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['fname']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['mname']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['prefix']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$data['position']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data['hired_date']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data['rate']);
			
			

			if($eagleAmount > 0) { $objPHPExcel->getActiveSheet()->getCommentByColumnAndRow(10,$row)->getText()->createTextRun("Adjustment To Thirteenth Month From EagleBytes: $eagleAmount"); }

			for($tt = 0; $tt <=10; $tt++) { $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($tt,$row)->applyFromArray($contentStyle); }
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8,$row)->getNumberFormat()->setFormatCode('#,##0.00');
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->getNumberFormat()->setFormatCode('#,##0.00');
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->getNumberFormat()->setFormatCode('#,##0.00');

			$basicPay1 = 0; $basicPay2 = 0; $adj1 = 0; $adj2 = 0; $col = 11; $yearBasic = 0;
			for($month = 1; $month <= 12; $month++) {
				$myMonth = str_pad($month,2,'0',STR_PAD_LEFT);
				$reportingMonth = STR_PAD($month,2,'0',STR_PAD_LEFT);
				
				if($month > 11) { 
					$myYearCut = $year . "-11-26";
					if($data['DATE_RET'] < $myYearCut && $data['DATE_RET'] != '0000-00-00') {
						$basicPay1 = 0;
						$basicPay2 = 0;
						$adj1 = 0;
						$adj2 = 0;
					} else {
						$basicPay1 = $data['semirate'];
						$basicPay2 = $data['semirate'];
						$adj1 = 0;
						$adj2 = 0;
					}

				} else {
				
					list($pid1) = $pay->getArray("select period_id from pccpayroll.pay_periods where reportingMonth = '$myMonth' and reportingYear = '$year' and weekOfMonth = '1' and period_id > 15;");
					list($pid2) = $pay->getArray("select period_id from pccpayroll.pay_periods where reportingMonth = '$myMonth' and reportingYear = '$year' and weekOfMonth = '2' and period_id > 15;");
					list($start1,$end1) = $pay->getArray("select period_start,period_end from pccpayroll.pay_periods where period_id = '$pid1';");
					list($start2,$end2) = $pay->getArray("select period_start,period_end from pccpayroll.pay_periods where period_id = '$pid2';");

					list($basicPay1) = $pay->getArray("SELECT IFNULL(SUM(basic_pay+sick_leave+vacation_leave+other_leaves+legal_holiday+special_holiday),0) as amount FROM pccpayroll.emp_payslip WHERE period_id = '$pid1' AND emp_id = '$data[id_no]';");
					list($basicPay2) = $pay->getArray("SELECT IFNULL(SUM(basic_pay+sick_leave+vacation_leave+other_leaves+legal_holiday+special_holiday),0) as amount FROM pccpayroll.emp_payslip WHERE period_id = '$pid2' AND emp_id = '$data[id_no]';");
					list($adj1) = $pay->getArray("SELECT IFNULL(SUM(IF(adjustment_type='DB',amount,(amount*-1))),0) AS amount FROM pccpayroll.emp_adjustments WHERE emp_id = '$data[id_no]' AND adjustment_date between '$start1' and '$end1' AND nature = '1';");
					list($adj2) = $pay->getArray("SELECT IFNULL(SUM(IF(adjustment_type='DB',amount,(amount*-1))),0) AS amount FROM pccpayroll.emp_adjustments WHERE emp_id = '$data[id_no]' AND adjustment_date between '$start2' and '$end2' AND nature = '1';");
				}
					
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col,$row,($basicPay1+$adj1));
				if($adj1 > 0) { $objPHPExcel->getActiveSheet()->getCommentByColumnAndRow($col,$row)->getText()->createTextRun("Adjustment To Basic Pay For The Period: $adj1"); }
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$row)->getNumberFormat()->setFormatCode('#,##0.00');
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$row)->applyFromArray($contentStyle);
				$col++;
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col,$row,($basicPay2+$adj2));
				if($adj2 > 0) { $objPHPExcel->getActiveSheet()->getCommentByColumnAndRow($col,$row)->getText()->createTextRun("Adjustment To Basic Pay For The Period: $adj2"); }
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$row)->getNumberFormat()->setFormatCode('#,##0.00');
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$row)->applyFromArray($contentStyle);
				$col++; $monthBasic = $basicPay1 + $basicPay2 + $adj1 + $adj2;
				$yearBasic+=$monthBasic;
			}

			$annualPay = ROUND($yearBasic / 12,2);

			list($isPaySlipExist) = $pay->getArray("select count(*) from pccpayroll.13th_month where cy = '$_GET[year]' and emp_id = '$data[id_no]';");
			if($isPaySlipExist > 0) {
				$pay->dbquery("update pccpayroll.13th_month set rate = '$data[rate]', annual = '$yearBaic', 13thmonth = '$annualPay' where emp_id = '$data[id_no]';");
			} else {
				$pay->dbquery("insert ignore into pccpayroll.13th_month (cy,emp_id,acct_no,lname,fname,mname,position,rate,annual,13thmonth) values ('$_GET[year]','$data[id_no]','$data[acct]','$data[lname]','$data[fname]','$data[mname]','$data[position]','$data[rate]','$yearBasic','$annualPay');");

			}



			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$yearBasic);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$annualPay);
		
			$row++; $ctr++;
		//}
	}
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("13th Month Pay");
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="13thmonthpay.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>