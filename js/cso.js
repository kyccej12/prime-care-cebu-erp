function getTotals() {
	$.post("cso.datacontrol.php", { mod: "getTotals", cso_no: $("#cso_no").val(), sid: Math.random() }, function(totals) {
		$("#gross").val(totals['gross']);
	 },"json");
}

function addItem() {
	$("#itemEntry").dialog({
		title: "Add Item", 
		width: 440, 
		resizable: false, 
		modal: true, 
		buttons: [
			{ 
				text: "Add Item",
				click: function() { 
					var msg = "";

					if($("#itemCode").val() == "") { msg = msg + "- Invalid Item Code<br/>"; }
					if($("#itemPatient").val() == "") { msg = msg + "- Invalid Patient ID/Name<br/>"; }
					
					if(msg != '') {
						parent.sendErrorMessage(msg);
					
					} else {
						$.post("cso.datacontrol.php", { 
							mod: "addItem", 
							cso_no: $("#cso_no").val(),
							trace_no: $("#trace_no").val(), 
							pid: $("#itemPid").val(),
							pname: $("#itemPatient").val(),	
							item: $("#itemCode").val(), 
							description: $("#itemDescription").val(), 
							price: $("#itemPrice").val(),
							loa: $("#itemLOA").val(),
							hmo: $("#itemHMO").val(),
							sid: Math.random() }, 
						function(gt) {
							redrawDataTable();
							getTotals();
							$("#frmItemEntry").trigger("reset");
						});
					}
				},
				icons: { primary: "ui-icon-check" }
		    }, 
			{ 
				text: "Close",
				click: function() { $(this).dialog("close"); $("#frmItemEntry").trigger("reset"); },
				icons: { primary: "ui-icon-closethick" }
			}
		]
	});

	$('#itemDescription').autocomplete({
		source:"suggestService.php?cid="+$("#customer_code").val()+"&sid="+Math.random()+"", 
		minLength:3,
		select: function(event,ui) {
			$("#itemCode").val(ui.item.code);
			$("#itemPrice").val(ui.item.price);
		}
	});

	$('#itemPid').autocomplete({
		source:'suggestPatient.php', 
		minLength:3,
		select: function(event,ui) {
			$("#itemPatient").val(ui.item.name);
		}
	});
}

function deleteItem(){
	var table = $("#details").DataTable();
	var arr = [];
   $.each(table.rows('.selected').data(), function() {
	   arr.push(this["id"]);
   });
  
	if(!arr[0]) {
		parent.sendErrorMessage("Please select a record to delete.");
	} else {
		if(confirm("Are you sure you want to remove this line entry?") == true) {
			$.post("cso.datacontrol.php", { mod: "deleteLine", lid: arr[0], cso_no: $("#cso_no").val(), sid: Math.random() }, function(gt) { redrawDataTable(); getTotals(); });
		}
	}
}

