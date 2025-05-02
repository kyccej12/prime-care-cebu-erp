<?php
	session_start();
	include("../handlers/_generics.php");
	$con = new _init;

	$date = $con->formatDate($_POST['xdate']);
	$q = $con->dbquery("SELECT * FROM (SELECT record_id AS gl_no, a.cy,doc_no,doc_date,b.check_no,DATE_FORMAT(b.check_date,'%m/%d/%y') AS check_date, DATE_FORMAT(doc_date,'%m/%d/%Y') AS ddate, credit AS cr_amount,a.doc_type,contact_id AS contact,tmp_cleared, a.doc_remarks FROM acctg_gl a LEFT JOIN cv_header b ON a.doc_no = b.cv_no AND a.branch = b.branch WHERE doc_date > '2018-12-31' AND doc_date <= '$date' AND a.cleared='N' AND a.branch='1' AND a.acct = '$_POST[acct_code]' AND credit > 0 AND a.doc_type = 'CV' UNION SELECT record_id AS gl_no, cy, doc_no, doc_date, '' AS check_no, '' AS check_date, DATE_FORMAT(doc_date,'%m/%d/%y') AS ddate, credit AS cr_amount,doc_type,contact_id AS contact,tmp_cleared,doc_remarks FROM acctg_gl WHERE doc_date > '2018-12-31' AND doc_date <= '$date' AND cleared='N' AND branch='1' AND acct='$_POST[acct_code]' AND credit > 0 AND doc_type!='CV' ORDER BY doc_type, doc_no ASC) a ORDER BY doc_date;");
	$i = 0;
	echo "<table width=100% cellpadding=0 cellspacing=0>";
	while ($row = $q->fetch_array()) {
		list($payee) = $con->getArray("select tradename from contact_info where file_id = '$row[contact]';");
		echo "<tr valign=top bgcolor = '".$con->initBackground($i)."'>
				<td class=grid width=100>" . $row['doc_type'] . '-' . $row['doc_no'] . "</td>
				<td class=grid width=90>" . $row['check_date'] . "</td>
				<td class=grid width=100>" . $row['check_no'] . "</td>
				<td class=grid width=210>" . $payee . "&nbsp;</td>
				<td class=grid>" . $row['doc_remarks'] . "&nbsp;</td>
				<td class=grid align=right style=\"padding-right: 20px;\" width=160>" . number_format($row['cr_amount'],2) . "</td>
				<td class=grid with=90><input type=checkbox id=\"glno_$row[gl_no]\" value=\"$row[gl_no]\" onclick=\"toggle_me(this.id,this.value);\" ";
				if($row['tmp_cleared'] == "Y") { echo "checked"; }
				echo "/></td>
			</tr>
		"; $i++; $payee = "";
	}
	echo "</table>";
?>