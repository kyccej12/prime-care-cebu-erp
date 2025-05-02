<?php
	//ini_set("display_errors","On");
	session_start();
	require_once("../lib/mpdf6/mpdf.php");
	require_once '../handlers/initDB.php';
	
	require_once "../handlers/_payroll.php";
	$pay = new payroll($_REQUEST['cutoff']);
	
	ini_set("max_execution_time",-1);
	ini_set("memory_limit",-1);
	
	$cutoff = $_REQUEST['cutoff'];
	$dept = $_REQUEST['dept'];
	$eid = $_REQUEST['eid'];
	
	$whereString = '';
	

	if($dept != '') { $whereString .= "and dept = '$dept' "; }
	if($eid != '') { $whereString .= "and emp_id = '$eid' "; }
	$q = $pay->dbquery("select * from pccpayroll.emp_payslip a where period_id = '$cutoff' $whereString order by emp_name asc;");
	list($dtf,$dt2,$paydate) = $pay->getArray("select date_format(period_start,'%m/%d/%Y'), date_format(period_end,'%m/%d/%Y'), date_format(date_add(period_end,INTERVAL 5 DAY),'%m/%d/%Y') from pccpayroll.pay_periods where period_id = '$cutoff';");
	
	$mpdf=new mPDF('win-1252','FOLIO','','',5,5,5,5,5,5);
	$mpdf->use_embeddedfonts_1252 = true;    // false s default
	$mpdf->SetProtection(array('print'));
	$mpdf->SetAuthor("PORT80 Solutions");
	$mpdf->useSubstitutions = false; 
	$mpdf->SetDisplayMode(60);