function updateItem() {
	var table = $("#details").DataTable();
	var arr = [];
	$.each(table.rows('.selected').data(), function() {
		arr.push(this["id"]);
	});

	if(!arr[0]) {
		parent.sendErrorMessage("Please select a record to update.");
	} else {
		
		$.post("cso.datacontrol.php", { mod: "retrieveLine", lid: arr[0], sid: Math.random() }, function(data) { 
			$("#itemPid").val(data['pid']);
			$("#itemPatient").val(data['pname']);
			$("#itemCode").val(data['code']);
			$("#itemDescription").val(data['description']);
			$("#itemPrice").val(data['unit_price']);
			$("#itemPrice").val(data['unit_price']);
			$("#itemLOA").val(data['loa']);
			$("#itemHMO").val(data['hmo']);
		
			$("#itemEntry").dialog({
				title: "Update Line Entry", 
				width: 440, 
				resizable: false, 
				modal: true, 
				buttons: [
					{ 
						text: "Save Changes",
						click: function() { 
							var msg = "";
			
							if($("#itemCode").val() == "") { msg = msg + "- Invalid Item Code<br/>"; }
							if($("#itemPatient").val() == "") { msg = msg + "- Invalid Patient ID/Name<br/>"; }
			
							if(msg != '') {
								parent.sendErrorMessage(msg);
							
							} else {
								if(confirm("Are you sure you want to save changes made to this entry?") == true) {
									$.post("cso.datacontrol.php", { 
										mod: "updateItem", 
										lid: arr[0],
										cso_no: $("#cso_no").val(),
										trace_no: $("#trace_no").val(), 
										pid: $("#itemPid").val(),
										pname: $("#itemPatient").val(),	
										item: $("#itemCode").val(), 
										description: $("#itemDescription").val(), 
										price: $("#itemPrice").val(),
										loa: $("#itemLOA").val(),
										hmo: $("#itemHMO").val(),
										sid: Math.random() }, 
									function(gt) {
										redrawDataTable();
										getTotals();
										$("#frmItemEntry").trigger("reset");
										
									});
								}
							}
						},
						icons: { primary: "ui-icon-check" }
					},
					{
						text: "Close",
						click: function() { $(this).dialog("close"); $("#frmItemEntry").trigger("reset"); },
						icons: { primary: "ui-icon-closethick" }
					}
				]
			});	
		
		},"json");


		$('#itemDescription').autocomplete({
			source:"suggestService.php?cid="+$("#customer_code").val()+"&sid="+Math.random()+"", 
			minLength:3,
			select: function(event,ui) {
				$("#itemCode").val(ui.item.code);
				$("#itemPrice").val(ui.item.price);
			}
		});
	
		$('#itemPid').autocomplete({
			source:'suggestPatient.php', 
			minLength:3,
			select: function(event,ui) {
				$("#itemPatient").val(ui.item.name);
			}
		});

	}
}

/* function passWordCheck() {
	var lineid;
	var table = $("#details").DataTable();
	$.each(table.rows('.selected').data(), function() {
		lineid = this["id"];
	});

	if(!lineid) {
		parent.sendErrorMessage("Please select a line entry to apply discount.");
	} else {
		var pass = $("#passcheck").dialog({
			title: "Supervisor Password Required", 
			width: 440, 
			resizable: false, 
			modal: true, 
			buttons: [
				{
					text: "Proceed",
					click: function() {
						if($("#spass")!='') {
							var myPass = $.md5($("#spass").val());
							$.post("src/sjerp.php",{ mod: "checkSPass", pass: myPass, sid: Math.random() },function(result) {  
								if(result == "ok") { pass.dialog("close"); applyDiscount(); $("#spass").val(''); } else { parent.sendErrorMessage("The password you entered is invalid!"); }
							},"html");
						} else { parent. sendErrorMessage("Invalid Password!"); }
					
					},
					icons: { primary: "ui-icon-check" }
				},
				{
					text: "Close",
					click: function() { $(this).dialog("close"); },
					icons: { primary: "ui-icon-closethick" }
				}
			]
		});
	}
}

function applyDiscount(){
	var lineid;
	var table = $("#details").DataTable();
	$.each(table.rows('.selected').data(), function() {
		lineid = this["id"];
	});

   if(!lineid) {
		parent.sendErrorMessage("Please select a line entry you wish to apply for discount.");
	} else {
		if($("#scpwd_id").val() != '' || $("#is_pwd").val() == 'Y') {
			parent.sendErrorMessage("It appears that this patient is a Senior Citizen or PWD and shall be automatically be given the government mandated 20% discounts")
		} else {
			var dis = $("#discounter").dialog({
				title: "Line Discount", 
				width: 440, 
				resizable: false, 
				modal: true, 
				buttons: [
					{ 
						text: "Apply Discount",
						click: function() { 
							$.post("cso.datacontrol.php", { mod: "applyDiscount", cid: $("#customer_code").val(), cso_no: $("#cso_no").val(), discPercent: $("#discountPercent").val(), discType: $("#discountType").val(), lid: lineid, sid: Math.random() }, function() {
								redrawDataTable(); 
								getTotals();
								dis.dialog("close");
							});
						},
						icons: { primary: "ui-icon-check" }
					},
					{
						text: "Close",
						click: function() { $(this).dialog("close"); },
						icons: { primary: "ui-icon-closethick" }
					}
				]
			});
		}
	}
} */

function checkClear(val) {
	if(val == '' || val == 0) {
		$("#customer_code").val(''); $("#customer_name").val(''); $("#customer_address").val(''); $("#terms").val(0);
	}
}

