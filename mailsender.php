<?php
	//Import PHPMailer classes into the global namespace
	//These must be at the top of your script, not inside a function
	
	ini_set("display_errors","On");
	
	ini_set("max_execution_time",-1);
	ini_set("memory_limit",-1);
	
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

	require 'lib/PHPMailer/src/PHPMailer.php';
	require 'lib/PHPMailer/src/SMTP.php';
	require 'lib/PHPMailer/src/Exception.php';
	require 'handlers/initDB.php';

	$con = new myDB();
	$mail = new PHPMailer(true);
	
	$cutoff = $_REQUEST['cutoff'];
	$dept = $_REQUEST['dept'];
	$eid = $_REQUEST['eid'];
	list($dtf,$dt2) = $con->getArray("select date_format(period_start,'%m/%d/%Y'), date_format(period_end,'%m/%d/%Y') from pccpayroll.pay_periods where period_id = '$cutoff';");
	
	
	$whereString = '';
	if($dept != '') { $whereString .= " and a.dept = '$dept' "; }
	if($eid != '') { $whereString .= " and a.emp_id = '$eid' "; }
	
	//Server settings
	//$mail->SMTPDebug  = 2;
	//$mail->SMTPDebug = SMTP::DEBUG_SERVER;                  //Enable verbose debug output
	$mail->isSMTP();                                          //Send using SMTP
	$mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
	$mail->SMTPAuth   = true;                                 //Enable SMTP authentication
	$mail->Username   = 'pccpayrollmailer@gmail.com';          //SMTP username
	$mail->Password   = 'zctapgdafkrxzffe';                   //SMTP password
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;          //Enable implicit TLS encryption
	$mail->Port       = 465;                                  //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

	//Recipients
	$mail->setFrom('qqpayrollmailer@gmail.com', 'PCA Payroll Mailer');
	$mail->addReplyTo('qqpayrollmailer@gmail.com', 'PCA Payroll Mailer');
	
	//Content
	$mail->isHTML(true);                                  //Set email format to HTML
	$mail->Subject = 'Your ' . $dtf . '-' . $dt2 . ' Payslip is Already Available for Download';

	$q = $con->dbquery("SELECT a.emp_name, a.hashkey, b.email_add, concat(b.fname,' ',b.lname) as emailname FROM pccpayroll.emp_payslip a LEFT JOIN pccpayroll.emp_masterfile b ON a.emp_id = b.emp_id WHERE a.period_id = '$cutoff' $whereString order by a.emp_name asc;");
	while($row = $q->fetch_array()) {
		$link = '';	
		
		if($row['email_add'] != '') {
			
			$link = "http://portal.primecarealpha.ph:8030/?hash=$row[hashkey]";
		
			
			try {
				$mail->addAddress(strtolower($row['email_add']), $row['emailname']);     //Add a recipient
				$mail->Body    = 'Click link provided below to download your payslip. For Google Chrome users, you may <b>Right click</b> the link provided, then choose "<b>Save Link As</b>" from the context menu download the file.<br/><br/>'.$link.'<br/><br/>Sincerely,<br/><br/>Payroll Officer<br/>Human Resource Department<br/>Primecare Alpha</b>';
				$mail->send();
				$mail->ClearAllRecipients();
				echo "Payslip for ". utf8_encode($row['emp_name']) . " successfully sent to his/her email address...<br/>";
				$con->dbquery("update ignore pccpayroll.emp_payslip set emailed = 'Y', emailed_on = now() where hashkey = '$row[hashkey]';");
			} catch (Exception $e) {
				echo "Unable to email " . utf8_encode($row['emp_name']) . "'s Payslip. Mailer Error: {$mail->ErrorInfo} <br/>";
				$con->dbquery("update ignore pccpayroll.emp_payslip set emailed = 'N', emailed_on = NULL where hashkey = '$row[hashkey]';");
			}
		}
	}
?>