<?php
    include("../handlers/initDB.php");

    $con = new myDB;

    $query = $con->dbquery("select * from pccpayroll.bank_accounts");
    while($row = $query->fetch_array()) {
        $con->dbquery("UPDATE pccpayroll.emp_masterfile set ACCT_NO = '$row[ACCTNO]', ATM_BANK = '1' where EMP_ID = '$row[IDNO]';");
        ECHO "UPDATING EMPID $row[IDNO] -> $row[ACCTNO]<br/>";

    }

?>