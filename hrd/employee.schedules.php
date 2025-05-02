<?php
	//ini_set("display_errors","On");
	require_once "../handlers/initDB.php";
	$con = new myDB;
	
	list($dtf,$dt2,$looper) = $con->getArray("SELECT period_start, period_end, DATEDIFF(period_end,period_start) AS looper FROM pccpayroll.pay_periods WHERE period_id = '$_REQUEST[period]';");
	$batch = $_REQUEST['batch']; $dept = $_REQUEST['dept']; $period = $_REQUEST['period'];

?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv = "Content-Type" content="text/html; charset=iso-8859-1">
	<title>Prime Care Cebu Payroll System Ver. 1.0b</title>
	<link href="../style/style.css" rel="stylesheet" type="text/css" />
	<link href="../ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="../ui-assets/datatables/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" charset="utf8" src="../ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="../ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/dataTables.select.js"></script>
	<script>


		$(document).ready(function() {


			var myTable = $('#itemlist').DataTable({
				"scrollY":  "540",
				"searching": false,
				"paging": false,
				"info": false,
				"select":	'single',
				"bSort": false
			});

		

		});
		
		function saveSched(shift,eid,date,batch,dept,period) {
			$.post("misc-data.php", { mod: "saveSchedule", shift: shift, eid: eid, date: date, batch: batch, dept: dept, period: period, sid: Math.random() });
		}
	
	</script>
	<style>
		.dataTables_wrapper {
			display: inline-block;
			font-size: 11px; padding: 3px;
			width: 99%; 
		}
		
		table.dataTable tr.odd { background-color: #f5f5f5;  }
		table.dataTable tr.even { background-color: white; }
		.dataTables_filter input { width: 250px; }
	</style>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >

	<table id = "itemlist" class = "cell-border" width=100%>
		<thead>
			<tr>

				<th width=15%>Employee</th>
			
				<?php
					
					for($x=0; $x <= $looper; $x++) {
						list($dayLabel) = $con->getArray("select date_format(date_add('$dtf',INTERVAL $x DAY),'%a %m/%d/%y');");
						echo '<th>'.$dayLabel.'</th>';
					}
		
				?>
			</tr>
		</thead>
		<tbody>
		
		<?php
			$eQuery = $con->dbquery("select emp_id,`area`,concat(lname,', ',fname,' ',left(mname,1),'.') as ename, shift from pccpayroll.emp_masterfile where payroll_batch = '$batch' and dept = '$dept' AND employment_status NOT IN (7,8,9,10) and FILE_STATUS != 'DELETED';");
			while($eRow = $eQuery->fetch_array(MYSQLI_BOTH)) {
				$i = 0;
				if($i%2==0){ $bgC = "#f5f5f5"; } else { $bgC = "#ffffff"; }
				
				echo '<tr>
						<td>'.$eRow['ename'].'</td>';
							$z = 0;
						for($x=0; $x <= $looper; $x++) {
							list($date) = $con->getArray("select date_add('$dtf',INTERVAL $x DAY);");
							list($dtrSched,$isLock) = $con->getArray("select `shift`,slock from pccpayroll.emp_dtrfinal where emp_id = '$eRow[emp_id]' and `date` = '$date';");
							if($sLock == 'Y') { $isDisabled = 'disabled'; }
							
							echo '<td>
									<select class="gridInput" id="emp_shift['.$z.']" name="emp_shift['.$z.']" style="width : 90%;" onchange="javascript: saveSched(this.value,\''.$eRow['emp_id'].'\',\''.$date.'\',\''.$batch.'\',\''.$dept.'\',\''.$period.'\');" '.$isDisabled.'>';
									$_sq = $con->dbquery("SELECT '0' AS shift_id, 'Rest Day' AS remarks UNION ALL SELECT '99' AS shift_id, 'Flexible Time' AS remarks UNION ALL SELECT '98' AS shift_id, 'Mobile' AS remarks UNION ALL SELECT '100' AS shift_id, 'Flex 12' AS remarks UNION ALL SELECT DISTINCT shift_id, remarks FROM pccpayroll.emp_shifts");
									echo '<option value="" title="Profile Default">- DEF -</option>';
									while($sqrow = $_sq->fetch_array()) {
										
										if($sqrow[0] == 0) { 
											$lbl = "RD"; 
										} elseif($sqrow[0] == 98) { 
											$lbl = "MOB";
										} elseif($sqrow[0] == 99) { 
											$lbl = "FLEX";
										} elseif($sqrow[0] == 100) { 
											$lbl = "FLEX-12";
										} else { $lbl = "S".$sqrow[0]; }
										
										echo "<option value='$sqrow[0]' title = '$sqrow[1]' ";
										if($sqrow[0] == $dtrSched) { echo "selected"; }
										echo ">$lbl</option>";
									}
							echo "</select></td>";
							$z++;
						}
			
					$i++;
				
				echo '</tr>';
			}
		?>
		</tbody>
	</table>

</body>
</html>