$html = '
<html>
<head>
<style>
body {font-family: sans-serif; font-size: 6.5pt; }
td { vertical-align: top; font-size: 6.5pt; }
.e_info { border-top: 0.05mm solid #000000; }
</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">

</htmlpageheader>

<sethtmlpageheader name="myheader" value="off" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="off" />
mpdf-->';

while($res = $q->fetch_array()) {
	
	if($res['net_pay'] != 0) {

		list($desg,$dhired,$dept,$vl_credit,$brate,$ptype,$area,$ssno,$tin) = $pay->getArray("select ucase(desg) as desg,date_format(date_hired,'%m/%d/%Y'),b.dept_name,vl_credit,a.basic_rate,a.payroll_type,a.area,a.sss_no,a.tin_no from pccpayroll.emp_masterfile a left join pccpayroll.options_dept b on a.dept=b.id where a.emp_id='$res[emp_id]' and a.file_status != 'DELETED';");
		$pay->getRates($ptype,$brate);

		$allowanceTotal = $res['incentives']+$res['allowance']+$res['nontax_allowance']+$res['meal_allowance']+$res['transpo_allowance']+$res['housing_allowance']+$res['rank_allowance']+$res['comms_allowance']+$res['hazard_pay'];
		$deductionsTotal = $res['sss_premium']+$res['pagibig_premium']+$res['philhealth_premium']+$res['coop_premium']+$res['union_dues']+$res['wtax']+$res['loans_total']+$res['others_total'];
		
		if($res['wtax'] > 0) { $taxLbl = "[".number_format($res['taxable_income'],2)."]"; } else { $taxLbl = ''; }

		$html = $html . '<table width="100%">
		<tr>
			<td width=40>
				<img src="../images/logosmall.png" width=40 height=40 />
			</td>
			<td>
				<span style="font-size: 7pt;"><b>Medgruppe Polyclinics & Diagnostic Center, Inc.</b><br/>2nd Level, APM Centrale, A. Soriano Jr. Ave., NRA, Mabolo, Cebu City, 6000 Philippines<br/>Tel # (032) 232-2273/266-3245</span>
			</td>
			<td width="20%" align=right>
				<span style="font-weight: bold; font-size: 12pt; color: #000000;">PAY SLIP
			</td>
		</tr>
	</table>
	<table width="100%" cellspacing=0 cellpadding=0 class=e_info>
		<tr>
			<td width=10%>ID NUMBER</td>
			<td width=30% style="padding-left: 5px;">: '.$res['emp_id']. '</td>
			<td width=60% colspan=4></td>
		</tr>
		<tr>
			<td width=10%>NAME</td>
			<td width=30% style="padding-left: 5px;">: '. strtoupper(iconv("UTF-8", "ISO-8859-1//IGNORE", $res['emp_name'])). '</td>
			<td width=60% colspan=4></td>
		</tr>
		<tr>
			<td width=10%>DESIGNATION</td>
			<td width=30% style="padding-left: 5px;">: '.$desg.'</td>
			<td width=10%>SSS ID NO.</td>
			<td width=20% style="padding-left: 5px;">: ' . $ssno . '</td>
			<td width=15%>PAYROLL CUT OFF DATE</td>
			<td width=15% style="padding-left: 5px;">: '. $dtf . ' - ' . $dt2 .'</td>
		</tr>
		<tr>
			<td width=10%>DEPARMENT</td>
			<td width=30% style="padding-left: 5px;">: '.$dept.'</td>
			<td width=10%>T-I-N</td>
			<td width=20% style="padding-left: 5px;">: ' . $tin . '</td>
			';
			
			if($pay_type == 2) {
				$html .= '<td width=10%>CREDITED HOURS</td>
						  <td width=20% style="padding-left: 5px;">: '. $base .'</td>';
			} else {
				$html .= '<td width=15%>ABSENCES (IN DAYS)</td>
						  <td width=15% style="padding-left: 5px;">: '. ROUND($res['absences']/$res['daily_rate'],2) . '</td>';
			}
			
		$html .= '</tr>
		<tr>
			<td width=10%>DATE HIRED</td><
			<td width=30% style="padding-left: 5px;">: '. $dhired . '</td>
			<td width=30% colspan=2>&nbsp;</td>
			';

			if($pay_type == 2) {
				$html .= '<td width=30% colspan=2></td>';
			} else {
				$html .= '<td width=15%>LATE (IN MINS)</td>
						  <td width=15% style="padding-left: 5px;">: '. ROUND($res['late']/$pay->minRate) . '</td>';
			}
		$html .= '</tr>
		<tr>
			<td width=10%>BASIC RATE</td>
			<td width=20% style="padding-left: 5px;">: ' . number_format($brate,2) . '</td>
			<td width=30% colspan=2>&nbsp;</td>
			';
			
			if($pay_type == 2) {
				$html .= '<td width=30% colspan=2></td>';
			} else {
			    $html .= '<td width=15%>UNDERTIME (IN MINS)</td>
						  <td width=15% style="padding-left: 5px;">: '.ROUND($res['undertime']/$pay->minRate) . '</td>';
			}
	$html .= '</tr>
		</table>
		<table width=100% cellpadding=0 cellspacing=0 style="font-size: 10px; margin-top: 5px;">
			<tr>
				<td width=35% style="border: 0.1em solid black; border-collapse: collapse;">
					<table width=100% cellpadding=0 cellspacing=0>
						<tr>
							<td width="75%" style="border-bottom: 0.1mm solid black; padding: 5px;"><b>ANNEX A:</b> EARNINGS</td>
							<td width="25%" style="border-bottom: 0.1mm solid black;"></td>
						</tr>	
						<tr>
							<td width="100%" colspan=2 style="padding-left: 10px; font-weight: bold;">Basic Income</td>
						</tr>
						
						<tr>
							<td style="padding-left: 25px;">Basic Pay</td>
							<td style="padding-right: 5px; text-align: right;"><b>'.number_format(($res['basic_pay'] + $res['absences'] + $res['late'] + $res['undertime'] + $res['legal_holiday'] + $res['special_holiday']),2).'</b></td>
						</tr>
						<tr>
							<td style="padding-left: 35px; font-style: italic; font-size: 8px;">*Less: Absences</td>
							<td style="padding-right: 5px; text-align: right; font-size: 8px;">('.number_format($res['absences'],2).')</td>
						</tr>
						<tr>
							<td style="padding-left: 35px; font-style: italic; font-size: 8px;">*Less: Late</td>
							<td style="padding-right: 5px; text-align: right; font-size: 8px;">('.number_format($res['late'],2).')</td>
						</tr>
						<tr>
							<td style="padding-left: 35px; font-style: italic; font-size: 8px;">*Less: Undertime</td>
							<td style="padding-right: 5px; text-align: right; font-size: 8px;">('.number_format($res['undertime'],2).')</td>
						</tr>
						<tr>
							<td style="padding-left: 25px;">('. round($res['holiday_on_restday']/$res['daily_rate'])  .') Holiday on Rest Day</td>
							<td style="padding-right: 5px; text-align: right;">'.number_format($res['holiday_on_restday'],2).'</td>
						</tr>
						<tr>
							<td style="padding-left: 25px;">('. round($res['vacation_leave']/$res['daily_rate']) .') Service Incentive Leaves</td>
							<td style="padding-right: 5px; text-align: right;">'.number_format($res['vacation_leave'],2).'</td>
						</tr>
						<tr>
							<td style="padding-left: 25px;">('. round($res['other_leaves']/$res['daily_rate']) .') Other Paid Leaves (ML,EL,etc.)</td>
							<td style="padding-right: 5px; text-align: right;">'.number_format($res['other_leaves'],2).'</td>
						</tr>
						<tr>
							<td style="padding-left: 10px; font-weight: bold;">Basic Income Sub-total</td>
							<td style="padding-right: 5px; text-align: right; font-weight: bold;">'.number_format(($res['basic_pay'] + $res['other_leaves'] + $res['vacation_leave'] + $res['holiday_on_restday'] + $res['legal_holiday'] + $res['special_holiday']),2).'</td>
						</tr>
						<tr><td colspan=2>&nbsp;</td></tr>
						<tr>
							<td style="padding-left: 10px; font-weight: bold; margin-top: 5px;">ADD: Overtime</td>
							<td style="padding-right: 5px; text-align: right;"></td>
						</tr>
						<tr>
							<td style="padding-left: 25px;">Regular Overtime</td>
							<td style="padding-right: 5px; text-align: right;">'.number_format(($res['ot_regular']+$res['ot_regular_ex']),2).'</td>
						</tr>
						<tr>
							<td style="padding-left: 25px;">Legal Holiday Overtime</td>
							<td style="padding-right: 5px; text-align: right;">'.number_format(($res['ot_legalholiday']+$res['ot_legalholidayex']),2).'</td>
						</tr>
						<tr>
							<td style="padding-left: 25px;">Special Holiday Overtime</td>
							<td style="padding-right: 5px; text-align: right;">'.number_format(($res['ot_specialholiday']+$res['ot_specialholidayex']),2).'</td>
						</tr>
						<tr>
							<td style="padding-left: 25px;">Rest Day Overtime</td>
							<td style="padding-right: 5px; text-align: right;">'.number_format(($res['ot_sunday']+$res['ot_sundayex']),2).'</td>
						</tr>
						<tr>
							<td style="padding-left: 25px;">Night Differentials</td>
							<td style="padding-right: 5px; text-align: right;">'.number_format($res['night_premium'],2).'</td>
						</tr>
						<tr>
							<td style="padding-left: 10px; padding-top: 2px;"><b>ADD:</b> ALLOWANCES,INCENTIVES (ANNEX B)</td>
							<td style="padding-right: 5px; padding-top: 2px;text-align: right;"><b>'.number_format($allowanceTotal,2).'</b></td>
						</tr>
						<tr>
							<td style="padding-left: 10px; "><b>ADD:</b> SALARY ADJUSTMENTS (ANNEX E)</td>
							<td style="padding-right: 5px; text-align: right;"><b>'.number_format($res['adjustments'],2).'</b></td>
						</tr>
						<tr>
							<td style="text-align: left; padding-left: 10px; padding-top: 5px; font-size: 9pt;"><b>GROSS PAY</b></td>
							<td style="text-align: right;padding-right: 5px; padding-top: 5px; font-size: 9pt;"><b>'.number_format($res['gross_pay'],2).'</td>
						</tr>
						<tr>
							<td style="text-align: left; padding-left: 10px;"><b>LESS:</b> DEDUCTIONS (ANNEX C)</td>
							<td style="text-align: right;padding-right: 5px;"><b>('.number_format($deductionsTotal,2).')</td>
						</tr>
						<tr>
							<td style="text-align: left; padding-left: 10px; padding-bottom: 5px;  padding-top: 5px; font-size: 9pt;"><b>TAKEHOME PAY &raquo;</b></td>
							<td style="text-align: right; padding-right: 5px; font-size: 9pt; padding-top: 5px;"><b>'.number_format($res['net_pay'],2).'</td>
						</tr>
					</table>
				</td>
				<td width=35% style="border: 0.1em solid black; border-collapse: collapse;">
					<table width=100% cellpadding=0 cellspacing=0>
						<tr>
							<td width="70%" style="border-bottom: 0.1mm solid black; padding: 5px;"><b>ANNEX B:</b> ALLOWANCES, INCENTIVES</td>
							<td width="30%" style="border-bottom: 0.1mm solid black;"></td>
						</tr>';

						$t = 0;
						
						if($res['cola'] > 0) {
							$html .= '<tr>
										   <td style="padding-left: 10px;">COLA</td>
										   <td style="padding-right: 5px; text-align: right;">'.number_format($res['cola'],2).'</td>
									   </tr>';
							$t++;
						}


						if($res['meal_allowance'] > 0) {
							$html .= '<tr>
										   <td style="padding-left: 10px;">Meal Allowance</td>
										   <td style="padding-right: 5px; text-align: right;">'.number_format($res['meal_allowance'],2).'</td>
									   </tr>';
							$t++;
						}

						if($res['transpo_allowance'] > 0) {
							$html .= '<tr>
										   <td style="padding-left: 10px;">Transpo. Allowance</td>
										   <td style="padding-right: 5px; text-align: right;">'.number_format($res['transpo_allowance'],2).'</td>
									   </tr>';
							$t++;
						}

						if($res['housing_allownce'] > 0) {
							$html .= '<tr>
										   <td style="padding-left: 10px;">Housing/Relocation Allowance</td>
										   <td style="padding-right: 5px; text-align: right;">'.number_format($res['housing_allowance'],2).'</td>
									   </tr>';
							$t++;
						}

						if($res['rank_allowance'] > 0) {
							$html .= '<tr>
										   <td style="padding-left: 10px;">Managerial/Supervisory Allowance</td>
										   <td style="padding-right: 5px; text-align: right;">'.number_format($res['rank_allowance'],2).'</td>
									   </tr>';
							$t++;	
						}

						if($res['comms_allowance'] > 0) {
							$html .= '<tr>
										   <td style="padding-left: 10px;">Communcations Allowance</td>
										   <td style="padding-right: 5px; text-align: right;">'.number_format($res['comms_allowance'],2).'</td>
									   </tr>';
							$t++;
						}

						if($res['hazard_pay'] > 0) {
							$html .= '<tr>
										   <td style="padding-left: 10px;">Hazard Pay</td>
										   <td style="padding-right: 5px; text-align: right;">'.number_format($res['hazard_pay'],2).'</td>
									   </tr>';
							$t++;
						}

						if($res['incentives'] > 0) {
							
							$incQuery = $pay->dbquery("SELECT IF(remarks='',b.incType,remarks) AS inctype, a.amount FROM pccpayroll.emp_incentives a LEFT JOIN pccpayroll.option_incentives b ON a.incentive_type = b.id WHERE emp_id = '$res[emp_id]' AND incentive_date BETWEEN '" . $pay->dtf . "' AND '" . $pay->dt2 . "' AND file_status = 'Active';");
							while($incRow = $incQuery->fetch_array()) {
								$html .= '<tr>
										   <td style="padding-left: 10px;">'.$incRow['inctype'].'</td>
										   <td style="padding-right: 5px; text-align: right;">'.number_format($incRow['amount'],2).'</td>
									   </tr>';
								$t++;
								
							}
						}

						if($res['allowance'] > 0) {
							$html .= '<tr>
										   <td style="padding-left: 10px;">Taxable Allowances</td>
										   <td style="padding-right: 5px; text-align: right;">'.number_format($res['allowance'],2).'</td>
									   </tr>';
							$t++;
						}

						if($res['nontax_allowance'] > 0) {
							
							$html .= '<tr>
										   <td style="padding-left: 10px;">Non-Taxable Allowances</td>
										   <td style="padding-right: 5px; text-align: right;">'.number_format($res['nontax_allowance'],2).'</td>
									   </tr>';
							$t++;
						}

						for($t; $t<=8; $t++) {
							$html .= '<tr><td colspan=2>&nbsp;</td></tr>';
						}

						$html .= '</table>';
				
				
				
				
					$html .= '<table width=100% cellpadding=0 cellspacing=0 style="margin-top: 5px;">
						<tr>
							<td width="70%" style="border-bottom: 0.1mm solid black; border-top: 0.1mm solid black; padding: 5px;"><b>ANNEX C: </b>DEDUCTIONS</td>
							<td width="30%" style="border-bottom: 0.1mm solid black; border-top: 0.1mm solid black;"></td>
						</tr>
						<tr>
							<td width="100%" colspan=2 style="padding-left: 10px;font-weight: bold;">Premium Contributions</td>
						</tr>';

						$z = 0;

						if($res['philhealth_premium'] > 0) {
							$html .= '<<tr>
										<td style="padding-left: 25px;">Philhealth</td>
										<td style="padding-right: 5px; text-align: right;">'.number_format($res['philhealth_premium'],2).'</td>
									</tr>';
							$z++;
						}

						if($res['sss_premium'] > 0) {
							$html .= '<<tr>
										<td style="padding-left: 25px;">SSS Premium</td>
										<td style="padding-right: 5px; text-align: right;">'.number_format($res['sss_premium'],2).'</td>
									</tr>';
							$z++;
						}
						
						if($res['pagibig_premium'] > 0) {
							$html .= '<<tr>
										<td style="padding-left: 25px;">Pagibig/HDMF Premium</td>
										<td style="padding-right: 5px; text-align: right;">'.number_format($res['pagibig_premium'],2).'</td>
									</tr>';
							$z++;
						}

						if($res['wtax'] > 0) {
							$html .= '<<tr>
										<td style="padding-left: 25px;">Withholding Tax '.$taxLbl.'</td>
										<td style="padding-right: 5px; text-align: right;">'.number_format($res['wtax'],2).'</td>
									</tr>';
							$z++;
						}

						if($res['coop_premium'] > 0) {
							$html .= '<tr>
										<td style="padding-left: 25px;">Coop Premium</td>
										<td style="padding-right: 5px; text-align: right;">'.number_format($res['coop_premium'],2).'</td>
									</tr>';
							$z++;
						}
						
						
						$loanQuery = $pay->dbquery("SELECT c.loan_type,a.amount FROM pccpayroll.emp_deductionmaster a LEFT JOIN pccpayroll.emp_loanmasterfile b ON a.ref_id = b.record_id LEFT JOIN pccpayroll.option_loantype c ON b.loan_type = c.id WHERE a.emp_id = '$res[emp_id]' AND a.period_id = '$cutoff' AND a.type = 'L';");
						if(mysqli_num_rows($loanQuery) > 0) {
							$html .= '<tr>
										<td width="100%" colspan=2 style="padding-left: 10px; font-weight: bold;">Loans & Other Long Term Deductions</td>
									  </tr>';
							
							
							while($loanRows = $loanQuery->fetch_array()) {
								$html .= '<tr>
											<td style="padding-left: 25px;">'.$loanRows[0].'</td>
											<td style="padding-right: 5px; text-align: right;">'.number_format($loanRows[1],2).'</td>
										</tr>';
								$z++;
							}
						}
						
						$othersQuery = $pay->dbquery("SELECT c.remarks AS others, SUM(a.amount) AS amount FROM pccpayroll.emp_deductionmaster a LEFT JOIN pccpayroll.option_deductiontype b ON a.ref_type = b.id LEFT JOIN pccpayroll.emp_otherdeductions c ON a.ref_id = c.record_id WHERE a.emp_id = '$res[emp_id]' AND a.type = 'O' AND a.period_id = '$cutoff' GROUP BY a.ref_type;");
						if(mysqli_num_rows($othersQuery) > 0) {
							$html .= '<tr>
										<td width="100%" colspan=2 style="padding-left: 10px;font-weight: bold;">Other Deductions</td>
									  </tr>';
							
							while($othersRow = $othersQuery->fetch_array()) {
								$html .= '<tr>
										<td style="padding-left: 25px;">'.$othersRow[0].'</td>
										<td style="padding-right: 5px; text-align: right;">'.number_format($othersRow[1],2).'</td>
									</tr>'; 
								$z++;
							} 
						}
						

						for($z; $z <= 6; $z++) {
							$html .= '<tr><td width="100%" colspan=2>&nbsp;</td></tr>';
						}
						
						
						$html .= '
					</table>
				</td>
				<td width=30% style="border: 0.1em solid black; border-collapse: collapse;">
					<table width=100% cellpadding=0 cellspacing=0>
						<tr>
							<td width="70%" colspan=2 style="border-bottom: 0.1mm solid black; padding: 5px;"><b>ANNEX D:</b> OVERTIME DETAILS</td>
							<td width="30%" style="border-bottom: 0.1mm solid black;"></td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">REG OT</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['ot_regular_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['ot_regular'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">RD OT</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['ot_sunday_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['ot_sunday'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">RD OT EX</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['ot_sundayex_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['ot_sundayex'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">SP HOL OT</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['ot_specialholiday_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['ot_specialholiday'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">SP HOL EX</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['ot_specialholidayex_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['ot_specialholidayex'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">REG HOL OT</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['ot_legalholiday_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['ot_legalholiday'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">REG HOL EX</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['ot_regularholidayex_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['ot_regularholidayex'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">NIGHT PREM</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['night_premium_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['night_premium'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;"></td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;"></td>
							<td width="30%" style="padding-right: 5px; text-align: right;"></td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;"></td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;"></td>
							<td width="30%" style="padding-right: 5px; text-align: right;"></td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;"></td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;"></td>
							<td width="30%" style="padding-right: 5px; text-align: right;"></td>
						</tr>
						<tr>
							<td width="100%" style="border-bottom: 0.1mm solid black; border-top: 0.1mm solid black; padding: 5px;" colspan=3><b>ANNEX E:</b> SALARY ADJUSTMENT DETAILS</td>
						</tr>';
						
						$adjQuery = $pay->dbquery("SELECT remarks, IF(adjustment_type='CR',amount*-1,amount) AS amount FROM pccpayroll.emp_adjustments WHERE emp_id = '$res[emp_id]' AND adjustment_date between  '".$pay->dtf."' AND '".$pay->dt2."' AND file_status = 'Active';");
						if($adjQuery) {
							while($adjRow = $adjQuery->fetch_array()) {
								$html .= '<tr>
												<td width="70%" style="padding-left: 5px;" colspan=2>'.$adjRow[0].'</td>
												<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($adjRow[1],2).'</td>
										   </tr>
								';
							}
						} else {
							$html .= '<tr><td width="100%" style="padding-left: 5px;" colspan=3>N-O-N-E</td></tr>';
						}
						
					$html .= '</table>
				</td>
			</tr>
		</table>
		<table><tr><td height=20 valign=middle>&#9986;----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</td></tr></table>';
	}

}
$html = $html . '</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;
?>