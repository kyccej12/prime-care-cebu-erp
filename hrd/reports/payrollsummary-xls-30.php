<?php
	session_start();
	//ini_set("display_errors","On");
	ini_set("memory_limit","1024M");
	ini_set("max_execution_time",0);

	require_once "../../handlers/initDB.php";
	$pay = new myDB;
	
	/* MYSQL QUERY */
		$cutoff = $_GET['cutoff'];
		if($_GET['dept'] != '') { $f1 = " and dept = '$_GET[dept]' "; }
		
		$now = date("m/d/Y h:i a");
		$co = $pay->getArray("select * from pccmain.companies where company_id = '1';");
		$fDates = $pay->getArray("select date_format(period_start,'%m/%d/%Y') as dtf, date_format(period_end,'%m/%d/%Y') as dt2 from pccpayroll.pay_periods where period_id = '$_GET[cutoff]';");
	
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
								 ->setTitle("$co[company_name] - PAYROLL SUMMARY")
								 ->setSubject("$co[company_name] - PAYROLL SUMMARY")
								 ->setDescription("$co[company_name] - PAYROLL SUMMARY")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Test result file");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1","$co[company_name]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2","$co[company_address]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3","$co[tel_no]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","PAYROLL REGISTER FOR CUTOFF PERIOD $fDates[0] to $fDates[1]");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","ID NO.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B6","EMPLOYEE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C6","ACCT #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D6","DESIGNATION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E6","DEPARTMENT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F6","NO. OF DAYS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G6","BASIC PAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H6","LESS: ABSENCES");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I6","LESS: LATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J6","LESS: UNDERTIME");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K6","COLA");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L6","VL PAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("M6","SL PAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("N6","OTHER LEAVES");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("O6","LGL HOLIDAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("P6","SP. HOLIDAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Q6","RD HOLIDAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("R6","OT REG");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("S6","OT SUN (REG+EX)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("T6","OT LH (REG+EX)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("U6","OT SH (REG+EX)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("V6","N. PREM");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("W6","ALLOWANCE (TAXABLE)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("X6","ALLOWANCE (NON-TAX)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Y6","ALLOWANCE (TRANSPO)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Z6","ALLOWANCE (RANK)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AA6","ALLOWANCE (HOUSING/RELOCATION)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AB6","ALLOWANCE (COMMS)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AC6","HAZARD PAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AD6","SAL. ADJ.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AE6","GROSS PAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AF6","SSS PREM");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AG6","HDMF PREM");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AH6","PHIC PREM");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AI6","COOP PREM");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AJ6","WTAX");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AK6","C.A");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AL6","SSS LOAN");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AM6","HDMF LOAN");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AN6","JAG LOAN");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AO6","LABORATORY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AP6","HEALTH INS.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AQ6","OTHER LOANS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AR6","OTHER DED.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AS6","TOTAL DED.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AT6","NET PAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AU6","ON HOLD");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AV6","ON CASH");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AW6","ON ATM");
	
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
	$objPHPExcel->getActiveSheet()->getColumnDimension("AJ")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AK")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AL")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AM")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AN")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AO")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AP")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AQ")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AR")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AS")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AT")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AU")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AV")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AW")->setAutoSize(true);
	
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
	$objPHPExcel->getActiveSheet()->getStyle('AJ6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AK6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AL6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AM6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AN6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AO6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AP6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AQ6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AR6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AS6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AT6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AU6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AV6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AW6')->applyFromArray($headerStyle);
	
	$row = 7;

	$a = $pay->dbquery("select * from pccpayroll.emp_payslip where period_id = '$cutoff' $f1 order by dept, emp_name;");
	while($data2 = $a->fetch_array()) {
		list($type,$desg,$dept) = $pay->getArray("select ATM_BANK,DESG,b.dept_name from pccpayroll.emp_masterfile a left join pccpayroll.options_dept b on a.dept = b.id where emp_id = '$data2[emp_id]';");
		if($row['on_hold'] != 'Y') {
			
			if($type == 0) { $cash = $data2['net_pay']; $atm = 0; } else { $cash = 0; $atm = $data2['net_pay']; }
		} else { $cash = 0; $atm = 0; }
	
		$otRD = $data2['ot_sunday'] + $data2['ot_sundayex'];
		$otLH = $data2['ot_legalholiday'] + $data2['ot_legalholidayex'];
		$otSH = $data2['ot_specialholiday'] + $data2['ot_specialholidayex'];
		$basicPay = $data2['basic_pay']+$data2['absences']+$data2['late']+$data2['undertime'];
		$dedTotal = $data2['sss_premium'] + $data2['philhealth_premium'] + $data2['hdmf_premium'] + $data2['wtax'] + $data2['hdmf_loan'] + $data2['sss_loan'] + $data2['cash_adv'] + $data2['coop_loan'] + $data2['coop_premium'] +  $data2['jag_loan'] + $data2['retirement_plan'] + $data2['health_ins'] + $data2['other_loans'] + $data2['others_total'];
		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data2['emp_id']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data2['emp_name']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data2['acct_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$desg);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$dept);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data2['basic_day']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$basicPay);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data2['absences']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data2['late']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$data2['undertime']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$data2['cola']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$data2['vacation_leave']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$row,$data2['sick_leave']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,$row,$data2['other_leaves']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14,$row,$data2['legal_holiday']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(15,$row,$data2['special_holiday']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(16,$row,$data2['holiday_on_restday']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(17,$row,$data2['ot_regular']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(18,$row,$otRD);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(19,$row,$otLH);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(20,$row,$otSH);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(21,$row,$data2['night_premium']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(22,$row,$data2['allowance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(23,$row,$data2['nontax_allowance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(24,$row,$data2['transpo_allowance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(25,$row,$data2['rank_allowance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(26,$row,$data2['housing_allowance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(27,$row,$data2['comms_allowance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(28,$row,$data2['hazard_pay']);

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(29,$row,$data2['adjustments']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(30,$row,$data2['gross_pay']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(31,$row,$data2['sss_premium']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(32,$row,$data2['hdmf_premium']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(33,$row,$data2['philhealth_premium']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(34,$row,$data2['coop_premium']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(35,$row,$data2['wtax']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(36,$row,$data2['cash_advance']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(37,$row,$data2['sss_loan']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(38,$row,$data2['hdmf_loan']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(39,$row,$data2['jag_loan']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(40,$row,$data2['laboratory']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(41,$row,$data2['health_ins']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(42,$row,$data2['other_loans']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(43,$row,$data2['others_total']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(44,$row,$dedTotal);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(45,$row,$data2['net_pay']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(46,$row,$data2['on_hold']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(47,$row,$cash);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(48,$row,$atm);
		for($y = 0; $y <= 48; $y++) { $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($y,$row)->applyFromArray($contentStyle); }
		for($z = 4; $z <= 48; $z++) { if($z!=46) { $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($z,$row)->getNumberFormat()->setFormatCode('#,##0.00'); }}
		
		$row++;
		$basicPayGT+=$data2['basic_pay'];
		$absGT+=$data2['absences'];
		$lateGT+=$data2['late'];
		$utGT+=$data2['undertime'];
		$colaGT+=$data2['cola'];
		$slGT+=$data2['sick_leave'];
		$vlGT+=$data2['vacation_leave'];
		$silGT+=$data2['other_leaves'];
		$lgGT+=$data2['legal_holiday']; 
		$spGT+=$data2['special_holiday'];
		$rdholGT+=$data2['holiday_on_restday'];
		$otGT+=$data2['ot_regular']; 
		$otRDGT+=$otRD;
		$otSHGT=$otSH;
		$otLHGT+=$otLH;
		$npGT+=$data2['night_premium']; 
		$altGT+=$data2['allowance']; 
		$alntGT+=$data2['nontax_allowance'];
		$mealGT+=$data2['meal_allowance'];
		$transpoGT+=$data2['transpo_allowance'];
		$commsGT+=$data2['comms_allowance'];
		$hazardGT+=$data2['hazard_pay'];
		$rankGT+=$data2['rank_allowance'];
		$houseGT+=$data2['housing_allowance'];
		$adjGT+=$data2['adjustments'];
		$grossGT+=$data2['gross_pay'];
		$sssGT+=$data2['sss_premium'];
		$hdmfGT+=$data2['hdmf_premium'];
		$phicGT+=$data2['philhealth_premium'];
		$cooppremGT+=$data2['coop_premium'];
		$wtaxGT+=$data2['wtax'];
		$caGT+=$data2['cash_adv'];
		$sssloanGT+=$data2['sss_loan'];
		$hdmfloanGT+=$data2['hdmf_loan'];
		$jagGT+=$data2['jag_loan'];
		$labGT+=$data2['laboratory'];
		$hinsGT+=$data2['health_ins'];
		$otherLoansGT+=$data2['other_loans'];
		$othersGT+=$data2['others_total'];
		$dedTotalGT+=$dedTotal;
		$netGT+=$data2['net_pay'];
		$cashGT+=$cash;
		$atmGT+=$atm;
			
	}

	/* GRAND TOTAL */
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$basicPayGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$absGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data2['late']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$utGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$colaGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$vlGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$row,$slGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,$row,$silGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14,$row,$lgGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(15,$row,$spGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(16,$row,$rdholGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(17,$row,$otGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(18,$row,$otRDGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(19,$row,$otLHGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(20,$row,$otSHGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(21,$row,$npGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(22,$row,$altGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(23,$row,$alntGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(24,$row,$transpoGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(25,$row,$rankGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(26,$row,$houseGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(27,$row,$commsGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(28,$row,$hazardGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(29,$row,$adjGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(30,$row,$grossGT);

	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(31,$row,$sssGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(32,$row,$hdmfGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(33,$row,$phicGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(34,$row,$cooppremGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(35,$row,$wtaxGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(36,$row,$caGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(37,$row,$sssloanGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(38,$row,$hdmfloanGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(39,$row,$jagGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(40,$row,$labGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(41,$row,$hinsGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(42,$row,$otherLoansGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(43,$row,$othersGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(44,$row,$dedTotalGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(45,$row,$netGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(47,$row,$cashGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(48,$row,$atmGT);
	
	for($y = 5; $y <= 48; $y++) { $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($y,$row)->applyFromArray($totalStyle); }
	for($z = 5; $z <= 48; $z++) { if($z!=46) { $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($z,$row)->getNumberFormat()->setFormatCode('#,##0.00'); }}
	
	$row+=2;
	

	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,"Prepared By:");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,"Checked By:");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,"Noted By:");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,"Approved By:");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,"Approved By:");
	
	$row+=2;
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,$row)->applyFromArray($signSpace);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2,$row)->applyFromArray($signSpace);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4,$row)->applyFromArray($signSpace);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6,$row)->applyFromArray($signSpace);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8,$row)->applyFromArray($signSpace);
	
	$row++;
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,"April Shawne Lucero (Payroll In-Charge)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,"Jenn Delmar T. Amora (HR Manager)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,"Janna M. Ragas (Finance Supervisor)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,"Jaime Gochoco (VP - Finance)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,"Dr. James Peter S. Aznar (President)");
	
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("Payroll Register");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="payregister-30.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>