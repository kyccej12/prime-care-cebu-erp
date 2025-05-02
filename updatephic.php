<?php
    include("handlers/initDB.php");
    $con = new myDB;
    
    ini_set("display_errors","On");

   $a = $con->dbquery("select emp_id, philhealth_premium, philhealth_premium_er from pccpayroll.emp_payslip2 where period_id = 18;");
    while($b = $a->fetch_array()) {
        $con->dbquery("update ignore pccpayroll.emp_payslip set philhealth_premium = '$b[philhealth_premium]', philhealth_premium_er = '$b[philhealth_premium_er]' where period_id = 18 and emp_id = '$b[emp_id]';");
        echo "update ignore pccpayroll.emp_payslip set philhealth_premium = '$b[philhealth_premium]', philhealth_premium_er = '$b[philhealth_premium_er]' where period_id = 18 and emp_id = '$b[emp_id]';<br/>";

    }

    echo "ECHO";
?>