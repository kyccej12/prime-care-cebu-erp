function computeItemAmount(qty) {
	if(isNaN(qty) == true) { parent.sendErrorMessage("-Invalid Quantity!"); $("#itemQty").val(''); }
	var cost = parseFloat(parent.stripComma($("#itemCost").val()));
	var sprice = parseFloat(parent.stripComma($("#itemSpecial").val()));

	if(cost != sprice) { var amount = qty * sprice; } else { var amount = qty * cost; }
	amount = amount.toFixed(2);

	$("#itemAmount").val(parent.kSeparator(amount));
}

function getTotals() {
	$.post("so.datacontrol.php", { mod: "getTotals", so_no: $("#so_no").val(), sid: Math.random() }, function(totals) {
		$("#gross").val(totals['gross']);
		$("#discount").val(totals['discount']);
		$("#amountdue").val(totals['amountdue']);
	 },"json");
}

function addItem() {
	saveSOHeader();
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
					if(isNaN($("#itemQty").val()) == true) { msg = msg + "- Invalid Quantity<br/>"; }

					if(msg != '') {
						parent.sendErrorMessage(msg);
					
					} else {
						$.post("so.datacontrol.php", { 
							mod: "addItem", 
							so_no: $("#so_no").val(),
							so_date: $("#so_date").val(),
							pid: $("#patient_id").val(),
							scpwd_id: $("#scpwd_id").val(),
							is_pwd: $("#is_pwd").val(),
							trace_no: $("#trace_no").val(), 
							item: $("#itemCode").val(), 
							description: $("#itemDescription").val(), 
							unit: $("#itemUnit").val(), 
							qty: $("#itemQty").val(),
							cost: $("#itemCost").val(), 
							sprice: $("#itemSpecial").val(), 
							ispecial: $("#is_sprice").val(),
							amount: $("#itemAmount").val(), 
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
			$("#itemUnit").val(ui.item.unit);
			$("#itemCost").val(ui.item.price);
			$("#itemSpecial").val(ui.item.specialprice);
			$("#is_sprice").val(ui.item.sprice);
			computeItemAmount($("#itemQty").val());
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
			$.post("so.datacontrol.php", { mod: "deleteLine", lid: arr[0], so_no: $("#so_no").val(), sid: Math.random() }, function(gt) { redrawDataTable(); getTotals(); });
		}
	}
}

function updateItem() {
	saveSOHeader();
	var table = $("#details").DataTable();
	var arr = [];
	$.each(table.rows('.selected').data(), function() {
		arr.push(this["id"]);
	});

	if(!arr[0]) {
		parent.sendErrorMessage("Please select a record to update.");
	} else {
		
		$.post("so.datacontrol.php", { mod: "retrieveLine", lid: arr[0], sid: Math.random() }, function(data) { 
			$("#itemDescription").val(parent.decodeEntities(data['description']));
			$("#itemCode").val(data['item_code']);
			$("#itemUnit").val(data['unit']);
			$("#itemQty").val(data['qty']);
			$("#itemCost").val(data['ucost']);
			$("#itemAmount").val(data['amt']);
		
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
							if(isNaN($("#itemQty").val()) == true) { msg = msg + "- Invalid Quantity<br/>"; }
			
							if(msg != '') {
								parent.sendErrorMessage(msg);
							
							} else {
								if(confirm("Are you sure you want to save changes made to this entry?") == true) {
									$.post("so.datacontrol.php", { 
										mod: "updateItem", 
										lid: arr[0],
										so_no: $("#so_no").val(), 
										so_date: $("#so_date").val(),
										pid: $("#patient_id").val(),
										scpwd_id: $("#scpwd_id").val(),
										is_pwd: $("#is_pwd").val(),
										trace_no: $("#trace_no").val(), 
										item: $("#itemCode").val(), 
										description: $("#itemDescription").val(), 
										unit: $("#itemUnit").val(), 
										qty: $("#itemQty").val(),
										cost: $("#itemCost").val(), 
										amount: $("#itemAmount").val(), 
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
				$("#itemUnit").val(ui.item.unit);
				$("#itemCost").val(ui.item.price);
				$("#itemSpecial").val(ui.item.specialprice);
				$("#is_sprice").val(ui.item.sprice);
				computeItemAmount($("#itemQty").val());
			}
		});

	}
}

function passWordCheck() {
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
							$.post("so.datacontrol.php", { mod: "applyDiscount", cid: $("#customer_code").val(), so_no: $("#so_no").val(), discPercent: $("#discountPercent").val(), discType: $("#discountType").val(), lid: lineid, sid: Math.random() }, function() {
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
}

function checkClear(val) {
	if(val == '' || val == 0) {
		$("#customer_code").val(''); $("#customer_name").val(''); $("#customer_address").val(''); $("#terms").val(0);
	}
}

function saveSOHeader() {
	var msg = "";
	if($("#patient_name").val() == "") { msg = msg + "- Please indicate Patient Information prior to saving any changes made to this document.<br/>"; }
	if($("#terms").val() != 0) {
		if($("#customer_name").val() == "") { msg = msg + "- For On-Account transactions, you must provide <b>Customer Information</b> prior to saving any changes to this document.<br/>"; }
	}

	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("so.datacontrol.php", { mod: "saveHeader", trace_no: $("#trace_no").val(), pri_no: $("#priority_no").val(), so_no: $("#so_no").val(), so_date: $("#so_date").val(), pid: $("#patient_id").val(), pname: $("#patient_name").val(), paddr: $("#patient_address").val(), cid: $("#customer_code").val(), cname: $("#customer_name").val(), caddr: $("#customer_address").val(), terms: $("#terms").val(), referred_by: $("#referred_by").val(),  hmo_provider: $("#hmo_provider").val(), hmo_no: $("#hmo_card_no").val(), card_expiry: $("#hmo_expiry_date").val(), is_pwd: $("#is_pwd").val(), sc_id: $("#scpwd_id").val(), with_loa: $("#with_loa").val(), loa_date: $("#loa_date").val(), pstat: $("#patient_stat").val(), physician: $("#physician").val(), dtype: $("#delivery_type").val(), email: $("#email_add").val(), remarks: $("#remarks").val(), sid: Math.random() }, function (data) {
			if($("#so_no").val() == "") { $("#so_no").val(data); }
		},"html");
		parent.popSaver();
	}
}

function saveSOHeader2() {
	var msg = "";
	if($("#patient_name").val() == "") { msg = msg + "- Please indicate Patient Information prior to saving any changes made to this document.<br/>"; }
	if($("#so_no").val() == '') { msg = msg + "- Invalid SO Number!"; }
	if($("#terms").val() != 0) {
		if($("#customer_name").val() == "") { msg = msg + "- For On-Account transactions, you must provide <b>Customer Information</b> prior to saving any changes to this document.<br/>"; }
	}

	if(msg != "") {
		parent.sendErrorMessage(msg);
	} else {
		$.post("so.datacontrol.php", { mod: "saveHeader2", trace_no: $("#trace_no").val(), pri_no: $("#priority_no").val(), so_no: $("#so_no").val(), so_date: $("#so_date").val(), pid: $("#patient_id").val(), pname: $("#patient_name").val(), paddr: $("#patient_address").val(), cid: $("#customer_code").val(), cname: $("#customer_name").val(), caddr: $("#customer_address").val(), terms: $("#terms").val(), referred_by: $("#referred_by").val(), hmo_provider: $("#hmo_provider").val(), hmo_no: $("#hmo_card_no").val(), card_expiry: $("#hmo_expiry_date").val(), is_pwd: $("#is_pwd").val(), sc_id: $("#scpwd_id").val(), with_loa: $("#with_loa").val(), loa_date: $("#loa_date").val(), pstat: $("#patient_stat").val(), physician: $("#physician").val(), dtype: $("#delivery_type").val(), email: $("#email_add").val(), remarks: $("#remarks").val(), sid: Math.random() }, function (data) {
			//if($("#so_no").val() == "") { $("#so_no").val(data); }
		},"html");
		parent.popSaver();
	}
}

function finalizeSO() {
	saveSOHeader();
	if(confirm("Are you sure you want to finalize this Sales Order?") == true) { 
	
		$.post("so.datacontrol.php", { mod: "check4print", so_no: $("#so_no").val(), sid: Math.random() }, function(data) {
			if(data == "noerror") {
				$("#uppermenus").html('');
				$.post("so.datacontrol.php", { mod: "finalize", so_no: $("#so_no").val(), sid: Math.random() }, function() {
					parent.viewSO($("#so_no").val());
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
	$.post("so.datacontrol.php", { mod: "checkBilled", so_no: $("#so_no").val(), sid: Math.random() }, function(stat) {
		if(stat == 'processed') {
			parent.sendErrorMessage("- It appears that this Sales Order has already been paid or billed...");
		} else {
			if(confirm("Are you sure you want to set this document to active status?") == true) {
				$.post("so.datacontrol.php", { mod: "reopen", so_no: $("#so_no").val(), sid: Math.random() }, function() {
					parent.viewSO($("#so_no").val()); 
				});
			}
		}
	},"html");
}

function cancel() {

	if(confirm("Are you sure you want to Cancel this document?") == true) {
		$.post("so.datacontrol.php", { mod: "cancel", so_no: $("#so_no").val(), sid: Math.random() }, function(){
			alert("Sales Order Successfully Cancelled!");
			parent.viewSO($("#so_no").val());
		});
	}
}

function reuse() {
	if(confirm("Are you sure you want to Recycle this document?") == true) {
		$.post("so.datacontrol.php", { mod: "reopen", so_no: $("#so_no").val(), sid: Math.random() }, function(){
			parent.viewSO($("#so_no").val());
		});
	}
}

function printSO() {
	var so_no = $("#so_no").val();
	parent.printSO(so_no);
}

function verify() {
	if(confirm("Are you sure you want to mark this on-account transaction as verified?") == true) {
		$.post("so.datacontrol.php", {
			mod: "verify",
			so_no: $("#so_no").val(),
			sid: Math.random() },
			function() {
				alert("On-Accounts transaction successfully verified!");
			}
		);

	}

}