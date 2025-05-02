<?php
	session_start();
	include('handlers/_generics.php');
	$o = new _init();

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>OMDC Prime Medical Diagnostics Corp.</title>
<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="style/jquery.timepicker.css" rel="stylesheet" type="text/css" />
<link href="style/style.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="ui-assets/keytable/css/keyTable.jqueryui.css">
<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery.timepicker.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/page.jumpToData().js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/keytable/js/dataTables.keyTable.min.js"></script>
<script>

	$(document).ready(function() {
		var myTable = $('#itemlist').DataTable({
			"keys": true,
			"scrollY":  "340px",
			"select":	'single',
			"pagingType": "full_numbers",
			"bProcessing": true,
			"responsive": true,
			"sAjaxSource": "data/pemelist.php?displayType=<?php echo $_REQUEST['displayType']; ?>",
			"scroller": true,
			"order": [[0, "desc"]],
			"aoColumns": [
			  { mData: 'so' } ,
			  { mData: 'sodate' } ,
			  { mData: 'pname' },
			  { mData: 'gender' },
			  { mData: 'bday' }, 
			  { mData: 'age' },
              { mData: 'compname' },
			  { mData: 'code' },
			  { mData: 'procedure' },
			  { mData: 'status' },
			  { mData: 'so_date' },
			  { mData: 'priority' },
			  { mData: 'birthdate' },
			  { mData: 'pid' },
			  { mData: 'pre_by' },
			  { mData: 'ex_by' }
			],
			"aoColumnDefs": [
			    { "className": "dt-body-center", "targets": [0,1,3,4,5,9]},
			    { "targets": [7,10,11,12,13], "visible": false }
            ]
		});

		$('#itemlist tbody').on('dblclick', 'tr', function () {
			var data = myTable.row( this ).data();	
			parent.collectVitals(data['so'],data['pid']);
		});

		$("#vitals_date").datetimepicker();
		$("#vitals_testkit_expiry").datepicker();

		$('#vitals_by').autocomplete({
			source:'suggestEmployee.php', 
			minLength:3
		});

	});

	function grabPatient() {
		var table = $("#itemlist").DataTable();		
		var so;
		var pri_no;
		var patient;
		var gender;

	   	$.each(table.rows('.selected').data(), function() {
		    so = this['so'];
			pri_no = this['priority'];
			patient = this['patient_name'];
			gender = this['gender'];
	   	});

		if(!pri_no) {
			parent.sendErrorMessage("- It appears you have not selected any record from the given list yet...");
		} else {
			$.post("src/sjerp.php", { mod: "grabPatient", so_no: so, pri_no: pri_no, patient: patient, gender: gender, callStation: "NURSING", sid: Math.random()},function() {
				alert("Patient will be called out in a while... You may prepare the necessary procedure requirements prior to patients arrival.")
			});
		}

	}
	
	function refreshList() {
		$('#itemlist').DataTable().ajax.url("data/pemelist.php").load();
	}

	function collectSample() {
		var table = $("#itemlist").DataTable();		
		var so_no;
		$.each(table.rows('.selected').data(), function() {
			so_no = this['so'];
			pid = this['pid'];
		});
		
		if(!so_no) {
			parent.sendErrorMessage("Unable to continue as it appears you haven't selected any record yet...")
		} else {
		
			parent.collectVitals(so_no,pid);
		}

	}

	function printBatchResult() {
		$("#mobileDate").datepicker();
		var disMessage = $("#printBatch").dialog({ 
			title: "Print Batch Result",
			width: "500",
			modal: true,
			resizeable: false,
			buttons: [
					{
						icons: { primary: "ui-icon-print" },
						text: "Print Results",
						click: function() { 
							window.open("print/result.peme.batch.php?so_no="+$("#mobileSoNo").val()+"&date="+$("#mobileDate").val()+"&shift="+$("#mobileShift").val()+"&sid="+Math.random()+"&sid="+Math.random()+"","Batch Result","location=1,status=1,scrollbars=1,width=640,height=720");
						}
					},
					{
						text: "Close",
						icons: { primary: "ui-icon-closethick" },
						click: function() {
							$(this).dialog("close");
						}
					}
			]
		});

	}

	function changeDisplay(val) {
		document.frmChangeDisplay.displayType.value = val;
		document.frmChangeDisplay.submit();
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

	no_bottom {
		border-top: none;
		border-left: none;
		border-bottom: 1px solid black;
		padding: 5px;
	}
</style>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div id = "main">
	<table width="100%" cellspacing="0" cellpadding="0" style="padding-left: 5px; margin-bottom: 2px;">
		<tr>
			<td>
				<button class="ui-button ui-widget ui-corner-all" onClick="grabPatient();">
					<span class="ui-icon ui-icon-volume-on"></span> Grab Patient
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="collectSample();">
				<span class="ui-icon ui-icon-print"></span> Collect Vitals & Print PE/ME Form
				</button>
				<button class="ui-button ui-widget ui-corner-all" onClick="refreshList();">
					<span class="ui-icon ui-icon-refresh"></span> Reload List
				</button>
			</td>
			<td align=right style="padding-right:18px;">
				<span class="spandix-l">Display Type :</span>
				<select name="displayType" class="gridInput" style="width: 250px; font-size: 11px;" onchange="javascript: changeDisplay(this.value);">
					<option value="">All Records</option>
					<option value="1" <?php if($_GET['displayType'] == 1) { echo "selected"; } ?>>For Doctor's Examination</option>
					<option value="2" <?php if($_GET['displayType'] == 2) { echo "selected"; } ?>>Completed</option>
				</select>
			</td>
		</tr>
	</table>
	<table id="itemlist" style="font-size:11px;">
		<thead>
			<tr>
				<th width=6%>SO #</th>
				<th width=6%>DATE</th>
				<th width=15%>PATIENT NAME</th>
				<th width=5%>SEX</th>
				<th width=8%>BIRTHDATE</th>
				<th width=5%>AGE</th>
				<th width=15%>COMPANY</th>
				<th>CODE</th>
				<th>PROCEDURE</th>
				<th width=7%>STATUS</th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th>PRE-EXAMINED BY</th>
				<th>EXAMINED BY</th>
			</tr>
		</thead>
	</table>
</div>
<div id="printBatch" style="display: none;">
	<form name="frmPrintBatch" id="frmPrintBatch">
		<table width=100% callpaddin=0 cellspacing=1>
			<tr>
				<td width=35% class="spandix-l">Mobile SO # :</td>
				<td>
					<input type="text" class="gridInput" style="width: 100%;" id="mobileSoNo" name="mobileSoNo" value = "">
				</td>
			</tr>

			<tr>
				<td width=35% class="spandix-l">Date :</td>
				<td><input type="text" id="mobileDate" name="mobileDate" class="gridInput" style="width: 100%;" value="" placeholder="Specify Date if Applicable"></td>
			</tr>
			
			<tr>
				<td width=35% class="spandix-l">Shift :</td>
				<td>
					<select name="mobileShift" id="mobileShift" class="gridInput" style="width: 100%; font-size: 11px;">
						<option value=''>Not Applicable</option>
						<option value='1'>Day Shift</option>
						<option value='2'>Night Shift</option>
					</select>
				</td>
			</tr>
		</table>
	</form>
</div>
<form name="frmChangeDisplay" id="frmChangeDisplay" method="GET" action="peme.list.php">
	<input type="hidden" name="displayType" id="displayType">
</form>
</body>
</html>