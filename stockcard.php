<?php
	session_start();
	include("handlers/_generics.php");
	$con = new _init;

	//if($_REQUEST['expiry'] == '') { $expiry = '0000-00-00'; } else { $expiry = $_GET['expiry']; }

	/* Check Last Physical Inventory for the Item */
	list($baseD8) = $con->getArray("select posting_date from phy_header a left join phy_details b on a.doc_no = b.doc_no and a.branch = b.branch where a.branch = '$_SESSION[branchid]' and b.item_code = '$_GET[item_code]' order by posting_date desc limit 1;");
	
	if($baseD8 == '') { $baseD8 = '2022-02-09'; }
	$dtf = $con->formatDate($_GET['dtf']);
	$dt2 = $con->formatDate($_GET['dt2']);


	if($dtf < $baseD8) { $dtf = $baseD8; }

	/* Forward Balance = From Last Date of Physical Count and Before Period Start */
	$pi = $con->getArray("select ifnull(sum(b.qty),0) from phy_header a left join phy_details b on a.doc_no = b.doc_no and a.branch=b.branch where a.branch = '$_SESSION[branchid]' and b.item_code = '$_GET[item_code]' and a.status = 'Finalized' and a.posting_date = '$baseD8' GROUP BY b.item_code;");
	$run = $con->getArray("select sum(purchases+inbound-outbound-pullouts-sold) as run from ibook where doc_date >= '$baseD8' and doc_date < '" . $dtf . "' and item_code = '$_GET[item_code]' and doc_branch = '$_SESSION[branchid]';");
	$query = $con->dbquery("select doc_type, lpad(doc_no,6,0) as xdoc, cname, date_format(doc_date,'%m/%d/%Y') as dd8, if((purchases+inbound-pullouts-outbound-sold) > 0,abs(purchases+inbound-pullouts-outbound-sold),0) as `in`, if((purchases+inbound-pullouts-outbound-sold) < 0,(purchases+inbound-pullouts-outbound-sold),0) as `out`, (purchases+inbound-pullouts-outbound-sold) as run from ibook where item_code = '$_GET[item_code]' and doc_branch = '$_SESSION[branchid]' and doc_date between '".$dtf."' and '".$dt2."' ORDER BY doc_date ASC;");
	
	$beg = $pi[0] + $run[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/datatables/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script>
		var sPO = "";

		$(document).ready(function() {
			$('#details').DataTable({
				"scrollY":  "280",
				"select":	'single',
				"searching": false,
				"paging": false,
				"bSort" : false,
				"info": false,
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [0,1,2,4,5]},
					{ className: "dt-body-right", "targets": [6]}
				]
			});
		});
		

		function viewDetails() {
	
			var table = $("#details").DataTable();
			$.each(table.rows('.selected').data(), function() {
				doc_no = this[0];
				doc_type = this[2];
			});
		
			if(!doc_no) {
				parent.sendErrorMessage("- It appears you have not selected any transaction yet to view...");

			} else {
				switch(doc_type) {
					case "SI":
						parent.viewSI(doc_no);
					break;
					case "RR":
						parent.viewRR(doc_no);
					break;
					case "SRR":
						parent.viewSRR(doc_no);
					break;
					case "SW":
						parent.viewSW(doc_no);
					break;
					case "STR":
						parent.viewSTR(doc_no);
					break;
				}

			}
		
		}

		function exportStockcard() {
			parent.exportStockcard(<?php echo "'$_REQUEST[item_code]','$_REQUEST[lot_no]','$_REQUEST[expiry]','$_REQUEST[dtf]','$_REQUEST[dt2]'"; ?>);
		}

	
	</script>
	<style>
		.dataTables_wrapper {
			display: inline-block;
			font-size: 11px;
			width: 100%; 
		}
		
		table.dataTable tr.odd { background-color: #f5f5f5;  }
		table.dataTable tr.even { background-color: white; }
		.dataTables_filter input { width: 250px; }
	</style>
</head>
<body bgcolor="#FFFFFF" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
	<table width=100% style="padding: 10px;" class="td_content">
		<tr>
			<td width=15% class="spandix-l">Item Code :</td>
			<td align=left><?php echo $_GET['item_code']; ?></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td width=15% class="spandix-l">Item Description :</td>
			<td align=left><?php echo $con->identItemDescription($_REQUEST['item_code']); ?></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td width=15% class="spandix-l">Lot # :</td>
			<td align=left><?php echo $_GET['lot_no']; ?></td>
		</tr>
		<tr>
			<td width=15% class="spandix-l">Expiry :</td>
			<td align=left><?php echo $_GET['expiry']; ?></td>
		</tr>
		<tr>
			<td width=15% class="spandix-l">Covered Period :</td>
			<td align=left><?php echo $_GET['dtf'] . " to " . $_GET['dt2']; ?></td>
			<td class="spandix-l" align=right>Balance Forwarded From Previous Period :</td>
			<td><b><?php echo number_format($beg,2) ?></b></td>
		</tr>
	</table>

	<table id="details">
		<thead>
			<tr>
				<th width=10%>DOC #</th>
				<th width=10%>DOC DATE</th>
				<th width=10%>DOC TYPE</th>
				<th>DESTINATION/ORIGIN</th>
				<th width=10%>IN</th>
				<th width=10%>OUT</th>
				<th width=12%>RUNNING BALANCE</th>
			</tr>
		</thead>
		<tbody>
		<?php
			
			
			while($row = $query->fetch_array()) {
				
				$beg += $row['run'];
				
				echo "<tr>
						<td>$row[xdoc]</td>
						<td>$row[dd8]</td>
						<td>$row[doc_type]</td>
						<td>$row[cname]</td>
						<td>".number_format($row['in'],2)."</td>
						<td>".number_format($row['out']*-1,2)."</td>
						<td>".number_format($beg,2)."</td>
				
				</tr>";


			}


		?>
		</tbody>
	</table>

    <table width=100% cellpadding=5 cellspacing=0 class="td_content">
		<tr>
			<td align=left>
				<button onClick="exportStockcard();" class="buttonding"><img src="images/icons/excel.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Export Stockcard to Excel</button>
				<button onClick="viewDetails();" class="buttonding"><img src="images/icons/bill.png" width=24 height=24 align=absmiddle />&nbsp;&nbsp;View Transaction Details</b></button>
			</td>
		</tr>
	</table>
	
	</body>
</html>