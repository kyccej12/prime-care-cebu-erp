/* Percentage of Screen Size */
var wWidth = $(window).width();
var xWidth = wWidth * 0.8;
var xWidth = wWidth * 0.95;

var monthNames = [
	"January",
	"February",
	"March",
	"April",
	"May",
	"June",
    "July",
	"August",
	"September",
	"October",
	"November",
	"December"
];

var enumResultSelection = [
	"NEGATIVE",
	"POSITIVE",
	"REACTIVE",
	"NON-REACTIVE",
	"WEAKLY REACTIVE"
]

var patStatus = [
	"A.P.E",
	"FOR EMPLOYMENT",
	"WALKIN",
	"A.P.E COMPLETION",
	"CONSULTATION",
	"PERSONAL",
	"MANDATORY REQUIREMENT",
	"STUDENT",
	"SYMPTOMATIC",
	"ASSYMPTOMATIC"
]

var dengueMethod = [
	"IMMUNOCHROMATOGRAPHIC ASSAY / RAPID TEST"
];

var dengueKit = [
	"SD BIOSENSOR STANDARD Q DENDUE DUO",
	"ABBOTT BIOLINE HIV-1/2 3.0"
];

var remarksAll = [
	"Screening Test Only.",
	"Screening Test Only. Please correlate clinically. Suggestive for confirmatory through quantitative testing."
];
/* Search Supplier on Reports */
$(document).ready(function($){
    $('#cab_snamem, #subledger_sname, #dsr_customer, #ape_customer').autocomplete({
		source:'suggestSupplier.php', 
		minLength:3,
	});

	$('#ape_referredby').autocomplete({
		source:'suggestReferredBy.php', 
		minLength:3
	});	

	$("#enum_remarks, #hiv_remarks, #sresult_remarks").autocomplete({
		source: remarksAll,
		minLength: 0
	}).focus(function() {
		$(this).data("uiAutocomplete").search($(this).val());
	});
	
	$("#enum_result, #hiv_result").autocomplete({
		source: enumResultSelection,
		minLength: 0
	}).focus(function() {
		$(this).data("uiAutocomplete").search($(this).val());
	});

	$("#dengue_method, #enum_method, #hiv_method").autocomplete({
		source: dengueMethod,
		minLength: 0
	}).focus(function() {
		$(this).data("uiAutocomplete").search($(this).val());
	});

	$("#dengue_testkit, #enum_testkit, #hiv_testkit").autocomplete({
		source: dengueKit,
		minLength: 0
	}).focus(function() {
		$(this).data("uiAutocomplete").search($(this).val());
	});

	$('#subledger_acct,#gls_acct').autocomplete({
		source:'suggestAcctCodeOnly.php', 
		minLength:3,
	});
	
	$('#gls_client, #subledger_sid').autocomplete({
		source:'suggestContacts.php', 
		minLength:3,
	});
	
	$("#enum_result").autocomplete({
		source: enumResultSelection,
		minLength: 0
	}).focus(function() {
		$(this).data("uiAutocomplete").search($(this).val());
	});

	$("#tmp_date").datepicker();

	$('#phleb_by').autocomplete({
		source:'suggestEmployee.php', 
		minLength:3
	});

	$('#appPatientId').autocomplete({
		source:'suggestPatient.php', 
		minLength:3,
		select: function(event,ui) {
			$("#appPatientName").val(ui.item.name);
			$("#appPatientAddress").val(ui.item.addr);
			$("#appGender").val(ui.item.gender);
			$("#appBirthdate").val(ui.item.bday);
			$("#appContactNo").val(ui.item.contactno);
		}
	});

	$('#dsr_idesc').autocomplete({
		source:'suggestService.php', 
		minLength:3,
		select: function(event,ui) {
			$("#dsr_icode").val(ui.item.code);
		}
	});


	$("#pt_result").autocomplete({
		 source: enumResultSelection
	});

	$("#enum_patientstat, #btype_patientstat").autocomplete({
		source: patStatus
   });

	$("#template_details").jqte();
});

function popSaver() {
	$('#popSaver').fadeIn('fast').delay(1000).fadeOut('slow');
}

function decodeEntities(encodedString) {
    var translate_re = /&(nbsp|amp|quot|lt|gt);/g;
    var translate = {
        "nbsp":" ",
        "amp" : "&",
        "quot": "\"",
        "lt"  : "<",
        "gt"  : ">"
    };
    return encodedString.replace(translate_re, function(match, entity) {
        return translate[entity];
    }).replace(/&#(\d+);/gi, function(match, numStr) {
        var num = parseInt(numStr, 10);
        return String.fromCharCode(num);
    });
}

function stripComma(val) {
	return val.replace(/,/g,"");
}

function kSeparator(val) {
	var val = parseFloat(val);
		val = val.toFixed(2);
	var a = val.split(".");
	var kValue = a[0];
	//if(a[1] == '' || a[1] == 'undefined') { a[1] = '00'; }

	var sRegExp = new RegExp('(-?[0-9]+)([0-9]{3})');
	while(sRegExp.test(kValue)) {
		kValue = kValue.replace(sRegExp, '$1,$2');
	}

	if(a[1] != "") {
		kValue = kValue + "." + a[1]; 
		return kValue;
	} else {
		return kValue + ".00";
	}
}
	
function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}	

function sendErrorMessage(msg) {
	$("#message").html(msg);
	$("#errorMessage").dialog({
		width: 400,
		resizable: false,
		modal: true,
		buttons: [
			{
			 	text: "Okay",
				click: function() { $(this).dialog("close"); },
				icons: { primary: "ui-icon-check" }
			}
		]
	});
}

function showLoaderMessage() {
	$("#loaderMessage").dialog({ width: 400, height: 150, closable: false, modal: true,  open: function(event, ui) {
        $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
    }});
}

function acctLookupReport(inputString,el) {
	if(inputString.length == 0) {
		$('#suggestions').hide();
	} else {
		var op = $("#"+el+"").offset();
		$.post("acctlookup_r.php", {queryString: inputString, el: el, sid: Math.random() }, function(data){
		if(data.length > 0) {
			$('#suggestions').css({top: op.top+20, left: op.left});
			$('#suggestions').show();
			$('#autoSuggestionsList').html(data);
		} else { $("#suggestions").hide(); }
		});
	}
}

function pickAccountReport(acct_code,el) {
	document.getElementById(el).value = acct_code;
}

