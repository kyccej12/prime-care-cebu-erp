<?php
    include("handlers/initDB.php");
   // ini_set("max_exectution_time",0);
    //ini_set("memory_limit",0);
    //ini_set("display_errors","On");

    $con = new myDB;

    $b = $con->dbquery("SELECT doc_no, or_no, doc_date, DATE_FORMAT(doc_date,'%Y') AS cy, customer_code, sc_discount, cash_tendered-change_due as cash, cc_tendered, check_tendered, ewt, amount_due, remarks FROM or_header WHERE `status` = 'Finalized' and doc_date = '2022-04-25';");
    while($a = $b->fetch_array()) {

        /* DEBIT SIDE */
        if($a['cash'] > 0) {
            $con->dbquery("INSERT IGNORE INTO acctg_gl (company,branch,cy,doc_no,doc_date,doc_type,contact_id,acct_branch,acct,debit,ref_no,ref_date,ref_type,cost_center,doc_remarks) VALUES ('1','1','$a[cy]','$a[doc_no]','$a[doc_date]','OR','$a[customer_code]','1','10102','$a[cash]','$a[or_no]','$a[doc_date]','OR','','".$con->escapeString($a['remarks'])."');");
        }

        if($a['check_tendered'] > 0) {
            $con->dbquery("INSERT IGNORE INTO acctg_gl (company,branch,cy,doc_no,doc_date,doc_type,contact_id,acct_branch,acct,debit,ref_no,ref_date,ref_type,cost_center,doc_remarks) VALUES ('1','1','$a[cy]','$a[doc_no]','$a[doc_date]','OR','$a[customer_code]','1','10102','$a[check_tendered]','$a[or_no]','$a[doc_date]','OR','','".$con->escapeString($a['remarks'])."');");
        }

        if($a['cc_tendered'] > 0) {
            $con->dbquery("INSERT IGNORE INTO acctg_gl (company,branch,cy,doc_no,doc_date,doc_type,contact_id,acct_branch,acct,debit,ref_no,ref_date,ref_type,cost_center,doc_remarks) VALUES ('1','1','$a[cy]','$a[doc_no]','$a[doc_date]','OR','$a[customer_code]','1','10202','$a[cc_tendered]','$a[or_no]','$a[doc_date]','OR','','".$con->escapeString($a['remarks'])."');");
        }

        if($a['ewt'] > 0) {
            $con->dbquery("INSERT IGNORE INTO acctg_gl (company,branch,cy,doc_no,doc_date,doc_type,contact_id,acct_branch,acct,debit,ref_no,ref_date,ref_type,cost_center,doc_remarks) VALUES ('1','1','$a[cy]','$a[doc_no]','$a[doc_date]','OR','$a[customer_code]','1','10403','$a[ewt]','$a[or_no]','$a[doc_date]','OR','','".$con->escapeString($a['remarks'])."');");
        }

        if($a['doc_date'] < '2022-01-17') {
            if($a['sc_discount'] > 0) {
                $con->dbquery("INSERT IGNORE INTO acctg_gl (company,branch,cy,doc_no,doc_date,doc_type,contact_id,acct_branch,acct,debit,ref_no,ref_date,ref_type,cost_center,doc_remarks) VALUES ('1','1','$a[cy]','$a[doc_no]','$a[doc_date]','OR','$a[customer_code]','1','60403','$a[sc_discount]','$a[or_no]','$a[doc_date]','OR','','".$con->escapeString($a['remarks'])."');");
            }
        }

        /* CREDIT SIDE */
        $c = $con->dbquery("SELECT a.doc_no, IF(b.rev_acct = '', '60101',b.rev_acct) AS rev_acct, SUM(amount_due) AS amount FROM or_details a LEFT JOIN services_master b ON a.code = b.code where a.doc_no = '$a[doc_no]' GROUP BY a.doc_no, b.rev_acct;");
        while($d = $c->fetch_array()) {
            $con->dbquery("INSERT IGNORE INTO acctg_gl (company,branch,cy,doc_no,doc_date,doc_type,contact_id,acct_branch,acct,credit,ref_no,ref_date,ref_type,cost_center,doc_remarks) VALUES ('1','1','$a[cy]','$a[doc_no]','$a[doc_date]','OR','$a[customer_code]','1','$d[rev_acct]','$d[amount]','$a[or_no]','$a[doc_date]','OR','','".$con->escapeString($a['remarks'])."');");
        }   

        echo "POSTING DOC # $a[doc_no] -> OR # $a[or_no]<br/>";


    }
    

?>