function saveHeader() {
	var msg = "";
	if($("#customer_code").val() == "") { msg = msg + "- Please indicate Customer Information prior to saving any changes made to this document.<br/>"; }
	if($("#cso_type").val() == 'Mobile') {
		if($("#location").val() == '') { msg = msg + "- For mobile operation, please indicate the full address of company where procedures are performed.<br/>"; }
	}
	if($("#contact_person").val() == '') { msg = msg + "- Please indicate the company's authorized representative.<br/>"; }

	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("cso.datacontrol.php", { mod: "saveHeader", trace_no: $("#trace_no").val(), cso_no: $("#cso_no").val(), cso_date: $("#cso_date").val(), cid: $("#customer_code").val(), cname: $("#customer_name").val(), caddr: $("#customer_address").val(), terms: $("#terms").val(), company: $("#company").val(), location: $("#location").val(), from: $("#from").val(), until: $("#until").val(), po_no: $("#po_no").val(), po_date: $("#po_date").val(), cso_type: $("#cso_type").val(), contact_person: $("#contact_person").val(), contact_no: $("#contact_no").val(), email_add: $("#contact_email").val(), remarks: $("#remarks").val(), sid: Math.random() }, function (data) {
			if($("#cso_no").val() == "") { $("#cso_no").val(data); }
		},"html");
		parent.popSaver();
	}
}

function finalize() {
	if(confirm("Are you sure you want to finalize this Corporate Sales Order?") == true) { 
	
		$.post("cso.datacontrol.php", { mod: "check4print", cso_no: $("#cso_no").val(), sid: Math.random() }, function(data) {
			if(data == "noerror") {
				$("#uppermenus").html('');
				$.post("cso.datacontrol.php", { mod: "finalize", cso_no: $("#cso_no").val(), sid: Math.random() }, function() {
					parent.viewCSO($("#cso_no").val());
				});
			} else {
				switch(data) {
					case "head": parent.sendErrorMessage("Unable to finalize this document as it seems it hasn't been saved yet."); break;
					case "det": parent.sendErrorMessage("Unable to finalize this document as it seems products or services haven't been added yet."); break;
					case "both": parent.sendErrorMessage("Unable to finalize this document as it seems it hasn't been saved yet."); break;
				}
			}
		},"html");
	}
}

function reopen() {
	$.post("cso.datacontrol.php", { mod: "checkBilled", cso_no: $("#cso_no").val(), sid: Math.random() }, function(stat) {
		if(stat == 'processed') {
			parent.sendErrorMessage("- It appears that this Sales Order has already been paid or billed...");
		} else {
			if(confirm("Are you sure you want to set this document to active status?") == true) {
				$.post("cso.datacontrol.php", { mod: "reopen", cso_no: $("#cso_no").val(), sid: Math.random() }, function() {
					parent.viewCSO($("#cso_no").val()); 
				});
			}
		}
	},"html");
}

function cancel() {

	if(confirm("Are you sure you want to Cancel this document?") == true) {
		$.post("cso.datacontrol.php", { mod: "cancel", cso_no: $("#cso_no").val(), sid: Math.random() }, function(){
			alert("Sales Order Successfully Cancelled!");
			parent.viewCSO($("#cso_no").val());
		});
	}
}

function reuse() {
	if(confirm("Are you sure you want to Recycle this document?") == true) {
		$.post("cso.datacontrol.php", { mod: "reopen", cso_no: $("#cso_no").val(), sid: Math.random() }, function(){
			parent.viewCSO($("#cso_no").val());
		});
	}
}

function printCSO() {
	var cso_no = $("#cso_no").val();
	parent.printCSO(cso_no);
}

function printBarcode() {
	var cso_no = $("#cso_no").val();
	parent.printCSOBarcode(cso_no);
}

function printCheckList() {
	var cso_no = $("#cso_no").val();
	parent.printCSOChecklist(cso_no);
}

function verify() {
	if(confirm("Are you sure you want to mark this on-account transaction as verified?") == true) {
		$.post("cso.datacontrol.php", {
			mod: "verify",
			cso_no: $("#cso_no").val(),
			sid: Math.random() },
			function() {
				alert("On-Accounts transaction successfully verified!");
			}
		);

	}

}