function showDashboard() {
	$("#preboard").dialog({title: "Data Dashboard", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function fetchDashboard() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='acctg_db.php?month="+$("#dboard_month").val()+"&year="+$("#dboard_year").val()+"'></iframe>";
	$("#acctgdash").html(txtHTML);
	$("#acctgdash").dialog({title: "Data Dashboard (Accounting)", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}
	
function showUsers() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='user.master.php'></iframe>";
	$("#userlist").html(txtHTML);
	$("#userlist").dialog({title: "System Users", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showChangePass() {
	$("#userChangePass").dialog({ title: "Update Password", width: 480, height: 190, resizable: false, modal: true, buttons: {
					"Update my Password": function() {
						var msg = "";

						if($("#pass1").val() == "" || $("#pass2").val() == "") { msg = msg + "The system cannot accept empty password.<br/>"; }
						if($("#pass1").val() != $("#pass2").val()) { msg = msg + "New Passwords do not match.<br/>"; }
					
						if(msg!="") {
							sendErrorMessage(msg);
						} else {

							$.post("src/sjerp.php", { mod: "changePassword", uid:  $("#myUID").val(), pass: $("#pass1").val(), sid: Math.random() },function() {
								alert("You have successfully updated your password!");
								$("#userChangePass").dialog("close");
							});
						}
					},
					"Continue with the System": function () { $(this).dialog("close"); }
				} }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true,
	});
}
function addUser() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='user.details.php'></iframe>";
	$("#userdetails").html(txtHTML);
	$("#userdetails").dialog({title: "System User Info.", width: 400, height: 260, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true,
	});
}

function viewUserInfo(eid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='user.update.php?eid="+eid+"'></iframe>";
	$("#userdetails").html(txtHTML);
	$("#userdetails").dialog({title: "System User Info.", width: 400, height: 260, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true,
	});
}


function showUserDetails(uid) {
	var uname;
	$.post("src/sjerp.php", { mod: "getUinfo", uid: uid, sid: Math.random() }, function(data) {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='user.rights.php?uid="+uid+"'></iframe>";
		$("#userrights").html(txtHTML);
		$("#userrights").dialog({title: "User Access Rights ("+data+")", width: 560, height: 670, resizable: false}).dialogExtend({
			"closable" : true,
		    "maximizable" : false,
		    "minimizable" : true,
		});
	 },"html");
}

function showCust(mod) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='contact.master.php?mod="+mod+"'></iframe>";
	$("#customerlist").html(txtHTML);
	$("#customerlist").dialog({title: "Customers/Payees/Suppliers", width: xWidth, height: 540,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function addPayee() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='contact.details.php?mod=1'></iframe>";
	$("#customerdetails").html(txtHTML);
	$("#customerdetails").dialog({title: "Customers/Payees/Suppliers", width: 1024, height: 520,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showPayeeInfo(fid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='contact.details.php?fid="+fid+"&mod=1&sid="+Math.random()+"'></iframe>";
	$("#customerdetails").html(txtHTML);
	$("#customerdetails").dialog({title: "Customers/Payees/Suppliers Info", width: 1024, height: 520, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

/****************/

function showPatients() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='patient.master.php'></iframe>";
	$("#customerlist").html(txtHTML);
	$("#customerlist").dialog({title: "Patient Archive", width: xWidth, height: 540,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function addPatient() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='patient.details.php?mod=1&sid="+Math.random()+"'></iframe>";
	$("#customerdetails").html(txtHTML);
	$("#customerdetails").dialog({title: "Patient Information", width: 720, height: 820,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showPatientInfo(pid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='patient.details.php?mod=1&pid="+pid+"&sid="+Math.random()+"'></iframe>";
	$("#customerdetails").html(txtHTML);
	$("#customerdetails").dialog({title: "Patient Information", width: 720, height: 820,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function printPatientInfo(pid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/patientinfo.php?pid="+pid+"&sid="+Math.random()+"'></iframe>";
	$("#report5").html(txtHTML);
	$("#report5").dialog({title: "Patient Information", width: 720, height: 820,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});

}

function showAppointments() {
	$("#appSOdate").datepicker();
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='appointment.list.php'></iframe>";
	$("#cvlist").html(txtHTML);
	$("#cvlist").dialog({title: "Clinic Appointments", width: xWidth, height: 540,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function makeAppointment() {
	$("#patientAppointment").trigger("reset");
	$("#appDate").datepicker();

	var dis = $("#appointment").dialog({ 
		title: "Make Appointment",
		width: "500",
		modal: true,
		resizeable: false,
		buttons: [
			{
				icons: { primary: "ui-icon-check" },
				text: "Make Appointment",
				click: function() { 
					if(confirm("Are you sure you want to make this appointment?") == true) {
						var dataString = $("#patientAppointment").serialize();
							dataString = "mod=newAppointment&" + dataString;
							$.ajax({
								type: "POST",
								url: "src/sjerp.php",
								data: dataString,
								success: function() {
								alert("Appointment Succesfully set!");
								showAppointments();
								$("#patientAppointment").trigger("reset");
							}
						});
					}	
				}
			},
			{
				icons: { primary: "ui-icon-closethick" },
				text: "Close Window",
				click: function() { 
					$("#appointment").dialog("close");
				}	

			}		

		]

	});
}

function viewAppointment(id) {
	$.post("src/sjerp.php", { mod: "viewAppointment", lid: id, sid: Math.random() }, function(data) {
		
		$("#appRecordId").val(data['record_id'])
		$("#appPatientId").val(data['patient_id']);
		$("#appPatientName").val(data['patient_name']);
		$("#appPatientAddress").val(data['address']);
		$("#appGender").val(data['gender']);
		$("#appBirthdate").val(data['bday']);
		$("#appContactNo").val(data['contact_no']);
		$("#appGuardian").val(data['guardian']);
		$("#appSOno").val(data['so_no']);
		$("#appSOdate").val(data['sodate']);
		$("#appDate").val(data['sdate']);
		$("#appSchedule").val(data['scheduled_slot']);
		$("#appConsultType").val(data['category']);
		$("#appCategory").val(data['request_category']);
		$("#appDoctor").val(data['preferred_doctor']);
		$("#appRemarks").val(data['memo']);

		var dis = $("#appointment").dialog({ 
			title: "Appointment Details",
			width: "500",
			modal: true,
			resizeable: false,
			buttons: [
				{
					icons: { primary: "ui-icon-check" },
					text: "Save Changes Made",
					click: function() { 
						if(confirm("Are you sure you want to save changes to this appointment?") == true) {
							var dataString = $("#patientAppointment").serialize();
								dataString = "mod=updateAppointment&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
									alert("Appointment Succesfully set!");
									showAppointments();
									$("#patientAppointment").trigger("reset");
								}
							});
						}	
					}
				},
				{
					icons: { primary: "ui-icon-closethick" },
					text: "Close Window",
					click: function() { 
						$("#appointment").dialog("close");
					}	
	
				}		
	
			]
	
		});

	},"json");
}

function queryRequestCategory(id,selbox,queryType) {
	$.post("src/sjerp.php", { mod: "queryRequestCategory", id: id, queryType: queryType, sid: Math.random() }, function(resultSet) {
		if(queryType == 1) {
			document.getElementById(selbox).innerHTML = resultSet;
		} else {
			$("#appConsultType").val(resultSet);
		}
	});

}

/****************/

function showSO(){
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='so.list.php'></iframe>";
	$("#projlist").html(txtHTML);
	$("#projlist").dialog({title: "Service Order Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewSO(so_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='so.details.php?so_no="+so_no+"'></iframe>";
	$("#projdetails").html(txtHTML);
	$("#projdetails").dialog({title: "Service Order Details", width: 1120, height: 640, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showSOBackdate() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='so.details.bk.php'></iframe>";
	$("#projdetails").html(txtHTML);
	$("#projdetails").dialog({title: "Service Order Details", width: 1120, height: 640, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printSO(so_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/so.print.php?so_no="+so_no+"&sid="+Math.random()+"'></iframe>";
	$("#poprint").html(txtHTML);
	$("#poprint").dialog({title: "PRINT >> SERVICE ORDER", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printLabRequest(so_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/labrequest.php?so_no="+so_no+"&sid="+Math.random()+"'></iframe>";
	$("#poprint").html(txtHTML);
	$("#poprint").dialog({title: "PRINT >> LAB REQUEST FORM", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showPEME(){
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='peme.list.php'></iframe>";
	$("#pemelist").html(txtHTML);
	$("#pemelist").dialog({title: "Physical/Medical Examination Requests", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}


/****************/
function showSOA() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='soa.list.php?sid="+Math.random()+"'></iframe>";
	$("#itemlist").html(txtHTML);
	$("#itemlist").dialog({title: "Statement of Account Summary", width: xWidth, height: 540,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function viewSOA(soa_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='soa.details.php?soa_no="+soa_no+"'></iframe>";
	$("#projdetails").html(txtHTML);
	$("#projdetails").dialog({title: "Statement of Account Details", width: 1120, height: 640, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printSOA(soa_no,tag,source) {
	if(tag == '') {
		if(source == 'PHARMA') {
			var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/soa.pharma.print.php?soa_no="+soa_no+"&sid="+Math.random()+"'></iframe>";
		}else {
			var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/soa.print.php?soa_no="+soa_no+"&sid="+Math.random()+"'></iframe>";
		}
	} else {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/soa.print2.php?soa_no="+soa_no+"&sid="+Math.random()+"'></iframe>";
	}
	
	$("#rrprint").html(txtHTML);
	$("#rrprint").dialog({title: "PRINT >> STATEMENT OF ACCOUNT", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showUnbilled() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='unbilledso.list.php?sid="+Math.random()+"'></iframe>";
	$("#pos").html(txtHTML);
	$("#pos").dialog({title: "Unbilled Sales Order", width: xWidth, height: 540,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showUnpaidStatement() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='unpaidsoa.list.php?sid="+Math.random()+"'></iframe>";
	$("#report5").html(txtHTML);
	$("#report5").dialog({title: "Unpaid Statement of Accounts", width: xWidth, height: 540,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}
/****************/

function showServices(code) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='services.php?sid="+Math.random()+"&code="+code+"'></iframe>";
	$("#itemlist").html(txtHTML);
	$("#itemlist").dialog({title: "List of Services", width: xWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showServiceInfo(id) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='service.details.php?id="+id+"&mod=1&sid="+Math.random()+"'></iframe>";
	$("#itemdetails").html(txtHTML);
	$("#itemdetails").dialog({title: "Service Details", width: 1120, height: 520, resizable: false }).dialogExtend({
		"closable" : true,
		"maximizable" : false,
		"minimizable" : true
	});
}


function showItems() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='items.master.php?sid="+Math.random()+"'></iframe>";
	$("#itemlist").html(txtHTML);
	$("#itemlist").dialog({title: "Supplies & Materials", width: xWidth, height: 600,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showItemInfo(rid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='items.details.php?id="+rid+"&mod=1&sid="+Math.random()+"'></iframe>";
	$("#itemdetails").html(txtHTML);
	$("#itemdetails").dialog({title: "Product Details", width: 1120, height: 520, resizable: false }).dialogExtend({
		"closable" : true,
		"maximizable" : false,
		"minimizable" : true
	});
}

function showSgroup() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='isubgroup.master.php'></iframe>";
	$("#report5").html(txtHTML);
	$("#report5").dialog({title: "Inventory Sub Group List", width: 800, height: 400,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function exportProducts(item_code,unit,mydate) {
	window.open("export/products.php?sid="+Math.random()+"","Inventory Stockcard","location=1,status=1,scrollbars=1,width=640,height=720");
}

function showVAT(){
	$("#vatRelief").dialog({title: "BIR RELIEF FILE GENERATOR", width: 400 }).dialogExtend({
		"closable" : true,
		"maximizable" : false,
		"minimizable" : true
	});
}

function generateVAT(){
	var vat_month = $("#vat_month").val();
	var vat_year = $("#vat_year").val();
	var vat_type = $("#vat_type").val();
	if(vat_type=='input'){
		window.open("export/input.php?month="+vat_month+"&year="+vat_year+"","Unclassified Accounts","location=1,status=1,scrollbars=1,width=640,height=720");
	}else{
		window.open("export/output.php?month="+vat_month+"&year="+vat_year+"","Unclassified Accounts","location=1,status=1,scrollbars=1,width=640,height=720");
	}	
}

function showAccounts() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='accounts.php'></iframe>";
	$("#accountlist").html(txtHTML);
	$("#accountlist").dialog({title: "Chart of Accounts", width: 1024, height: 480, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showAccountInfo(rid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='accounts.details.php?code="+rid+"'></iframe>";
	$("#accountdetails").html(txtHTML);
	$("#accountdetails").dialog({title: "Chart of Accounts", width: 480, height: 215, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showBanks() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='banks.master.php'></iframe>";
	$("#banklist").html(txtHTML);
	$("#banklist").dialog({title: "CIB Accounts", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showBankDetails(bid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='bank.details.php?bid="+bid+"'></iframe>";
	$("#bankdetails").html(txtHTML);
	$("#bankdetails").dialog({title: "CIB Account Details", width: 480, height: 332, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showPOList() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='po.list.php'></iframe>";
	$("#polist").html(txtHTML);
	$("#polist").dialog({title: "Purchase Order Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewPO(po_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='po.details.php?po_no="+po_no+"'></iframe>";
	$("#podetails").html(txtHTML);
	$("#podetails").dialog({title: "Purchase Order Details", width: 1120, height: 540, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printPO(po_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/po.print.php?po_no="+po_no+"&rePrint="+rePrint+"&sid="+Math.random()+"&user="+uid+"'></iframe>";
	$("#poprint").html(txtHTML);
	$("#poprint").dialog({title: "PRINT >> PURCHASE ORDER", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printPOPList(po_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/po.plist.php?po_no="+po_no+"&rePrint="+rePrint+"&sid="+Math.random()+"&user="+uid+"'></iframe>";
	$("#poprint").html(txtHTML);
	$("#poprint").dialog({title: "PRINT >> PURCHASE ORDER PACKING LIST", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showJOList() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='jo.list.php'></iframe>";
	$("#jolist").html(txtHTML);
	$("#jolist").dialog({title: "Job Order Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewJO(doc_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='jo.details.php?doc_no="+doc_no+"'></iframe>";
	$("#jodetails").html(txtHTML);
	$("#jodetails").dialog({title: "Job Order Details", width: 1120, height: 360, resizable: true, modal: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printJO(doc_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/jo.print.php?doc_no="+doc_no+"&sid="+Math.random()+"'></iframe>";
	$("#poprint").html(txtHTML);
	$("#poprint").dialog({title: "PRINT >> JOB ORDER", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showRRList() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='rr.list.php'></iframe>";
	$("#rrlist").html(txtHTML);
	$("#rrlist").dialog({title: "Receiving Report Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewRR(rr_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='rr.details.php?rr_no="+rr_no+"'></iframe>";
	$("#rrdetails").html(txtHTML);
	$("#rrdetails").dialog({title: "Receiving Report Details", width: xWidth, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printRR(rr_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/rr.print.php?rr_no="+rr_no+"&sid="+Math.random()+"'></iframe>";
	$("#rrprint").html(txtHTML);
	$("#rrprint").dialog({title: "PRINT >> RECEIVING REPORT", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showAPVList() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='apv.list.php'></iframe>";
	$("#apvlist").html(txtHTML);
	$("#apvlist").dialog({title: "Accounts Payable Voucher Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewAP(apv_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='apv.details.php?apv_no="+apv_no+"'></iframe>";
	$("#apvdetails").html(txtHTML);
	$("#apvdetails").dialog({title: "Accounts Payable Voucher Details", width: 1120, height: 600, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printAPV(apv_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/apv.print.php?apv_no="+apv_no+"&rePrint="+rePrint+"&sid="+Math.random()+"&user="+uid+"'></iframe>";
	$("#apvprint").html(txtHTML);
	$("#apvprint").dialog({title: "PRINT >> Vouchers Payable", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showRFP(){
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='rfp.list.php'></iframe>";
	$("#rfplist").html(txtHTML);
	$("#rfplist").dialog({title: "Request for Payment", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewRFP(rfp_no)	
	{
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='rfp.details.php?rfp_no="+rfp_no+"'></iframe>";
		$("#rfpdetails").html(txtHTML);
		$("#rfpdetails").dialog({title: "Request for Payment", width: 1120, height: 540, resizable: true }).dialogExtend({
			"closable" : true,
		    "maximizable" : true,
		    "minimizable" : true
		});
	}

function printRFP(rfp,uid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/rfp.print.php?rfp_no="+rfp+"&sid="+Math.random()+"&user="+uid+"'></iframe>";
	$("#rfpprint").html(txtHTML);
	$("#rfpprint").dialog({title: "PRINT >> REQUEST FOR PAYMENT", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showGRFP(){//grfp_list
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='grfp.list.php'></iframe>";
	$("#grfplist").html(txtHTML);
	$("#grfplist").dialog({title: "Petty Cash Request", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewGRFP(grfp_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='grfp.details.php?grfp_no="+grfp_no+"'></iframe>";
	$("#grfpdetails").html(txtHTML);
	$("#grfpdetails").dialog({title: "Petty Cash Request", width: 1024, height: 360, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printGRFP(grfp_no,uid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/grfp.print.php?grfp_no="+grfp_no+"&sid="+Math.random()+"&user="+uid+"'></iframe>";
	$("#grfpprint").html(txtHTML);
	$("#grfpprint").dialog({title: "PRINT >> Petty Cash Request", width: 560, height: 500, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showCV() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='cv.list.php'></iframe>";
	$("#cvlist").html(txtHTML);
	$("#cvlist").dialog({title: "Cash/Check Disbursement Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewCV(cv_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='cv.details.php?cv_no="+cv_no+"'></iframe>";
	$("#cvdetails").html(txtHTML);
	$("#cvdetails").dialog({title: "Cash/Check Disbursement Details", width: xWidth, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function jumpCVPage(pageNum,stext,sdetails) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='cv.list.php?page="+pageNum+"&searchtext="+stext+"&includeDetails="+sdetails+"'></iframe>";
	$("#cvlist").html(txtHTML);
	$("#cvlist").dialog({title: "Cash/Check Disbursement Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function printCV(cv_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/cv.print.php?cv_no="+cv_no+"&rePrint="+rePrint+"&sid="+Math.random()+"&user="+uid+"'></iframe>";
	$("#cvprint").html(txtHTML);
	$("#cvprint").dialog({title: "PRINT >> Cash/Check Voucher", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showJV() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='jv.list.php'></iframe>";
	$("#jvlist").html(txtHTML);
	$("#jvlist").dialog({title: "Journal Voucher Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewJV(j_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='jv.details.php?j_no="+j_no+"'></iframe>";
	$("#jvdetails").html(txtHTML);
	$("#jvdetails").dialog({title: "Journal Voucher Details", width: xWidth, height: 540, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function jumpJVPage(pageNum,stext,sdetails) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='jv.list.php?page="+pageNum+"&searchtext="+stext+"&includeDetails="+sdetails+"'></iframe>";
	$("#jvlist").html(txtHTML);
	$("#jvlist").dialog({title: "Journal Voucher Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}


function printJV(j_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/jv.print.php?j_no="+j_no+"&rePrint="+rePrint+"&sid="+Math.random()+"&user="+uid+"'></iframe>";
	$("#apvprint").html(txtHTML);
	$("#apvprint").dialog({title: "PRINT >> JOURNAL VOUCHER", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

/* Fixed Asset Management */
function showFA() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='fa.list.php'></iframe>";
	$("#falist").html(txtHTML);
	$("#falist").dialog({title: "Fixed Assets Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewFA(fid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='fa.details.php?fid="+fid+"&sid="+Math.random()+"'></iframe>";
	$("#fadetails").html(txtHTML);
	$("#fadetails").dialog({title: "Fixed Asset Details", width: 960, height: 515, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewMlogs(fid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='fa_mlogs.php?fid="+fid+"&sid="+Math.random()+"'></iframe>";
	$("#stockcard").html(txtHTML);
	$("#stockcard").dialog({title: "Asset Maintenance Logs", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}



function showOR() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='or.list.php'></iframe>";
	$("#crlist").html(txtHTML);
	$("#crlist").dialog({title: "Official Receipts Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewOR(doc_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='or.details.php?doc_no="+doc_no+"'></iframe>";
	$("#crdetails").html(txtHTML);
	$("#crdetails").dialog({title: "Official Receipt Details", width: 1120, height: 740, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printOR(doc_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/or.print.php?doc_no="+doc_no+"&sid="+Math.random()+"'></iframe>";
	$("#crprint").html(txtHTML);
	$("#crprint").dialog({title: "PRINT >> OFFICIAL RECEIPT RECEIPT", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showARBeginning() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='arbeginning.php'></iframe>";
	$("#jvdetails").html(txtHTML);
	$("#jvdetails").dialog({title: "Consolidated Beginning Balance (Accounts Receivable)", width: xWidth, height: 540, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showAPBeginning() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='apbeginning.php'></iframe>";
	$("#jvdetails").html(txtHTML);
	$("#jvdetails").dialog({title: "Consolidated Beginning Balance (Accounts Payable)", width: xWidth, height: 540, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showSRR() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='srr.list.php'></iframe>";
	$("#srrlist").html(txtHTML);
	$("#srrlist").dialog({title: "Stocks Return Slip Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewSRR(srr_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='srr.details.php?srr_no="+srr_no+"'></iframe>";
	$("#srrdetails").html(txtHTML);
	$("#srrdetails").dialog({title: "Stocks Return Slip Details", width: xWidth, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printSRR(srr_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/srr.print.php?srr_no="+srr_no+"&uid="+uid+"&rePrint="+rePrint+"&sid="+Math.random()+"'></iframe>";
	$("#srrprint").html(txtHTML);
	$("#srrprint").dialog({title: "PRINT >> STOCKS RETURN SLIP", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showPhy() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='phy.list.php'></iframe>";
	$("#phylist").html(txtHTML);
	$("#phylist").dialog({title: "Physical Inventory Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewPhy(doc_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='phy.details.php?doc_no="+doc_no+"'></iframe>";
	$("#phydetails").html(txtHTML);
	$("#phydetails").dialog({title: "Physical Inventory Form", width: 1120, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}


function printPhy(doc_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/phy.print.php?doc_no="+doc_no+"&sid="+Math.random()+"'></iframe>";
	$("#srrprint").html(txtHTML);
	$("#srrprint").dialog({title: "PRINT >> Physical Inventory Form", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showSW() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='sw.list.php'></iframe>";
	$("#swlist").html(txtHTML);
	$("#swlist").dialog({title: "Stocks Withdrawal Slip Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewSW(sw_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='sw.details.php?sw_no="+sw_no+"'></iframe>";
	$("#swdetails").html(txtHTML);
	$("#swdetails").dialog({title: "Stocks Withdrawal Slip Details", width: 1120, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printSW(sw_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/sw.print.php?sw_no="+sw_no+"&uid="+uid+"&rePrint="+rePrint+"&sid="+Math.random()+"'></iframe>";
	$("#srrprint").html(txtHTML);
	$("#srrprint").dialog({title: "PRINT >> STOCKS WITHDRAWAL SLIP", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showMRS() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='mrs.list.php'></iframe>";
	$("#swlist").html(txtHTML);
	$("#swlist").dialog({title: "Material Request Slip", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewMRS(mrs_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='mrs.details.php?mrs_no="+mrs_no+"'></iframe>";
	$("#swdetails").html(txtHTML);
	$("#swdetails").dialog({title: "Materials Request Details", width: 1120, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printMRS(mrs_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/mrs.print.php?mrs_no="+mrs_no+"&uid="+uid+"&rePrint="+rePrint+"&sid="+Math.random()+"'></iframe>";
	$("#srrprint").html(txtHTML);
	$("#srrprint").dialog({title: "PRINT >> Materials Requests", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showSTR() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='str.list.php'></iframe>";
	$("#strlist").html(txtHTML);
	$("#strlist").dialog({title: "Stocks Transfer Receipt Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewSTR(str_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='str.details.php?str_no="+str_no+"'></iframe>";
	$("#strdetails").html(txtHTML);
	$("#strdetails").dialog({title: "Stocks Transfer Receipt Details", width: xWidth, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printSTR(str_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/str.print.php?str_no="+str_no+"&uid="+uid+"&rePrint="+rePrint+"&sid="+Math.random()+"'></iframe>";
	$("#srrprint").html(txtHTML);
	$("#srrprint").dialog({title: "PRINT >> STOCKS TRANSFER RECEIPT", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showAdj() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='adj.list.php'></iframe>";
	$("#adjlist").html(txtHTML);
	$("#adjlist").dialog({title: "Inventory Adjustments Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewAdj(doc_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='adj.details.php?doc_no="+doc_no+"'></iframe>";
	$("#strdetails").html(txtHTML);
	$("#strdetails").dialog({title: "Inventory Adjustment Form", width: xWidth, height: 540, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showIBook() {
	$("#ibook_dtf").datepicker(); $("#ibook_dt2").datepicker(); 
	$("#inventorybook").dialog({title: "Inventory Summary", width: 480 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function processInventory() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='ibook.php?group="+$("#ibook_group").val()+"&dtf="+$("#ibook_dtf").val()+"&dt2="+$("#ibook_dt2").val()+"'></iframe>";
	$("#ibook").html(txtHTML);
	$("#ibook").dialog({title: "Inventory Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function jumpIBookPage(page,stxt,group,dtf,dt2) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='ibook.php?page="+page+"&searchtext="+stxt+"&group="+group+"&dtf="+dtf+"&dt2="+dt2+"'></iframe>";
	$("#ibook").html(txtHTML);
	$("#ibook").dialog({title: "Inventory Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewStockcard(item_code,lot_no,expiry,dtf,dt2) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='stockcard.php?item_code="+item_code+"&lot_no="+lot_no+"&expiry="+expiry+"&dtf="+dtf+"&dt2="+dt2+"'></iframe>";
	$("#stockcard").html(txtHTML);
	$("#stockcard").dialog({title: "Inventory Stockcard", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function exportStockcard(item_code,unit,dtf,dt2) {
	window.open("export/stockard.php?item_code="+item_code+"&unit="+unit+"&dtf="+dtf+"&dt2="+dt2+"&sid="+Math.random()+"","Inventory Stockcard","location=1,status=1,scrollbars=1,width=640,height=720");
}

function exportInventoryNow() {
	window.open("export/ibook.php?group="+$("#ibook_group").val()+"&dtf="+$("#ibook_dtf").val()+"&dt2="+$("#ibook_dt2").val()+"&sid="+Math.random()+"","Inventory Book","location=1,status=1,scrollbars=1,width=640,height=720");
}

function showBranches() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='branch.master.php'></iframe>";
	$("#customerlist").html(txtHTML);
	$("#customerlist").dialog({title: "Branch List", width: 1024, height: 530,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewBranch(code) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='branch.details.php?code="+code+"'></iframe>";
	$("#customerdetails").html(txtHTML);
	$("#customerdetails").dialog({title: "Branch Details", width: 480, height: 460,resizable: false, modal: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

/* REPORTING */
function showJSched() {
	$("#js_dtf").datepicker(); $("#js_dt2").datepicker();
	$("#jsched").dialog({title: "Journal Schedule", width: 400, height: 290 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateSchedule() {
	if(isNaN($("#js_acct").val()) == true) {
		sendErrorMessage("Invalid account code specified...");
	} else {
		if($("#js_conso").prop("checked")) { var conso = "Y"; } else { var conso = "N"; }
		if($("#js_rtype").val() == "1") {
			window.open("reports/journalschedule-html.php?type="+$("#js_type").val()+"&dtf="+$("#js_dtf").val()+"&dt2="+$("#js_dt2").val()+"&acct="+$("#js_acct").val()+"&sid="+Math.random()+"","Journal Schedule","location=1,status=1,scrollbars=1,width=640,height=720");
		} else {
			window.open("reports/journalschedule-s.php?type="+$("#js_type").val()+"&dtf="+$("#js_dtf").val()+"&dt2="+$("#js_dt2").val()+"&acct="+$("#js_acct").val()+"&conso="+conso+"&sid="+Math.random()+"","Journal Schedule","location=1,status=1,scrollbars=1,width=640,height=720");	
		}
	}
}

function generateScheduleXLS() {
	if(isNaN($("#js_acct").val()) == true) {
		sendErrorMessage("Invalid account code specified...");
	} else {
		if($("#js_conso").prop("checked")) { var conso = "Y"; } else { var conso = "N"; }
		if($("#js_rtype").val() == "1") {
			window.open("export/journalschedule.php?type="+$("#js_type").val()+"&dtf="+$("#js_dtf").val()+"&dt2="+$("#js_dt2").val()+"&acct="+$("#js_acct").val()+"&conso="+conso+"&sid="+Math.random()+"","Journal Schedule","location=1,status=1,scrollbars=1,width=640,height=720");
		} else {
			window.open("export/journalschedule-s.php?type="+$("#js_type").val()+"&dtf="+$("#js_dtf").val()+"&dt2="+$("#js_dt2").val()+"&acct="+$("#js_acct").val()+"&conso="+conso+"&sid="+Math.random()+"","Journal Schedule","location=1,status=1,scrollbars=1,width=640,height=720");	
		}
	}
}

function showTrialDiv() {
	$("#tb_asof").datepicker();
	$("#tbalance").dialog({title: "Trial Balance", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateTB() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/trialbalance.php?asof="+$("#tb_asof").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report7").html(txtHTML);
	$("#report7").dialog({title: "Cummulative Trial Balance", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function generateTBXLS() {
	if($("#tb_conso").prop("checked")) { var conso = "Y"; } else { var conso = "N"; }
	window.open("export/trialbalance.php?asof="+$("#tb_asof").val()+"&sid="+Math.random()+"","Trial Balance","location=1,status=1,scrollbars=1,width=640,height=720");
}

function showMoTbal() {
	$("#moTBalance").dialog({title: "Trial Balance of Mo. Transactions", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateMOTB() {
	$.post("src/sjerp.php", { mod: "checkLockStatus", month: $("#motb_month").val(), year: $("#motb_year").val(), sid: Math.random() }, function(ret) {
		if(ret == "NotOK") {
			window.open("reports/motb.php?month="+$("#motb_month").val()+"&year="+$("#motb_year").val()+"&branch="+$("#motb_branch").val()+"&sid="+Math.random()+"","Trial Balance of Mo. Transactions","location=1,status=1,scrollbars=1,width=640,height=720");
		} else {
			$("#message").html("It appears that the period you have selected has yet to be closed. Should you wish to generate interim Trial Balance of Transactions, you may click <b>\"Proceed Anyway\"</b>. Please take note that interim Trial Balance may take longer to generate.");
			$("#errorMessage").dialog({
				width: 400,
				resizable: false,
				modal: true,
				buttons: {
					"Proceed Anyway": function() {
						var dtf = $("#motb_month").val()+"/01/"+$("#motb_year").val();
						var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/motrialbalance.php?dtf="+dtf+"&sid="+Math.random()+"'></iframe>";
						$("#report9").html(txtHTML);
						$("#report9").dialog({title: "Trial Balance of Monthly Transactions", width: 560, height: 620, resizable: true }).dialogExtend({
							"closable" : true,
							"maximizable" : true,
							"minimizable" : true
						});
					},
					"Cancel": function () { $(this).dialog("close"); }
				}
			});
		}
	},"html");
}

function showGLSched() {
	$("#gls_dtf").datepicker(); $("#gls_dt2").datepicker();
	$("#glsched").dialog({title: "GL Account Schedule", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateGL(tag) {
	if(isNaN($("#gls_acct").val()) == true) {
		sendErrorMessage("Invalid account code specified...");
	} else {
		if(tag == 1) {
			var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/glschedule.php?dtf="+$("#gls_dtf").val()+"&dt2="+$("#gls_dt2").val()+"&acct="+$("#gls_acct").val()+"&client="+$("#gls_client").val()+"&sid="+Math.random()+"'></iframe>";
			$("#report8").html(txtHTML);
			$("#report8").dialog({title: "General Ledger Account Schedule", width: xWidth, height: 620, resizable: true }).dialogExtend({
				"closable" : true,
				"maximizable" : true,
				"minimizable" : true
			});
		
		} else {
			window.open("export/glschedule.php?dtf="+$("#gls_dtf").val()+"&dt2="+$("#gls_dt2").val()+"&acct="+$("#gls_acct").val()+"&sid="+Math.random()+"","Trial Balance","location=1,status=1,scrollbars=1,width=640,height=720");
		}
	}
}

function showCashFlow() {
	$("#cf_dtf").datepicker(); $("#cf_dt2").datepicker();
	$("#cashflow").dialog({title: "Cash Position Report", width: 400, height: 210 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateCashFlow() {
	var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/cashposition.php?dtf="+$("#cf_dtf").val()+"&dt2="+$("#cf_dt2").val()+"&source="+$("#cf_source").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report7").html(txtHTML);
	$("#report7").dialog({title: "Cash Position Report", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function showVAR() {
	$("#var").dialog({title: "Variance Analysis Report", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateVAR() {
	var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/var.php?type="+$("#budType").val()+"&year="+$("#budYear").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report7").html(txtHTML);
	$("#report7").dialog({title: "Variance Analysis Report", width: xWidth, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function showBST() {
	$("#bst").dialog({title: "Budgets & Sales Targets", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function getBST() {
	var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/bst.php?type="+$("#bstType").val()+"&year="+$("#bstYear").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report8").html(txtHTML);
	$("#report8").dialog({title: "Budgets & Sales Targets", width: 640, height: 520, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function showDSR() {
	$("#dsr_dtf").datepicker(); $("#dsr_dt2").datepicker();
	$("#dsr").dialog({
		title: "Detailed Sales & Collection Report", 
		width: 480, 
		buttons: [
			{
				text: "Generate Report in PDF",
				icons: { primary: "ui-icon-print" },
				click: function() {	
					var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/dsr.php?dtf="+$("#dsr_dtf").val()+"&dt2="+$("#dsr_dt2").val()+"&customer="+$("#dsr_customer").val()+"&code="+$("#dsr_icode").val()+"&desc="+$("#dsr_idesc").val()+"&uid="+$("#dsr_uid").val()+"&sid="+Math.random()+"'></iframe>";
					$("#report3").html(txtHTML);
					$("#report3").dialog({title: "Detailed Sales Report", width: 640, height: 520, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});
				}
			},
			{
				text: "Export Report to Excel",
				icons: { primary: "ui-icon-folder-open" },
				click: function() {	
					window.open("export/dsr.php?dtf="+$("#dsr_dtf").val()+"&dt2="+$("#dsr_dt2").val()+"&code="+$("#dsr_icode").val()+"&desc="+$("#dsr_idesc").val()+"&customer="+$("#dsr_customer").val()+"&uid="+$("#dsr_uid").val()+"&sid="+Math.random()+"","Detailed Sales Report","location=1,status=1,scrollbars=1,width=640,height=720");
				}
			}
		]
	});
}

function showCensusReport() {
	$("#dcr_dtf").datepicker(); $("#dcr_dt2").datepicker();
	$("#dcr").dialog({
		title: "Detailed Daily Census Report", 
		width: 480, 
		buttons: [
			{
				text: "Generate Report in PDF",
				icons: { primary: "ui-icon-print" },
				click: function() {	
					var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/dcr.php?dtf="+$("#dcr_dtf").val()+"&dt2="+$("#dcr_dt2").val()+"&customer="+$("#dcr_customer").val()+"&uid="+$("#dcr_uid").val()+"&sid="+Math.random()+"'></iframe>";
					$("#report3").html(txtHTML);
					$("#report3").dialog({title: "Detailed Daily Census Report", width: 640, height: 520, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});
				}
			}
			// {
			// 	text: "Export Report to Excel",
			// 	icons: { primary: "ui-icon-folder-open" },
			// 	click: function() {	
			// 		window.open("export/dcr.php?dtf="+$("#dcr_dtf").val()+"&dt2="+$("#dcr_dt2").val()+"&customer="+$("#dcr_customer").val()+"&uid="+$("#dcr_uid").val()+"&sid="+Math.random()+"","Detailed Sales Report","location=1,status=1,scrollbars=1,width=640,height=720");
			// 	}
			// }
		]
	});
}

function showAPESummary() {
	$("#ape_dtf").datepicker(); $("#ape_dt2").datepicker();
	$("#apeSummary").dialog({
		title: "Detailed Daily Census Report", 
		width: 480, 
		buttons: [
			{
				text: "Generate Report in Excel",
				icons: { primary: "ui-icon-folder-open" },
				click: function() {	
					window.open("export/apesummary.php?dtf="+$("#ape_dtf").val()+"&dt2="+$("#ape_dt2").val()+"&customer="+$("#ape_customer").val()+"&referredby="+$("#ape_referredby").val()+"&sid="+Math.random()+"","APE Summary Report","location=1,status=1,scrollbars=1,width=640,height=720");
				}
			}
		]
	});
}

function showCashReceipts() {
	$("#crs_dtf").datepicker(); $("#crs_dt2").datepicker();
	$("#cashreceipts").dialog({
		title: "Cash Receipts Summary", 
		width: 480, 
		buttons: [
			{
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
			},
			{
				text: "Export Report to Excel",
				icons: { primary: "ui-icon-folder-open" },
				click: function() {	
					window.open("export/cashreceiptssummary.php?dtf="+$("#crs_dtf").val()+"&dt2="+$("#crs_dt2").val()+"&customer="+$("#crs_customer").val()+"&uid="+$("#crs_uid").val()+"&sid="+Math.random()+"","Cash Receipts Summary","location=1,status=1,scrollbars=1,width=640,height=720");
				}
			}
		]
	});
}

/* Search Supplier on Reports */
$(document).ready(function($){
    $('#po_sname').autocomplete({
		source:'suggestSupplier.php', 
		minLength:3,
	});
});

function showPurchases() {
	$("#po_dtf").datepicker(); $("#po_dt2").datepicker();
	$("#purchases").dialog({title: "Summary of Purchases", width: 400, height: 245 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generatePurchases() {
	if($("#po_sname").val() != "") {
		var str = $("#po_sname").val();
		var supplier = str.substr(1,6);
	} else { var supplier = ""; }
	
	
	var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/purchases.php?dtf="+$("#po_dtf").val()+"&dt2="+$("#po_dt2").val()+"&type="+$("#po_type").val()+"&supplier="+supplier+"&sid="+Math.random()+"'></iframe>";
	$("#report9").html(txtHTML);
	$("#report9").dialog({title: "Summary of Purchases", width: 800, height: 540, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
	//window.open("reports/purchases.php?dtf="+$("#po_dtf").val()+"&dt2="+$("#po_dt2").val()+"&type="+$("#po_type").val()+"&supplier="+supplier+"&sid="+Math.random()+"","Trial Balance","location=1,status=1,scrollbars=1,width=640,height=720");
}

function generatePurchasesX() {
	if($("#po_sname").val() != "") {
		var str = $("#po_sname").val();
		var supplier = str.substr(1,6);
	} else { var supplier = ""; }
	
	
	//var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/purchases.php?dtf="+$("#po_dtf").val()+"&dt2="+$("#po_dt2").val()+"&type="+$("#po_type").val()+"&supplier="+supplier+"&sid="+Math.random()+"'></iframe>";

	window.open("export/purchase.php?dtf="+$("#po_dtf").val()+"&dt2="+$("#po_dt2").val()+"&type="+$("#po_type").val()+"&supplier="+supplier+"&sid="+Math.random()+"","Summary of Purchases","location=1,status=1,scrollbars=1,width=640,height=720");
}

function showSumExpenses() {
	$("#exs_dtf").datepicker(); $("#exs_dt2").datepicker();
	$("#expsum").dialog({title: "Summary of Purchases & Expenditures", width: 450 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateSummaryExpenses() {
	window.open("export/expenditures.php?dtf="+$("#exs_dtf").val()+"&dt2="+$("#exs_dt2").val()+"&sid="+Math.random()+"","Trial Balance","location=1,status=1,scrollbars=1,width=640,height=720");
}


function showChecks() {
	$("#ic_dtf").datepicker(); $("#ic_dt2").datepicker();
	$("#checks").dialog({title: "Summary of Issued Checks", width: 400, height: 210 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateChecks() {
	var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/checkssummary.php?dtf="+$("#ic_dtf").val()+"&dt2="+$("#ic_dt2").val()+"&source="+$("#ic_source").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report6").html(txtHTML);
	$("#report6").dialog({title: "Summary of Issued Checks", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
	//window.open("reports/checkssummary.php?dtf="+$("#ic_dtf").val()+"&dt2="+$("#ic_dt2").val()+"&source="+$("#ic_source").val()+"&sid="+Math.random()+"","Trial Balance","location=1,status=1,scrollbars=1,width=640,height=720");
}

function generateChecksXLS() {
	window.open("export/checkssummary.php?dtf="+$("#ic_dtf").val()+"&dt2="+$("#ic_dt2").val()+"&source="+$("#ic_source").val()+"&sid="+Math.random()+"","Trial Balance","location=1,status=1,scrollbars=1,width=640,height=720");
}

function showAcctgReports() {
	$("#acctgReportMain").dialog({title: "Accounting & Financial Reports", width: 1080 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showCAB() {
	$("#cab_asof").datepicker();
	$("#accountbalance").dialog({title: "Statement of Account", width: 400, height: 245 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateSOA() {
	var msg = "";
	if($("#cab_sname").val() == '') { 
		msg = msg + "- You have specified an invalid Customer<br/>"; 
	} else {
		var str = $("#cab_sname").val();
		var cid = str.substr(1,6);
		$.post("src/sjerp.php", { "mod": "verifyCID", cid: cid, sid: Math.random() }, function(res) { if(res != "Ok") { msg = msg + "- You have specified an invalid Customer<br/>";  }},"html");
	}

	if(msg == "") {
		if($("#with_soa_num").prop("checked")) { var with_soa_num = "Y"; } else { var with_soa_num = "N"; }
		if($("#overdue_only").prop("checked")) { var overdue_only = "Y"; } else { var overdue_only = "N"; }
		var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/soa.php?asof="+$("#cab_asof").val()+"&cid="+cid+"&with_soa_num="+with_soa_num+"&overdue_only="+overdue_only+"&sid="+Math.random()+"'></iframe>";
		$("#report8").html(txtHTML);
		$("#report8").dialog({title: "Statement of Accouont", width: 560, height: 620, resizable: true }).dialogExtend({
			"closable" : true,
			"maximizable" : true,
			"minimizable" : true
		});
	} else { sendErrorMessage(msg); }
	
}

function showOutInvoices() {
	$("#coi_asof").datepicker();
	$.post("src/sjerp.php", { mod: "getHomeowners", sid: Math.random() }, function(ret) { $("#coi_cust").html(ret); },"html");
	$("#outstandingInvoices").dialog({title: "Unpaid Billing Statements", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateOutstanding() {
	if($("#coi_isoverdue").prop("checked")) { var od = "Y"; } else { var od = "N"; }
	var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/outstanding.php?asof="+$("#coi_asof").val()+"&cust="+$("#coi_cust").val()+"&od="+od+"&sid="+Math.random()+"'></iframe>";
	$("#report8").html(txtHTML);
	$("#report8").dialog({title: "Unpaid Billing Statements", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function showAPPayable() {
	$("#oap_asof").datepicker();
	$.post("src/sjerp.php", { mod: "getSuppliers", sid: Math.random() }, function(ret) { $("#oap_payee").html(ret); },"html");
	$("#overdueAP").dialog({title: "Outstanding Accounts Payable Voucher", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateOAP() {
	if($("#oap_isoverdue").prop("checked")) { var od = "Y"; } else { var od = "N"; }
	var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/apoutstanding.php?asof="+$("#coi_asof").val()+"&cust="+$("#oap_payee").val()+"&od="+od+"&sid="+Math.random()+"'></iframe>";
	$("#report6").html(txtHTML);
	$("#report6").dialog({title: "Outstanding Accounts Payable Voucher", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function showInVat() {
	$("#ivat_dtf").datepicker(); $("#ivat_dt2").datepicker();
	$.post("src/sjerp.php", { mod: "getSuppliers", sid: Math.random() }, function(ret) { $("#ivat_payee").html(ret); },"html");
	$("#inVatSummary").dialog({title: "Summary of Purchases", width: 400, modal: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateInVatSummaryPDF() {
	var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/invatSummary.php?dtf="+$("#ivat_dtf").val()+"&dt2="+$("#ivat_dt2").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report3").html(txtHTML);
	$("#report3").dialog({title: "Summary of Purchases", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function generateInVatSummaryXLS() {
	window.open("export/invatSummary.php?dtf="+$("#ivat_dtf").val()+"&dt2="+$("#ivat_dt2").val()+"&sid="+Math.random()+"","Summary of Vatable Purchases","location=1,status=1,scrollbars=1,width=640,height=720");
}

function showSubLedger() {
	$("#subledger_asof").datepicker();
	$("#subledger").dialog({title: "Account Balance", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateSubLedger() {
	var msg = "";
	if($("#subledger_sid").val() == '') { 
		msg = msg + "- You have specified an invalid Customer or Supplier<br/>"; 
	} 
	if($("#subledger_acct").val() == '') { 
		msg = msg + "- Subsidiary Account must be identified before trying to generate this report<br/>"; 
	} else {
		$.post("src/sjerp.php", { "mod": "verifyACCT", acct: $("#subledger_acct").val(), sid: Math.random() }, function(res) { if(res == "NotFound") { msg = msg + "- You have specified an invalid Account Code<br/>";  }},"html");
	}
	
	
	if(msg == "") {
		var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/accountbalance.php?asof="+$("#subledger_asof").val()+"&acct="+$("#subledger_acct").val()+"&cid="+$("#subledger_sid").val()+"&sid="+Math.random()+"'></iframe>";
		$("#report9").html(txtHTML);
		$("#report9").dialog({title: "Account Balance", width: 560, height: 620, resizable: true }).dialogExtend({
			"closable" : true,
			"maximizable" : true,
			"minimizable" : true
		});
	} else { sendErrorMessage(msg); }
	
}

function showARAS() {
	$("#aras_asof").datepicker();
	$("#aras").dialog({title: "AR - Aging Schedule", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateARAS() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/aras.php?asof="+$("#aras_asof").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report2").html(txtHTML);
	$("#report2").dialog({title: "AR - Aging Schedule", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function showAPAS() {
	$("#apas_asof").datepicker();
	$("#apas").dialog({title: "AP - Aging Schedule", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateAPAS() {
	if($("#apas_conso").prop("checked")) { var conso = "Y"; } else { var conso = "N"; }
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/apas.php?asof="+$("#apas_asof").val()+"&sid="+Math.random()+"&conso="+conso+"'></iframe>";
	$("#report3").html(txtHTML);
	$("#report3").dialog({title: "AP - Aging Schedule", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function showIS() {
	$("#incomestatement").dialog({title: "Profit & Loss Statement", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateIS() {
	$.post("src/sjerp.php", { mod: "checkLockStatus", month: $("#is_month").val(), year: $("#is_year").val(), sid: Math.random() }, function(ret) {
		if(ret == "NotOK") {
			var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/mois-pdf.php?month="+$("#is_month").val()+"&cc="+$("#is_cc").val()+"&year="+$("#is_year").val()+"&sid="+Math.random()+"'></iframe>";
			$("#report1").html(txtHTML);
			$("#report1").dialog({title: "PROFIT & LOSS STATEMENT", width: 560, height: 620, resizable: true }).dialogExtend({
				"closable" : true,
				"maximizable" : true,
				"minimizable" : true
			});
		} else {
			$("#message").html("It appears that the period you have selected has yet to be closed. Should you wish to generate interim Trial Balance of Transactions, you may click <b>\"Proceed Anyway\"</b>. Please take note that interim Trial Balance may take longer to generate.");
			$("#errorMessage").dialog({
				width: 400,
				resizable: false,
				modal: true,
				buttons: {
					"Proceed Anyway": function() {
						window.open("reports/incomestatement.php?month="+$("#is_month").val()+"&cc="+$("#is_cc").val()+"&year="+$("#is_year").val()+"&sid="+Math.random()+"","INCOME STATEMENT","location=1,status=1,scrollbars=1,width=640,height=720");
						$(this).dialog("close");
					},
					"Cancel": function () { $(this).dialog("close"); }
				}
			});
		}
	},"html");
}

function generateISEX() {
	window.open("export/incomestatement.php?month="+$("#is_month").val()+"&cc="+$("#is_cc").val()+"&year="+$("#is_year").val()+"&sid="+Math.random()+"","Income Statement - Excel","location=1,status=1,scrollbars=1,width=400,height=240");
}

function showClosing() {
	$("#transLock").dialog({title: "Finalize Monthly Transactions", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function lockTransactions() {
	if($("#lock_year").val() == "" || isNaN($("#lock_year").val() == true)) {
			sendErrorMessage("Please specify a valid year in a form of integer or YYYY format");
		} else {
			$.post("src/sjerp.php", { mod: "checkLockStatus", month: $("#lock_month").val(), year: $("#lock_year").val(), sid: Math.random() }, function(ret) {
			if(ret == "Ok") {
				if(confirm("This action would lock transactions of the specified period. You can no longer make changes nor add transactions unless otherwise the specified period is lifted from the locking database. Do you still wish to continue?") == true) {	
					showLoaderMessage();
					$.post("src/sjerp.php", { mod: "lockStatusOk", branch: $("#lock_branch").val(), month: $("#lock_month").val(), year: $("#lock_year").val(), memo: $("#lock_memo").val(), sid: Math.random() }, function() {
						$("#loaderMessage").dialog("close");
						closeDialog("#transLock");
						$("#lock_year").val('');
						$("#lock_month").val('01');
						$("#lock_memo").val('');
						alert("Transactions for the specified has been successfully finalized and locked!");
					});	
				}
			} else {
				sendErrorMessage("The period you have selected appears to have been Finalized & already locked.");
			}
		},"html");
	}
}

function showUnlock() {
	$("#uLock").dialog({title: "Unlock Transactions", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function unlockTransactions() {
	if($("#ulock_year").val() == "" || isNaN($("#ulock_year").val() == true)) {
			sendErrorMessage("Please specify a valid year in a form of integer or YYYY format");
		} else {
			$.post("src/sjerp.php", { mod: "checkLockStatus", month: $("#ulock_month").val(), year: $("#ulock_year").val(), sid: Math.random() }, function(ret) {
			if(ret != "Ok") {
				if(confirm("This action would unlock transactions of the specified period. Changes made to documents on this period may affect previously posted Financial and other relevant reports. Do you wish to continue?") == true) {	
					$.post("src/sjerp.php", { mod: "unLock", branch: $("#ulock_branch").val(), month: $("#ulock_month").val(), year: $("#ulock_year").val(), sid: Math.random() }, function() {
						alert("Transactions for the specified period was successfully unlocked!");
						closeDialog("#uLock");
						$("#ulock_year").val('');
						$("#ulock_month").val('01');
					});	
				}
			} else {
				sendErrorMessage("The period you have selected seems to have not been locked yet...");
			}
		},"html");
	}
}

function showBS() {
	$("#balanceSheet").dialog({title: "Balance Sheet", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateBalanceSheet(tag) {
	$.post("src/sjerp.php", { mod: "checkLockStatus", month: $("#is_month").val(), year: $("#is_year").val(), sid: Math.random() }, function(ret) {
		if(ret == "NotOK") {
			var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/mobs.php?month="+$("#bs_month").val()+"&year="+$("#bs_year").val()+"&sid="+Math.random()+"'></iframe>";
			$("#report1").html(txtHTML);
			$("#report1").dialog({title: "INCOME STATEMENT", width: 560, height: 620, resizable: true }).dialogExtend({
				"closable" : true,
				"maximizable" : true,
				"minimizable" : true
			});
		} else {
			$("#message").html("It appears that the period you have selected has yet to be closed. Should you wish to generate interim Balance Sheet, you may click <b>\"Proceed Anyway\"</b>. Please take note that interim Balance Sheet may take longer to generate.");
			$("#errorMessage").dialog({
				width: 400,
				resizable: false,
				modal: true,
				buttons: {
					"Proceed Anyway": function() {
						if(tag == 1) {
							window.open("reports/balancesheet.php?month="+$("#bs_month").val()+"&year="+$("#bs_year").val()+"&sid="+Math.random()+"","Balance Sheet","location=1,status=1,scrollbars=1,width=640,height=720");
						} else {
							window.open("export/balancesheet.php?month="+$("#bs_month").val()+"&year="+$("#bs_year").val()+"&sid="+Math.random()+"","Balance Sheet","location=1,status=1,scrollbars=1,width=640,height=720");
						}
						$(this).dialog("close");
					},
					"Cancel": function () { $(this).dialog("close"); }
				}
			});
		}
	},"html");
}

function showColPerf() {
	$("#ColPerf").dialog({title: "Collection Performance", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateColPerf() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/colperf-2.php?year="+$("#colperf_year").val()+"&month="+$("#colperf_month").val()+"&branch="+$("#colperf_branch").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report7").html(txtHTML);
	$("#report7").dialog({title: "COLLECTION PERFORMANCE", width: xWidth, height: 520, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function showSalesPerCat() {
	$("#salescat_dtf").datepicker({ changeMonth: true, changeYear: true }); $("#salescat_dt2").datepicker({ changeMonth: true, changeYear: true });
	$("#salesPerCat").dialog({title: "Sales Report Per Category", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateSalesCat() {
	window.open("export/salescat.php?&category="+$("#salescat_cat").val()+"&dtf="+$("#salescat_dtf").val()+"&dt2="+$("#salescat_dt2").val()+"&sid="+Math.random()+"","Yearly Sales Category","location=1,status=1,scrollbars=1,width=800,height=760");
}

function showExpSched() {
	$("#ExpSched").dialog({title: "Schedule of Expense", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateExpSched(tag) {
	if($("#expsched_year").val() == "") {
		sendErrorMessage("You must indicate the calendar year for this Collection Performance report...");
	} else {
		if(tag == 1) {
			window.open("reports/expsched.php?month="+$("#expsched_month").val()+"&year="+$("#expsched_year").val()+"&sid="+Math.random()+"","Collection Performance","location=1,status=1,scrollbars=1,width=800,height=760");
		} else {
			window.open("export/expsched.php?month="+$("#expsched_month").val()+"&year="+$("#expsched_year").val()+"&sid="+Math.random()+"","Collection Performance","location=1,status=1,scrollbars=1,width=800,height=760");
		}
	}
}

function showSR() {
	$("#salesReportMain").dialog({title: "Sales & Inventory Reports", width: 980, height: 580 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showSalesSummary() {
	$("#ss_dtf").datepicker(); $("#ss_dt2").datepicker();
	$("#salessummary").dialog({title: "Sales Summary Report", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateSalesSummary() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/sr_summary-2.php?dtf="+$("#ss_dtf").val()+"&dt2="+$("#ss_dt2").val()+"&type="+$("#ss_type").val()+"&branch="+$("#ss_branch").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report3").html(txtHTML);
	$("#report3").dialog({title: "Sales Summary Report", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function showCPDC() {
	$("#cpdc_date").datepicker();
	$("#cpdc").dialog({title: "On-Due PDC Checks", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateCPDC() {
	var txtHTML = "<iframe id='frmsosummary' frameborder=0 width='100%' height='100%' src='reports/duepdcs.php?dtf="+$("#cpdc_date").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report1").html(txtHTML);
	$("#report1").dialog({title: "Sales Order Summary", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}


function showInquiry() {
	$("#sinq_date").datepicker();
	$("#salesinquiry").dialog({title: "Daily Sales & Collection Report", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateInquiry() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/dscr.php?date="+$("#sinq_date").val()+"&branch="+$("#sinq_branch").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report3").html(txtHTML);
	$("#report3").dialog({title: "Daily Sales & Collection Report", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function showSalesContrib() {
	$("#scontrib_dtf").datepicker(); $("#scontrib_dt2").datepicker();
	$("#salesContrib").dialog({title: "Sales Contribution Per Product Group", width: 400, height: 210 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateContrib() {
	window.open("reports/salescontrib.php?dtf="+$("#scontrib_dtf").val()+"&dt2="+$("#scontrib_dt2").val()+"&branch="+$("#scontrib_branch").val()+"&sid="+Math.random()+"","Sales Contribution Per Product Group","location=1,status=1,scrollbars=1,width=840,height=720");	
}

function showContribBranch() {
	$("#scontrib2_dtf").datepicker(); $("#scontrib2_dt2").datepicker();
	$("#salesContribBranch").dialog({title: "Sales Contribution Per Branch", width: 400, height: 210 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateContribBranch() {
	window.open("reports/salescontrib2.php?dtf="+$("#scontrib2_dtf").val()+"&dt2="+$("#scontrib2_dt2").val()+"&group="+$("#scontrib2_type").val()+"&sid="+Math.random()+"","Sales Contribution Per Branch","location=1,status=1,scrollbars=1,width=840,height=720");	
}

function showSPerf() {
	$("#salesPerformance").dialog({title: "Sales Performance Per Product", width: 400, height: 210 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateSPerf() {
	window.open("reports/sperf.php?year="+$("#sperf_year").val()+"&group="+$("#sperf_type").val()+"&branch="+$("#sperf_branch").val()+"&sid="+Math.random()+"","Sales Performance Per Product","location=1,status=1,scrollbars=1,width=720,height=640");	
}

function showTopProducts() {
	$("#tp_dtf").datepicker(); $("#tp_dt2").datepicker();
	$("#topproducts").dialog({title: "Top Selling Products", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateTopProducts() {
	if(isNaN($("#tp_ranks").val() || $("#tp_rank").val() == '') == true) {
		sendErrorMessage("Cannot process report. No. of Ranks must be an integer...");
	} else {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/topproducts.php?pg="+$("#tp_group").val()+"&dtf="+$("#tp_dtf").val()+"&dt2="+$("#tp_dt2").val()+"&type="+$("#tp_type").val()+"&ranks="+$("#tp_ranks").val()+"&branch="+$("#tp_branch").val()+"&sid="+Math.random()+"'></iframe>";
		$("#report5").html(txtHTML);
		$("#report5").dialog({title: "Top Products", width: 560, height: 620, resizable: true }).dialogExtend({
			"closable" : true,
			"maximizable" : true,
			"minimizable" : true
		});
	}
}

function showTopBranches() {
	$("#tc_dtf").datepicker(); $("#tc_dt2").datepicker();
	$("#topbranches").dialog({title: "Top Performing Branches", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateTopBranches() {
	window.open("reports/topbranches.php?dtf="+$("#tpb_dtf").val()+"&dt2="+$("#tpb_dt2").val()+"&sid="+Math.random()+"","Top Performing Branches","location=1,status=1,scrollbars=1,width=640,height=720");
}

function showTopCustomers() {
	$("#tc_dtf").datepicker(); $("#tc_dt2").datepicker();
	$("#topcustomers").dialog({title: "Top Customers", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateTopCustomers() {
	if(isNaN($("#tp_ranks").val()) == true) {
		sendErrorMessage("Cannot process report. No. of Ranks must be an integer...");
	} else {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/topcustomers.php?group="+$("#tc_group").val()+"&dtf="+$("#tc_dtf").val()+"&dt2="+$("#tc_dt2").val()+"&ranks="+$("#tc_ranks").val()+"&branch="+$("#tc_branch").val()+"&sid="+Math.random()+"'></iframe>";
		$("#report2").html(txtHTML);
		$("#report2").dialog({title: "Top Customers", width: 560, height: 620, resizable: true }).dialogExtend({
			"closable" : true,
			"maximizable" : true,
			"minimizable" : true
		});
	}
}

function showWeeklySales() {
	$("#weeklySales").dialog({title: "Weekly Sales Report", width: 400, height: 240 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateWS() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/weeklySales.php?month="+$("#ws_month").val()+"&year="+$("#ws_year").val()+"&branch="+$("#ws_branch").val()+"&group="+$("#ws_group").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report4").html(txtHTML);
	$("#report4").dialog({title: "Weekly Sales Performance", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
	
	//window.open("reports/weeklySales.php?month="+$("#ws_month").val()+"&year="+$("#ws_year").val()+"&branch="+$("#ws_branch").val()+"&group="+$("#ws_group").val()+"&sid="+Math.random()+"","Weekly Sales Report","location=1,status=1,scrollbars=1,width=640,height=720");
}


/* Inventory Reports */

function showInventoryReport() {
	$("#inventoryReportMain").dialog({title: "Sales & Inventory Reports", width: 680, height: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showRRS() {
	$("#rrs_dtf").datepicker(); $("#rrs_dt2").datepicker();
	$("#rrsummary").dialog({
		title: "Summary of Goods Received", 
		width: 400,
		buttons: [
			{
				text: "Generate Report in PDF",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var txtHTML = "<iframe id='frmrrsummary' frameborder=0 width='100%' height='100%' src='reports/rr_summary.php?dtf="+$("#rrs_dtf").val()+"&dt2="+$("#rrs_dt2").val()+"&cid="+$("#rrs_cid").val()+"&item="+$("#rrs_item").val()+"&sid="+Math.random()+"'></iframe>";
					$("#report4").html(txtHTML);
					$("#report4").dialog({title: "Summary of Goods Received from Suppliers", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});
				 }
			},
			{
				text: "Export to Excel",
				icons: { primary: "ui-icon-clipboard" },
				click: function() {
					window.open("export/rr_summary.php?dtf="+$("#rrs_dtf").val()+"&dt2="+$("#rrs_dt2").val()+"&cid="+$("#rrs_cid").val()+"&item="+$("#rrs_item").val()+"&sid="+Math.random()+"","Summary of Goods Withdrawn","location=1,status=1,scrollbars=1,width=640,height=720");
				 }
			}

		]
	});
}

function showPOSummary() {
	$("#pos_dtf").datepicker(); $("#pos_dt2").datepicker();
	$("#posummary").dialog({
		title: "Summary of Purchases", 
		width: 400,
		buttons: [
			{
				text: "Generate Report in PDF",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var txtHTML = "<iframe id='frmposummary' frameborder=0 width='100%' height='100%' src='reports/po_summary.php?dtf="+$("#pos_dtf").val()+"&dt2="+$("#pos_dt2").val()+"&cid="+$("#pos_cid").val()+"&status="+$("#pos_status").val()+"&item="+$("#rrs_item").val()+"&sid="+Math.random()+"'></iframe>";
					$("#report4").html(txtHTML);
					$("#report4").dialog({title: "Summary of Purchase Orders", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});
				 }
			}

		]
	});
}

function showSRRS() {
	$("#srrs_dtf").datepicker(); $("#srrs_dt2").datepicker();
	$("#srrsummary").dialog({
		title: "Summary of Goods Received or Returned", 
		width: 400,
		buttons: [
			{
				text: "Generate Report",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var txtHTML = "<iframe id='frmrrsummary' frameborder=0 width='100%' height='100%' src='reports/srr_summary.php?dtf="+$("#rrs_dtf").val()+"&dt2="+$("#rrs_dt2").val()+"&cid="+$("#rrs_cid").val()+"&item="+$("#rrs_item").val()+"&sid="+Math.random()+"'></iframe>";
					$("#report4").html(txtHTML);
					$("#report4").dialog({title: "Summary of Goods Received from Suppliers", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});
				 }
			},
			{
				text: "Export to Excel",
				icons: { primary: "ui-icon-clipboard" },
				click: function() {
					window.open("export/srr_summary.php?dtf="+$("#srrs_dtf").val()+"&dt2="+$("#srrs_dt2").val()+"&item="+$("#srrs_item").val()+"&sid="+Math.random()+"","Summary of Goods Withdrawn","location=1,status=1,scrollbars=1,width=640,height=720");
				 }
			}


		]
	});
}


function showFASummary() {
	$("#fa_dtf").datepicker({changeMonth: true, changeYear: true}); $("#fa_dt2").datepicker({changeMonth: true, changeYear: true});
	$("#fasummary").dialog({title: "Inventory Summary", width: 480 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function exportFASUmmary () {
	window.open("export/fasummary.php?category="+$("#fa_category").val()+"&dtf="+$("#fa_dtf").val()+"&dt2="+$("#fa_dt2").val()+"&costcenter="+$("#fa_costcenter").val()+"&sid="+Math.random()+"","FA SUMMARY","location=1,status=1,scrollbars=1,width=640,height=720");
}

/* Sales Reports */

function showDetailedSales() {
	$("#dsa_dtf").datepicker(); $("#dsa_dt2").datepicker();
	$("#detailedSales").dialog({title: "Detailed Sales Report", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateDetailedSales() {
	var txtHTML = "<iframe id='frmdetailedsales' frameborder=0 width='100%' height='100%' src='reports/sr_detailed-2.php?dtf="+$("#dsa_dtf").val()+"&dt2="+$("#dsa_dt2").val()+"&cid="+$("#dsa_cid").val()+"&branch="+$("#dsa_branch").val()+"&group="+$("#dsa_group").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report4").html(txtHTML);
	$("#report4").dialog({title: "Customer Detailed Sales", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function showSOS() {
	$("#sos_dtf").datepicker(); $("#sos_dt2").datepicker();
	$("#sosummary").dialog({title: "Sales Order Summary", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateSOSummary() {
	var txtHTML = "<iframe id='frmsosummary' frameborder=0 width='100%' height='100%' src='reports/so_summary.php?dtf="+$("#sos_dtf").val()+"&dt2="+$("#sos_dt2").val()+"&cid="+$("#sos_cid").val()+"&srep="+$("#sos_srep").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report1").html(txtHTML);
	$("#report1").dialog({title: "Sales Order Summary", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
	//window.open("reports/so_summary.php?dtf="+$("#sos_dtf").val()+"&dt2="+$("#sos_dt2").val()+"&cid="+$("#sos_cid").val()+"&sid="+Math.random()+"","Sales Order Summary","location=1,status=1,scrollbars=1,width=640,height=720");
}

function showSPS() {
	$("#sps_dtf").datepicker(); $("#sps_dt2").datepicker();
	$("#sperman").dialog({title: "Sales Report per Salesman", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateSPS() {
	var txtHTML = "<iframe id='frmsosummary' frameborder=0 width='100%' height='100%' src='reports/sperman.php?dtf="+$("#sps_dtf").val()+"&dt2="+$("#sps_dt2").val()+"&srep="+$("#sps_srep").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report4").html(txtHTML);
	$("#report4").dialog({title: "Sales Report per Salesman", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
	//window.open("reports/so_summary.php?dtf="+$("#sos_dtf").val()+"&dt2="+$("#sos_dt2").val()+"&cid="+$("#sos_cid").val()+"&sid="+Math.random()+"","Sales Order Summary","location=1,status=1,scrollbars=1,width=640,height=720");
}


function showSGW() {
	$("#sgw_dtf").datepicker(); $("#sgw_dt2").datepicker();
	$("#sgwsummary").dialog({
		title: "Summary of Goods Withdrawn", 
		width: 400,
		buttons: [
			{
				text: "Generate Report",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var txtHTML = "<iframe id='frmsgw' frameborder=0 width='100%' height='100%' src='reports/sw_summary.php?dtf="+$("#sgw_dtf").val()+"&dt2="+$("#sgw_dt2").val()+"&cc="+$("#sgw_costcenter").val()+"&ppp="+$("#sgw_ppp").val()+"&clinic="+$("#sgw_clinic").val()+"&type="+$("#sgw_type").val()+"&item="+$("#sgw_item").val()+"&sid="+Math.random()+"'></iframe>";
					$("#report5").html(txtHTML);
					$("#report5").dialog({title: "Summary of Goods Withdrawn", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});
				 }
			},
			{
				text: "Export Report to Excel",
				icons: { primary: "	ui-icon-extlink" },
				click: function() {
					//var txtHTML = "<iframe id='frmrrsummary' frameborder=0 width='100%' height='100%' src='reports/rr_summary.php?dtf="+$("#rrs_dtf").val()+"&dt2="+$("#rrs_dt2").val()+"&cid="+$("#rrs_cid").val()+"&item="+$("#rrs_item").val()+"&sid="+Math.random()+"'></iframe>";
					window.open("export/sw_summary.php?dtf="+$("#sgw_dtf").val()+"&dt2="+$("#sgw_dt2").val()+"&cc="+$("#sgw_costcenter").val()+"&ppp="+$("#sgw_ppp").val()+"&clinic="+$("#sgw_clinic").val()+"&type="+$("#sgw_type").val()+"&item="+$("#sgw_item").val()+"&sid="+Math.random()+"","Summary of Goods Withdrawn","location=1,status=1,scrollbars=1,width=640,height=720");
				 }
			}
		] 
	});
}

function generateDailySales() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='reports/dailysales.php?date="+$("#ds_date").val()+"&group="+$("#ds_group").val()+"&branch="+$("#ds_branch").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report1").html(txtHTML);
	$("#report1").dialog({title: "Daily Sales Report", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function showColReport() {
	$("#colrep_dtf").datepicker(); $("#colrep_dt2").datepicker();
	$("#collection").dialog({title: "Collection Report", width: 400, height: 245 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateCollectionReport() {
	if($("#colrep_sname").val() != "") {
		var str = $("#colrep_sname").val();
		var cid = str.substr(1,6);
	} else { cid = ""; }
	
	if($("#colrep_type").val() == 1) {
		window.open("reports/collectionreport-d.php?dtf="+$("#colrep_dtf").val()+"&dt2="+$("#colrep_dt2").val()+"&cid="+cid+"&sid="+Math.random()+"","Collection Report Detailed","location=1,status=1,scrollbars=1,width=640,height=720");
	} else {
		window.open("reports/collectionreport-s.php?dtf="+$("#colrep_dtf").val()+"&dt2="+$("#colrep_dt2").val()+"&cid="+cid+"&sid="+Math.random()+"","Collection Report Summary","location=1,status=1,scrollbars=1,width=640,height=720");
	}
}

function backupDBase() {
	window.open("dbBackup.php","Backup Database","location=1,status=1,scrollbars=1,width=300,height=180");
}

function closeDialog(frame) {
	$(frame).dialog("close");
}

	function open_overlay(){	
		$("#mainLoading").css("z-index","999");
		$("#mainLoading").show();
	}
	
	function close_overlay(){	
		$("#mainLoading").hide();
	}
	
	function showUnclass(){
		$("#unclass_dtf").datepicker(); $("#unclass_dt2").datepicker();
		$("#unclass").dialog({title: "Unclassified Accounts", width: 400 }).dialogExtend({
			"closable" : true,
		    "maximizable" : false,
		    "minimizable" : true
		});
	}
	
	function genUnclassified(){
		window.open("print/unclassified.php?dtf="+$("#unclass_dtf").val()+"&dt2="+$("#unclass_dt2").val()+"&doc_type="+$("#unclass_doc").val()+"&sid="+Math.random()+"","Unclassified Accounts","location=1,status=1,scrollbars=1,width=640,height=720");
	
	}
	
	function showIssuedDoc(){
		$("#listofdoc_dtf").datepicker(); $("#listofdoc_dt2").datepicker();
		$("#listofdoc").dialog({title: "Issued Documents", width: 400 }).dialogExtend({
			"closable" : true,
		    "maximizable" : false,
		    "minimizable" : true
		});
	}
	
	function genDocList(){
		window.open("print/issued_doc.php?dtf="+$("#listofdoc_dtf").val()+"&dt2="+$("#listofdoc_dt2").val()+"&doc_type="+$("#listofdoc_type").val()+"&status="+$("#listofdoc_status").val()+"","Unclassified Accounts","location=1,status=1,scrollbars=1,width=640,height=720");
	
	}
	
	function showBankRecon(){
		$('#clearedbalance').val('0.00');
		$('#diffbalance').val('0.00');
		$('#balanceend').val('0.00');
		$('#balend').val('0.00');
		$('#balopen').val('0.00');
		$('#debits').val('0');
		$('#credits').val('0');
		$('#acct_code').val('');
		$("#cr_transaction").html("");
		$("#db_transaction").html("");
		$("#bankrecondiv").dialog({title: "Bank Reconciliation", width: xWidth }).dialogExtend({
			"closable" : true,
		    "maximizable" : false,
		    "minimizable" : true
		});
	}
	
	function bank_getData() {
		var xdt = $("#tmp_date").val();
		var acct = $("#acct_code").val();
		if(xdt!="" && acct!="") {
			$.post("bankrecon/acctg.bankrecon_getdebit.php", { xdate: xdt, acct_code:acct, sid: Math.random() }, function (data) {
				$("#db_transaction").html(data);
			}, "html");
			$.post("bankrecon/acctg.bankrecon_getcredit.php", { xdate: xdt, acct_code:acct, sid: Math.random() }, function (data) {
				$("#cr_transaction").html(data);
			}, "html");
			$.post("bankrecon/acctg.bankrecon_getbalances.php", { xdate: xdt, acct_code:acct, sid: Math.random() }, function (data) {
				$("#balanceopen").val(data.balance_beginning);
				$("#clearedbalance").val(data.clearedbalance);
				$("#debits").val(data.d_cleared);
				$("#credits").val(data.c_cleared);
				
				var end = $("#balanceend").val()
					end = end.replace(/,/g,"");
				var cleared = data.abscbalance;
					cleared = cleared.replace(/,/g,"");

				var diff = parseFloat(end) + parseFloat(cleared);
				$("#diffbalance").val(kSeparator(diff.toFixed(2)));
			}, "json");
		}
	}
	
	function toggle_me(el,val) {
		var xdt = $("#tmp_date").val();
		var acct = $("#acct_code").val();
		var obj = document.getElementById(el);
		if(obj.checked == true) { var push = "Y"; } else { var push = "N"; }
		$.post("bankrecon/acctg.queforclearing.php", { push: ""+push+"", xval: ""+val+"", xdate: ""+xdt+"", acct_code: ""+acct+"", sid:""+Math.random()+""}, function(data) {
			$("#clearedbalance").val(kSeparator(data.cbalance));
			$("#debits").val(data.d_cleared);
			$("#credits").val(data.c_cleared);
			var end = $("#balend").val()
				end = parseFloat(end.replace(/,/g,""));
			var diffBalance = end - parseFloat(data.abscbalance);				
			$("#diffbalance").val(kSeparator(diffBalance.toFixed(2)));
		},"json");
	}
	
	function clear_selected() {
		if($("#acct_code").val() == "") {
			alert("Error: Unable to continue. No data to reconcile.")
		} else {
			var diff = $("#diffbalance").val();
		    diff = parseFloat(diff.replace(/,/g,""))
			if(confirm("Are you sure you want to finalized this bank recon process?") == true) {
				if(diff > 0 || diff < 0) {
					alert("Error: Cannot continue... Difference Balance should be zero (0) upon finalizing Bank Recon");
				} else {
					$.post("bankrecon/acctg.bankrecon_clearnow.php", { acct_code: $("#acct_code").val(), date: $("#tmp_date").val(), balOpen: $("#balanceopen").val(), balend: $("#balend").val(), sid: Math.random() }, function(traceNo) {
						
					});
				}
			}
		}	
	}
	
	function check_all() {
	   if(confirm("Are you sure you want to check all entries on this form?") == true) {
			var acct = $("#acct_code").val();
			if(acct != "") {
				$.post("bankrecon/acctg.bankrecon_checkall.php", { acct_code: ""+$("#acct_code").val(), date: ""+$("#tmp_date").val(), sid: ""+Math.random()+"" }, function(data) {
					/* $('input[type=checkbox]').attr('checked',true);
					$("#clearedbalance").val(addCommas(data['cbalance']));
					$("#debits").val(data['d_cleared']);
					$("#credits").val(data['c_cleared']);
					var end = $("#balend").val()
					if(end != '') {	end = parseFloat(end.replace(/,/g,"")) } else { end = 0; }
					var diffBalance = end - parseFloat(data['abscbalance']);				
					$("#diffbalance").val(kSeparator(diffBalance.toFixed(2)));
					*/
					
					bank_getData();
				},"json");
			} else {
				alert("Error: Invalid Bank Account to reconcile.");
			}
	   }
	}
	
	function uncheck_all() {
	   if(confirm("Are you sure you want to uncheck all entries on this form?") == true) {
			var acct = $("#acct_code").val();
			if(acct != "") {
				$.post("bankrecon/acctg.bankrecon_uncheckall.php", { acct_code: ""+$("#acct_code").val(), date: ""+$("#tmp_date").val(), sid: ""+Math.random()+"" }, function(data) {
					$('input[type=checkbox]').attr('checked',false);
					$("#clearedbalance").val(addCommas(data['cbalance']));
					$("#debits").val(data['d_cleared']);
					$("#credits").val(data['c_cleared']);
					var end = $("#balend").val()
					if(end != '') {	end = parseFloat(end.replace(/,/g,"")) } else { end = 0; }
					var diffBalance = end - parseFloat(data['abscbalance']);				
					$("#diffbalance").val(kSeparator(diffBalance.toFixed(2)));
					
				},"json");
			} else {
				alert("Error: Invalid Bank Account to reconcile...");
			}
	   }
	}
	
	function computeDifference(endBalance) {
		
		//alert(endBalance);
		
		var end = parseFloat(stripComma(endBalance));
		var cleared = parseFloat(stripComma($("#clearedbalance").val()));

		var diff = cleared - end;
			
		$("#diffbalance").val(kSeparator(diff.toFixed(2)));

	}
	
		
	function unserved_po(){
		$("#unservedpo_dtf").datepicker(); $("#unservedpo_dt2").datepicker();
		$("#unservedpo").dialog({title: "Unserved S.O.", width: 400 }).dialogExtend({
			"closable" : true,
		    "maximizable" : false,
		    "minimizable" : true
		});
	}
	
	function showAudTrail() {
		$("#audDTF").datepicker(); $("#audDT2").datepicker(); 
		$("#audtrail").dialog({title: "Audit Trail", width: 400 }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}
	
	function viewAuditTrail(){
		window.open("reports/audittrail.php?dtf="+$("#audDTF").val()+"&dt2="+$("#audDT2").val()+"&user="+$("#audUser").val()+"&module="+$("#audType").val()+"&sid="+Math.random()+"","Unclassified Accounts","location=1,status=1,scrollbars=1,width=640,height=720");
	}
	
	/* Human Resources */
	function showEmployees() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/employee.list.php'></iframe>";
		$("#e_list").html(txtHTML);
		$("#e_list").dialog({title: "Employee Masterfile", width: xWidth, height: 540, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function jumpEPage(pageNum,stext) {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/employee.list.php?page="+pageNum+"&searchtext="+stext+"&sid="+Math.random()+"'></iframe>";
		$("#e_list").html(txtHTML);
		$("#e_list").dialog({title: "Employee Masterfile", width: xWidth, height: 540, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function showEmpProfile(emp_id) {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/employee.details.php?emp_idno="+emp_id+"&sid="+Math.random()+"'></iframe>";
		$("#e_details").html(txtHTML);
		$("#e_details").dialog({title: "Employee Profile", width: xWidth, height: 800, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function newEmp() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/employee.details.php?sid="+Math.random()+"'></iframe>";
		$("#e_details").html(txtHTML);
		$("#e_details").dialog({title: "New Employee Record", width: xWidth, height: 800, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function managePayroll() {
		$("#managePayroll").dialog({title: "Manage Payroll", width: xWidth, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}
	
	function managePayroll2() {
		$("#managePayroll2").dialog({title: "Manage Payroll", width: 1024, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function getPayperiods(ptype,selbox) {
		$.post("hrd/misc-data.php", { mod: "getPeriods", type: ptype, sid: Math.random() }, function(data) { document.getElementById(selbox).innerHTML = data; },"html");
	}
	
	
	
	function getEmployeesByDept(dept,batch,selbox) {
		$.post("hrd/misc-data.php", { mod: "getEmployeesByDept", dept: dept, batch: batch, sid: Math.random() }, function(data) { document.getElementById(selbox).innerHTML = data; },"html");
	}
	
	
	function populatePeriods(batch,selbox) {
		$.post("hrd/misc-data.php", { mod: "populatePeriods", batch: batch, sid: Math.random() }, function(htmlData) { document.getElementById(selbox).innerHTML = htmlData; },"html");
	}
	
	function populateEmployees(batch,selbox) {
		$.post("hrd/misc-data.php", { mod: "populateEmployees", batch: batch, sid: Math.random() }, function(htmlData) { document.getElementById(selbox).innerHTML = htmlData; },"html");
	}
	
	function showPayPeriods() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/payperiods.php?sid="+Math.random()+"'></iframe>";
		$("#payperiods").html(txtHTML);
		$("#payperiods").dialog({title: "Payroll Cut-offs", width: 960, height: 480, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function showHolidays() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/holidays.php?sid="+Math.random()+"'></iframe>";
		$("#holidays").html(txtHTML);
		$("#holidays").dialog({title: "National Holidays", width: 960, height: 480, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}
	
	function showLocalHolidays() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/localholidays.php?sid="+Math.random()+"'></iframe>";
		$("#holidays").html(txtHTML);
		$("#holidays").dialog({title: "Local Holidays", width: 960, height: 480, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function showLeaves() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/leaves.php'></iframe>";
		$("#leaves").html(txtHTML);
		$("#leaves").dialog({title: "Leaves & Absences",width: xWidth, height: 480, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}


	function showDeductions() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/deductions.php'></iframe>";
		$("#deductions").html(txtHTML);
		$("#deductions").dialog({title: "Outright Payroll Deductions",width: xWidth, height: 480, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}
	
	function showIncentives() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/incentives.php'></iframe>";
		$("#deductions").html(txtHTML);
		$("#deductions").dialog({title: "Salary Incentives",width: xWidth, height: 480, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function showLoans() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/loans.php'></iframe>";
		$("#loans").html(txtHTML);
		$("#loans").dialog({title: "Loans/Long Term Deductions",width: xWidth, height: 480, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function showAdjustments() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/adjustments.php'></iframe>";
		$("#adjustments").html(txtHTML);
		$("#adjustments").dialog({title: "Salary Adjustments",width: xWidth, height: 480, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}
	
	function showBasic2() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/basic2.php'></iframe>";
		$("#adjustments").html(txtHTML);
		$("#adjustments").dialog({title: "Basic Salary 2",width: xWidth, height: 480, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function showImport() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/importdtr.php'></iframe>";
		$("#importdtr").html(txtHTML);
		$("#importdtr").dialog({title: "Import DTR from Biometrics", width: 380, height: 240, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function showEmpDTR() {
		$("#manageDTR").dialog({title: "Manage Daily Time Record", width: 400, modal: true }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}
	
	function showEmpSchedules() {
		$("#plotSchedules").dialog({title: "Plot Employee Schedules", width: 400, modal: true }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}
	
	function plotSchedules() {
		var txtHTML = "<iframe id='frmSchedules' frameborder=0 width='100%' height='100%' src='hrd/employee.schedules.php?batch="+$("#plot_batch").val()+"&dept="+$("#plot_dept").val()+"&period="+$("#plot_cutoff").val()+"&sid="+Math.random()+"'></iframe>";
		$("#empdtr").html(txtHTML);
		$("#empdtr").dialog({title: "Plot Employee Schedules", width: xWidth, height: 660, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
		
	}
	
	function showEmpOT() {
		$("#manageOT").dialog({title: "Manage Employee Overtime", width: 400, modal: true }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function mdtr_pop_emp(type) {
		$.post("hrd/misc-data.php", { mod: "populateEmp", type: type, sid: Math.random() }, function(data) {
			$("#mdtr_emp").html(data);
		},"html");
	}
	
	function lb_pop_emp(dept) {
		if(dept!=""){
			$.post("hrd/misc-data.php", { mod: "populateEmp", dept: dept, sid: Math.random() }, function(data) {
				$("#lb_emp").html(data);
			},"html");
		} else { $("#mdtr_emp").html("<option value=''>- All Employees -</option>"); }
	}

	function getDTR() {
		if($("#mdtr_emp").val() != "" && $("#mdtr_cutoff").val() != "") {
			
			$.post("hrd/misc-data.php", { mod: "getEmpName", eid: $("#mdtr_emp").val(), sid: Math.random() }, function(data) {
				var txtHTML = "<iframe id='frmDTR' frameborder=0 width='100%' height='100%' src='hrd/employee.dtr.php?eid="+$("#mdtr_emp").val()+"&period="+$("#mdtr_cutoff").val()+"&sid="+Math.random()+"'></iframe>";
				$("#empdtr").html(txtHTML);
				$("#empdtr").dialog({title: "Manage Employee Daily Time Record "+data[0]+"", width: xWidth, height: 660, resizable: false }).dialogExtend({
					"closable" : true,
					"maximizable" : false,
					"minimizable" : true
				});
			},"json");
		} else {
			parent.sendErrorMessage("Unable to continue as you may have not selected an employee from the given list or you may have not specify the payroll period you wish to manage...");
		}
	}
	
	function getOT() {
		var txtHTML = "<iframe id='frmDTR' frameborder=0 width='100%' height='100%' src='hrd/employee.overtime.php?period="+$("#mot_cutoff").val()+"&sid="+Math.random()+"'></iframe>";
		$("#manageovertime").html(txtHTML);
		$("#manageovertime").dialog({title: "Manage Employee Daily Time Record", width: xWidth, height: 660, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function refreshDTR(eid,period,dept) {
		$.post("hrd/misc-data.php", { mod: "getEmpName", eid: eid, sid: Math.random() }, function(data) {
			var txtHTML = "<iframe id='frmDTR' frameborder=0 width='100%' height='100%' src='hrd/employee.dtr.php?eid="+eid+"&period="+period+"&sid="+Math.random()+"'></iframe>";
			$("#empdtr").html(txtHTML);
			$("#empdtr").dialog({title: "Manage Employee Daily Time Record "+data[0]+"", width: xWidth, height: 660, resizable: false }).dialogExtend({
				"closable" : true,
				"maximizable" : false,
				"minimizable" : true
			});
		},"json");
	}

	function showPrintDTR() {
		$("#printDTR").dialog({title: "Print Daily Time Record", width: 400, modal: true }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function printDTR(tag) {

		if(tag == 1) {
			var txtHTML = "<iframe id='frmDTR' frameborder=0 width='100%' height='100%' src='hrd/reports/dtr.php?period="+$("#pdtr_cutoff").val()+"&dept="+$("#pdtr_dept").val()+"&sid="+Math.random()+"'></iframe>";
			$("#report3").html(txtHTML);
			$("#report3").dialog({title: "Daily Time Record", width: 560, height: 620, resizable: true }).dialogExtend({
				"closable" : true,
				"maximizable" : true,
				"minimizable" : true
			});
		
		} else {
			window.open("hrd/reports/dtr-xls.php?period="+$("#pdtr_cutoff").val()+"&dept="+$("#pdtr_dept").val()+"&ptype="+$("#pdtr_type").val()+"&sid="+Math.random()+"","Daily Time Record (Excel)","location=1,status=1,scrollbars=1,width=640,height=720");
		}
		
	}

	function showPrintTardy() {
		$("#tardyDtf").datepicker(); $("#tardyDt2").datepicker();
		$("#printTardy").dialog({title: "Tardiness Report", width: 400, modal: true }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function printTardy() {
		var txtHTML = "<iframe id='frmDTR' frameborder=0 width='100%' height='100%' src='hrd/reports/tardiness.php?dtf="+$("#tardyDtf").val()+"&dt2="+$("#tardyDt2").val()+"&dept="+$("#tardyDept").val()+"&sid="+Math.random()+"'></iframe>";
		$("#report5").html(txtHTML);
		$("#report5").dialog({title: "Tardiness Report", width: 560, height: 620, resizable: true }).dialogExtend({
			"closable" : true,
			"maximizable" : true,
			"minimizable" : true
		});
	}

	function showPay() {
		if(confirm("Before processing payroll, please ensure that all short and long term deductions were considered, leaves & absences had been encoded and Daily Time Record was already checked and corrected. Do you still wish to continue?") == true) {
			$("#processPay").dialog({title: "Process Payroll", width: 400, modal: true }).dialogExtend({
				"closable" : true,
				"maximizable" : false,
				"minimizable" : true
			});
		}	
	}

	function processPay() {
		if(confirm("Are you sure you want to process payroll and prepare payslip thereafter?") == true) {
			window.open("hrd/processpay.php?cutoff="+$("#payCutoff").val()+"&dept="+$("#payDept").val()+"&sid="+Math.random()+"","Process Payroll","location=1,status=1,scrollbars=1,width=640,height=720");
		}
	}
	
	function showPrintPaySlip() {
		$("#printPaySlip").dialog({title: "Print Payslip", width: 400, modal: true }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function printPaySlip() {
		window.open("hrd/payslip.php?cutoff="+$("#payslipCutoff").val()+"&dept="+$("#payslipDept").val()+"&eid="+$("#payslipEmployee").val()+"&sid="+Math.random()+"","Print Payslip","location=1,status=1,scrollbars=1,width=640,height=720");
	}

	function emailPayslip() {
		window.open("mailsender.php?cutoff="+$("#payslipCutoff").val()+"&dept="+$("#payslipDept").val()+"&eid="+$("#payslipEmployee").val()+"&sid="+Math.random()+"","Email Payslip","location=1,status=1,scrollbars=1,width=640,height=720");
	}

	function showPrintPaySummary() {
		$("#paySummary").dialog({title: "Payroll Summary", width: 400, modal: true }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function processPaySummary() {
		window.open("hrd/reports/payrollsummary.php?cutoff="+$("#paySCutoff").val()+"&dept="+$("#paySDept").val()+"&sid="+Math.random()+"","Payroll Register","location=1,status=1,scrollbars=1,width=640,height=720");
	}

	function processPaySummaryExcel() {
		window.open("hrd/reports/payrollsummary-xls.php?cutoff="+$("#paySCutoff").val()+"&dept="+$("#paySDept").val()+"&sid="+Math.random()+"","Payroll Register","location=1,status=1,scrollbars=1,width=640,height=720");
	}

	function showLoanBalanceSummary() {
		$("#employeeLoanBalances").dialog({ title: "Employee Loan Balances Summary", width: 400, modal: true });
	}

	function generateLoanBalances() {
		window.open("hrd/reports/loanbalances.php?cutoff="+$("#lbs_cutoff").val()+"&eid="+$("#lbs_emp").val()+"&type="+$("#lbs_type").val()+"&sid="+Math.random()+"","Loan Balances Summary","location=1,status=1,scrollbars=1,width=640,height=720");
	}

	function showBDO() {
		if(confirm("Once Bank transmittal has been created, records pertaining to the period selected are deemed final and shall be locked for future editing unless otherwise overridden by the Finance Manager or any person under authority. Do you still wish to continue?") == true) {
			$("#bdo_creditdate").datepicker();
			$("#bdoTransmittal").dialog({title: "BDO Transmittal Tool", width: 400, modal: true }).dialogExtend({
				"closable" : true,
				"maximizable" : false,
				"minimizable" : true
			});
		}	
	}
	
	function generateTransmittal() {
		window.open("export/bdo_transmittal.php?cutoff="+$("#bdo_cutoff").val()+"&proj="+$("#bdo_proj").val()+"&date="+$("#bdo_creditdate").val()+"&batch="+$("#bdo_batchcode").val()+"&sid="+Math.random()+"","Payroll Register","location=1,status=1,scrollbars=1,width=640,height=720");
	}
	
	function generateTransmittalX() {
		window.open("export/bdo_transmittalx.php?cutoff="+$("#bdo_cutoff").val()+"&proj="+$("#bdo_proj").val()+"&date="+$("#bdo_creditdate").val()+"&batch="+$("#bdo_batchcode").val()+"&sid="+Math.random()+"","Payroll Register","location=1,status=1,scrollbars=1,width=640,height=720");
	}
	
	
	function showOT() {
		$("#otSummary").dialog({title: "Summary of Approved Overtime", width: 400, modal: true }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function processOT() {
		
		//window.open("hrd/reports/otsummary.php?ptype="+$("#otType").val()+"&cutoff="+$("#otCutoff").val()+"&sid="+Math.random()+"","Overtime Summary","location=1,status=1,scrollbars=1,width=640,height=720");
	
		var txtHTML = "<iframe id='frmDTR' frameborder=0 width='100%' height='100%' src='hrd/reports/otsummary.php?cutoff="+$("#otCutoff").val()+"&dept="+$("#otDept").val()+"&sid="+Math.random()+"'></iframe>";
		$("#report2").html(txtHTML);
		$("#report2").dialog({title: "Overtime Summary", width: 560, height: 620, resizable: true }).dialogExtend({
			"closable" : true,
			"maximizable" : true,
			"minimizable" : true
		});
	}
	
	function showEmpLoanBalance() {
		$("#loanBalances").dialog({title: "Employee Loan Balances", width: 400, modal: true }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}
	
	function getEmpLoans() {
		window.open("hrd/reports/loanbalance-html.php?id_no="+$("#lb_emp").val()+"&type="+$("#lb_type").val()+"&sid="+Math.random()+"","Employee Loan Balances","location=1,status=1,scrollbars=1,width=640,height=720");
	}
	
	function showStatutory() {
		$("#printStatutory").dialog({title: "Summary of Statutory Deductions", width: 400, modal: true }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function printStatutory() {
		if($("#statYear").val() == "" && isNaN($("#statYear").val())==true) {
			parent.sendErrorMessage("- You have specified an invalid Year format!");
		} else {
			window.open("hrd/reports/statutory.php?year="+$("#statYear").val()+"&month="+$("#statMonth").val()+"&proj="+$("#statProj").val()+"&sid="+Math.random()+"","Summary of Statutory Deductions","location=1,status=1,scrollbars=1,width=640,height=720");
		}
	}
	
	function exportStatutory() {
		if($("#statYear").val() == "" && isNaN($("#statYear").val())==true) {
			parent.sendErrorMessage("- You have specified an invalid Year format!");
		} else {
			window.open("hrd/reports/statutory-xls.php?year="+$("#statYear").val()+"&month="+$("#statMonth").val()+"&proj="+$("#statProj").val()+"&sid="+Math.random()+"","Summary of Statutory Deductions","location=1,status=1,scrollbars=1,width=640,height=720");
		}
	}
	
	function showGrossCompensation() {
		$("#printGrossCompensation").dialog({title: "Employee Gross Compensation Report", width: 400, modal: true }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function printGrossCompensation() {
		if($("#grossYear").val() == "" && isNaN($("#grossYear").val())==true) {
			parent.sendErrorMessage("- You have specified an invalid Year format!");
		} else {
			window.open("hrd/reports/gross-compensation.php?year="+$("#grossYear").val()+"&emp_type="+$("#grossType").val()+"&sid="+Math.random()+"","Gross Compensation Report","location=1,status=1,scrollbars=1,width=640,height=720");
		}
	}
	
	function showThirteenth() {

		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='hrd/thirteenth.upload.php'></iframe>";
		$("#empfam").html(txtHTML);
		$("#empfam").dialog({title: "Upload Thirteenth Month File", width: 400, height: 200 }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}
	
	function printThirteenth() {
		$("#13PayDate").datepicker();
		$("#printThirteenth").dialog({title: "Print Thirteenth Month Payslip", width: 400 }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function print13() {
		if($("#13PayYear").val() == '2024') {
			window.open("hrd/reports/thirteenth.slip.manual.php?year="+$("#13PayYear").val()+"&date="+$("#13PayDate").val()+"&sid="+Math.random()+"","Thirteenth Month Pay","location=1,status=1,scrollbars=1,width=640,height=720");
		} else {
			window.open("hrd/reports/thirteenth.slip.php?year="+$("#13PayYear").val()+"&date="+$("#13PayDate").val()+"&sid="+Math.random()+"","Thirteenth Month Pay","location=1,status=1,scrollbars=1,width=640,height=720");
		}
	}

	function my13thMonthPay() {
		$("#13MonthPay").dialog({title: "13th Month Pay", width: 400, modal: true }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true
		});
	}

	function generate13Month() {
		if($("#13Year").val() == "" && isNaN($("#13Year").val())==true) {
			parent.sendErrorMessage("- You have specified an invalid Year format!");
		} else {
			window.open("hrd/reports/13thPay.php?year="+$("#13Year").val()+"&dept="+$("#13Dept").val()+"&eid="+$("#13Employee").val()+"&sid="+Math.random()+"","13th Month Pay","location=1,status=1,scrollbars=1,width=640,height=720");
		}
	}

	function showEmpSummary() {

		$("#recordHiredDTF").datepicker({changeMonth: true, changeYear: true });
		$("#recordHiredDT2").datepicker({changeMonth: true, changeYear: true });

		$("#eesummary").dialog({
			title: "Generate Employee Summary List", 
			width: 480, 
			resizable: false,
			buttons: [
				{
					 text: "Generate Report in Excel",
					click: function() { 
						window.open("hrd/reports/employees.php?class="+$("#recordClass").val()+"&dept="+$("#recordDepartment").val()+"&estatus="+$("#recordEstatus").val()+"&dtf="+$("#recordHiredDTF").val()+"&dt2="+$("#recordHiredDT2").val()+"&fstatus="+$("#recordStatus").val()+"","Inventory Stockcard","location=1,status=1,scrollbars=1,width=640,height=720");	

					 },
					icons: { primary: "ui-icon-check" }
				},
				{
					text: "Cancel",
				    click: function() { $(this).dialog("close"); },
				    icons: { primary: "ui-icon-cancel" }
			   }
			]
		});
	}
	
	/* 201 Addition */
	function showFam(eid) {
		$.post("payroll.datacontrol.php", { mod: "getEmpName", record_id: eid, sid: Math.random() }, function(data) {
			var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='employee.fbackground.php?eid="+eid+"'></iframe>";
			$("#empfam").html(txtHTML);
			$("#empfam").dialog({title: "Employee Family Background ("+data+")", width: xWidth, height: 520 }).dialogExtend({
				"closable" : true,
				"maximizable" : false,
				"minimizable" : true
			});
		});
	}

	function showEdu(eid) {
		$.post("payroll.datacontrol.php", { mod: "getEmpName", record_id: eid, sid: Math.random() }, function(data) {
			var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='employee.edubackground.php?eid="+eid+"'></iframe>";
			$("#empedu").html(txtHTML);
			$("#empedu").dialog({title: "Employee Educational Background ("+data+")", width: xWidth, height: 520 }).dialogExtend({
				"closable" : true,
				"maximizable" : false,
				"minimizable" : true
			});
		});
	}

	function showErecord(eid) {
		$.post("payroll.datacontrol.php", { mod: "getEmpName", record_id: eid, sid: Math.random() }, function(data) {
			var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='employee.experience.php?eid="+eid+"'></iframe>";
			$("#empexp").html(txtHTML);
			$("#empexp").dialog({title: "External Work Experience ("+data+")", width: xWidth, height: 520 }).dialogExtend({
				"closable" : true,
				"maximizable" : false,
				"minimizable" : true
			});
		});
	}

	function showErecord2(eid) {
		$.post("payroll.datacontrol.php", { mod: "getEmpName", record_id: eid, sid: Math.random() }, function(data) {
			var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='employee.experience2.php?eid="+eid+"'></iframe>";
			$("#empexpinternal").html(txtHTML);
			$("#empexpinternal").dialog({title: "Internal Work Experience ("+data+")", width: xWidth, height: 480 }).dialogExtend({
				"closable" : true,
				"maximizable" : false,
				"minimizable" : true
			});
		});
	}
	
	function showCert(eid) {
		$.post("payroll.datacontrol.php", { mod: "getEmpName", record_id: eid, sid: Math.random() }, function(data) {
			var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='employee.certificates.php?eid="+eid+"'></iframe>";
			$("#empcert").html(txtHTML);
			$("#empcert").dialog({title: "Memos, Certificates & Clearances ("+data+")", width: xWidth, height: 520 }).dialogExtend({
				"closable" : true,
				"maximizable" : false,
				"minimizable" : true
			});
		});
	}
	
	function uploadBio() {
		var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='e_uploaddtr.php'></iframe>";
		$("#changepass").html(txtHTML);
		$("#changepass").dialog({title: "Upload Data From Logbox", width: 480, height: 210, resizable: false }).dialogExtend({
			"closable" : true,
			"maximizable" : false,
			"minimizable" : true,
		});
	}

	/* PEME Reports */

function showPEMEReport() {
	$("#pemeReportMain").dialog({title: "PE/ME Reports", width: 680, height: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showMaxicareCensus() {
	$("#mx_dtf").datepicker(); $("#mx_dt2").datepicker();
	$("#mxcensus").dialog({
		title: "Maxicare Census", 
		width: 400,
		buttons: [
			{
				text: "Generate Report to Excel",
				icons: { primary: "ui-icon-clipboard" },
				click: function() {
					window.open("export/censusMaxicare.php?dtf="+$("#mx_dtf").val()+"&dt2="+$("#mx_dt2").val()+"&sid="+Math.random()+"","Maxicare Census","location=1,status=1,scrollbars=1,width=640,height=720");
				 }
			}

		]
	});
}

/* Xray & Radiology */
function showImagingQueue() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='imaging.list.php?sid="+Math.random()+"'></iframe>";
	$("#itemlist").html(txtHTML);
	$("#itemlist").dialog({title: "Imaging Queueing List", width: xWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function manageECGResults() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='ecgSamples.php?sid="+Math.random()+"'></iframe>";
	$("#srrlist").html(txtHTML);
	$("#srrlist").dialog({title: "Manage ECG Samples", width: xWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function validateECGResult(lid,code) {
	$("#ecgResult").html("<iframe id='frmEcgResult' frameborder=0 width='100%' height='100%' src='result.ecg.php?lid="+lid+"'></iframe>");
	$("#ecgResult").dialog({
		title: "Write Result",
		width: xWidth,
		height: 695,
		resizeable: false,
		modal: false
	});
}

function printECGResult(so_no,code,serialno) {

	var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.ecg.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
	$("#report5").html(txtHTML);
	$("#report5").dialog({title: "Print - ECG RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});

}

function showImgSamples() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='imgsamples.list.php?sid="+Math.random()+"'></iframe>";
	$("#polist").html(txtHTML);
	$("#polist").dialog({title: "Manage Imaging Samples", width: xWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function manageImgResults() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='imgvalidation.php?sid="+Math.random()+"'></iframe>";
	$("#srrlist").html(txtHTML);
	$("#srrlist").dialog({title: "Validate Imaging Results", width: xWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showXrayTemplates() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='xray.templates.php?sid="+Math.random()+"'></iframe>";
	$("#solist").html(txtHTML);
	$("#solist").dialog({title: "X-Ray - Ultrasound Result Templates", width: xWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function xrayTemplateDetails(id) {
	$("#report3").html("<iframe id='xrayTemplate' frameborder=0 width='100%' height='100%' src='xray.templatedetails.php?id="+id+"&sid="+Math.random()+"'></iframe>");
	var dis = $("#report3").dialog({
		title: "X-Ray - Ultrasound Result Templates",
		width: 1024,
		height: 680,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Changes Made",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#xrayTemplate').contents().find('#frmXrayTemplate').serialize();
					
						//var dataString = $("#frmDescResult").serialize();
						dataString = "mod=saveXrayTemplate&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Template Successfully Saved!");
								dis.dialog("close");
								$("#frmDescResult").trigger("reset");
							}
						});
					}
				}
			},
			{
				text: "Mark Template as Inactive",
				icons: { primary: "ui-icon-cancel" },
				click: function() {

				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function printDescriptiveResult(so_no,code,serialno) {

	var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.xray.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
	$("#report5").html(txtHTML);
	$("#report5").dialog({title: "Print - XRAY RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});

}

function showXrayLogbook() {
	var dis = $("#xrayLogBook").dialog({
		title: "Xray Results Logbook", 
		width: 480,
		resizable: false, 
		buttons: [
			{
				icons: { primary: "ui-icon-print" },
				text: "Generate Logbook",
				click: function() { 
					var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='reports/xray.logbook.php?dtf="+$("#xraylog_dtf").val()+"&dt2="+$("#xraylog_dt2").val()+"&consultant="+$("#xraylog_consultant").val()+"&type="+$("#xraylog_type").val()+"&encode="+$("#xraylog_encoder").val()+"&xraylog_sort="+$("#xraylog_sort").val()+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report5").html(txtHTML);
					$("#report5").dialog({title: "Xray Logbook", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});
				}
			},
			{
				icons: { primary: "ui-icon-closethick" },
				text: "Close Window",
				click: function() { 
					dis.dialog("close");
				}
			}
		]
	});
}

/* Pharmacy Section */
function showPharmaItems(icode) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='pharma.master.php?sid="+Math.random()+"&icode="+icode+"'></iframe>";
	$("#itemlist").html(txtHTML);
	$("#itemlist").dialog({title: "Pharmacy Product List", width: xWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showPharmaItemInfo(rid) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='pharma.details.php?id="+rid+"&mod=1&sid="+Math.random()+"'></iframe>";
	$("#itemdetails").html(txtHTML);
	$("#itemdetails").dialog({title: "Product Details", width: 1120, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
		"maximizable" : false,
		"minimizable" : true
	});
}

function showPharmaSO(){
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='pharma.solist.php'></iframe>";
	$("#projlist").html(txtHTML);
	$("#projlist").dialog({title: "Sales Order Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewPharmaSO(so_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='pharma.sodetails.php?so_no="+so_no+"'></iframe>";
	$("#projdetails").html(txtHTML);
	$("#projdetails").dialog({title: "Sales Order Details", width: 1120, height: 690, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printPharmaSO(so_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/pharma.soprint.php?so_no="+so_no+"&sid="+Math.random()+"'></iframe>";
	$("#poprint").html(txtHTML);
	$("#poprint").dialog({title: "PRINT >> PHARMACY SALES ORDER", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printPharmaSOLetter(so_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/pharma.letter.soprint.php?so_no="+so_no+"&sid="+Math.random()+"'></iframe>";
	$("#poprint").html(txtHTML);
	$("#poprint").dialog({title: "PRINT >> PHARMACY SALES ORDER", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showPharmaSI(){
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='pharma.silist.php'></iframe>";
	$("#polist").html(txtHTML);
	$("#polist").dialog({title: "Sales Invoice Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function printPharmaCSI(so_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/pharma.csi.print.php?so_no="+so_no+"&sid="+Math.random()+"'></iframe>";
	$("#poprint").html(txtHTML);
	$("#poprint").dialog({title: "PRINT >> PHARMACY CHARGE SALES INVOICE", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function viewPharmaSI(doc_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='pharma.sidetails.php?doc_no="+doc_no+"'></iframe>";
	$("#podetails").html(txtHTML);
	$("#podetails").dialog({title: "Sales Invoice Details", width: 1120, height: 640, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printPharmaSI(doc_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/pharma.siprint.php?doc_no="+doc_no+"&sid="+Math.random()+"'></iframe>";
	$("#poprint").html(txtHTML);
	$("#poprint").dialog({title: "PRINT >> PHARMACY SALES INVOICE", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showPharmaReport() {
	$("#phar_dsr_dtf").datepicker(); $("#phar_dsr_dt2").datepicker();
	$("#pharDsr").dialog({title: "Pharmacy Detailed Sales Report", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generatePharDSR() {
	var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/pharDsr.php?dtf="+$("#phar_dsr_dtf").val()+"&dt2="+$("#phar_dsr_dt2").val()+"&item="+$("#phar_dsr_item").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report3").html(txtHTML);
	$("#report3").dialog({title: "Pharmacy Detailed Sales Report", width: 640, height: 520, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function generatePharDSRX() {
	window.open("export/phardsr.php?dtf="+$("#phar_dsr_dtf").val()+"&dt2="+$("#phar_dsr_dt2").val()+"&sid="+Math.random()+"","Inventory Stockcard","location=1,status=1,scrollbars=1,width=640,height=720");
}

function showPharmaRR() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='pharmarr.list.php'></iframe>";
	$("#pharmarrlist").html(txtHTML);
	$("#pharmarrlist").dialog({title: "Pharmacy Receiving Report Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewPharmaRR(rr_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='pharmarr.details.php?rr_no="+rr_no+"'></iframe>";
	$("#pharmarrdetails").html(txtHTML);
	$("#pharmarrdetails").dialog({title: "Pharmacy Receiving Report Details", width: 1120, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printPharmaRR(rr_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/pharmarr.print.php?rr_no="+rr_no+"&sid="+Math.random()+"'></iframe>";
	$("#pharmarrprint").html(txtHTML);
	$("#pharmarrprint").dialog({title: "PRINT >> PHARMACY RECEIVING REPORT", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showPharmaSW() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='pharmasw.list.php'></iframe>";
	$("#pharmaswlist").html(txtHTML);
	$("#pharmaswlist").dialog({title: "Pharmacy Stocks Withdrawal Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewPharmaSW(sw_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='pharmasw.details.php?sw_no="+sw_no+"'></iframe>";
	$("#pharmarrdetails").html(txtHTML);
	$("#pharmarrdetails").dialog({title: "Pharmacy Receiving Report Details", width: 1120, height: 560, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printPharmaSW(sw_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/pharmasw.print.php?sw_no="+sw_no+"&uid="+uid+"&rePrint="+rePrint+"&sid="+Math.random()+"'></iframe>";
	$("#pharmaswprint").html(txtHTML);
	$("#pharmaswprint").dialog({title: "PRINT >>PHARMACY STOCKS WITHDRAWAL SLIP", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showPharmaPOList() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='pharmapo.list.php'></iframe>";
	$("#pharmapolist").html(txtHTML);
	$("#pharmapolist").dialog({title: "Pharmacy Purchase Order Summary", width: xWidth, height: 540, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewPharmaPO(po_no) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='pharmapo.details.php?po_no="+po_no+"'></iframe>";
	$("#pharmapodetails").html(txtHTML);
	$("#pharmapodetails").dialog({title: "Pharmacy Purchase Order Details", width: 1120, height: 540, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printPharmaPO(po_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/pharmapo.print.php?po_no="+po_no+"&rePrint="+rePrint+"&sid="+Math.random()+"&user="+uid+"'></iframe>";
	$("#pharmapoprint").html(txtHTML);
	$("#pharmapoprint").dialog({title: "PRINT >>PHARMACY PURCHASE ORDER", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printPharmaPOPList(po_no,uid,rePrint) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/pharmapo.plist.php?po_no="+po_no+"&rePrint="+rePrint+"&sid="+Math.random()+"&user="+uid+"'></iframe>";
	$("#pharmapoprint").html(txtHTML);
	$("#pharmapoprint").dialog({title: "PRINT >> PHARMACY PURCHASE ORDER PACKING LIST", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showCountList() {
	$("#countlist_dtf").datepicker(); $("#countlist_dt2").datepicker();
	$("#countlist").dialog({title: "Top Selling Products Report", width: 400 }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function generateCountList() {
	var txtHTML = "<iframe id='frmglsched' frameborder=0 width='100%' height='100%' src='reports/countList.php?dtf="+$("#countlist_dtf").val()+"&dt2="+$("#countlist_dt2").val()+"&sid="+Math.random()+"'></iframe>";
	$("#report3").html(txtHTML);
	$("#report3").dialog({title: "Top Selling Products Report", width: 640, height: 520, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});
}

function generateCountListX() {
	window.open("export/countList.php?dtf="+$("#countlist_dtf").val()+"&dt2="+$("#countlist_dt2").val()+"&item="+$("#countlist_item").val()+"&sid="+Math.random()+"","Top Selling Products Report","location=1,status=1,scrollbars=1,width=640,height=720");
}

/* END OF PHARMA */

/* Nursing Station */
function showLabCollection() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='phleb.list.php?sid="+Math.random()+"'></iframe>";
	$("#report1").html(txtHTML);
	$("#report1").dialog({title: "Phleb Queueing List", width: xWidth, height: 500, resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function printBarcode(serialno) {
	$.post("src/sjerp.php", { mod: "checkSerialStatus", serialno: serialno, sid: Math.random() }, function(result) {
		if(parseFloat(result['mycount']) == 0) {
			sendErrorMessage("It appears that this specimen record hasn't been saved yet.. Please click save and try to print the barcode again.");
		} else {
			var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='print/specimenbarcode.php?id="+serialno+"&sid="+Math.random()+"'></iframe>";
			$("#barcode").html(txtHTML);
			$("#barcode").dialog({title: "Print - Barcode", width: 400, height: 200, resizable: false }).dialogExtend({
				"closable" : true,
				"maximizable" : false,
				"minimizable" : true
			});


		}
	});
}

function showMedConsultant() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='doctors.php?sid="+Math.random()+"'></iframe>";
	$("#itemlist").html(txtHTML);
	$("#itemlist").dialog({title: "Care Providers & Consultants", width: xWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function newCareProviderProfile() {
	$("#frmNewDoctorFile").trigger("reset");
	$("#doctorMon").attr({"checked": false });
	$("#doctorTue").attr({"checked": false });
	$("#doctorWed").attr({"checked": false });
	$("#doctorThu").attr({"checked": false });
	$("#doctorFri").attr({"checked": false });
	$("#doctorSat").attr({"checked": false });
	$("#doctorSignatureImage").html('');
	var dis = $("#doctorDetails").dialog({ 
		title: "New Care Provider/Consultant Profile",
		width: "540",
		modal: true,
		resizeable: false,
		buttons: [
			{
				icons: { primary: "ui-icon-check" },
				text: "Save Record",
				click: function() { 
					if(confirm("Are you sure you want to make this appointment?") == true) {
						var dataString = $("#frmNewDoctorFile").serialize();
							


							$.ajax({
								type: "POST",
								url: "src/sjerp.php",
								data: new FormData($('#frmNewDoctorFile')[0]),
								cache: false,
								contentType: false,
								processData: false,
								success: function() {
								alert("Appointment Succesfully set!");
								showMedConsultant();
								$("#frmNewDoctorFile").trigger("reset");
							}
						});
					}	
				}
			},
			{
				icons: { primary: "ui-icon-closethick" },
				text: "Close Window",
				click: function() { 
					dis.dialog("close");
				}	

			}		

		]

	});
}

function viewCareProviderProfile(id) {
	$.post("src/sjerp.php", { mod: "viewProviderProfile", id: id, sid: Math.random() }, function(data) {
		$("#doctorFullname").val(decodeURIComponent(data['xfullname']));
		$("#doctorPrefix").val(data['prefix']);
		$("#doctorSpecialization").val(data['specialization']);
		$("#doctorLicenseNo").val(data['license_no']);
		$("#doctorContact").val(data['contact_no']);

		if(data['mon'] == 'Y') { $("#doctorMon").attr({"checked": true }); } else { $("#doctorMon").attr({"checked": false }); }
		if(data['tue'] == 'Y') { $("#doctorTue").attr({"checked": true }); } else { $("#doctorTue").attr({"checked": false }); }
		if(data['wed'] == 'Y') { $("#doctorWed").attr({"checked": true }); } else { $("#doctorWed").attr({"checked": false }); }
		if(data['thu'] == 'Y') { $("#doctorThu").attr({"checked": true }); } else { $("#doctorThu").attr({"checked": false }); }
		if(data['fri'] == 'Y') { $("#doctorFri").attr({"checked": true }); } else { $("#doctorFri").attr({"checked": false }); }
		if(data['sat'] == 'Y') { $("#doctorSat").attr({"checked": true }); } else { $("#doctorSat").attr({"checked": false }); }

		$("#doctorMonSchedule").val(data['mon_schedule']);
		$("#doctorTueSchedule").val(data['tue_schedule']);
		$("#doctorWedSchedule").val(data['wed_schedule']);
		$("#doctorThuSchedule").val(data['thu_schedule']);
		$("#doctorFriSchedule").val(data['fri_schedule']);
		$("#doctorSatSchedule").val(data['sat_schedule']);

		$("#doctorSignatureImage").html(data['signature']);

		var dis = $("#doctorDetails").dialog({ 
			title: "New Care Provider/Consultant Profile",
			width: "540",
			modal: true,
			resizeable: false,
			buttons: [
				{
					icons: { primary: "ui-icon-check" },
					text: "Save Changes",
					click: function() { 
						if(confirm("Are you sure you want to make this appointment?") == true) {
							$.ajax({
								type: "POST",
								url: "src/sjerp.php",
								data: new FormData($('#frmNewDoctorFile')[0]),
								cache: false,
								contentType: false,
								processData: false,
								success: function() {
									alert("Appointment Succesfully set!");
									showMedConsultant();
									$("#frmNewDoctorFile").trigger("reset");
								}
							});
						}	
					}
				},
				{
					icons: { primary: "ui-icon-closethick" },
					text: "Close Window",
					click: function() { 
						dis.dialog("close");
						$("#frmNewDoctorFile").trigger("reset");
					}	
	
				}		
	
			]
	
		});

	},"json");
}

/* Consultation Form */
function showMC() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='consult.list.php'></iframe>";
	$("#consultlist").html(txtHTML);
	$("#consultlist").dialog({title: "Med. Consultations Summary", width: xWidth, height: 540,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewConsultForm(trans_id,pid) {
	var txtHTML = "<iframe id='frmEMRX' frameborder=0 width='100%' height='100%' src='consultform.php?transid="+trans_id+"&pid="+pid+"&sid="+Math.random()+"'></iframe>";
	$("#consultdetails").html(txtHTML);
	$("#consultdetails").dialog({title: "Consultation Summary", width: 1300, height: 720,resizable: true }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function showMedClearance() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='medclearance.list.php'></iframe>";
	$("#consultlist").html(txtHTML);
	$("#consultlist").dialog({title: "Consultation Summary", width: xWidth, height: 540,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function viewMedClearanceForm(id) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='medclearance.php?id="+id+"&sid="+Math.random()+"'></iframe>";
	$("#consultdetails").html(txtHTML);
	$("#consultdetails").dialog({title: "Consultation Summary", width: 1024, height: 720,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : false,
	    "minimizable" : true
	});
}

function autoSavePrescription() {
	var dataString = $('#frmEMRX').contents().find('#frmEMR').serialize();
		dataString = "mod=savePrescription&" + dataString;
	
		$.ajax({
			type: "POST",
			url: "consult.datacontrol.php",
			data: dataString,
			success: function() {
				popSaver();
			}
		});
}

function printMedCertX(transid,pid,sono) {
	window.open("print/medcert.php?transid="+transid+"&pid="+pid+"&sono="+sono+"&sid="+Math.random()+"","Medical Certificate","location=1,status=1,scrollbars=1,width=640,height=720");
}

function printRXX(transid,pid,sono) {
	window.open("print/rxform.php?transid="+transid+"&pid="+pid+"&sono="+sono+"&sid="+Math.random()+"","Medical Certificate","location=1,status=1,scrollbars=1,width=640,height=720");
}

function printLabReq(transid,pid,sono) {
	window.open("print/rxlabrequest.php?transid="+transid+"&pid="+pid+"&sono="+sono+"&sid="+Math.random()+"","Doctors Lab Request","location=1,status=1,scrollbars=1,width=640,height=720");
}

/**** Lab Functions */

function showServiceInfo(id) {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='service.details.php?id="+id+"&mod=1&sid="+Math.random()+"'></iframe>";
	$("#itemdetails").html(txtHTML);
	$("#itemdetails").dialog({title: "Service Details", width: 1120, height: 520, resizable: false }).dialogExtend({
		"closable" : true,
		"maximizable" : false,
		"minimizable" : true
	});
}

function showResults() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='results.list.php?sid="+Math.random()+"'></iframe>";
	$("#polist").html(txtHTML);
	$("#polist").dialog({title: "Results & Releasing", width: xWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showSamples() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='samples.list.php?sid="+Math.random()+"'></iframe>";
	$("#srrlist").html(txtHTML);
	$("#srrlist").dialog({title: "Manage Lab Samples", width: xWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function showValidation() {
	var txtHTML = "<iframe id='frmcust' frameborder=0 width='100%' height='100%' src='validation.php?sid="+Math.random()+"'></iframe>";
	$("#srrlist").html(txtHTML);
	$("#srrlist").dialog({title: "Validate Lab Results", width: xWidth, height: 500,resizable: false }).dialogExtend({
		"closable" : true,
	    "maximizable" : true,
	    "minimizable" : true
	});
}

function writeResult(lid,code) {
	switch(code) {
		case "L046":
		case "L084":	
		case "L043":
		case "L039":
		case "L136":	
		case "L007":
		case "L071":
		case "L086":
		case "L099":
		case "L100":
		case "L041":
		case "L146":
		case "L033":
		case "L034":	
		case "L101":
		case "L062":
		case "L206":
			enumResult(lid,code);
		break;
		case "L087":
		case "L015":
		case "L096":
		case "L096":
		case "L193":
		case "L170":
			antigenResult(lid,code);
		break;
		case "L079":
		case "L147":
			dengueResult(lid,code);
		break;
		case "L010":
		case "L204":
		case "L210":
			cbcResult(lid,code);
		break;
		case "L011":
			bloodChem(lid,code);
		break;
		case "L012":
		case "L205":
		case "O153":
			uaResult(lid,code);
		break;
		case "L013":
		case "L211":
			stoolExam(lid,code);
		break;
		case "L014":
			semenAnalysis(lid,code);
		break;
		case "L063":
		case "L064":
			pregnancyResult(lid,code);
		break;
		case "L052":
		case "L053":
			bloodTyping(lid,code);
		break;
		case "L051":
		case "L056":
			bleedclotResult(lid,code);
		break;
		case "L082":
			hivResult(lid,code);
		break;
		default:
			singleValueResult(lid,code);
		break;
	}
}

function validateResult(lid,code) {
	switch(code) {
		case "L046":
		case "L084":	
		case "L043":
		case "L039":	
		case "L136":	
		case "L007":
		case "L071":
		case "L086":
		case "L099":
		case "L100":
		case "L041":
		case "L146":
		case "L033":
		case "L034":	
		case "L101":
		case "L062":
		case "L206":
			validateEnumResult(lid,code);
		break;
		case "L087":
		case "L015":
		case "L096":
		case "L193":
		case "L170":
			validateAntigenResult(lid,code);
		break;
		case "L079":
		case "L147":
			validateDengueResult(lid,code);
		break;
		case "L010":
		case "L204":
		case "L210":
			validateCbcResult(lid,code);
		break;
		case "L011":
			validateBloodChem(lid,code);
		break;
		case "L012":
		case "L205":
		case "O153":
			validateUaResult(lid,code);
		break;
		case "L013":
		case "L211":
			validateStoolExam(lid,code);
		break;
		case "L014":
			validateSemenAnalysis(lid,code);
		break;
		case "L063":
		case "L064":
			validatePregnancyResult(lid,code);
		break;
		case "L052":
		case "L053":
			validateBloodtype(lid,code);
		break;
		case "L051":
		case "L056":
			validateBleedclotResult(lid,code);
		break;
		case "L082":
			validateHivResult(lid,code);
		break;
		case "O001":
		case "O156":
			validateECGResult(lid,code);
		break;
		default:
			validateSingleValueResult(lid,code);
		break;
	}
}

function printResult(code,so_no,serialno) {
	let xCode = code.substring(0,1);
	if(xCode == 'X') {
		var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.xray.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
	} else {
		switch(code) {

			case "L010":
			case "L204":
			case "L210":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.cbc.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L011":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.bloodchem.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L012":
			case "L205":
			case "O153":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.ua.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L013":
			case "L211":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.stool.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L014":
				semenAnalysis(lid,code);
			break;
			case "L046":	
			case "L084":	
			case "L043":
			case "L039":
			case "L136":	
			case "L007":
			case "L071":
			case "L086":
			case "L100":
			case "L132":
			case "L041":
			case "L146":
			case "L033":
			case "L034":	
			case "L101":
			case "L062":
			case "L206":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.enum.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L087":
			case "L015":
			case "L096":
			case "L193":
			case "L170":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.antigen.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			// case "L041":
			// 	var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.reactives.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			// break;

			case "L063":
			case "L064":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.pt.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L052":
			case "L053":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.bt.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L079":
			case "L147":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.dengue.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L051":
			case "L056":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.bleedclot.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "L082":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.hiv.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			case "O001":
			case "O156":
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.ecg.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
			default:
				var txtHTML = "<iframe id='printResult' frameborder=0 width='100%' height='100%' src='print/result.singlevalue.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
			break;
		}
	}
	
	$("#report3").html(txtHTML);
	$("#report3").dialog({title: "Print Result", width: 560, height: 620, resizable: true }).dialogExtend({
		"closable" : true,
		"maximizable" : true,
		"minimizable" : true
	});

}

function writeImagingResult(lid,code) {
	$("#descResult").html("<iframe id='frmResult' frameborder=0 width='100%' height='100%' src='result.descriptive.php?lid="+lid+"'></iframe>");
	$("#descResult").dialog({
		title: "Write Result",
		width: 1024,
		height: 695,
		resizeable: false,
		modal: false
	});
}

function singleValueResult(lid,code) {

	$("#sresult_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "labSingle",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#sresult_sono").val(data['myso']);
			$("#sresult_sodate").val(data['sodate']);
			$("#sresult_pid").val(data['mypid']);
			$("#sresult_pname").val(data['pname']);
			$("#sresult_gender").val(data['gender']);
			$("#sresult_birthdate").val(data['bday']);
			$("#sresult_age").val(data['age']);
			$("#sresult_patientstat").val(data['patientstatus']);
			$("#sresult_physician").val(data['physician']);
			$("#sresult_procedure").val(data['procedure']);
			$("#sresult_code").val(data['code']);
			$("#sresult_spectype").val(data['sampletype']);
			$("#sresult_serialno").val(data['serialno']);
			$("#sresult_extractdate").val(data['exday']);
			$("#sresult_extracttime").val(data['etime']);
			$("#sresult_by").val(data['extractby']);
			$("#sresult_location").val(data['location']);
			$("#sresult_attribute").val(data['attribute']);
			$("#sresult_unit").val(data['unit']);
			$("#sresult_value").val(data['value']);
			$("#sresult_remarks").val(data['remarks']);

			var dis = $("#singleValueResult").dialog({
				title: "Write Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
						
							
							if($("#sresult_value").val() == '') {
								parent.sendErrorMessage("Result Value is empty!");
							} else {
								if(confirm("Are you sure you want save this data?") == true) {
									var dataString = $("#frmsingleValue").serialize();
										dataString = "mod=saveSingleValueResult&" + dataString;
										$.ajax({
											type: "POST",
											url: "src/sjerp.php",
											data: dataString,
											success: function() {
											alert("Result Successfully Saved!");
											dis.dialog("close");
											$("#singleValueResult").trigger("reset");
										}
									});
								}
							}

						}
					},
					{
						text: "Print Result",
						icons: { primary: "ui-icon-print" },
						click: function() {
							
							var so_no = $("#sresult_sono").val();
							var code = $("#sresult_code").val();
							var serialno = $("#sresult_serialno").val();

							var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.singlevalue.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
							$("#report5").html(txtHTML);
							$("#report5").dialog({title: "Result - "+ $("#sresult_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
								"closable" : true,
								"maximizable" : true,
								"minimizable" : true
							});

						 }
					},
					{
						text: "Close",
						icons: { primary: "ui-icon-closethick" },
						click: function() { $(this).dialog("close"); $("#frmsingleValue").trigger("reset"); }
					}
				]
			});

		},"json"
	);
}

function validateSingleValueResult(lid,code) {

	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "labSingle",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#sresult_sono").val(data['myso']);
			$("#sresult_sodate").val(data['sodate']);
			$("#sresult_pid").val(data['mypid']);
			$("#sresult_pname").val(data['pname']);
			$("#sresult_gender").val(data['gender']);
			$("#sresult_birthdate").val(data['bday']);
			$("#sresult_age").val(data['age']);
			$("#sresult_patientstat").val(data['patientstatus']);
			$("#sresult_physician").val(data['physician']);
			$("#sresult_procedure").val(data['procedure']);
			$("#sresult_code").val(data['code']);
			$("#sresult_spectype").val(data['sampletype']);
			$("#sresult_serialno").val(data['serialno']);
			$("#sresult_extractdate").val(data['exday']);
			$("#sresult_extracttime").val(data['etime']);
			$("#sresult_by").val(data['extractby']);
			$("#sresult_location").val(data['location']);
			$("#sresult_attribute").val(data['attribute']);
			$("#sresult_unit").val(data['unit']);
			$("#sresult_value").val(data['value']);
			$("#sresult_remarks").val(data['remarks']);

			var dis = $("#singleValueResult").dialog({
				title: "Validate Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Changes & Mark as Validated",
						icons: { primary: "ui-icon-check" },
						click: function() {
						
							
							if($("#sresult_value").val() == '') {
								parent.sendErrorMessage("Result Value is empty!");
							} else {
								if(confirm("Are you sure you want save this data?") == true) {
									var dataString = $("#frmsingleValue").serialize();
										dataString = "mod=validateSingleValueResult&" + dataString;
										$.ajax({
											type: "POST",
											url: "src/sjerp.php",
											data: dataString,
											success: function() {
											alert("Result Successfully Marked as Validated!");
											dis.dialog("close");
											showValidation();
										}
									});
								}
							}

						}
					},
					{
						text: "Print Result",
						icons: { primary: "ui-icon-print" },
						click: function() {
							
							var so_no = $("#sresult_sono").val();
							var code = $("#sresult_code").val();
							var serialno = $("#sresult_serialno").val();

							var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.singlevalue.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
							$("#report5").html(txtHTML);
							$("#report5").dialog({title: "Result - "+ $("#sresult_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
								"closable" : true,
								"maximizable" : true,
								"minimizable" : true
							});

						 }
					},
					{
						text: "Close",
						icons: { primary: "ui-icon-closethick" },
						click: function() { $(this).dialog("close"); $("#frmsingleValue").trigger("reset"); }
					}
				]
			});

		},"json"
	);
}

function enumResult(lid,code) {

	$("#enum_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "enumResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#enum_sono").val(data['myso']);
			$("#enum_sodate").val(data['sodate']);
			$("#enum_pid").val(data['mypid']);
			$("#enum_pname").val(data['pname']);
			$("#enum_gender").val(data['gender']);
			$("#enum_birthdate").val(data['bday']);
			$("#enum_age").val(data['age']);
			$("#enum_patientstat").val(data['patientstatus']);
			$("#enum_physician").val(data['physician']);
			$("#enum_procedure").val(data['procedure']);
			$("#enum_code").val(data['code']);
			$("#enum_spectype").val(data['sampletype']);
			$("#enum_serialno").val(data['serialno']);
			$("#enum_method").val(data['method']);
			$("#enum_testkit").val(data['testkit']);
			$("#enum_testkit_lotno").val(data['lotno']);
			$("#enum_testkit_expiry").val(data['expiry']);
			$("#enum_extractdate").val(data['exday']);
			$("#enum_extracttime").val(data['etime']);
			$("#enum_extractby").val(data['extractby']);
			$("#enum_result").val(data['result']);
			$("#enum_result_by").val(data['performed_by']);
			$("#enum_remarks").val(data['remarks']);

			var dis = $("#enumResult").dialog({
				title: "Write Result",
				width: 1024,
				height: 690,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $("#frmEnumResult").serialize();
								dataString = "mod=saveEnumResult&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Saved!");
										$("#frmEnumResult").trigger("reset");
										dis.dialog("close");
										showValidation();
									}
								});
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

function validateEnumResult(lid,code) {
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "enumResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#enum_sono").val(data['myso']);
			$("#enum_sodate").val(data['sodate']);
			$("#enum_pid").val(data['mypid']);
			$("#enum_pname").val(data['pname']);
			$("#enum_gender").val(data['gender']);
			$("#enum_birthdate").val(data['bday']);
			$("#enum_age").val(data['age']);
			$("#enum_patientstat").val(data['patient_stat']);
			$("#enum_physician").val(data['physician']);
			$("#enum_procedure").val(data['procedure']);
			$("#enum_code").val(data['code']);
			$("#enum_spectype").val(data['sampletype']);
			$("#enum_serialno").val(data['serialno']);
			$("#enum_method").val(data['method']);
			$("#enum_testkit").val(data['testkit']);
			$("#enum_testkit_lotno").val(data['lotno']);
			$("#enum_testkit_expiry").val(data['expiry']);
			$("#enum_extractdate").val(data['exday']);
			$("#enum_extracttime").val(data['etime']);
			$("#enum_extractby").val(data['extractby']);
			$("#enum_result").val(data['result']);
			$("#enum_result_by").val(data['performed_by']);
			$("#enum_remarks").val(data['remarks']);

			var dis = $("#enumResult").dialog({
				title: "Validate Result",
				width: 1024,
				height: 690,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Changes & Mark as Validated",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $("#frmEnumResult").serialize();
								dataString = "mod=validateEnumResult&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Validated!");
										showValidation();
										dis.dialog("close");
									}
								});
							}
						}
					},
					{
						text: "Print Result",
						icons: { primary: "ui-icon-print" },
						click: function() {
							
							var so_no = $("#sresult_sono").val();
							var code = $("#sresult_code").val();
							var serialno = $("#sresult_serialno").val();

							var txtHTML = "<iframe id='printSingleValue' frameborder=0 width='100%' height='100%' src='print/result.enum.php?so_no="+$("#enum_sono").val()+"&code="+$("#enum_code").val()+"&serialno="+$("#enum_serialno").val()+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
							$("#report5").html(txtHTML);
							$("#report5").dialog({title: "Result - "+ $("#enum_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
								"closable" : true,
								"maximizable" : true,
								"minimizable" : true
							});

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

function antigenResult(lid,code) {

	$("#antigen_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "antigenResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#antigen_sono").val(data['myso']);
			$("#antigen_sodate").val(data['sodate']);
			$("#antigen_pid").val(data['mypid']);
			$("#antigen_pname").val(data['pname']);
			$("#antigen_gender").val(data['gender']);
			$("#antigen_birthdate").val(data['bday']);
			$("#antigen_age").val(data['age']);
			$("#antigen_patientstat").val(data['patientstatus']);
			$("#antigen_physician").val(data['physician']);
			$("#antigen_procedure").val(data['procedure']);
			$("#antigen_code").val(data['code']);
			$("#antigen_spectype").val(data['sampletype']);
			$("#antigen_serialno").val(data['serialno']);
			$("#antigen_testkit").val(data['testkit']);
			$("#antigen_testkit_lotno").val(data['lotno']);
			$("#antigen_testkit_expiry").val(data['expiry']);
			$("#antigen_extractdate").val(data['exday']);
			$("#antigen_extracttime").val(data['etime']);
			$("#antigen_extractby").val(data['extractby']);
			$("#antigen_result").val(data['result']);
			$("#antigen_result_by").val(data['performed_by']);
			$("#antigen_remarks").val(data['remarks']);

			var dis = $("#antigenResult").dialog({
				title: "Write Result",
				width: 1040,
				height: 690,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
						
								if(confirm("Are you sure you want save this data?") == true) {
									var dataString = $("#frmAntigenResult").serialize();
										dataString = "mod=saveAntigenResult&" + dataString;
										$.ajax({
											type: "POST",
											url: "src/sjerp.php",
											data: dataString,
											success: function() {
												alert("Result Successfully Saved!");
												dis.dialog("close");
												$("#frmAntigenResult").trigger("reset");
										}
									});
								}
							}
					},
					{
						text: "Close",
						icons: { primary: "ui-icon-closethick" },
						click: function() { $(this).dialog("close"); $("#frmAntigenResult").trigger("reset"); }
					}
				]
			});

		},"json"
	);
}

function validateAntigenResult(lid,code) {

	$("#antigen_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "antigenResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#antigen_sono").val(data['myso']);
			$("#antigen_sodate").val(data['sodate']);
			$("#antigen_pid").val(data['mypid']);
			$("#antigen_pname").val(data['pname']);
			$("#antigen_gender").val(data['gender']);
			$("#antigen_birthdate").val(data['bday']);
			$("#antigen_age").val(data['age']);
			$("#antigen_patientstat").val(data['patientstatus']);
			$("#antigen_physician").val(data['physician']);
			$("#antigen_procedure").val(data['procedure']);
			$("#antigen_code").val(data['code']);
			$("#antigen_spectype").val(data['sampletype']);
			$("#antigen_serialno").val(data['serialno']);
			$("#antigen_testkit").val(data['testkit']);
			$("#antigen_testkit_lotno").val(data['lotno']);
			$("#antigen_testkit_expiry").val(data['expiry']);
			$("#antigen_extractdate").val(data['exday']);
			$("#antigen_extracttime").val(data['etime']);
			$("#antigen_extractby").val(data['extractby']);
			$("#antigen_result").val(data['result']);
			$("#antigen_result_by").val(data['performed_by']);
			$("#antigen_remarks").val(data['remarks']);

			var dis = $("#antigenResult").dialog({
				title: "Validate Antigen Result",
				width: 1040,
				height: 690,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Changes & Mark as Validated",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want confirm and validate this result?") == true) {
							var dataString = $("#frmAntigenResult").serialize();
								dataString = "mod=validateAntigenResult&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Validated!");
										$("#frmAntigenResult").trigger("reset");
										dis.dialog("close");
										showValidation();
				
									}
								});
							}
						}
					},
					{
						text: "Print Result",
						icons: { primary: "ui-icon-print" },
						click: function() {
							
							var so_no = $("#antigen_sono").val();
							var serialno = $("#antigen_serialno").val();
							var code = $("#antigen_code").val();
							
							var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.antigen.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
							$("#report10").html(txtHTML);
							$("#report10").dialog({title: "Print - ANTIGEN Result", width: 560, height: 620, resizable: true }).dialogExtend({
								"closable" : true,
								"maximizable" : true,
								"minimizable" : true
							});
		
		
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

function hivResult(lid,code) {

	$("#hiv_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "hivResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#hiv_sono").val(data['myso']);
			$("#hiv_sodate").val(data['sodate']);
			$("#hiv_pid").val(data['mypid']);
			$("#hiv_pname").val(data['pname']);
			$("#hiv_gender").val(data['gender']);
			$("#hiv_birthdate").val(data['bday']);
			$("#hiv_age").val(data['age']);
			$("#hiv_patientstat").val(data['patientstatus']);
			$("#hiv_physician").val(data['physician']);
			$("#hiv_procedure").val(data['procedure']);
			$("#hiv_code").val(data['code']);
			$("#hiv_spectype").val(data['sampletype']);
			$("#hiv_serialno").val(data['serialno']);
			$("#hiv_method").val(data['method']);
			$("#hiv_testkit").val(data['testkit']);
			$("#hiv_testkit_lotno").val(data['lotno']);
			$("#hiv_testkit_expiry").val(data['expiry']);
			$("#hiv_extractdate").val(data['exday']);
			$("#hiv_extracttime").val(data['etime']);
			$("#hiv_extractby").val(data['extractby']);
			$("#hiv_result").val(data['result']);
			$("#hiv_result_by").val(data['performed_by']);
			$("#hiv_remarks").val(data['remarks']);

			var dis = $("#frmHivResult").dialog({
				title: "Write Result",
				width: 1024,
				height: 690,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $("#frmHivResult").serialize();
								dataString = "mod=saveHivResult&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Saved!");
										$("#frmHivResult").trigger("reset");
										dis.dialog("close");
										showValidation();
									}
								});
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

function validateHivResult(lid,code) {
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "hivResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#hiv_sono").val(data['myso']);
			$("#hiv_sodate").val(data['sodate']);
			$("#hiv_pid").val(data['mypid']);
			$("#hiv_pname").val(data['pname']);
			$("#hiv_gender").val(data['gender']);
			$("#hiv_birthdate").val(data['bday']);
			$("#hiv_age").val(data['age']);
			$("#hiv_patientstat").val(data['patientstatus']);
			$("#hiv_physician").val(data['physician']);
			$("#hiv_procedure").val(data['procedure']);
			$("#hiv_code").val(data['code']);
			$("#hiv_spectype").val(data['sampletype']);
			$("#hiv_serialno").val(data['serialno']);
			$("#hiv_method").val(data['method']);
			$("#hiv_testkit").val(data['testkit']);
			$("#hiv_testkit_lotno").val(data['lotno']);
			$("#hiv_testkit_expiry").val(data['expiry']);
			$("#hiv_extractdate").val(data['exday']);
			$("#hiv_extracttime").val(data['etime']);
			$("#hiv_extractby").val(data['extractby']);
			$("#hiv_result").val(data['result']);
			$("#hiv_result_by").val(data['performed_by']);
			$("#hiv_remarks").val(data['remarks']);

			var dis = $("#hivResult").dialog({
				title: "Validate Result",
				width: 1024,
				height: 690,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Changes & Mark as Validated",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $("#frmHivResult").serialize();
								dataString = "mod=validateHivResult&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Validated!");
										showValidation();
										dis.dialog("close");
									}
								});
							}
						}
					},
					{
						text: "Print Result",
						icons: { primary: "ui-icon-print" },
						click: function() {
							
							var so_no = $("#hiv_sono").val();
							var serialno = $("#hiv_serialno").val();
							var code = $("#hiv_code").val();
		
							var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.hiv.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
							$("#report3").html(txtHTML);
							$("#report3").dialog({title: "Print - HIV RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
								"closable" : true,
								"maximizable" : true,
								"minimizable" : true
							});
		
		
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

function cbcResult(lid,code) {

	$("#cbcResult").html("<iframe id='frmCbcResult' frameborder=0 width='100%' height='100%' src='result.cbc.php?lid="+lid+"'></iframe>");
	
	var dis = $("#cbcResult").dialog({
		title: "Write Result",
		width: 1024,
		height: 690,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var msg = '';
						
						if($('#frmCbcResult').contents().find("#wbc").val() == '' ) { msg = msg + "- Invalid or Empty Value for <b>WBC</b> count<br/>"; }
						if($('#frmCbcResult').contents().find("#rbc").val() == '' ) { msg = msg + "- Invalid or Empty Value for <b>RBC</b> count<br/>"; }
						if($('#frmCbcResult').contents().find("#hemoglobin").val() == '' ) { msg = msg + "- Invalid or Empty Value for <b>Hemoglobin</b> count<br/>"; }
						if($('#frmCbcResult').contents().find("#hematocrit").val() == '' ) { msg = msg + "- Invalid or Empty Value for <b>Hematocrit</b> count<br/>"; }
						if($('#frmCbcResult').contents().find("#platelate").val() == '' ) { msg = msg + "- Invalid or Empty Value for <b>Platelate</b> count<br/>"; }

						var totalDifferential = parseFloat($('#frmCbcResult').contents().find("#neutrophils").val()) + parseFloat($('#frmCbcResult').contents().find("#lymphocytes").val()) + parseFloat($('#frmCbcResult').contents().find("#monocytes").val()) + parseFloat($('#frmCbcResult').contents().find("#eosinophils").val()) + parseFloat($('#frmCbcResult').contents().find("#basophils").val());

						if(totalDifferential != 100) { msg = msg + "- <b>Total Differential Count</b> != <b>100%</b><br/>"; }


						if(msg != '') {
							parent.sendErrorMessage(msg);
						} else {
							var dataString = $('#frmCbcResult').contents().find('#frmCBCResult').serialize();
							dataString = "mod=saveCBCResult&" + dataString;
							$.ajax({
								type: "POST",
								url: "src/sjerp.php",
								data: dataString,
								success: function() {
									alert("Result Successfully Saved!");
									dis.dialog("close");
									$("#frmCBCResult").trigger("reset");
								}
							});
						}
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var so_no = $('#frmCbcResult').contents().find('#cbc_sono').val();
					var serialno = $('#frmCbcResult').contents().find('#cbc_serialno').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.cbc.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report3").html(txtHTML);
					$("#report3").dialog({title: "Print - CBC RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function validateCbcResult(lid,code) {

	$("#cbcResult").html("<iframe id='frmCbcResult' frameborder=0 width='100%' height='100%' src='result.cbc.php?lid="+lid+"'></iframe>");
	
	var dis = $("#cbcResult").dialog({
		title: "Validate Result",
		width: 1024,
		height: 690,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Changes & Mark Result as Validated",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmCbcResult').contents().find('#frmCBCResult').serialize();
					
						//var dataString = $("#frmDescResult").serialize();
						dataString = "mod=validateCBCResult&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Marked as Validated!");
								showValidation();
								dis.dialog("close");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var so_no = $('#frmCbcResult').contents().find('#cbc_sono').val();
					var serialno = $('#frmCbcResult').contents().find('#cbc_serialno').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.cbc.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report3").html(txtHTML);
					$("#report3").dialog({title: "Print - CBC RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function bloodChem(lid,code) {
	
	$("#bloodChemResult").html("<iframe id='frmBloodChem' frameborder=0 width='100%' height='100%' src='result.bloodchem.php?lid="+lid+"'></iframe>");
	
	$("#bloodChemResult").dialog({
		title: "Write Result",
		width: 1024,
		height: 920,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmBloodChem').contents().find('#frmBloodChemResult').serialize();
						dataString = "mod=saveBloodChem&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved!");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var so_no = $('#frmBloodChem').contents().find('#bloodchem_sono').val();
					var serialno = $('#frmBloodChem').contents().find('#bloodchem_serialno').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.bloodchem.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report3").html(txtHTML);
					$("#report3").dialog({title: "Print - BLOOD CHEMISTRY RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function validateBloodChem(lid,code) {
	
	$("#bloodChemResult").html("<iframe id='frmBloodChem' frameborder=0 width='100%' height='100%' src='result.bloodchem.php?lid="+lid+"'></iframe>");
	
	var dis = $("#bloodChemResult").dialog({
		title: "Write Result",
		width: 1024,
		height: 920,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Changes & Mark Result as Validated",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmBloodChem').contents().find('#frmBloodChemResult').serialize();
						dataString = "mod=validateBloodChem&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Marked as Validated!");
								showValidation();
								dis.dialog("close");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var so_no = $('#frmBloodChem').contents().find('#bloodchem_sono').val();
					var serialno = $('#frmBloodChem').contents().find('#bloodchem_serialno').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.bloodchem.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report3").html(txtHTML);
					$("#report3").dialog({title: "Print - BLOOD CHEMISTRY RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

/* Blood Chem Consolidated */
function writeChemistryResult(so_no) {
	
	$("#bloodChemResult").html("<iframe id='frmBloodChem' frameborder=0 width='100%' height='100%' src='result.bloodchem.conso.php?so_no="+so_no+"'></iframe>");
	
	$("#bloodChemResult").dialog({
		title: "Write Result",
		width: 1024,
		height: 920,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmBloodChem').contents().find('#frmBloodChemResult').serialize();
						dataString = "mod=saveBloodChem&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved!");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var so_no = $('#frmBloodChem').contents().find('#bloodchem_sono').val();
					var serialno = $('#frmBloodChem').contents().find('#bloodchem_serialno').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.bloodchem.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report3").html(txtHTML);
					$("#report3").dialog({title: "Print - BLOOD CHEMISTRY RESULT", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}


function uaResult(lid,code) {
	
	$("#uaResult").html("<iframe id='frmUA' frameborder=0 width='100%' height='100%' src='result.ua.php?lid="+lid+"'></iframe>");
	
	$("#uaResult").dialog({
		title: "Write Result",
		width: 1024,
		height: 720,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmUA').contents().find('#frmUrinalysisReport').serialize();
						dataString = "mod=saveUAReport&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved!");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var so_no = $('#frmUA').contents().find('#ua_sono').val();
					var serialno = $('#frmUA').contents().find('#ua_serialno').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.ua.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report1").html(txtHTML);
					$("#report1").dialog({title: "Print - Uranilysis (UA)", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function validateUaResult(lid,code) {
	
	$("#uaResult").html("<iframe id='frmUA' frameborder=0 width='100%' height='100%' src='result.ua.php?lid="+lid+"'></iframe>");
	
	var dis = $("#uaResult").dialog({
		title: "Validate Result",
		width: 1024,
		height: 720,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Changes & Mark Resullt as Validated",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmUA').contents().find('#frmUrinalysisReport').serialize();
						dataString = "mod=validateUAReport&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Marked as Validated!");
								showValidation();
								dis.dialog("close");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var so_no = $('#frmUA').contents().find('#ua_sono').val();
					var serialno = $('#frmUA').contents().find('#ua_serialno').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.ua.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report1").html(txtHTML);
					$("#report1").dialog({title: "Print - Uranilysis (UA)", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}


function stoolExam(lid,code) {
	
	$("#stoolResult").html("<iframe id='frmStoolExam' frameborder=0 width='100%' height='100%' src='result.stool.php?lid="+lid+"'></iframe>");
	
	$("#stoolResult").dialog({
		title: "Write Result",
		width: 1024,
		height: 680,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmStoolExam').contents().find('#frmStoolReport').serialize();
						dataString = "mod=saveStoolExam&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved!");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var so_no = $('#frmStoolExam').contents().find('#stool_sono').val();
					var serialno = $('#frmStoolExam').contents().find('#stool_serialno').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.stool.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report2").html(txtHTML);
					$("#report2").dialog({title: "Print - Stool Exam", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function validateStoolExam(lid,code) {
	
	$("#stoolResult").html("<iframe id='frmStoolExam' frameborder=0 width='100%' height='100%' src='result.stool.php?lid="+lid+"'></iframe>");
	
	var dis = $("#stoolResult").dialog({
		title: "Write Result",
		width: 1024,
		height: 680,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Confirm & Validate Result",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want confirm and publish this result?") == true) {
						var dataString = $('#frmStoolExam').contents().find('#frmStoolReport').serialize();
						dataString = "mod=validateStoolExam&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Marked as Validated!");
								showValidation();
								dis.dialog("close");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var so_no = $('#frmStoolExam').contents().find('#stool_sono').val();
					var serialno = $('#frmStoolExam').contents().find('#stool_serialno').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.stool.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report2").html(txtHTML);
					$("#report2").dialog({title: "Print - Stool Exam", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function semenAnalysis(lid,code) {
	
	$("#semAnalReport").html("<iframe id='frmSemenAnalysis' frameborder=0 width='100%' height='100%' src='result.sar.php?lid="+lid+"'></iframe>");
	
	$("#stoolResult").dialog({
		title: "Write Result",
		width: 1024,
		height: 680,
		resizeable: false,
		modal: true,
		buttons: [
			{
				text: "Save Result Pending Validation",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmSemenAnalysis').contents().find('#frmSemenAnalysisReport').serialize();
						dataString = "mod=saveSemenAnalysis&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved!");
							}
						});
					}
				}
			},
			{
				text: "Print Result",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var so_no = $('#frmSemenAnalysis').contents().find('#semen_sono').val();
					var serialno = $('#frmSemenAnalysis').contents().find('#semen_serialno').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.sar.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report2").html(txtHTML);
					$("#report2").dialog({title: "Print - Stool Exam", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function pregnancyResult(lid,code) {

	$("#pt_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "enumResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#pt_sono").val(data['myso']);
			$("#pt_sodate").val(data['sodate']);
			$("#pt_pid").val(data['mypid']);
			$("#pt_pname").val(data['pname']);
			$("#pt_gender").val(data['gender']);
			$("#pt_birthdate").val(data['bday']);
			$("#pt_age").val(data['age']);
			$("#pt_patientstat").val(data['patientstatus']);
			$("#pt_physician").val(data['physician']);
			$("#pt_procedure").val(data['procedure']);
			$("#pt_code").val(data['code']);
			$("#pt_spectype").val(data['sampletype']);
			$("#pt_serialno").val(data['serialno']);
			$("#pt_extractdate").val(data['exday']);
			$("#pt_extracttime").val(data['etime']);
			$("#pt_extractby").val(data['extractby']);
			$("#pt_result").val(data['result']);
			$("#pt_remarks").val(data['remarks']);

			var dis = $("#pregnancyResult").dialog({
				title: "Write Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $("#frmPregnancyResult").serialize();
								dataString = "mod=savePregnancyResult&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Saved!");
										$("#frmPregnancyResult").trigger("reset");
										dis.dialog("close");
										showValidation();
				
									}
								});
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

function validatePregnancyResult(lid,code) {
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "enumResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#pt_sono").val(data['myso']);
			$("#pt_sodate").val(data['sodate']);
			$("#pt_pid").val(data['mypid']);
			$("#pt_pname").val(data['pname']);
			$("#pt_gender").val(data['gender']);
			$("#pt_birthdate").val(data['bday']);
			$("#pt_age").val(data['age']);
			$("#pt_patientstat").val(data['patientstatus']);
			$("#pt_physician").val(data['physician']);
			$("#pt_procedure").val(data['procedure']);
			$("#pt_code").val(data['code']);
			$("#pt_spectype").val(data['sampletype']);
			$("#pt_serialno").val(data['serialno']);
			$("#pt_extractdate").val(data['exday']);
			$("#pt_extracttime").val(data['etime']);
			$("#pt_extractby").val(data['extractby']);
			$("#pt_result").val(data['result']);
			$("#pt_remarks").val(data['remarks']);

			var dis = $("#pregnancyResult").dialog({
				title: "Validate Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save & Confirm Result",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want confirm and publish this result?") == true) {
							var dataString = $("#frmPregnancyResult").serialize();
								dataString = "mod=validatePregnancyResult&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Confirmed & Published");
										showValidation();
										dis.dialog("close");
									}
								});
							}
						}
					},
					{
						text: "Print Result",
						icons: { primary: "ui-icon-print" },
						click: function() {
							
							var so_no = $("#sresult_sono").val();
							var code = $("#sresult_code").val();
							var serialno = $("#sresult_serialno").val();

							var txtHTML = "<iframe id='printPregnancyResult' frameborder=0 width='100%' height='100%' src='print/result.pt.php?so_no="+$("#pt_sono").val()+"&code="+$("#pt_code").val()+"&serialno="+$("#pt_serialno").val()+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
							$("#report5").html(txtHTML);
							$("#report5").dialog({title: "Result - "+ $("#pt_procedure").val() +"", width: 560, height: 620, resizable: true }).dialogExtend({
								"closable" : true,
								"maximizable" : true,
								"minimizable" : true
							});

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

function bloodTyping(lid,code) {

	$("#btype_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "bloodType",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#btype_sono").val(data['myso']);
			$("#btype_sodate").val(data['sodate']);
			$("#btype_pid").val(data['mypid']);
			$("#btype_pname").val(data['pname']);
			$("#btype_gender").val(data['gender']);
			$("#btype_birthdate").val(data['bday']);
			$("#btype_age").val(data['age']);
			$("#btype_patientstat").val(data['patientstatus']);
			$("#btype_physician").val(data['physician']);
			$("#btype_procedure").val(data['procedure']);
			$("#btype_code").val(data['code']);
			$("#btype_spectype").val(data['sampletype']);
			$("#btype_serialno").val(data['serialno']);
			$("#btype_extractdate").val(data['exday']);
			$("#btype_extracttime").val(data['etime']);
			$("#btype_extractby").val(data['extractby']);
			$("#btype_result").val(data['result']);
			$("#btype_rh").val(data['rh']);
			$("#btype_result_by").val(data['performed_by']);
			$("#btype_remarks").val(data['remarks']);

			var dis = $("#bloodtypeResult").dialog({
				title: "Write Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $("#frmBloodType").serialize();
								dataString = "mod=saveBloodType&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Saved!");
										$("#frmBloodType").trigger("reset");
										dis.dialog("close");
										showValidation();
									}
								});
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

function validateBloodtype(lid,code) {

	$("#btype_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "bloodType",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#btype_sono").val(data['myso']);
			$("#btype_sodate").val(data['sodate']);
			$("#btype_pid").val(data['mypid']);
			$("#btype_pname").val(data['pname']);
			$("#btype_gender").val(data['gender']);
			$("#btype_birthdate").val(data['bday']);
			$("#btype_age").val(data['age']);
			$("#btype_patientstat").val(data['patientstatus']);
			$("#btype_physician").val(data['physician']);
			$("#btype_procedure").val(data['procedure']);
			$("#btype_code").val(data['code']);
			$("#btype_spectype").val(data['sampletype']);
			$("#btype_serialno").val(data['serialno']);
			$("#btype_extractdate").val(data['exday']);
			$("#btype_extracttime").val(data['etime']);
			$("#btype_extractby").val(data['extractby']);
			$("#btype_result").val(data['result']);
			$("#btype_rh").val(data['rh']);
			$("#btype_result_by").val(data['performed_by']);
			$("#btype_remarks").val(data['remarks']);

			var dis = $("#bloodtypeResult").dialog({
				title: "Write Result",
				width: 540,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save & Confirm Result",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want confirm and publish this result?") == true) {
							var dataString = $("#frmBloodType").serialize();
								dataString = "mod=validateBloodType&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Saved!");
										$("#frmBloodType").trigger("reset");
										dis.dialog("close");
										showValidation();
									}
								});
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

function dengueResult(lid,code) {

	$("#sresult_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "dengueResultView",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#dengue_sono").val(data['myso']);
			$("#dengue_sodate").val(data['sodate']);
			$("#dengue_pid").val(data['mypid']);
			$("#dengue_pname").val(data['pname']);
			$("#dengue_gender").val(data['gender']);
			$("#dengue_birthdate").val(data['bday']);
			$("#dengue_age").val(data['age']);
			$("#dengue_patientstat").val(data['patientstatus']);
			$("#dengue_physician").val(data['physician']);
			$("#dengue_procedure").val(data['procedure']);
			$("#dengue_code").val(data['code']);
			$("#dengue_spectype").val(data['sampletype']);
			$("#dengue_serialno").val(data['serialno']);
			$("#dengue_extractdate").val(data['exday']);
			$("#dengue_extracttime").val(data['etime']);
			$("#dengue_extractby").val(data['extractby']);
			$("#dengue_method").val(data['method']);
			$("#dengue_testkit").val(data['testkit']);
			$("#dengue_testkit_lotno").val(data['lotno']);
			$("#dengue_testkit_expiry").val(data['expiry']);
			$("#dengue_ag").val(data['dengue_ag']);
			$("#dengue_igg").val(data['dengue_igg']);
			$("#dengue_igm").val(data['dengue_igm']);
			$("#dengue_result_by").val(data['performed_by']);
			$("#dengue_remarks").val(data['remarks']);

			var dis = $("#dengueResult").dialog({
				title: "Write Result",
				width: 1040,
				height: 690,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
						
								if(confirm("Are you sure you want save this data?") == true) {
									var dataString = $("#frmDengueResult").serialize();
										dataString = "mod=saveDengueResult&" + dataString;
										$.ajax({
											type: "POST",
											url: "src/sjerp.php",
											data: dataString,
											success: function() {
												alert("Result Successfully Saved!");
												dis.dialog("close");
												$("#frmDengueResult").trigger("reset");
										}
									});
								}
							}
					},
					{
						text: "Close",
						icons: { primary: "ui-icon-closethick" },
						click: function() { $(this).dialog("close"); $("#frmsingleValue").trigger("reset"); }
					}
				]
			});

		},"json"
	);
}

function validateDengueResult(lid,code) {

	$("#dengue_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "dengueResultView",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#dengue_sono").val(data['myso']);
			$("#dengue_sodate").val(data['sodate']);
			$("#dengue_pid").val(data['mypid']);
			$("#dengue_pname").val(data['pname']);
			$("#dengue_gender").val(data['gender']);
			$("#dengue_birthdate").val(data['bday']);
			$("#dengue_age").val(data['age']);
			$("#dengue_patientstat").val(data['patientstatus']);
			$("#dengue_physician").val(data['physician']);
			$("#dengue_procedure").val(data['procedure']);
			$("#dengue_code").val(data['code']);
			$("#dengue_spectype").val(data['sampletype']);
			$("#dengue_serialno").val(data['serialno']);
			$("#dengue_extractdate").val(data['exday']);
			$("#dengue_extracttime").val(data['etime']);
			$("#dengue_extractby").val(data['extractby']);
			$("#dengue_method").val(data['method']);
			$("#dengue_testkit").val(data['testkit']);
			$("#dengue_testkit_lotno").val(data['lotno']);
			$("#dengue_testkit_expiry").val(data['expiry']);
			$("#dengue_ag").val(data['dengue_ag']);
			$("#dengue_igg").val(data['dengue_igg']);
			$("#dengue_igm").val(data['dengue_igm']);
			$("#dengue_result_by").val(data['performed_by']);
			$("#dengue_remarks").val(data['remarks']);

			var dis = $("#dengueResult").dialog({
				title: "Validate Dengue Result",
				width: 1040,
				height: 690,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Changes & Mark as Validated",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want confirm and validate this result?") == true) {
							var dataString = $("#frmDengueResult").serialize();
								dataString = "mod=validateDengueResult&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Validated!");
										$("#frmDengueResult").trigger("reset");
										dis.dialog("close");
										showValidation();
				
									}
								});
							}
						}
					},
					{
						text: "Print Result",
						icons: { primary: "ui-icon-print" },
						click: function() {
							
							var so_no = $("#dengue_sono").val();
							var serialno = $("#dengue_serialno").val();
							var code = $("#dengue_code").val();
							
							var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.dengue.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
							$("#report10").html(txtHTML);
							$("#report10").dialog({title: "Print - Dengue Result", width: 560, height: 620, resizable: true }).dialogExtend({
								"closable" : true,
								"maximizable" : true,
								"minimizable" : true
							});
		
		
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

function collectVitals(so_no,pid) {
	$("#pemeresult").html("<iframe id='frmPEME' frameborder=0 width='100%' height='100%' src='result.peme.php?so_no="+so_no+"&pid="+pid+"&sid="+Math.random()+"'></iframe>");
	$("#pemeresult").dialog({
		title: "Physical/Medical Examination Form",
		width: xWidth,
		height: 720,
		resizeable: false,
		modal: false,
		buttons: [
			{
				text: "Save Data",
				icons: { primary: "ui-icon-check" },
				click: function() {
					var msg = '';

					if(confirm("Are you sure you want save this data?") == true) {
						var dataString = $('#frmPEME').contents().find('#frmVitals').serialize();
						dataString = "mod=saveVitals&" + dataString;
						$.ajax({
							type: "POST",
							url: "src/sjerp.php",
							data: dataString,
							success: function() {
								alert("Result Successfully Saved!");
							}
						});
					}
				}
			},
			{
				text: "Attach Patient's Signature",
				icons: { primary: "ui-icon-pencil" },
				click: function() {
					
					document.getElementById('frmPEME').contentWindow.captureMySignature();

				}
			},
			{
				text: "Capture Photo",
				icons: { primary: "ui-icon-contact" },
				click: function() {

					$("#cameraFrame").html("<iframe id='frmCamera' frameborder=0 width='100%' height='100%' src='maniniyot.php?so_no="+so_no+"&pid="+pid+"&sid="+Math.random()+"'></iframe>");


					$("#cameraFrame").dialog({
						title: "Capture Photo",
						width: 400,
						height: 420,
						resizeable: false,
						modal: false
					}); 
				}
			},
			{
				text: "Print Form",
				icons: { primary: "ui-icon-print" },
				click: function() {
					
					var so_no = $('#frmPEME').contents().find('#pe_sono').val();
					var pid = $('#frmPEME').contents().find('#pe_pid').val();

					var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.peme.php?so_no="+so_no+"&pid="+pid+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
					$("#report1").html(txtHTML);
					$("#report1").dialog({title: "Print - Physical/Medical Examinition Form", width: 560, height: 620, resizable: true }).dialogExtend({
						"closable" : true,
						"maximizable" : true,
						"minimizable" : true
					});


				 }
			},
			{
				text: "Close",
				icons: { primary: "ui-icon-closethick" },
				click: function() { $(this).dialog("close"); }
			}
		]
	});
}

function bleedclotResult(lid,code) {

	$("#bleed_clot_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "bleedclotResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#bleed_clot_sono").val(data['myso']);
			$("#bleed_clot_sodate").val(data['sodate']);
			$("#bleed_clot_pid").val(data['mypid']);
			$("#bleed_clot_pname").val(data['pname']);
			$("#bleed_clot_gender").val(data['gender']);
			$("#bleed_clot_birthdate").val(data['bday']);
			$("#bleed_clot_age").val(data['age']);
			$("#bleed_clot_patientstat").val(data['patientstatus']);
			$("#bleed_clot_physician").val(data['physician']);
			$("#bleed_clot_procedure").val(data['procedure']);
			$("#bleed_clot_code").val(data['code']);
			$("#bleed_clot_spectype").val(data['sampletype']);
			$("#bleed_clot_serialno").val(data['serialno']);
			$("#bleed_clot_extractdate").val(data['exday']);
			$("#bleed_clot_extracttime").val(data['etime']);
			$("#bleed_clot_extractby").val(data['extractby']);
			$("#bleed_clot_testkit").val(data['testkit']);
			$("#bleed_clot_testkit_lotno").val(data['lotno']);
			$("#bleed_clot_testkit_expiry").val(data['expiry']);
			$("#bleed_mins").val(data['bleed_min']);
			$("#bleed_secs").val(data['bleed_sec']);
			$("#clot_mins").val(data['clot_min']);
			$("#clot_secs").val(data['clot_sec']);
			$("#bleed_clot_result_by").val(data['performed_by']);
			$("#bleed_clot_remarks").val(data['remarks']);

			var dis = $("#bleedclotResult").dialog({
				title: "Write Result",
				width: 1024,
				height: 690,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Result Pending Validation",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want save this data?") == true) {
							var dataString = $("#frmBleedClot").serialize();
								dataString = "mod=saveBleedClot&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Saved!");
										$("#frmBleedClot").trigger("reset");
										dis.dialog("close");
									}
								});
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

function validateBleedclotResult(lid,code) {

	$("#bleed_clot_date").datepicker();
	
	$.post("src/sjerp.php", {
		mod: "resultSingle",
		submod: "bleedclotResult",
		lid: lid,
		sid: Math.random() },
		function(data) {

			$("#bleed_clot_sono").val(data['myso']);
			$("#bleed_clot_sodate").val(data['sodate']);
			$("#bleed_clot_pid").val(data['mypid']);
			$("#bleed_clot_pname").val(data['pname']);
			$("#bleed_clot_gender").val(data['gender']);
			$("#bleed_clot_birthdate").val(data['bday']);
			$("#bleed_clot_age").val(data['age']);
			$("#bleed_clot_patientstat").val(data['patientstatus']);
			$("#bleed_clot_physician").val(data['physician']);
			$("#bleed_clot_procedure").val(data['procedure']);
			$("#bleed_clot_code").val(data['code']);
			$("#bleed_clot_spectype").val(data['sampletype']);
			$("#bleed_clot_serialno").val(data['serialno']);
			$("#bleed_clot_extractdate").val(data['exday']);
			$("#bleed_clot_extracttime").val(data['etime']);
			$("#bleed_clot_extractby").val(data['extractby']);
			$("#bleed_clot_testkit").val(data['testkit']);
			$("#bleed_clot_testkit_lotno").val(data['lotno']);
			$("#bleed_clot_testkit_expiry").val(data['expiry']);
			$("#bleed_mins").val(data['bleed_min']);
			$("#bleed_secs").val(data['bleed_sec']);
			$("#clot_mins").val(data['clot_min']);
			$("#clot_secs").val(data['clot_sec']);
			$("#bleed_clot_result_by").val(data['performed_by']);
			$("#bleed_clot_remarks").val(data['remarks']);

			var dis = $("#bleedclotResult").dialog({
				title: "Write Result",
				width: 1024,
				height: 690,
				resizeable: false,
				modal: true,
				buttons: [
					{
						text: "Save Changes & Mark as Validated",
						icons: { primary: "ui-icon-check" },
						click: function() {
							var msg = '';
	
							if(confirm("Are you sure you want confirm and publish this result?") == true) {
							var dataString = $("#frmBleedClot").serialize();
								dataString = "mod=validateBleedClot&" + dataString;
								$.ajax({
									type: "POST",
									url: "src/sjerp.php",
									data: dataString,
									success: function() {
										alert("Result Successfully Validated!");
										$("#frmBleedClot").trigger("reset");
										dis.dialog("close");
										showValidation();
									}
								});
							}
						}
					},
					{
						text: "Print Result",
						icons: { primary: "ui-icon-print" },
						click: function() {
							
							var so_no = $("#bleed_clot_sono").val();
							var serialno = $("#bleed_clot_serialno").val();
							var code = $("#bleed_clot_code").val();
							
							var txtHTML = "<iframe id='prntXrayResult' frameborder=0 width='100%' height='100%' src='print/result.bleedclot.php?so_no="+so_no+"&code="+code+"&serialno="+serialno+"&sid="+Math.random()+"&sid="+Math.random()+"'></iframe>";
							$("#report10").html(txtHTML);
							$("#report10").dialog({title: "Print - Bleeding & Clotting Time Result", width: 560, height: 620, resizable: true }).dialogExtend({
								"closable" : true,
								"maximizable" : true,
								"minimizable" : true
							});
		
		
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

function showLabSummary() {
	$("#census_dtf").datepicker(); $("#census_dt2").datepicker();
	$("#censusReport").dialog({
		title: "Summary of Performed Test", 
		width: 480,
		modal: true,
		resizable: false,
		buttons: {
			"Generate Report": function() {
				var type = $("#census_type").val();
				if(type == 1) {
					window.open("export/census_summary.php?category="+$("#census_category").val()+"&dtf="+$("#census_dtf").val()+"&dt2="+$("#census_dt2").val()+"&sid="+Math.random()+"","Summary of Performed Tests","location=1,status=1,scrollbars=1,width=640,height=720");
				} else {
					window.open("export/census_detailed.php?category="+$("#census_category").val()+"&dtf="+$("#census_dtf").val()+"&dt2="+$("#census_dt2").val()+"&sid="+Math.random()+"","Summary of Performed Tests","location=1,status=1,scrollbars=1,width=640,height=720");
				}
			},
			"Close": function() {
				$(this).dialog("close");
			}
		} 
	});
}