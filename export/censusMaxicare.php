<?php
	session_start();
	//ini_set("display_errors","on");
	ini_set("max_execution_time",0);
	ini_set("memory_limit",-1);
	
	require_once '../lib/PHPExcel/PHPExcel.php';
	include("../handlers/_generics.php");
	$con = new _init;

	date_default_timezone_set('Asia/Manila');
	set_time_limit(0);


	$dtf = $con->formatDate($_GET['dtf']);
	$dt2 = $con->formatDate($_GET['dt2']);
	
	$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");	
	$so = $con->getArray("SELECT company, tradename, address, shipping_address FROM contact_info WHERE file_id = '112';");

	$headerStyle = array(
		'font' => array('bold' => true,'color' => array('rgb' => 'FFFFFF')),
		'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
		'borders' => array('outline' => array('style' =>PHPExcel_Style_Border::BORDER_THIN)),
		'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => '00b33c'))
	);

	$headerStyle2 = array(
		'font' => array('bold' => true,'color' => array('rgb' => 'FFFFFF')),
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
	
	//$sql = $con->dbquery("SELECT b.cso_no, date_format(b.cso_date,'%d %b %Y') AS date_availed, b.cso_date, a.pid, CONCAT(c.lname,', ',c.fname, ', ',c.mname) AS pname, IF(c.gender='M','MALE','FEMALE') AS gender, DATE_FORMAT(c.birthdate,'%d %b %Y') AS bday, c.birthdate, a.wt, a.ht, a.bmi, a.bmi_category, a.bp, a.pulse, a.rr, CONCAT(lefteye,' - ',righteye) AS visual, fm_history, IF(cbc_normal='Y','NORMAL',cbc_findings) AS cbc, IF(ua_normal='Y','NORMAL',ua_findings) AS ua, IF(se_normal='Y','NORMAL',se_findings) AS se, IF(chest_normal='Y','NORMAL',chest_findings) AS chest, IF(ecg_normal='Y','NORMAL',ecg_findings) AS ecg, IF(pap_normal='Y','NORMAL',pap_findings) AS pap, classification, class_b_remarks1, pending_remarks, overall_remarks FROM peme a LEFT JOIN cso_header b ON a.so_no = b.cso_no LEFT JOIN pccmain.patient_info c ON a.pid = c.patient_id WHERE so_no = '$_REQUEST[so_no]' order by c.lname, c.fname;");
	
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
	$objPHPExcel->getProperties()->setCreator("Root Admin")
								 ->setLastModifiedBy("Root Admin")
								 ->setTitle("$co[company_name] - Center Result Summary")
								 ->setSubject("$co[company_name] - Center Result Summary")
								 ->setDescription("$co[company_name] - Center Result Summary")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Exported File");

	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,1,"MEDGRUPPE POLYCLINICS & DIAGNOSTIC CENTER INCORPORATED");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,2,"2nd Level, APM Central, A. Soriano Ave., NRA, Mabolo, Cebu City, 6000 Philippines");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,3,"Tel No. (32) 232-2273");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,4,"CENTER MAXICARE CENSUS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,6,"Billed To:");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,6,"MAXICARE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,7,"Billing Address:");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,7,"$so[address]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,8,"Location:");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,8,"CENTER");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,9,"Maxicare Census covering the period: ");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,9,"$_GET[dtf] to $_GET[dt2]");
	
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,1)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,2)->getFont()->setItalic(true);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,3)->getFont()->setItalic(true);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,4)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,6)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,7)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,8)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,9)->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,10)->getFont()->setBold(true);

	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,11,"NO.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,11,"SO NO.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,11,"COMPLETE NAME OF MEMBER");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,11,"COMPANY NAME");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,11,"MAXICARE NO");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,11,"TYPE OF AVAILMENT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,11,"MEMBERTYPE (Principal/Dependent)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,11,"AGE");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,11,"GENDER");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,11,"WEIGHT (kg)");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,11,"HEIGHT");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,11,"BMI");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,11,"CATEGORY");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,11,"BLOOD PRESSURE");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14,11,"BLOOD");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(15,11,"HEART RATE");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(16,11,"EYE");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(17,11,"ISHIHARA/COLOR");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(18,11,"SMOKER");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(19,11,"NO OF PACKS");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(20,11,"ALCOHOLIC");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(21,11,"AMOUNT OF ALCOHOL");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(22,11,"CHEST X-RAY");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(23,11,"TB");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(24,11,"Hgb");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(25,11,"Hct");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(26,11,"WBC COUNT");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(27,11,"PLATELET");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(28,11,"PROTEIN");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(29,11,"GLUCOSE");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(30,11,"RBC");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(31,11,"WBC");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(32,11,"BACTERIAL");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(33,11,"CASTS/CRYSTALS/OTHERS");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(34,11,"RESULT");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(35,11,"IF ABNORMAL");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(36,11,"FECALYSIS");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(37,11,"ECG");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(38,11,"PAPSMEAR");

    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(62,11,"OPHTALMOGOLIC");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(63,11,"GI &/OR GU");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(64,11,"BODY FAT ANALYSIS");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(65,11,"BONE DENSITY SCAN");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(66,11,"MAMOGRAM");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(67,11,"FINAL CLASSIFICATION");
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(68,11,"DIAGNOSIS");
	
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(17,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(18,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(19,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(20,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(21,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(22,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(23,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(24,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(25,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(26,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(27,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(28,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(29,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(30,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(31,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(32,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(33,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(34,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(35,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(36,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(37,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(38,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(39,11)->applyFromArray($headerStyle);

	$headQuery = $con->dbquery("SELECT DISTINCT `code`,UPPER(`description`) AS `procedure` FROM pccmain.services_master WHERE `code` IN ('L022','L113','L019','L018','L032','L029','L028','L004','L005','L020','L001','L016','L027','L109','L053','L082','O063','L122','L071','L006','L112','L030','L017','O002','') ORDER BY `code`;");
	$col = 39;
	while($headerRow = $headQuery->fetch_array()) {
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col,11,$headerRow[1]);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,11)->applyFromArray($headerStyle);
		$col++;
	}

	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(62,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(63,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(64,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(65,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(66,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(67,11)->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(68,11)->applyFromArray($headerStyle);


	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(34);
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
	$objPHPExcel->getActiveSheet()->getColumnDimension("AW")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AX")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AY")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AZ")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BA")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BB")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BC")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BD")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BE")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BF")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BG")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BH")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BI")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BJ")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BK")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BL")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BM")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BN")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BO")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BP")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("BQ")->setAutoSize(true);

	$row = 12; $no = 1;
	$dataQuery = $con->dbquery("SELECT c.*,'PEME' AS avail_type, a.so_no, a.so_date, b.employer, a.customer_code, customer_name, b.hmo_no AS max_no, a.patient_id as pid, a.patient_name as pname, 'Principal' AS membertype, b.gender, DATE_FORMAT(birthdate,'%d %b %Y') AS bday, birthdate,'' AS tb, c.wt, c.ht, c.bmi, c.bmi_category, c.bp, c.rr, c.pulse, CONCAT(c.lefteye,' - ',c.righteye) AS eye, c.classification, CONCAT(c.class_b_remarks1, ' ',c.class_b_remarks2, ' ',c.class_c_remarks1, '',c.class_c_remarks2, '',c.pending_remarks, '',overall_remarks) AS diagnosis, 'X' AS ishihara, c.smoker, '' AS blood, '' AS no_of_packs, c.alcoholic, '' AS amount_alcohol, d.protein, d.glucose, 'Normal' as normal, 'X' AS opthalmologic, 'X' AS giorgu, 'X' AS bodyfat, 'X' AS bonedensity, 'X' AS mamogram FROM so_header a LEFT JOIN patient_info b ON a.patient_id = b.patient_id LEFT JOIN peme c ON a.patient_id = c.pid LEFT JOIN lab_uaresult d ON a.patient_id = d.pid WHERE a.so_date between '$dtf' and '$dt2' and a.customer_code= '112' and c.so_no != '0' GROUP BY c.so_no;");

	while($data = $dataQuery->fetch_array()) {

		$age = $con->calculateAge($data['birthdate']);

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$no);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['so_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,html_entity_decode($data['pname']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['employer']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['max_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['avail_type']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$data['membertype']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$age);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data['gender']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$data['wt']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$data['ht']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$data['bmi']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$row,$data['bmi_category']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,$row,$data['bp']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14,$row,$data['blood']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(15,$row,$data['pulse']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(16,$row,$data['eye']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(17,$row,$data['ishihara']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(18,$row,$data['smoker']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(19,$row,$data['no_of_packs']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(20,$row,$data['alcoholic']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(21,$row,$data['amount_alcohol']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(21,$row,$data['smoker']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(21,$row,$data['no_of_packs']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(21,$row,$data['alcoholic']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(21,$row,$data['amount_alcohol']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(26,$row,$data['tb']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(27,$row,$data['protein']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(28,$row,$data['glucose']);
if($data['protein'] != '' && $data['glucose'] != '') {
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(33,$row,$data['normal']);
}else {
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(33,$row,"X");

}
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(62,$row,$data['opthalmologic']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(63,$row,$data['giorgu']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(64,$row,$data['bodyfat']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(65,$row,$data['bonedensity']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(66,$row,$data['mamogram']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(67,$row,$data['classification']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(68,$row,$data['diagnosis']);

		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(11,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(14,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(15,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(16,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(17,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(18,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(19,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(20,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(21,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(22,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(23,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(24,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(25,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(26,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(27,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(28,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(29,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(30,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(31,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(32,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(33,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(34,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(35,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(36,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(37,$row)->applyFromArray($contentStyle2);

		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(62,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(63,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(64,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(65,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(66,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(67,$row)->applyFromArray($contentStyle2);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(68,$row)->applyFromArray($contentStyle2);

        $dataInnerQuery = $con->dbquery("SELECT DISTINCT `code`,UPPER(`procedure`) AS `procedure` FROM lab_samples WHERE so_no = '$data[so_no]' and `code` like '%X%';");
		while($dataInnerRow = $dataInnerQuery->fetch_array()) {
				list($stat,$extracted) = $con->getArray("select status, extracted from lab_samples where so_no = '$data[so_no]' and `code` = '$dataInnerRow[code]';");
					if($extracted == 'Y') {
						$val = "YES";
							if($extracted == '') {
								$val = "X";
							}
					} else {
						$val = "NO";
					}
					/************************************************************************************* */
				list($hemoglobin) = $con->getArray("SELECT a.hemoglobin from lab_cbcresult a LEFT JOIN lab_samples b on a.serialno = b.serialno where a.so_no = '$data[so_no]';");
					if($hemoglobin != '') { 
						if($hemoglobin < 12 ) { $val1 = "Low"; } else if($hemoglobin > 17){ $val1 = "High"; }else { $val1 = "Normal"; }
					} else {
						$val1 = "X";
					}
					/************************************************************************************ */
				list($hematocrit) = $con->getArray("SELECT a.hematocrit from lab_cbcresult a LEFT JOIN lab_samples b on a.serialno = b.serialno where a.so_no = '$data[so_no]';");
                	if($hematocrit > 0) {
						if($hematocrit < 40 ) { $val2 = "Low"; } else if($hematocrit > 50){ $val2 = "High"; }else { $val2 = "Normal"; }
					}else {
						$val2 = "X";
					}
					/************************************************************************************ */
				list($wbc) = $con->getArray("SELECT a.wbc from lab_cbcresult a LEFT JOIN lab_samples b on a.serialno = b.serialno where a.so_no = '$data[so_no]';");
                	if($wbc > 0) {
						if($wbc < 4000 ) { $val3 = "Low"; } else if($wbc > 10000){ $val3 = "High"; }else { $val3 = "Normal"; }
					} else {
						$val3 = "X";
					}
					/*********************************************************************************** */
				list($platelet) = $con->getArray("SELECT a.platelate from lab_cbcresult a LEFT JOIN lab_samples b on a.serialno = b.serialno where a.so_no = '$data[so_no]';");
                	if($platelet > 0) {
						if($platelet < 150000 ) { $val4 = "Low"; } else if($platelet > 450000){ $val4 = "High"; }else { $val4 = "Normal"; }
					}else {
						$val4 = "X";
					}
					/*********************************************************************************** */
				list($wbc) = $con->getArray("SELECT a.wbc_hpf from lab_uaresult a LEFT JOIN lab_samples b on a.serialno = b.serialno where a.so_no = '$data[so_no]';");
                	if($wbc > 0) {
						if($wbc < 0-1 ) { $val5 = "Low"; } else if($wbc > 2 ){ $val5 = "High"; }else { $val5 = "Normal"; }
					}else {
						$val5 = "X";
					}
				// 	/*********************************************************************************** */
				list($rbc) = $con->getArray("SELECT a.rbc_hpf from lab_uaresult a LEFT JOIN lab_samples b on a.serialno = b.serialno where a.so_no = '$data[so_no]';");
                	if($rbc > 0) {
						if($rbc < 0-1 ) { $val6 = "Low"; } else if($rbc > 2){ $val6 = "High"; }else { $val6 = "Normal"; }
					}else {
						$val6 = "X";
					}
				// 	/*********************************************************************************** */
				list($bacteria) = $con->getArray("SELECT bacteria from lab_uaresult a LEFT JOIN lab_samples b on a.serialno = b.serialno where a.so_no = '$data[so_no]';");
                	if($bacteria > 0) {
						if($bacteria == 'FEW' ) { $val7 = "Low"; } else if($bacteria == 'MANY' && $bacteria == 'ABUNDAnt'){ $val7 = "High"; }else { $val7 = "Normal"; }
					}else {
						$val7 = "X";
					}

        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(22,$row,$val);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(24,$row,$val1);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(25,$row,$val2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(26,$row,$val3);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(27,$row,$val4);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(30,$row,$val5);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(31,$row,$val6);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(32,$row,$val7);

        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(22,$row)->applyFromArray($contentStyle2);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(24,$row)->applyFromArray($contentStyle2);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(25,$row)->applyFromArray($contentStyle2);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(26,$row)->applyFromArray($contentStyle2);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(27,$row)->applyFromArray($contentStyle2);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(30,$row)->applyFromArray($contentStyle2);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(31,$row)->applyFromArray($contentStyle2);
        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(32,$row)->applyFromArray($contentStyle2);
        $col++;
		}

		$dataInnerQuery = $con->dbquery("SELECT DISTINCT `code`,UPPER(`procedure`) AS `procedure` FROM lab_samples WHERE so_no = '$data[so_no]' and `code` != 'O009' and `code` in ('O001','L013','L066') ORDER BY `code`;");
		$col = 36;
		while($dataInnerRow = $dataInnerQuery->fetch_array()) {
			
				list($stat,$extracted) = $con->getArray("select status, extracted from lab_samples where so_no = '$data[so_no]' and `code` = '$dataInnerRow[code]';");
				if($stat == '') {
					$val = "X";
				} else {
					if($extracted == 'Y') {
						$val = "✓";
					} else {
						$val = "NOT COMPLIED";
					}
				}
			
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col,$row,$val);
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(36,$row)->applyFromArray($contentStyle2);
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(37,$row)->applyFromArray($contentStyle2);
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(38,$row)->applyFromArray($contentStyle2);
			$col++;
		}


		$dataInnerQuery = $con->dbquery("SELECT DISTINCT `code`,UPPER(`description`) AS `procedure` FROM pccmain.services_master WHERE `code` IN ('L022','L113','L019','L018','L032','L029','L028','L004','L005','L020','L001','L016','L027','L109','L053','L082','O063','L122','L071','L006','L112','L030','L017','O002','') ORDER BY `code`;");
		$col = 39;
		while($dataInnerRow = $dataInnerQuery->fetch_array()) {
			
			list($stat,$extracted) = $con->getArray("select status, extracted from lab_samples where so_no = '$data[so_no]' and `code` = '$dataInnerRow[code]';");
			if($stat == '') {
				$val = "X";
			} else {
				if($extracted == 'Y') {
					$val = "✓";
				} else {
					$val = "NOT COMPLIED";
				}
			}
		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col,$row,$val);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$row,$val)->applyFromArray($contentStyle2);
		$col++;
	}


		$row++; $no++;
	}
	


	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("Center Maxicare Census");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="centermaxicarecensus.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>