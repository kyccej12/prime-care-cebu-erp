<?php
	session_start();
	
	/* Added for Advance Search Function */
	if($_POST['sflag'] == 'Y') {
		if($_POST['stxt_description'] != '') { $b = "&idesc=$_POST[stxt_description]"; }
		if($_POST['stxt_dtf'] != '' && $_POST['stxt_dt2'] != '') {
			$c = "&dtf=".formatDate($_POST['stxt_dtf'])."&dt2=".formatDate($_POST['stxt_dt2']);
		} else {
			if($_POST['stxt_dtf'] != '') { $c = "&doc_date=".formatDate($_POST['stxt_dtf']); } 
			if($_POST['stxt_dt2'] != '') { $c = "&doc_date= '".formatDate($_POST['stxt_dt2']); }
		}
	}
	
?>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script>
		function viewSOA() {
			var table = $("#itemlist").DataTable();
			var arr = [];
		    $.each(table.rows('.selected').data(), function() { soa_no = this["soa_no"]; });
			
			if(!soa_no) {
				parent.sendErrorMessage("Please select record to view.");
			} else {
				parent.viewSOA(soa_no);
			}
		}

		$(document).ready(function() {
			$('#itemlist').dataTable({
				"ajax": {
					"url": "data/soalist-unpaid.php",
					"method": "POST"
				},
				"scrollY":  "350px",
				"select":	'single',
				"pagingType": "full_numbers",
				"order": [[ 1, "desc" ]],
				"aoColumns": [
				  { mData: 'soa_no' },
				  { mData: 'soano' },
				  { mData: 'sd8' },
				  { mData: 'customer_name' },
				  { mData: 'remarks' },
				  { mData: 'terms_desc' },
				  { mData: 'amount' },
				  { mData: 'paid' },
				  { mData: 'balance' },
				  { mData: 'status' },
				],
				"aoColumnDefs": [
					{ "className": "dt-body-center", "targets": [1,5,6,8] },
					{ "targets": [ 0], "visible": false }
				]
			});
		});
		
		function searchRecord() {
			$("#stxt_dtf").datepicker(); $("#stxt_dt2").datepicker();
			$("#searchDiv").dialog({
				title: "Search Record", 
				width: 400,
				resizable: false, 
				modal: true, 
				buttons: {
					"Search Record": function() {
						document.getElementById('frmSearch').submit();
					},
					"Close": function() { $(this).dialog("close"); }
				}
			});
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
							window.open("export/unpaidsoa.php?dtf="+$("#dtf").val()+"&dt2="+$("#dt2").val()+"&cid="+$("#cid").val()+"&cname="+$("#cname").val()+"&sid="+Math.random()+"","Unpaid Statement of Account","location=1,status=1,scrollbars=1,width=640,height=720");
						}
					}
				]
			});
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

	<table width=100% cellpadding=5 cellspacing=0>
		
		<tr>
			<td align=left>
		
				<button class="ui-button ui-widget ui-corner-all" onClick="exportlist('');">
					<img src="images/icons/excel.png" size=12 height=12 align=absmiddle /> Export List to Excel
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="viewSOA();">
					<span class="ui-icon ui-icon-newwin"></span> Open Selected Statement
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="parent.showUnpaidStatement();">
					<span class="ui-icon ui-icon-refresh"></span> Reload List
				</button>
			</td>
		</tr>
	</table>
	<table id="itemlist" style="font-size:11px;">
		<thead>
			<tr>
				<th></th>	
				<th width=6%>SOA #</th>
				<th width=6%>DATE</th>
				<th width=15%>BILLED TO</th>
				<th >REMARKS</th>
				<th width=8%>TERMS</th>
				<th width=10%>AMT DUE</th>
				<th width=10%>AMT PAID</th>
				<th width=10%>BALANCE</th>
				<th width=10%>DOC STATUS</th>
			</tr>
		</thead>
	</table>
    <div id="searchDiv" style="display: none;">
		<form name = "frmSearch" id = "frmSearch" method = "POST" action = "phy.list.php">
			<input type = "hidden" name = "sflag" id = "sflag" value = "Y">
			<table width = "100%" cellpading = 0 cellspacing = 0>
				<tr>
					<td class="spandix-l">Date Covered :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_dtf" id="stxt_dtf"></td>
				</tr>
				<tr>
					<td class="spandix-l"></td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_dt2" id="stxt_dt2"></td>
				</tr>
				<tr>
					<td class="spandix-l">Item Description :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_description" id="stxt_description"></td>
				</tr>
			</table>
		</form>
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

	</body>
</html>