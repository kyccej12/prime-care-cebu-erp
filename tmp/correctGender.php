<?php

    require_once('../handlers/initDB.php');
    $con = new myDB();

    $a = $con->dbquery("SELECT * from univ_csv;");
    while($b = $a->fetch_array()) {
        $con->dbquery("UPDATE IGNORE patient_info set gender = '$b[GENDER]' where LNAME = '$b[LNAME]' and FNAME = '$b[FNAME]';");
        echo "UPDATE IGNORE patient_info set gender = '$b[GENDER]' where LNAME = '$b[LNAME]' and FNAME = '$b[FNAME]';<br/>";
    }

?>