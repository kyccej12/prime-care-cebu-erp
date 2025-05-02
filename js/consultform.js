function finalizeRecord() {
	if(confirm("Are you sure you want to finalize this record?") == true) { 
	
		$.post("consult.datacontrol.php", { mod: "check4print", so_no: $("#so_no").val(), sid: Math.random() }, function(data) {
			if(data == "noerror") {
				$("#uppermenus").html('');
				$.post("consult.datacontrol.php", { mod: "finalize", so_no: $("#so_no").val(), sid: Math.random() }, function() {
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

function printMedCert() {
    var sono = $("#so_no").val();
    var transid = $("#trans_id").val();
    var pid = $("#pid").val();
    parent.printMedCertX(transid,pid,sono);
    
}

function printReferral() {
    window.open("print/referral.pdf","Medical Clearance","location=1,status=1,scrollbars=1,width=640,height=720");
}

function printRX() {
    var sono = $("#so_no").val();
    var transid = $("#trans_id").val();
    var pid = $("#pid").val();
    parent.printRXX(transid,pid,sono);
    
}

function printReq() {
    var sono = $("#so_no").val();
    var transid = $("#trans_id").val();
    var pid = $("#pid").val();
    parent.printLabReq(transid,pid,sono);
    
}

function printMedClearance() {
    window.open("print/medclearance.pdf","Medical Clearance","location=1,status=1,scrollbars=1,width=640,height=720");
}

function saveRecord() {
    var msg = "";
    if($("#cSelected").val() == "N") { msg = msg + "- Invalid or missing Customer Information<br/>"; }
    if(msg != "") {
        parent.sendErrorMessage(msg);
    } else {

        $.post("consult.datacontrol.php", { 
            mod: "saveRecord", 
            trace_no: $("#trace_no").val(), 
            with_appointment: $("#with_appointment").val(), 
            pid: $("#patient_id").val(),
            pname: $("#patient_name").val(), 
            dept: $("#dept").val(), 
            com_id: $("#company_id").val(), 
            pname: $("#patient_name").val(), 
            birthdate: $("#birthdate").val(), 
            gender: $("#gender").val(), 
            age: $("#age").val(), 
            last_visit: $("#last_visit").val(), 
            date: $("#date").val(), 
            is_pwd: $("#is_pwd").val(), 
            temp: $("#temp").val(), 
            weight: $("#weight").val(), 
            height: $("#height").val(), 
            pulse: $("#pulse").val(), 
            respiratory: $("#respiratory").val(), 
            bp: $("#bp").val(), 
            bmi: $("#bmi").val(), 
            physician: $("#physician").val(), 
            sid: Math.random() },
            function() {
                parent.popSaver();
            }
        );
    }
}

function saveComplaint() {
    $.post("consult.datacontrol.php", { 
        mod: "saveComplaint",
        trace_no: $("#trace_no").val(),
        complaint: $("#complaint").val(),
        sid: Math.random() }
    );
}

function saveDiagnosis() {
    $.post("consult.datacontrol.php", { 
        mod: "saveDiagnosis",
        trace_no: $("#trace_no").val(),
        diagnosis: $("#diagnosis").val(),
        sid: Math.random() }
    );
}

function saveTreatment() {
    $.post("consult.datacontrol.php", { 
        mod: "saveTreatment",
        trace_no: $("#trace_no").val(),
        treatment: $("#treatment").val(),
        sid: Math.random() }
    );
}

function saveRecommendation() {
    $.post("consult.datacontrol.php", { 
        mod: "saveRecommendation",
        trace_no: $("#trace_no").val(),
        recommendation: $("#recommendation").val(),
        sid: Math.random() }
    );
}

function savePrescription() {
    $.post("consult.datacontrol.php", { 
        mod: "savePrescription",
        trace_no: $("#trace_no").val(),
        prescription1: $("#prescription1").val(),
        prescription2: $("#prescription2").val(),
        prescription3: $("#prescription3").val(),
        prescription4: $("#prescription4").val(),
        prescription5: $("#prescription5").val(),
        sig: $("#sig").val(),
        sid: Math.random() },
        function() {
            parent.popSaver();
        }
    );
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

					if(msg != '') {
						parent.sendErrorMessage(msg);
					
					} else {
						$.post("consult.datacontrol.php", { 
							mod: "addItem", 
							so_no: $("#so_no").val(),
							pid: $("#pid").val(),
							trace_no: $("#trace_no").val(), 
							item: $("#itemCode").val(), 
							description: $("#itemDescription").val(), 
							unit: $("#itemUnit").val(), 
							sid: Math.random() }, 
						function() {
							redrawDataTable();
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
		}
	});
}

function deleteItem(){
	var table = $("#labrequest").DataTable();
	var arr = [];
   $.each(table.rows('.selected').data(), function() {
	   arr.push(this["id"]);
   });
  
	if(!arr[0]) {
		parent.sendErrorMessage("Please select a record to delete.");
	} else {
		if(confirm("Are you sure you want to remove this line entry?") == true) {
			$.post("consult.datacontrol.php", { mod: "deleteLine", lid: arr[0], so_no: $("#so_no").val(), pid: $("#pid").val(), traceno: $("#trace_no").val(),  sid: Math.random() }, function(gt) { redrawDataTable(); });
		}
	}
}