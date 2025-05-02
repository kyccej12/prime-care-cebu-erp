<?php
	session_start();
	include("handlers/_generics.php");
	$o = new _init;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Opon Medical Diagnostic Corporation</title>
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
			"keys": true,
			"scrollY":  "300px",
			"select":	'single',
			"pageLength": 50,
			"pagingType": "full_numbers",
			"bProcessing": true,
			"responsive": true,
			"sAjaxSource": "data/ecgSamples.php",
			"order": [[1, "desc"]],
			"scroller": true,
			<?php if($_GET['code'] != 'undefined') { ?>
				"initComplete": function() {
					this.api().page.jumpToData("<?php echo $_GET['code']; ?>",1);
				},
			<?php } ?>
			"aoColumns": [
			  { mData: 'id' } ,
			  { mData: 'sono' } ,
			  { mData: 'sodate' },
			  { mData: 'result_date' },
			  { mData: 'pname' },
			  { mData: 'age' },
			  { mData: 'gender' },
			  { mData: 'employer' },
			  { mData: 'procedure' },
              { mData: 'lotno' },
			  { mData: 'tstamp' },
              { mData: 'status' },
			  { mData: 'ostat' },
			  { mData: 'serialno' },
			  { mData: 'code' },
			],
			"aoColumnDefs": [
			    { "className": "dt-body-center", "targets": [1,2,3,5,6,9,10]},
				{ "className": "dt-body-left", "targets": [3,7]},
			    { "targets": [0,12,13,14], "visible": false }
            ]
		});

	});
	
	function refreshList() {
		$('#itemlist').DataTable().ajax.url("data/ecgSamples.php").load();
	}

	function rejectSample() {

		var table = $("#itemlist").DataTable();		
		var lid; var stat;
	   	$.each(table.rows('.selected').data(), function() {
		    lid = this["id"]; stat = this["ostat"]; 
	   	});

		if(!lid) {
			parent.sendErrorMessage("Please select record from the given list!");
		} else {

			var msg ='';

			if(stat == '2') { msg = "Sample has already been marked as \"Rejected\"!"; }
			/* if(stat == '3') { msg = "Result is already available for this procedure."; } */
			if(stat == '4') { msg = "Result is already available for this procedure."; }

			if(msg != '') {
				parent.sendErrorMessage(msg);
			} else {

				$.post("src/sjerp.php", {
					mod: "retrieveSample",
					lid: lid,
					sid: Math.random() },
					function(data) {

						$("#phleb_pname").val(data[0]);
						$("#phleb_procedure").val(data[1]);
						$("#phleb_code").val(data[2]);
						$("#phleb_spectype").val(data[3]);
						$("#phleb_serialno").val(data[4]);
						$("#phleb_location").val(data[5]);
						$("#phleb_date").val(data[6]);
						$("#phleb_hr").val(data[7]);
						$("#phleb_min").val(data[8]);
						$("#phleb_by").val(data[9]);

						var dis = $("#sampleDetails").dialog({
							title: "Sample Rejection",
							width: 540,
							resizeable: false,
							modal: true,
							buttons: [
								{
									text: "Reject Sample",
									icons: { primary: "ui-icon-check" },
									click: function() {
								
										if(confirm("Are you sure you want to mark this sample as Rejected?") == true) {
										
											$.post("src/sjerp.php", { 
												mod: "rejectSample",
												lid: lid,
												reason: $("#phleb_remarks").val(),
												sid: Math.random() }, 
												function() {
													alert("Sample successfully marked as \"REJECTED\"!");
													dis.dialog("close");
													refreshList();
												}
											);
										}	
									}
										
								},
								{
									text: "Close",
									icons: { primary: "ui-icon-closethick" },
									click: function() { $(this).dialog("close"); }
								}
							]
						});
					},"json"
				);
			}
		}
	}

	function validateResult() {
		var table = $("#itemlist").DataTable();		
		var lid; var stat;
	   	$.each(table.rows('.selected').data(), function() {
		    lid = this["id"];
			code = this['code'];
            stat = this['ostat'];
	   	});

		if(!lid) {
			parent.sendErrorMessage("- It appears you have not selected any orders from the given list yet...");
		} else {
            var msg ='';
           
			if(code == 'O001') { 
				if(stat == '1') { msg = "It appears you have not attached file yet...";
				} 
			}

			if(msg != '') {
				parent.sendErrorMessage(msg);
			} else {
				parent.validateECGResult(lid,code);
			}
		}

	}

    function attachFile() {

    var table = $("#itemlist").DataTable();		
    var so;
    var code;
    var sn;

    $.each(table.rows('.selected').data(), function() {
        so = this["sono"];
        code = this['code'];
        sn = this['serialno'];
    });

    if(!so) {
        parent.sendErrorMessage("Please select a result to associate this file.");
        } else {
            $("#frmAttachFile").trigger("reset");
            var dis = $("#attachFile").dialog({
                title: "Attach File",
                width: 480,
                resizeable: false,
                modal: true,
                buttons: [
                    {
                        text: "Attach File",
                        icons: { primary: "ui-icon-check" },
                        click: function() {
                            var msg = "";
                
                            if($("#att_title").val() == "") { msg = msg + "Please Indicate File TItle<br/>"; }
                            if($("#att_file").val() == "") { msg = msg + "Invlaid File.<br/>"; }
                        
                            document.getElementById("att_sono").value = so;
                            document.getElementById("att_code").value = code;
                            document.getElementById("att_serialno").value = sn;
                            
                            if(msg!="") {
                                parent.sendErrorMessage(msg);
                            } else {

                                $.ajax({
                                    type: "POST",
                                    url: "src/sjerp.php",
                                    data: new FormData($('#frmAttachFile')[0]),
                                    cache: false,
                                    contentType: false,
                                    processData: false,
                                    success: function() {
                                        alert("File Successfully Successfully Saved!");
                                        parent.manageECGResults();
                                    }
                                });
                            }

                        }
                            
                    },
                    {
                        text: "Close",
                        icons: { primary: "ui-icon-closethick" },
                        click: function() { $(this).dialog("close"); $("#frmAttachFile").trigger("reset"); }
                    }
                ]
            });	
        }
    }

    function printResult() {
		var table = $("#itemlist").DataTable();		
	
	   	$.each(table.rows('.selected').data(), function() {
		    serialno = this["serialno"];
			code = this['code'];
			so_no = this['sono'];
			status = this['ostat'];
	   	});

		if(serialno == '') {
			parent.sendErrorMessage("Cannot print selected record as it appears result isn't available or you didn't select any records from the give list yet.");
		} else {

			if(status == '3' || status == '4') {
				parent.printResult(code,so_no,serialno) 
				
			} else {
				parent.sendErrorMessage("- It appears that a result isn't availale yet for the selected record.");
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
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div id = "main">
	<table height="100%" width="100%" border="0" cellspacing="0" cellpadding="0" >
		<tr>
			<td style="padding:0px;" valign=top>
				<table width="100%" cellspacing="0" cellpadding="0" style="padding-left: 5px; margin-bottom: 5px;">
					<tr>
						<td>
							<a href="#" class="topClickers" onClick="validateResult();"><img src="images/icons/syringe.png" width=18 height=18 align=absmiddle />&nbsp;Write Result</a>&nbsp;
							<!-- <a href="#" class="topClickers" onClick="rejectSample();"><img src="images/icons/cancel48.png" width=18 height=18 align=absmiddle />&nbsp;Reject Result</a>&nbsp; -->
							<a href="#" class="topClickers" onClick="printResult();"><img src="images/icons/print.png" width=18 height=18 align=absmiddle />&nbsp;Print Selected Result</a>&nbsp;
							<a href="#" class="topClickers" onClick="attachFile();"><img src="images/icons/attachments.png" width=18 height=18 align=absmiddle />&nbsp;Attach File</a>&nbsp;
                            <a href="#" class="topClickers" onClick="refreshList();"><img src="images/icons/refresh.png" width=18 height=18 align=absmiddle />&nbsp;Refresh List</a>&nbsp;
                        </td>
					</tr>
				</table>
				<table class="cell-border" id="itemlist" style="font-size:11px;">
				<thead>
						<tr>
							<th></th>
							<th width=5%>SO #</th>
							<th width=8%>SO DATE</th>
							<th width=10%>RESULT DATE</th>
							<th width=15%>PATIENT NAME</th>
                            <th width=5%>AGE</th>
							<th width=7%>GENDER</th>
							<th width=15%>COMPANY</th>
							<th>PROCEDURE</th>
                            <th width=8%>ECG #</th>
							<th width=10%>TIMESTAMP</th>
                            <th width=8%>STATUS</th>
							<th></th>
							<th></th>
							<th></th>
						</tr>
					</thead>
				</table>
			</td>
		</tr>
	</table>
</div>
<div id="singleResult" style="display: none;"></div>
<div id="attachFile" name="attachFile" style="display: none;">
	<form enctype="multipart/form-data" name="frmAttachFile" id="frmAttachFile" method="POST">
		<input type="hidden" name="mod" id="mod" value="attachLabSampleFile">
		<input type="hidden" name="att_sono" id="att_sono" value="">
		<input type="hidden" name="att_serialno" id="att_serialno" value="">
		<input type="hidden" name="att_code" id="att_code" value="">
		<table border="0" cellpadding="0" cellspacing="2" width=100%>
			<tr>
				<td width=35%><span class="spandix-l">File Title :</span></td>
				<td>
					<input type="text" name="att_title" id="att_title" class="nInput" style="width: 80%;" />
				</td>
			</tr>
			<tr>
				<td width=35%><span class="spandix-l">File Remarks :</span></td>
				<td>
					<textarea name="att_remarks" id="att_remarks" rows=3 style="width: 80%;"></textarea>
				</td>
			</tr>				
			<tr>
				<td width=35% class="spandix-l">File to Attach: </td>
				<td><input type=file name="att_file" id="att_file" style="width: 80%;"></td>
			</tr>
		</table>
	</form>
</div>
</body>
</html>