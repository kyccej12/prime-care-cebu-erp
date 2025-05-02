<?php
	session_start();
	include("handlers/_generics.php");
	$con = new _init();
	
?>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="ui-assets/keytable/css/keyTable.jqueryui.css">
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/page.jumpToData().js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/keytable/js/dataTables.keyTable.min.js"></script>
	<script>
		
		$(document).ready(function() {

			var myTable = $('#itemlist').DataTable({
				"scrollY":  "380",
				"select":	'single',
				"searching": false,
				"paging": false,
				"info": false,
				"bSort": false,
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [0,1,4,7] },
					{ className: "dt-body-right", "targets": [5] }
				]
			});

			$('#itemlist tbody').on('dblclick', 'tr', function () {
				var data = myTable.row( this ).data();	
				parent.viewSO(data[0]);
			});

			$("#stxt").keyup(function(e) { 
				if(e.keyCode === 13 ) { searchRecord(); }
			})

		});
		
		function viewSO() {
			var table = $("#itemlist").DataTable();		
			var so_no;
			$.each(table.rows('.selected').data(), function() {
				so_no = this[0];
			});

			if(!so_no) {
				parent.sendErrorMessage("Please select record to view.");
			} else {
				parent.viewSO(so_no);
			}
		}

		function searchRecord() {
			$("#mainLoading").css("z-index","999");
			$("#mainLoading").show();

			var stxt = $("#stxt").val();
			document.frmSearch.searchtext.value = stxt;
			document.frmSearch.submit();
		}

		function jumpPage(page,stxt) {

			$("#mainLoading").css("z-index","999");
			$("#mainLoading").show();

			document.frmPaging.page.value = page;
			document.frmPaging.searchtext.value = stxt;
			document.frmPaging.submit();
		}


		function exportlist() {
			$("#frmExport").trigger("reset");

			$('#cid').autocomplete({
				source:'suggestContacts.php', 
				minLength:3,
				select: function(event,ui) {
					$("#cname").val(decodeURIComponent(ui.item.cname));
				}
			});

			$("#dtf").datepicker(); $("#dt2").datepicker();
			$("#exportDiv").dialog({
				title: "Export List to Excel", 
				width: 480,
				modal: true,
				buttons: [
					/* {
						text: "Generate Report in PDF",
						icons: { primary: "ui-icon-print" },
						click: function() {	
							var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/cashreceiptssummary.php?dtf="+$("#crs_dtf").val()+"&dt2="+$("#crs_dt2").val()+"&customer="+$("#crs_customer").val()+"&uid="+$("#crs_uid").val()+"&sid="+Math.random()+"'></iframe>";
							$("#report3").html(txtHTML);
							$("#report3").dialog({title: "Cash Receipts Summary", width: 640, height: 520, resizable: true }).dialogExtend({
								"closable" : true,
								"maximizable" : true,
								"minimizable" : true
							});
						}
					}, */
					{
						text: "Export Report to Excel",
						icons: { primary: "ui-icon-folder-open" },
						click: function() {	
							window.open("export/unbilledso.php?dtf="+$("#dtf").val()+"&dt2="+$("#dt2").val()+"&cid="+$("#cid").val()+"&cname="+$("#cname").val()+"&sid="+Math.random()+"","Unbilled Sales Order","location=1,status=1,scrollbars=1,width=640,height=720");
						}
					}
				]
			});
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
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
	<div id="mainDiv">
		<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:2px;">
			<tr>
				<td>
					<button class="ui-button ui-widget ui-corner-all" onClick="exportlist('');">
						<img src="images/icons/excel.png" size=12 height=12 align=absmiddle /> Export List to Excel
					</button>
					<button class="ui-button ui-widget ui-corner-all" onClick="viewSO();">
						<span class="ui-icon ui-icon-newwin"></span> Open Selected Sales Order
					</button>
					<button class="ui-button ui-widget ui-corner-all" onClick="parent.showUnbilled();">
						<span class="ui-icon ui-icon-refresh"></span> Reload List
					</button>
				</td>
				<td align=right>
					<input name="stxt" id="stxt" type="text" class="gridInput" style="width: 240px; height: 24px;" value="<?php echo $_REQUEST['searchtext']; ?>" placeholder="Search Record">
					<button class="ui-button ui-widget ui-corner-all" onClick="javascript: searchRecord();">
						<span class="ui-icon ui-icon-search"></span> Search Record
					</button>
				</td>
			</tr>
		</table>
		<table id="itemlist" style="font-size:11px;">
			<thead>
				<tr>
					<th width=6%>SO #</th>
					<th width=6%>DATE</th>
					<th width=15%>PATIENT NAME</th>
					<th width=15%>BILLED TO</th>
					<th width=6%>TERMS</th>
					<th width=8%>AMOUNT</th>
					<th>DOCUMENT REMARKS</th>
					<th width=10%>DOC. STATUS</th>
					<th width=20%>ORDER STATUS</th>
				</tr>
			</thead>

			<tbody>
				<?php
					$rowsPerPage = 50;
					if(isset($_REQUEST['page'])) { if($_REQUEST['page'] <= 0) { $pageNum = 1; } else { $pageNum = $_REQUEST['page']; }} else { $pageNum = 1; }
					$offset = ($pageNum - 1) * $rowsPerPage;
					$searchString = '';

					if($_REQUEST['searchtext'] && $_REQUEST['searchtext'] != '') {
						$term = htmlentities(trim($_REQUEST['searchtext']));
						
						list($dCount) = $con->getArray("select count(*) from so_details where (code = '$_REQUEST[searchtext]' || description like '%$term%');");
						if($dCount > 0) {
							$inQuery = '';
							$dCountQuery = $con->dbquery("select so_no from so_details where (code = '$_REQUEST[searchtext]' || description like '%$term%');");
							while(list($dSO) = $dCountQuery->fetch_array()) {
								$inQuery .= "'$dSO',";
							}
							$inQuery .= "'0'";
							$inSO = "so_no in ($inQuery)";
						} else { $inSO = "so_no = '$_REQUEST[searchtext]'"; }
						
						$searchString .= " and ($inSO || scpwd_id = '$_REQUEST[searchtext]' || patient_id = '$_REQUEST[searchtext]' || patient_name like '%$term%' || customer_name like '%$term%' || remarks like '%$term%' || physician like '%$_REQUEST[searchtext]%' || b.description like '%$_REQUEST[searchtext]%') ";
					
						list($totalRows) = $con->getArray("select format(count(*),0) from so_header;");
						$ender = "(filtered from $totalRows total entries)";
		
					} else { $ender = "entries"; }

					$query = "SELECT LPAD(so_no,6,0) AS so, DATE_FORMAT(so_date,'%m/%d/%Y') AS sdate, patient_name, IF(customer_code=0,concat(patient_name, '(Patient)'),customer_name) AS customer, b.description AS terms_desc, remarks, FORMAT(amount,2) AS amount, `status`, c.sostatus AS cstat FROM so_header a LEFT JOIN options_terms b ON a.terms = b.terms_id LEFT JOIN options_sostatus c ON a.cstatus = c.id WHERE branch = '$_SESSION[branchid]' and terms not in (0,100) and billed = 'N' and a.status='Finalized' and so_date >= '2022-01-01' and amount > 0 $searchString";
			
					/* Paging Section */
					$numrows = $con->getArray("select count(*) from ($query) a;");
					$maxPage = ceil($numrows[0]/$rowsPerPage);
					$_i = $con->dbquery("$query ORDER BY so_no desc LIMIT $offset,$rowsPerPage");
					
					$showFrom = ($pageNum - 1) * $rowsPerPage + 1;
					$showTo = $showFrom + $rowsPerPage - 1;
					if($showTo > $numrows[0]) { $showTo = $numrows[0]; }

					while($row = $_i->fetch_array()) {
		
						echo "<tr>
								<td>$row[so]</td>
								<td>$row[sdate]</td>
								<td>$row[patient_name]</td>
								<td>$row[customer]</td>
								<td>$row[terms_desc]</td>
								<td>$row[amount]</td>
								<td>$row[remarks]</td>
								<td>$row[status]</td>
								<td>$row[cstat]</td>
						</tr>";

					}

				?>

			</tbody>
		</table>
		<table bgcolor="#e9e9e9" width=100% cellpadding=5 cellspacing=0>
			<tr>
				<?php if($numrows[0] > 0) { ?>
				<td>
					<span style="font-size: 11px; font-weight: bold;"><?php echo "Showing " . number_format($showFrom) . " to " . number_format($showTo) . " of " . number_format($numrows[0]) . " " . $ender ?></span>
				</td>
				<td align=right style="padding-right: 10px;"><?php if ($pageNum > 1) { ?><a href="javascript:jumpPage('<?php echo ($pageNum - 1); ?>','<?php echo $_REQUEST['searchtext']; ?>')" class="a_link" title="Previous Page"><span style="font-size: 18px;">&laquo;</span></a>&nbsp;<?php } ?>
					<span style="font-size: 11px;">Page <?php echo $pageNum; ?> of <?php echo $maxPage; ?></span>&nbsp;
						<?php if($pageNum != $maxPage) { ?><a href="javascript:jumpPage('<?php echo ($pageNum + 1); ?>','<?php echo $_REQUEST['searchtext']; ?>')" class="a_link" title="Next Page"><span style="font-size: 18px;">&raquo;</span></a><?php } ?>&nbsp;&nbsp;
							<?php if($maxPage > 1) { ?>
							<span style="font-size: 11px;">Jump To: </span>
								<select id="jpage" name="jpage" style="width: 40px; padding: 0px;" onchange="javascript:jumpPage(this.value,'<?php echo $_REQUEST['searchtext']; ?>');">
								<?php
										for ($x = 1; $x <= $maxPage; $x++) {
											echo "<option value='$x' ";
											if($pageNum == $x) { echo "selected"; }
											echo ">$x</option>";
										}
									?>
									</select>
						<?php } ?>
				</td> 
				<?php } ?>
			</tr>
		</table>
	</div>
	
	<div id="exportDiv" style="display: none;">
		<form name="frmeExport" id="frmExport">
			<table width=100% cellpadding=3 cellspacing=1>
				<tr>
					<td width=35% class="spandix-l">Billed To:</td>
					<td><input id="cid" name="cid" type="text" class="inputSearch2" style="width: 80%; padding-left: 22px;" placeholder="All Customers" onchange="javascript: if(this.value == '') { $('#cname').val(''); }"></td>
				</tr>
				<tr>
					<td width=35% class="spandix-l"></td>
					<td><input id="cname" name="cname" type="text" class="gridInput" style="width: 80%;" placeholder="Customer Name" readonly></td>
				</tr>
				<tr>
					<td width=35% class="spandix-l">Date From :</td>
					<td><input id="dtf" name="dtf" type = "text" class="gridInput" style="width: 80%;" value="<?php echo date('m/01/Y'); ?>"></td>
				</tr>
				<tr>
					<td width=35% class="spandix-l">Date To :</td>
					<td><input id="dt2" name="dt2" type="text" class="gridInput" style="width: 80%;" value="<?php echo date('m/d/Y'); ?>"></td>
				</tr>
			</table>
		</form>
	</div>


	<div id="mainLoading" style="display:none; width:100%;height:100%;position:absolute;top:0;margin:auto;"> 
		<div style="background-color:white;width:10%;height:20%;;margin:auto;position:relative;top:100;">
			<img style="display:block;margin-left:auto;margin-right:auto;" src="images/ajax-loader.gif" width=100 height=100 align=absmiddle /> 
		</div>
		<div id="mainLoading2" style="background-color:white;width:100%;height:100%;position:absolute;top:0;margin:auto;opacity:0.8;"> </div>
	</div>
	<form name="frmSearch" id="frmSearch" action="unbilledso.list.php" method="POST">
		<input type="hidden" name="isSearch" id="isSearch" value="Y">
		<input type="hidden" name="searchtext" id="searchtext" value="<?php echo $_REQUEST['searchtext']; ?>">
	</form>
	<form name="frmPaging" id="frmPaging" action="unbilledso.list.php" method="POST">
		<input type="hidden" name="page" id="page" value="<?php echo $pageNum; ?>">
		<input type="hidden" name="searchtext" id="searchtext" value="<?php echo $_REQUEST['searchtext']; ?>">	
	</form>
</body>
</html>