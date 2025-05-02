<?php
    include("handlers/initDB.php");
    $con = new myDB;

    ini_set("display_errors","On");

    $dQ = $con->dbquery("select id from pccpayroll.options_dept;");
    while($dR = $dQ->fetch_array()) {
        $a = $con->dbquery("SELECT emp_id, emp_type, payroll_type, `area`, dept FROM pccpayroll.emp_masterfile WHERE dept = '$dR[0]' AND file_status != 'Deleted';");
        while(list($eid,$etype,$ptype, $area, $dept) = $a->fetch_array()) { 
            $r = $con->dbquery("select record_id as loan_id,loan_type, if(dedu_type=3,ROUND(semi_amrtz * multiplier,2),monthly_amrtz) as amrtz, date_loan from pccpayroll.emp_loanmasterfile where emp_id = '$eid' and '2022-09-11' <= date_add(effective_date,INTERVAL loan_terms MONTH) and '2022-09-25' >= effective_date and file_status != 'Deleted' and `active` = 'Y' and dedu_type in ('2','3');");

            while(list($lid,$ltype,$samt,$d8) = $r->fetch_array(MYSQLI_BOTH)) {
                $con->dbquery("insert ignore into pccpayroll.emp_deductionmaster (period_id,emp_type,pay_type,emp_id,type,area,dept,ref_id,ref_date,ref_type,amount,posted_by,posted_on) values ('18','$etype','$ptype','$eid','L','$area','$dept','$lid','$d8','$ltype','$samt','$_SESSION[userid]',now());");

                list($tLoanApplied) = $con->getArray("select sum(amount) from pccpayroll.emp_deductionmaster where ref_id = '$lid' and emp_id = '$eid' and `type` = 'L';");
                $con->dbquery("update ignore pccpayroll.emp_loanmasterfile set amt_paid = 0$tLoanApplied, balance = loan_amt - 0$tLoanApplied where record_id = '$lid' and emp_id = '$eid';");
            }
        }
    }
?>