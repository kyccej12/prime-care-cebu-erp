<?php
	session_start();
	include("../handlers/_generics.php");
	$con = new _init;

	$con->dbquery("update acctg_gl set tmp_cleared='Y' where tmp_cleared = 'N' and acct = '$_POST[acct_code]' and branch = '1';");

	$date = $con->formatDate($_POST['xdate']);
	
	$cleared_d8 = $con->getArray("select distinct cleared_on from acctg_gl where cleared_on < '$date' and branch = '1' and acct = '$_POST[acct_code]' order by cleared_on desc limit 1;");
	if($cleared_d8[0] != '0000-00-00') { $prev_date = $cleared_d8[0]; } else { $prev_date = $date; }
	$x = $con->getArray("select sum(debit-credit) from acctg_gl where cleared_on <= '$prev_date' and acct_code='$_POST[acct_code]' and cleared='Y' and branch = '1' group by acct;");
	
	$cbalance = $con->getArray("select ROUND(sum(debit-credit),2),abs(ROUND(sum(debit-credit),2)) from acctg_gl where (tmp_cleared='Y' or cleared='Y') and acct = '$_POST[acct_code]' and branch = '1';");
	$c_cleared = $con->getArray("select count(*) from acctg_gl where tmp_cleared='Y' and credit > 0 and acct = '$_POST[acct_code]' and branch = '1';");
	$d_cleared = $con->getArray("select count(*) from acctg_gl where tmp_cleared='Y' and debit > 0 and acct ='$_POST[acct_code]' and branch = '1';");

	echo json_encode(array("bbalance"=>ROUND($x[0],2), "balance_beginning" => number_format($x[0],2), "cbalance"=>ROUND($cbalance[0],2), "abscbalance"=>ROUND($cbalance[1],2),"clearedbalance"=>number_format($cbalance[0],2),"c_cleared"=>$c_cleared[0],"d_cleared"=>$d_cleared[0]));

?>