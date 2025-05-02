<?php
	//ini_set("display_errors","On");
	require_once 'initDB.php';
	class payroll extends myDB {
	
		public $cutoff;
		public $ptype;
		public $ndays;
		public $restDays;
		public $baseDays;
		public $dtf;
		public $dt2; 
		public $foy;
		public $bleep;
		public $wom;
		public $dtrCount;
		public $reportingMonth;
		public $reportingYear;
		public $semiRate;
		public $dailyRate;
		public $hrate;
		public $minRate;
		
		public $def_ins;
		public $def_ins_min;
		public $def_ins_max;
		public $def_oas;
		public $def_oas_min;
		public $def_oas_max;
		public $def_ips;
		public $def_ips_min;
		public $def_ips_max;
		public $def_ops;
		public $def_ops_min;
		public $def_ahrs;
		public $def_phrs;
		public $defHours;
		
		public $ins;
		public $oas;
		public $ips;
		public $ops;
		
		public $late;
		public $ut;
		public $twork;
		public $overtime;
		public $restday;
		public $premium;
		public $htype;
		
		public $vl;
		public $sl;
		public $sil;
		public $absences;
		public $sss_premium;
		public $sss_premium_er;
		public $pg_premium;
		public $pg_premium_er;
		public $ph_premium;
		public $ph_premium_er;
		public $previousTaxable;
		public $onRestDayHoliday;
		public $additionalAbsent;
		public $minusRestHoliday;
		
		public function __construct($pid) {
			$a = parent::getArray("select period_start, period_end, date_format(period_end,'%d') as bleep, weekOfMonth, reportingMonth, reportingYear from pccpayroll.pay_periods where period_id = '$pid';");
			$this->cutoff = $pid;
			$this->dtf = $a['period_start'];
			$this->dt2 = $a['period_end']; 
			$this->bleep = $a['bleep'];
			$this->wom = $a['weekOfMonth'];
			$this->reportingMonth = $a['reportingMonth'];
			$this->reportingYear = $a['reportingYear'];
			
			list($this->foy) = parent::getArray("select date_format('".$this->dt2."','%Y-01-01');");
			list($this->ndays) = parent::getArray("select datediff('".$this->dt2."','".$this->dtf."') + 1;");
		
		}
			
		public function _toHrs($_x) {
			return ROUND($_x / 3600,2);
		}
		
		public function checkHoliday($date,$shift,$area) {
			if($shift == 0) {
				$this->htype = 'RD'; return true;
			} else {
				list($type) = parent::getArray("SELECT DISTINCT `type` FROM (SELECT `date`,IF(`type`=1,'LH','SH') AS `type` FROM pccpayroll.pay_holiday_nat WHERE `date` = '$date' UNION SELECT `date`,'SH' AS `type` FROM pccpayroll.pay_holiday_local WHERE `date` = '$date' and `area` = '$area') a limit 1;");
				if($type != '') {
					$this->htype = $type; return true;
				} else { $this->htype = 'NA'; return false; }
			}
		}
		
		public function checkRestDay($date,$eid) {

			list($myShift) = parent::getArray("select shift from pccpayroll.emp_dtrfinal where emp_id = '$eid' and date = '$date';");
			if($myShift == 0) { $this->htype = "RD"; return true; } else { $this->htype = "NA"; return false; }

			//if($day == 'Sat' || $day == "Sun") { $this->htype = "RD"; return true; } else { $this->htype = "NA"; return false; }
		}
		
		public function getRates($ptype,$rate) {
			if($ptype == 2) {
				$this->dailyRate = $rate;
				$this->hrate = ROUND($this->dailyRate/8,2);
				$this->minRate = ROUND($this->hrate/60,2);
			} else {
				$this->semiRate = ROUND($rate/2,2);
				$this->dailyRate = ROUND(($rate * 12) / 314,2);
				$this->hrate = ROUND((($rate * 12) / 314)/8,2);
				$this->minRate = ROUND($this->hrate/60,2);
			}
		}

		public function timeToSeconds($time) {
			$arr = explode(':', $time);
			if (count($arr) === 3) {
				return $arr[0] * 3600 + $arr[1] * 60 + $arr[2];
			}
			return $arr[0] * 60 + $arr[1];
		}
		
		public function getTimeDefaults($shift) {
			
			$slist = array('0','98','99');

			if(in_array($shift,$slist)) {
				$sqlTxt = "SELECT 25200 as def_in, 57600 as def_out, 8 as regular_hours;";
			} else {
				$sqlTxt = "SELECT TIME_TO_SEC(clockin) as def_in, TIME_TO_SEC(clockout) as def_out, regular_hours FROM pccpayroll.emp_shifts WHERE shift_id = '$shift';";
			}

			$a = parent::getArray($sqlTxt);
			$this->def_ins = $a['def_in'];
			$this->def_ins_min = $a['def_in'] - 7200;
			$this->def_ins_max = $a['def_in'] + 7200;
			$this->defHours = $a['regular_hours'];
			
			if( $a['def_out'] >= 60 &&  $a['def_out'] <= 32400) {
				$this->def_ops = $a['def_out'] + 86400;
			} else {
				$this->def_ops = $a['def_out'];
			}

			$this->def_ops_min = $this->def_ops - 7200;
			
		}
		
		public function computeTimeSheets($eid,$date,$shift,$in,$out) {
		
			$late = 0;
			$undertime = 0;

			$this->twork = 0; 
			$this->overtime = 0; 
			$this->premium = 0; 
			$this->late = 0;  
			$this->ut = 0;
			
			$slist = array('0','98','99','100');

			if(in_array($shift,$slist)) {
			
				$this->def_ins = $in;
				$this->def_ins_min = $in;
				$this->def_ins_max = $in;
				
				/* if($shift == '100') {
					$this->defHours = 12;
				} else {
					$this->defHours = 8;
				} */

				switch($shift) {
					case "98":
						$this->defHours = 8;
						$this->def_ops =  $in + 28800;
					break;
					case "100":
						$this->defHours = 12;
						$this->def_ops =  $in + 43200;
					break;
					default:
						$this->defHours = 8;
						$this->def_ops =  $in + 32400;
					break;
				}

				$this->def_ops_min = $this->def_ops - 7200;

			} else {
				$this->getTimeDefaults($shift);
			}

			
			/* AM Working Hours */
			if($in > 0) {
				if($in < $this->def_ins) { $in = $this->def_ins; }
				if($in > $this->def_ins) { 	$late = $this->_toHrs(($in - $this->def_ins)); } 

				if($in < 21600) {
					$this->premium = $this->_toHrs(21600-$in);
				}

				if($late >= 4) {
					$this->defHours = 4;
					$late = $this->_toHrs($in - ($this->def_ins + 18000)); 
					$this->late = $late;
				}
			}

			/* Compute if Time out is past midnight */
			if($out >= 60 && $out <= 32400) {
				$out += 86400;
				$this->premium = $this->_toHrs($out - 79200);
			}		
			
			/* Compute Overtime */
			if($out > $this->def_ops) {
				$this->overtime = $this->_toHrs($out - $this->def_ops);
				if($out > 79200) { $this->premium = $this->_toHrs($out - 79200); }
			}
			
			if($this->premium > 8) { $this->premium = 8; }
			
			/* Compute Undertime */
			if($out < $this->def_ops) {
				
				$halfDay = $this->def_ins + 16200;
				
				if($out <= $halfDay) {
					
					if($out > ($this->def_ins + 14400)) {
						$ut = 0;
					} else {
						$ut = 4 - ($this->_toHrs($out - $this->def_ins));
					}
					
					$u2 = 8 - ($this->_toHrs($out - $this->def_ins));
				} else {
					$ut = $this->_toHrs($this->def_ops - $out);
					$u2 = $ut;
				}
				
			}

			$this->twork = $this->defHours - $late - $u2; 

			if($this->twork > 4 && $this->twork <= 4.5) { $this->twork = 4 - $late; }

			if($shift != '0') { $this->late = $late; $this->ut = $ut; }
		}
		
		public function getEmployeeRestDays($eid,$area) {
			
			$this->restDays = 0; $this->onRestDayHoliday = 0; $holRD = 0;
			
			for($i = 0; $i < $this->ndays; $i++) {
				list($date,$nameOfDay) = parent::getArray("SELECT DATE_ADD('" .$this->dtf . "',INTERVAL $i DAY), DATE_FORMAT(DATE_ADD('" . $this->dtf . "',INTERVAL $i DAY),'%a')");
				list($rd,$rdDate) = parent::getArray("SELECT count(*),`date` from pccpayroll.emp_dtrfinal where emp_id = '$eid' and `DATE` = '$date' and `SHIFT` = '0';");
				
				if($rd > 0) { 
					$this->restDays++;
					
					/* Check for Rest Day Holiday */
					list($holRD) = parent::getArray("SELECT COUNT(*) FROM (SELECT `date` FROM pccpayroll.pay_holiday_nat WHERE `date` = '$rdDate' AND `type` = '2' UNION ALL SELECT DISTINCT `date` FROM pccpayroll.pay_holiday_local WHERE `date` = '$rdDate' AND `area` = '$area' UNION ALL SELECT DATE FROM pccpayroll.pay_holiday_nat WHERE `date`= '$rdDate' AND `type` = '1') a;");
					if($holRD > 0) {
						$this->onRestDayHoliday+=$holRD;
					}
				}
				
				/* else {
					if($this->checkRestDay($date,$eid)) {
						$this->restDays++;
					} 
				} */
				
				
				$this->baseDays = $this->ndays - $this->restDays;
			}

		}

		public function countHolidays($eid,$area) {
			$this->sholiday = 0; $this->lholiday = 0; $this->holidayCount = 0; $this->additionalAbsent = 0; $this->minusRestHoliday = 0;
			
			$regQuery = parent::dbquery("select `DATE` FROM pccpayroll.emp_dtrfinal where emp_id = '$eid' and `DATE` between '".$this->dtf."' and '".$this->dt2."';");
			while($regRow = $regQuery->fetch_array()) {
				
				if($this->cutoff == 50) {
					$xmas = array('2023-12-24','2023-12-25','2023-12-26');
					$newYear = array('2023-12-30','2023-12-31','2024-01-01');
					$allArray = array('2023-12-24','2023-12-25','2023-12-26','2023-12-30','2023-12-31','2024-01-01');

				

					if(!in_array($regRow[0],$allArray)) {
						list($sholiday) = parent::getArray("select count(*) FROM (SELECT `date` FROM pccpayroll.pay_holiday_nat WHERE `date` = '" . $regRow[0] . "' AND `type` = '2' UNION ALL SELECT `date` FROM pccpayroll.pay_holiday_local WHERE `date`= '" . $regRow[0] . "' AND `area` = '$area') a;");
						list($lholiday) = parent::getArray("select count(*) from pccpayroll.pay_holiday_nat where `date` = '". $regRow[0] . "' and `type` = '1';");
						$this->holidayCount += ($sholiday + $lholiday);
						$this->sholiday += $sholiday;
						$this->lholiday += $lholiday;
					} else {

						if(in_array($regRow[0],$xmas)) {
							list($woPayBefore) = parent::getArray("select count(*) from pccpayroll.pay_loa where w_pay = 'N' and date_to = '2023-12-22' and emp_id = '$eid';");
							list($woPayAfter) = parent::getArray("select count(*) from pccpayroll.pay_loa where w_pay = 'N' and (date_from = '2023-12-27' or date_to = '2023-12-27') and emp_id = '$eid';");
	
							if($woPayBefore == 0 && $woPayAfter == 0) {
								list($sholiday) = parent::getArray("select count(*) FROM (SELECT `date` FROM pccpayroll.pay_holiday_nat WHERE `date` = '" . $regRow[0] . "' AND `type` = '2' UNION ALL SELECT `date` FROM pccpayroll.pay_holiday_local WHERE `date`= '" . $regRow[0] . "' AND `area` = '$area') a;");
								list($lholiday) = parent::getArray("select count(*) from pccpayroll.pay_holiday_nat where `date` = '". $regRow[0] . "' and `type` = '1';");
								$this->holidayCount += ($sholiday + $lholiday);
								$this->sholiday += $sholiday;
								$this->lholiday += $lholiday;	
	
							} else {
								list($isRestDay) = parent::getArray("SELECT COUNT(*) FROM pccpayroll.emp_dtrfinal WHERE `date` = '$regRow[0]' AND SHIFT = 0 AND emp_id = '$eid';");
								if($isRestDay > 0) {
									$this->minusRestHoliday++;
								} else {
									$this->additionalAbsent++;
								}
							}
						}
	
						if(in_array($regRow[0],$newYear)) {
							list($woPayBefore) = parent::getArray("select count(*) from pccpayroll.pay_loa where w_pay = 'N' and date_to = '2023-12-29' and emp_id = '$eid';");
							list($woPayAfter) = parent::getArray("select count(*) from pccpayroll.pay_loa where w_pay = 'N' and (date_from = '2024-01-02' or date_to = '2024-01-02') and emp_id = '$eid';");
	
							if($woPayBefore == 0 && $woPayAfter == 0) {
								list($sholiday) = parent::getArray("select count(*) FROM (SELECT `date` FROM pccpayroll.pay_holiday_nat WHERE `date` = '" . $regRow[0] . "' AND `type` = '2' UNION ALL SELECT `date` FROM pccpayroll.pay_holiday_local WHERE `date`= '" . $regRow[0] . "' AND `area` = '$area') a;");
								list($lholiday) = parent::getArray("select count(*) from pccpayroll.pay_holiday_nat where `date` = '". $regRow[0] . "' and `type` = '1';");
								$this->holidayCount += ($sholiday + $lholiday);
								$this->sholiday += $sholiday;
								$this->lholiday += $lholiday;	
	
							} else {
								echo "SELECT COUNT(*) FROM pccpayroll.emp_dtrfinal WHERE `date` = '$regRow[0]' AND SHIFT = 0 AND emp_id = '$eid';";
								list($isRestDay) = parent::getArray("SELECT COUNT(*) FROM pccpayroll.emp_dtrfinal WHERE `date` = '$regRow[0]' AND SHIFT = 0 AND emp_id = '$eid';");
								if($isRestDay > 0) {
									$this->minusRestHoliday++;
								} else {
									$this->additionalAbsent++;
								}
							}
						}


					}

				} else {
					list($sholiday) = parent::getArray("select count(*) FROM (SELECT `date` FROM pccpayroll.pay_holiday_nat WHERE `date` = '" . $regRow[0] . "' AND `type` = '2' UNION ALL SELECT `date` FROM pccpayroll.pay_holiday_local WHERE `date`= '" . $regRow[0] . "' AND `area` = '$area') a;");
					list($lholiday) = parent::getArray("select count(*) from pccpayroll.pay_holiday_nat where `date` = '". $regRow[0] . "' and `type` = '1';");
					$this->holidayCount += ($sholiday + $lholiday);
					$this->sholiday += $sholiday;
					$this->lholiday += $lholiday;
				}
			}
		}
		
		public function getAbsences($eid,$area) {	
			
			$this->getEmployeeRestDays($eid,$area);
			$this->countHolidays($eid,$area);
			
			
			list($wholeDay) = parent::getArray("SELECT COUNT(*) FROM pccpayroll.emp_dtrfinal WHERE TOT_WORK > 4 AND EMP_ID = '$eid' AND `DATE` BETWEEN '". $this->dtf ."' AND '". $this->dt2 ."' AND SHIFT != 0;");
			list($halfDay) = parent::getArray("SELECT COUNT(*) / 2 FROM pccpayroll.emp_dtrfinal WHERE TOT_WORK > 0 AND TOT_WORK <= 4 AND EMP_ID = '$eid' AND `DATE` BETWEEN '". $this->dtf ."' AND '". $this->dt2 ."' AND SHIFT != 0;");
			
			$this->dtrCount = $wholeDay + $halfDay;
			list($sil) = parent::getArray("SELECT ifnull(SUM(`length`),0) FROM pccpayroll.pay_loa WHERE date_from >= '" . $this->dtf . "' AND date_to <= '" . $this->dt2 . "' AND w_pay = 'Y' AND emp_id = '$eid' and file_status != 'Deleted';");
			$myabsences = $this->baseDays - $sil - $this->dtrCount - $this->holidayCount; 
			if($myabsences > 0) { $this->absences = $myabsences; } else { return $this->absences = 0; }	
			
		}
		
		public function checkVL($eid,$credits) {
			list($prev_vl) = parent::getArray("SELECT ifnull(SUM(`length`),0) AS sil FROM pccpayroll.pay_loa WHERE emp_id = '$eid' and date_to < '" . $this->dtf . "' and date_to >= '". date('Y-01-01') ."' and w_pay = 'Y' and leave_type in ('1','2') and file_status != 'Deleted';");
			if($prev_vl < $credits) {
				$vlBalance = $credits - $prev_vl;	
				list($cur_vl) = parent::getArray("SELECT ifnull(SUM(`length`),0) AS sl FROM pccpayroll.pay_loa WHERE emp_id = '$eid' and date_to >= '". $this->dtf. "' AND date_to <= '" . $this->dt2 . "' and w_pay = 'Y' and leave_type in ('1','2') and file_status != 'Deleted';");
				if($vlBalance > $cur_vl) { $this->vl = $cur_vl; } else { $this->vl = $vlBalance; }
			} else { $this->vl = 0; }
		}
		
		public function checkSIL($eid) {
			list($sil) = parent::getArray("SELECT ifnull(SUM(`length`),0) AS sil FROM pccpayroll.pay_loa WHERE emp_id = '$eid' and date_to >= '". $this->dtf. "' AND date_to <= '" . $this->dt2 . "' and w_pay = 'Y' and leave_type not in (1,2) and file_status != 'Deleted';");
			$this->sil = $sil;
		}
		
		public function myPremiums($rate,$eid,$etype,$hdmf,$wSSS,$wPH,$wHDMF) {	
			$this->pg_premium = 0; $this->pg_premium_er = 0; $this->ph_premium = 0; $this->ph_premium_er = 0; $this->sss_premium = 0; $this->sss_premium_er = 0;
			
			if($rate > 1000) {
				if($this->wom == 2) { 
					if($wSSS == 'Y') {
						list($this->sss_premium,$this->sss_premium_er) = parent::getArray("select ee_share as ee, er_share as er from pccpayroll.sss_table where $rate >= range1 and $rate <= range2;");
					}
					if($wPH == 'Y') { 
						
						/* Update for New Contribution Table */
						if($this->cutoff > 50) {
							if($rate <= 10000) { $this->ph_premium = '250.00'; } else { if($rate >= 100000) { $this->ph_premium = "2500.00"; } else { $this->ph_premium = ROUND((($rate * 0.05)/2),2); }}
						} else {
							if($rate <= 10000) { $this->ph_premium = '200.00'; } else { if($rate >= 80000) { $this->ph_premium = "1600.00"; } else { $this->ph_premium = ROUND((($rate * 0.04)/2),2); }}
						}
						$this->ph_premium_er = $this->ph_premium;
					}
				} else {
					if($this->cutoff > 51) {
						if($wHDMF == 'Y') {	$this->pg_premium_er = 200; if($hdmf == 0 ) { $this->pg_premium = 200;	} else { $this->pg_premium = $hdmf; }}
					} else {
						if($wHDMF == 'Y') {	$this->pg_premium_er = 100; if($hdmf == 0 ) { $this->pg_premium = 100;	} else { $this->pg_premium = $hdmf; }}
					}
				}
			}
		}
		
		public function getPreviousPays($eid) {
			list($lastPID) = parent::getArray("select period_id from pccpayroll.pay_periods where reportingMonth='".$this->reportingMonth."' and reportingYear = '".$this->reportingYear."' and weekOfMonth = '1';");
			list($a) = parent::getArray("select gross_pay from pccpayroll.emp_payslip where emp_id = '$eid' and period_id = '$lastPID';");
			$this->previousTaxable = $a;
		}
		
		
		public function loadLoans($eid,$etype,$area,$dept,$ptype) {
			$r = parent::dbquery("select record_id as loan_id,loan_type, if(dedu_type=3,ROUND(semi_amrtz * multiplier,2),monthly_amrtz) as amrtz, date_loan, effective_date, amount_offsetted as offset from pccpayroll.emp_loanmasterfile where emp_id = '$eid' and '". $this->dtf ."' <= date_add(effective_date,INTERVAL loan_terms MONTH) and '". $this->dt2 ."' >= effective_date and file_status != 'Deleted' and `active` = 'Y' and file_status != 'Deleted' and dedu_type in ('". $this->wom . "','3') and balance > 0;");
			while(list($lid,$ltype,$samt,$d8,$ed8,$offset) = $r->fetch_array()) {
				parent::dbquery("insert ignore into pccpayroll.emp_deductionmaster (period_id,emp_type,pay_type,emp_id,type,area,dept,ref_id,ref_date,ref_type,amount,posted_by,posted_on) values ('". $this->cutoff ."','$etype','$ptype','$eid','L','$area','$dept','$lid','$d8','$ltype','$samt','$_SESSION[userid]',now());");
		
				/* Added Code due to compute tDeductions >= Period ID of Effective Date of Deduction */
				list($periodStartOfDeduction) = parent::getArray("SELECT period_id FROM pccpayroll.pay_periods WHERE '$ed8' >= period_start AND '$ed8' <= period_end;");

				list($tDeducted) = parent::getArray("select sum(amount) from pccpayroll.emp_deductionmaster where ref_id = '$lid' and emp_id = '$eid' and `type` = 'L' and period_id >= '$periodStartOfDeduction';");
				
				$tLoanApplied = $tDeducted + $offset;
				parent::dbquery("update ignore pccpayroll.emp_loanmasterfile set amt_paid = 0$tLoanApplied, balance = loan_amt - 0$tLoanApplied where record_id = '$lid' and emp_id = '$eid';");
			}
			
		}

		public function getLoans($eid,$pid,$type) {
			$amt = 0;
			list($amt) = parent::getArray("select sum(amount) from pccpayroll.emp_deductionmaster where period_id = '$pid' and emp_id='$eid' and type = 'L' and ref_type = '$type';");
			return $amt;
		}
		
		public function getOtherLoans($eid,$pid) {
			$amt = 0;
			list($amt) = parent::getArray("select sum(amount) from pccpayroll.emp_deductionmaster where period_id = '$pid' and emp_id='$eid' and type = 'L' and ref_type not in (1,2,4,5,6,7,8,10);");
			return $amt;
		}
		
	}
	
?>