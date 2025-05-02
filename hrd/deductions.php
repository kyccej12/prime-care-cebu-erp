<?php
	
	require_once "../handlers/_generics.php";
	$mydb = new _init;

?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
	<link href="../ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="../style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="../ui-assets/datatables/css/jquery.dataTables.css">
	<script language="javascript" src="../ui-assets/jquery/jquery-1.12.3.js"></script>
	<script language="javascript" src="../ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script language="javascript" src="../js/jquery.dialogextend.js"></script>
	<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/dataTables.select.js"></script>
	<script>

		var UID = "";

		function init() {
	
			var table = $("#itemlist").DataTable();
			var arr = [];
		   $.each(table.rows('.selected').data(), function() {
			  //arr.push(this["rid"]);
			  UID = this["rid"];
		   });
		   
		   //var UID = arr[0];
		}	  
		
		$(document).ready(function() { 
			$("#date").datepicker(); 
			$('#emp_name').autocomplete({
				source:'../suggestEmployee.php', 
				minLength:3,
				select: function(event,ui) {
					$("#emp_id").val(ui.item.emp_id);
				}
			});

			$('#itemlist').dataTable({
				"scrollY":  "300px",
				"select":	'single',
				"pagingType": "full_numbers",
				"bProcessing": true,
				"sAjaxSource": "listings.php?mod=deductions&sid="+Math.random()+"",
				"order": [[1,"desc"]],
				"aoColumns": [
				  { mData: 'rid' },
				  { mData: 'deduction_date' },
				  { mData: 'period' },
				  { mData: 'emp_id' },
				  { mData: 'emp_name' },
				  { mData: 'dtype' },
				  { mData: 'amount', render: $.fn.dataTable.render.number(',', '.', 2, '') },
				  { mData: 'ref_no' },
				  { mData: 'remarks' }
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [2,3,5,6,7]},
					{ "targets": [0,1], "visible": false }
				]
			});	
		});
		
		function viewRecord() {
			
			init();
			
			if(!UID) {
				parent.sendErrorMessage("Unable to retrieve record. Please select a record from the list, and once highlighted, press  \"<b><i>View Record Details</i></b>\" button again...");
			} else {
				
				$.post("misc-data.php", { mod: "getDeduction", id: UID, sid: Math.random() }, function(ech) {
					
					$("#rid").val(ech['record_id']);
					$("#deduction_type").val(ech['deduction_type']);
					$("#date").val(ech['ddate']);
					$("#amount").val(ech['amt']);
					$("#ref_no").val(ech['ref_no']);
					$("#remarks").html(ech['remarks']);
					$("#emp_id").val(ech['emp_id']);
					$("#emp_name").val(ech['ename']);

					$("#record").dialog({title: "Record Details", width: 440, resizable: false, modal: true, buttons: { 
						"Save Changes": function() {
							$.post("misc-data.php", { mod: "updateDeduction", rid: $("#rid").val(), emp_id: $("#emp_id").val(), type: $("#deduction_type").val(), date: $("#date").val(), ref_no: $("#ref_no").val(), amount: $("#amount").val(), remarks: $("#remarks").val(), sid: Math.random() }, function() {
								alert("Record Successfully Updated!");
								parent.showDeductions();
							});
						},
						"Close": function() { $("#record").dialog("close"); }
					}}).dialogExtend({
						"closable" : true,
						"maximizable" : false,
						"minimizable" : true
					});
				},"json");
			}
		}
		
		function newRecord() {
			$(document.irec)[0].reset(); $("#remarks").html('');
			$("#record").dialog({title: "New Record", width: 440, resizable: false, modal: true, buttons: {
				"Save Record": function() {
					var msg = "";
					if($("#emp_id").val() == "") { msg = msg + "- Please select employee from the given list<br/>"; }
					if($("#remarks").val() == "") { msg = msg + "- Transaction remarks must be duly noted for future reference.<br/>"; }
					if($("#date").val() == "") { msg = msg + "- Deduction date must be duly specified.<br/>"; }
					if($("#amount").val() != "") {
						var amt = parent.stripComma($("#amount").val());
						if(isNaN(amt) == true) { msg = msg + "- You have specified an invalid amount."; }
					}
						
					if(msg != "") { parent.sendErrorMessage(msg); } else {
						$.post("misc-data.php", { mod: "newDeduction", emp_id: $("#emp_id").val(), type: $("#deduction_type").val(), date: $("#date").val(), ref_no: $("#ref_no").val(), amount: $("#amount").val(), remarks: $("#remarks").val(), sid: Math.random() }, function() {
							alert("Record Successfully Saved!");
							parent.showDeductions();
						});
					}
				},
				"Close": function() { $("#record").dialog("close"); }
			}}).dialogExtend({
				"closable" : true,
				"maximizable" : false,
				"minimizable" : true
			});
		}
		
		function deleteRecord() {
			
			init();
			
			if(!UID) {
					parent.sendErrorMessage("Nothing to delete. Please select a record from the list, and once highlighted, press  \"<b><i>Delete Record</i></b>\" button again...");
			} else {
				if(confirm("Are you sure you want to delete this record?") == true) {
					$.post("misc-data.php", { mod: "deleteDeduction", rid: UID, sid: Math.random() }, function() {
						alert("Record Successfully Deleted!");
						parent.showDeductions();
					});
				}	
			}
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
	<div id="main">
		<table cellspacing=0 cellpadding=0 width=100% align=center style="margin-top: 10px;">
			<tr>
				<td align=left style="padding-right: 20px;">
					<a href="#" class="topClickers" onClick="newRecord();"><img src="../images/icons/adduser.png" width=18 height=18 align=absmiddle />&nbsp;New Record</a>&nbsp;
					<a href="#" class="topClickers" onClick="viewRecord();"><img src="../images/icons/edit.png" width=18 height=18 align=absmiddle />&nbsp;View/Edit Selected Record</a>&nbsp;
					<a href="#" class="topClickers" onClick="parent.showDeductions();"><img src="../images/icons/refresh.png" width=18 height=18 align=absmiddle />&nbsp;Reload List</a>&nbsp;
					<a href="#" class="topClickers" onClick="deleteRecord();"><img src="../images/icons/delete.png" width=18 height=18 align=absmiddle />&nbsp;Delete Selected Record</a>
				</td>
			</tr>
		</table>
		<table id="itemlist" style="font-size:11px;">
			<thead>
				<tr>
					<th></th>
					<th></th>
					<th width=10%>DATE</th>
					<th width=10%>EMP. ID</th>
					<th width=15%>EMP. NAME</th>
					<th width=10%>TYPE</th>
					<th width=10%>AMOUNT</th>
					<th width=10%>REF #</th>
					<th>REMARKS</th>
				</tr>
			</thead>
		</table>
	</div>
	<div id="record" style="display: none;">
		<form name="irec" id="irec">
			<table width=100% cellspacing=2 cellpadding=0>
				<tr>
					<td class="bareThin" align=left width=40%>Employee Name :</td>
					<td align=left>
						<input type="text" name="emp_name" id="emp_name" class="inputSearch2" style="width: 80%; padding-left: 22px;">
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Employee ID :</td>
					<td align=left>
						<input type="hidden" name="rid" id="rid" value="">
						<input type="text" name="emp_id" id="emp_id" class="gridInput" style="width: 80%;" readonly>
					</td>
				</tr>

				<tr>
					<td class="bareThin" align=left width=40%>Type :</td>
					<td align=left>
						<select name="deduction_type" id="deduction_type" class="gridInput" style="width: 80%; font-size: 11px; padding: 5px;">
							<option value="">- Select Type -</option>
							<?php 
								$d_res = $mydb->dbquery("select id, deduction_type from pccpayroll.option_deductiontype order by deduction_type asc;");
								while($d_row = $d_res->fetch_array(MYSQLI_BOTH)) {
									print "<option value='$d_row[0]' ";
										if($d_row[0] == $data['deduction_type']){ print "selected"; }
									print ">$d_row[1]</option>";
								}
								unset($d_res);
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Date of Deduction :</td>
					<td align=left>
						<input type="text" name="date" id="date" class="gridInput" style="width: 80%; font-size: 11px; padding: 5px;">
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Reference # :</td>
					<td align=left>
						<input type="text" name="ref_no" id="ref_no" class="gridInput" style="width: 80%; font-size: 11px; padding: 5px;">
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Amount :</td>
					<td align=left>
						<input type="text" name="amount" id="amount" style="width: 80%;">
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40% valign=top>Other Remarks :</td>
					<td align=left><textarea style="width: 80%; font-size: 11px;" rows=3 name="remarks" id="remarks"></textarea></td>
				</tr>
			</table>
		</form>
	</div>
</body>
</html>
<?php mysql_close($con); ?>