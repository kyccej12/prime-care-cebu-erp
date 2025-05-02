<?php
    include("../handlers/initDB.php");

    $con = new myDB();

    $a = $con->dbquery("SELECT `CODE`, DESCRIPTION, a.SALES_CATEGORY, b.sales_id AS SALES_ID FROM services_csv a LEFT JOIN options_salescategory b ON a.SALES_CATEGORY = b.sales_category");
    while($b = $a->fetch_array()) {
        $con->dbquery("update services_master set sales_cat = '$b[SALES_ID]' where `code` = '$b[CODE]';");
        echo "update services_master set sales_cat = '$b[SALES_ID]' where `code` = '$b[CODE]';<br/>";

    }
?>