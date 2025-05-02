<?php
    include("handlers/initDB.php");

    $con = new myDB;

    $a = $con->dbquery("select distinct code from lab_samples");
    while($b = $a->fetch_array()) {
        list($type) = $con->getArray("select container_type from services_master where `code` = '$b[code]';");
        if($type) { $con->dbquery("update lab_samples set samplecontainer = '$type' where `code` = '$b[code]';"); }
        unset($type);
    }

?>