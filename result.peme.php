<?php 
	
	session_start();
	require_once "handlers/_generics.php";
	
    $o = new _init;
   
    $a = $o->getArray("select * from patient_info where patient_id = '$_REQUEST[pid]';");
    $b = $o->getArray("select * from peme where so_no = '$_REQUEST[so_no]' and pid = '$_REQUEST[pid]';");
    $c = $o->getArray("SELECT pid,examined_by,DATE_FORMAT(examined_on,'%m/%d/%Y') AS examin_d8,TIME_FORMAT(examined_on,'%h:%m:%s') AS examin_tym,pre_examined_by,DATE_FORMAT(pre_examined_on,'%d/%m/%Y') AS pre_d8, TIME_FORMAT(pre_examined_on,'%h:%m:%s') AS pre_tym FROM peme WHERE so_no = '$_REQUEST[so_no]' and pid = '$_REQUEST[pid]';");
    $d = $o->getArray("SELECT LPAD(prio,6,0) AS prio, LPAD(so_no,6,0) AS so, DATE_FORMAT(so_date,'%m/%d/%Y') AS sodate, `code`, `procedure`, CONCAT(b.lname,', ',b.fname,', ',b.mname) AS pname, b.gender, DATE_FORMAT(b.birthdate,'%m/%d/%Y') AS bday,  FLOOR(DATEDIFF(so_date,b.birthdate)/364.25) AS age, compname, a.status, a.so_date,prio AS `priority`,b.birthdate, a.pid, CONCAT(c.fullname,', ',c.prefix) AS ex_by, CONCAT(d.fullname,', ',d.prefix) AS pre_by FROM peme a LEFT JOIN patient_info b ON a.pid = b.patient_id LEFT JOIN options_doctors c ON a.examined_by = c.id LEFT JOIN options_doctors d ON a.pre_examined_by = d.id where so_no = '$_REQUEST[so_no]' and pid = '$_REQUEST[pid]';");


    $pmh = explode(",",$b['pm_history']);

    list($brgy) = $o->getArray("SELECT brgyDesc FROM options_brgy WHERE brgyCode = '$a[brgy]';");
    list($ct) = $o->getArray("SELECT citymunDesc FROM options_cities WHERE cityMunCode = '$a[city]';");
    list($prov) = $o->getArray("SELECT provDesc FROM options_provinces WHERE provCode = '$a[province]';");

    if($a['street'] != '') { $myaddress.=$a['street'].", "; }
    if($brgy != "") { $myaddress .= $brgy.", "; }
    if($ct != "") { $myaddress .= $ct.", "; }
    if($prov != "")  { $myaddress .= $prov.", "; }
    $myaddress = substr($myaddress,0,-2);

    list($cstat) = $o->getArray("select civil_status from options_civilstatus where csid = '$a[cstat]';");

    if($c['examined_by'] != '') {
        list($docfullname,$docprefix,$doclicenseno) = $o->getArray("SELECT concat(fullname,',') as fullname, concat(prefix, ' &raquo;') as prefix, license_no FROM options_doctors WHERE id = '$c[examined_by]';");
    }

	// if($c['evaluated_by'] != '') {
    //     list($doctorfullname,$doctorprefix,$doctorlicenseno) = $o->getArray("SELECT concat(fullname,',') as fullname, concat(prefix, ' &raquo;') as prefix, license_no FROM options_doctors WHERE id = '$c[evaluated_by]';");
    // }

?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Primecare Mobile Laboratory System Ver. 1.0b</title>
	<link rel="icon" type="image/x-icon" href="favicon.ico" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
    <link href="ui-assets/wacom/wacom-buttons.css" rel="stylesheet" type="text/css" />

	<script language="javascript" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script language="javascript" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script language="javascript" src="js/main.js?sid=<?php echo uniqid(); ?>"></script>

    <script type="text/javascript" src="ui-assets/wacom/BigInt.js"></script>
    <script type="text/javascript" src="ui-assets/wacom/wacom-encryption.js"></script>
    <script src="ui-assets/wacom/q.js" charset="UTF-8"></script>
    <script src="ui-assets/wacom/wgssStuSdk.js" charset="UTF-8"></script>

    <script>
        $(function() { 
            $("#cbc_date").datepicker();
            $("#pe_date").datepicker(); 

            var peResultCollection = [
            "YES",
            "NO",
            "OCCASIONAL",
            ];

            var pePregnantCollection = [
            "YES",
            "NO",
            ];

            
            $("#pe_pregnant").autocomplete({
                source: pePregnantCollection, minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });


            $("#pe_smoker,#pe_alcoholic,#pe_drugs").autocomplete({
                source: peResultCollection, minLength: 0
            }).focus(function() {
                $(this).data("uiAutocomplete").search($(this).val());
            });
                    
        });

        $(document).on('keydown', 'input[pattern]', function(e){
            var input = $(this);
            var oldVal = input.val();
            var regex = new RegExp(input.attr('pattern'), 'g');

            setTimeout(function(){
                var newVal = input.val();
                if(!regex.test(newVal)){
                input.val(oldVal); 
                }
            }, 1);
        });

        function calculateBMI() {
            var ht = parseFloat($("#pe_ht").val()) / 100;
            var wt = parseFloat($("#pe_wt").val());
            

            if(ht>0 && wt>0) {
                var bmi = wt / (ht*ht);
                    bmi = bmi.toFixed(2);
                $("#pe_bmi").val(bmi);
            }

        }

        function checkResult(code) {
            $.post("src/sjerp.php",{ mod: "checkPEResult", so_no: $("#pe_sono").val(), pid: $("#pe_pid").val(), code: code, sid: Math.random() }, function(data) {
                if(data['lid']) {
                    parent.printResult(code,$("#pe_sono").val(),data['serialno'],data['lid']);
                }
            },"json");
        }

        function checkXray(code) {
            $.post("src/sjerp.php",{ mod: "checkPEResult", so_no: $("#pe_sono").val(), pid: $("#pe_pid").val(), code: code, sid: Math.random() }, function(data) {
                if(data['lid']) {
                    parent.printResult(code,$("#pe_sono").val(),data['serialno'],data['lid']);
                }
            },"json");
        }

        function checkEcg(code) {
            $.post("src/sjerp.php",{ mod: "checkPEResult", so_no: $("#pe_sono").val(), pid: $("#pe_pid").val(), code: code, sid: Math.random() }, function(data) {
                if(data['lid']) {
                    parent.printResult(code,$("#pe_sono").val(),data['serialno'],data['lid']);
                }
            },"json");
        }

        function openAttachment(code) {
            $.post("src/sjerp.php",{ mod: "openAttachment", so_no: $("#pe_sono").val(), pid: $("#pe_pid").val(), code: code, sid: Math.random() }, function(data) {
                
                if(data.length > 0) {
                
                    $("#fileLocation").html(data);
                    var dis = $("#imageAttachment").dialog({
                    title: "Image File Attachment",
                    width: 640,
                    height: 640,
                    resizeable: false,
                    modal: true,
                    buttons: [
                            {
                                text: "Close",
                                icons: { primary: "ui-icon-closethick" },
                                click: function() { $(this).dialog("close"); }
                            }
                        ]
                    });
               
                 } else {
                    parent.sendErrorMessage("It appears that there is no file associated or attached to this test yet.."); 
                }

            },"html");
        }

        function marknormal() {
            $("#pe_sa_normal").val('Y');
            $("#pe_hs_normal").val('Y');
            $("#pe_mouth_normal").val('Y');
            $("#pe_neck_normal").val('Y');
            $("#pe_lungs_normal").val('Y');
            $("#pe_heart_normal").val('Y');
            $("#pe_check_normal").val('Y');
            $("#pe_abdomen_normal").val('Y');
            $("#pe_ref_normal").val('Y');
            $("#pe_extr_normal").val('Y');
            $("#pe_ee_normal").val('Y');
            $("#pe_nose_normal").val('Y');
            $("#pe_genitals_normal").val('Y');
            $("#pe_bpe_normal").val('Y');
            $("#pe_rect_normal").val('Y');

        }


        /* WACOM SIGNATURE FUNCTION ON THIS AREA */

        var m_btns; // The array of buttons that we are emulating.
        var m_clickBtn = -1;
        var intf;
        var formDiv;
        var protocol;
        var m_usbDevices;
        var tablet;
        var m_capability;
        var m_inkThreshold;
        var m_imgData;
        var m_encodingMode;
        var ctx;
        var canvas;
        var modalBackground;
        var formDiv;
        var m_penData;
        var lastPoint;
        var isDown;

        var retry = 0;
        function checkForSigCaptX() {
            // Establishing a connection to SigCaptX Web Service can take a few seconds,
            // particularly if the browser itself is still loading/initialising
            // or on a slower machine.
            retry = retry + 1;
            if (WacomGSS.STU.isServiceReady()) {
                retry = 0;
                console.log("SigCaptX Web Service: ready");
            } else {
                console.log("SigCaptX Web Service: not connected");
                if (retry < 20) {
                    setTimeout(checkForSigCaptX, 1000);
                }
                else {
                    console.log("Unable to establish connection to SigCaptX");
                }
            }

        }

        setTimeout(checkForSigCaptX, 500);

        function onDCAtimeout() {
            // Device Control App has timed-out and shut down
            // For this sample, we just closedown tabletDemo (assumking it's running)
            console.log("DCA disconnected");
            setTimeout(close, 0);
        }

        function Rectangle(x, y, width, height) {
            this.x = x;
            this.y = y;
            this.width = width;
            this.height = height;

            this.Contains = function (pt) {
                if (((pt.x >= this.x) && (pt.x <= (this.x + this.width))) &&
                ((pt.y >= this.y) && (pt.y <= (this.y + this.height)))) {
                return true;
                } else {
                return false;
                }
            }
        }

        // In order to simulate buttons, we have our own Button class that stores the bounds and event handler.
        // Using an array of these makes it easy to add or remove buttons as desired.
        //  delegate void ButtonClick();
        function Button() {
        this.Bounds; // in Screen coordinates
        this.Text;
        this.Click;
        };

        function Point(x, y) {
        this.x = x;
        this.y = y;
        }


        function createModalWindow(width, height) {
            modalBackground = document.createElement('div');
            modalBackground.id = "modal-background";
            modalBackground.className = "active";
            modalBackground.style.width = window.innerWidth;
            modalBackground.style.height = window.innerHeight;
            document.getElementsByTagName('body')[0].appendChild(modalBackground);

            formDiv = document.createElement('div');
            formDiv.id = "signatureWindow";
            formDiv.className = "active";
            formDiv.style.top = (window.innerHeight / 2) - (height / 2) + "px";
            formDiv.style.left = (window.innerWidth / 2) - (width / 2) + "px";
            formDiv.style.width = width + "px";
            formDiv.style.height = height + "px";
            document.getElementsByTagName('body')[0].appendChild(formDiv);

            canvas = document.createElement("canvas");
            canvas.id = "myCanvas";
            canvas.height = formDiv.offsetHeight;
            canvas.width = formDiv.offsetWidth;
            formDiv.appendChild(canvas);
            ctx = canvas.getContext("2d");

            if (canvas.addEventListener) {
                canvas.addEventListener("click", onCanvasClick, false);
            } else if (canvas.attachEvent) {
                canvas.attachEvent("onClick", onCanvasClick);
            } else {
                canvas["onClick"] = onCanvasClick;
            }
        }

        function disconnect() {
            var deferred = Q.defer();
            if (!(undefined === tablet || null === tablet)) {
                var p = new WacomGSS.STU.Protocol();
                tablet.setInkingMode(p.InkingMode.InkingMode_Off)
                .then(function (message) {
                    console.log("received: " + JSON.stringify(message));
                    return tablet.endCapture();
                })
                .then(function (message) {
                    console.log("received: " + JSON.stringify(message));
                    if (m_imgData !== null) {
                    return m_imgData.remove();
                    }
                    else {
                    return message;
                    }
                })
                .then(function (message) {
                    console.log("received: " + JSON.stringify(message));
                    m_imgData = null;
                    return tablet.setClearScreen();
                })
                .then(function (message) {
                    console.log("received: " + JSON.stringify(message));
                    return tablet.disconnect();
                })
                .then(function (message) {
                    console.log("received: " + JSON.stringify(message));
                    tablet = null;
                    // clear canvas
                    clearCanvas(canvas, ctx);
                })
                .then(function (message) {
                    deferred.resolve();
                })
                .fail(function (message) {
                    console.log("disconnect error: " + message);
                    deferred.resolve();
                })
            } else {
                deferred.resolve();
            }
            return deferred.promise;
        }

        window.addEventListener("beforeunload", function (e) {
        var confirmationMessage = "";
        WacomGSS.STU.close();
        (e || window.event).returnValue = confirmationMessage; // Gecko + IE
        return confirmationMessage;                            // Webkit, Safari, Chrome
        });

        // Error-derived object for Device Control App not ready exception
        function DCANotReady() { }
        DCANotReady.prototype = new Error();

        function captureMySignature() {
            var p = new WacomGSS.STU.Protocol();
            var intf;
            var m_usingEncryption = false;
            var m_encH;
            var m_encH2;
            var m_encH2Impl;

            WacomGSS.STU.isDCAReady()
            .then(function (message) {
                if (!message) {
                throw new DCANotReady();
                }
                // Set handler for Device Control App timeout
                WacomGSS.STU.onDCAtimeout = onDCAtimeout;

                return WacomGSS.STU.getUsbDevices();
            })
            .then(function (message) {
                if (message == null || message.length == 0) {
                throw new Error("No STU devices found");
                }
                console.log("received: " + JSON.stringify(message));
                m_usbDevices = message;
                return WacomGSS.STU.isSupportedUsbDevice(m_usbDevices[0].idVendor, m_usbDevices[0].idProduct);
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                intf = new WacomGSS.STU.UsbInterface();
                return intf.Constructor();
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                return intf.connect(m_usbDevices[0], true);
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                console.log(0 == message.value ? "connected!" : "not connected");
                if (0 == message.value) {
                m_encH = new WacomGSS.STU.EncryptionHandler(new encryptionHandler());
                return m_encH.Constructor();
                }
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                m_encH2Impl = new encryptionHandler2();
                m_encH2 = new WacomGSS.STU.EncryptionHandler2(m_encH2Impl);
                return m_encH2.Constructor();
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                tablet = new WacomGSS.STU.Tablet();
                return tablet.Constructor(intf, m_encH, m_encH2);
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                intf = null;
                return tablet.getInkThreshold();
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                m_inkThreshold = message;
                return tablet.getCapability();
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                m_capability = message;
                createModalWindow(m_capability.screenWidth, m_capability.screenHeight);
                return tablet.getInformation();
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                return tablet.getInkThreshold();
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                return tablet.getProductId();
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                return WacomGSS.STU.ProtocolHelper.simulateEncodingFlag(message, m_capability.encodingFlag);
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                var encodingFlag = message;
                if ((encodingFlag & p.EncodingFlag.EncodingFlag_24bit) != 0) {
                return tablet.supportsWrite()
                    .then(function (message) {
                    m_encodingMode = message ? p.EncodingMode.EncodingMode_24bit_Bulk : p.EncodingMode.EncodingMode_24bit;
                    });
                } else if ((encodingFlag & p.EncodingFlag.EncodingFlag_16bit) != 0) {
                return tablet.supportsWrite()
                    .then(function (message) {
                    m_encodingMode = message ? p.EncodingMode.EncodingMode_16bit_Bulk : p.EncodingMode.EncodingMode_16bit;
                    });
                } else { // assumes 1bit is available
                m_encodingMode = p.EncodingMode.EncodingMode_1bit;
                }
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                return tablet.isSupported(p.ReportId.ReportId_EncryptionStatus); // v2 encryption
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                m_usingEncryption = message;
                // if the encryption script is missing turn off encryption regardless
                if (typeof window.sjcl == 'undefined') {
                console.log("sjcl not found - encryption disabled");
                m_usingEncryption = false;
                }
                return tablet.getDHprime();
            })
            .then(function (dhPrime) {
                console.log("received: " + JSON.stringify(dhPrime));
                return WacomGSS.STU.ProtocolHelper.supportsEncryption_DHprime(dhPrime); // v1 encryption
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                m_usingEncryption = (message ? true : m_usingEncryption);
                return tablet.setClearScreen();
            })
            .then(function (message) {
                if (m_usingEncryption) {
                return tablet.startCapture(0xc0ffee);
                }
                else {
                return message;
                }
            })
            .then(function (message) {
                if (typeof m_encH2Impl.error !== 'undefined') {
                throw new Error("Encryption failed, restarting demo");
                }
                return message;
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                return tablet.isSupported(p.ReportId.ReportId_PenDataOptionMode);
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                if (message) {
                return tablet.getProductId()
                    .then(function (message) {
                    var penDataOptionMode = p.PenDataOptionMode.PenDataOptionMode_None;
                    switch (message) {
                        case WacomGSS.STU.ProductId.ProductId_520A:
                        penDataOptionMode = p.PenDataOptionMode.PenDataOptionMode_TimeCount;
                        break;
                        case WacomGSS.STU.ProductId.ProductId_430:
                        case WacomGSS.STU.ProductId.ProductId_530:
                        penDataOptionMode = p.PenDataOptionMode.PenDataOptionMode_TimeCountSequence;
                        break;
                        default:
                        console.log("Unknown tablet supporting PenDataOptionMode, setting to None.");
                    };
                    return tablet.setPenDataOptionMode(penDataOptionMode);
                    });
                }
                else {
                m_encodingMode = p.EncodingMode.EncodingMode_1bit;
                return m_encodingMode;
                }
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                addButtons();
                var canvasImage = canvas.toDataURL("image/jpeg");
                return WacomGSS.STU.ProtocolHelper.resizeAndFlatten
                (
                canvasImage,
                0,
                0,
                0,
                0,
                m_capability.screenWidth,
                m_capability.screenHeight,
                m_encodingMode,
                1,
                false,
                0,
                true
                );
            })
            .then(function (message) {
                m_imgData = message;
                console.log("received: " + JSON.stringify(message));
                return tablet.writeImage(m_encodingMode, message);
            })
            .then(function (message) {
                if (m_encH2Impl.error) {
                throw new Error("Encryption failed, restarting demo");
                }
                return message;
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                return tablet.setInkingMode(p.InkingMode.InkingMode_On);
            })
            .then(function (message) {
                console.log("received: " + JSON.stringify(message));
                var reportHandler = new WacomGSS.STU.ProtocolHelper.ReportHandler();
                lastPoint = { "x": 0, "y": 0 };
                isDown = false;
                ctx.lineWidth = 1;

                var penData = function (report) {
                //console.log("report: " + JSON.stringify(report));
                m_penData.push(report);
                processButtons(report, canvas);
                processPoint(report, canvas, ctx);
                }
                var penDataEncryptedOption = function (report) {
                //console.log("reportOp: " + JSON.stringify(report));
                m_penData.push(report.penData[0], report.penData[1]);
                processButtons(report.penData[0], canvas);
                processPoint(report.penData[0], canvas, ctx);
                processButtons(report.penData[1], canvas);
                processPoint(report.penData[1], canvas, ctx);
                }

                var log = function (report) {
                //console.log("report: " + JSON.stringify(report));
                }

                var decrypted = function (report) {
                //console.log("decrypted: " + JSON.stringify(report));
                }
                m_penData = new Array();
                reportHandler.onReportPenData = penData;
                reportHandler.onReportPenDataOption = penData;
                reportHandler.onReportPenDataTimeCountSequence = penData;
                reportHandler.onReportPenDataEncrypted = penDataEncryptedOption;
                reportHandler.onReportPenDataEncryptedOption = penDataEncryptedOption;
                reportHandler.onReportPenDataTimeCountSequenceEncrypted = penData;
                reportHandler.onReportDevicePublicKey = log;
                reportHandler.onReportEncryptionStatus = log;
                reportHandler.decrypt = decrypted;
                return reportHandler.startReporting(tablet, true);
            })
            .fail( function(ex) {
                console.log(ex);

                if (ex instanceof DCANotReady) {
                // Device Control App not detected 
                // Reinitialize and re-try
                WacomGSS.STU.Reinitialize();
                setTimeout(tabletDemo, 1000);
                }
                else {
                // Some other error - Inform the user and closedown 
                alert("tabletDemo failed:\n" + ex);
                setTimeout(close(), 0);
                }
            });
            }

            function addButtons() {
                m_btns = new Array(3);
                m_btns[0] = new Button();
                m_btns[1] = new Button();
                m_btns[2] = new Button();

                if (m_usbDevices[0].idProduct != WacomGSS.STU.ProductId.ProductId_300) {
                    // Place the buttons across the bottom of the screen.
                    var w2 = m_capability.screenWidth / 3;
                    var w3 = m_capability.screenWidth / 3;
                    var w1 = m_capability.screenWidth - w2 - w3;
                    var y = m_capability.screenHeight * 6 / 7;
                    var h = m_capability.screenHeight - y;

                    m_btns[0].Bounds = new Rectangle(0, y, w1, h);
                    m_btns[1].Bounds = new Rectangle(w1, y, w2, h);
                    m_btns[2].Bounds = new Rectangle(w1 + w2, y, w3, h);
                } else {
                    // The STU-300 is very shallow, so it is better to utilise
                    // the buttons to the side of the display instead.

                    var x = m_capability.screenWidth * 3 / 4;
                    var w = m_capability.screenWidth - x;

                    var h2 = m_capability.screenHeight / 3;
                    var h3 = m_capability.screenHeight / 3;
                    var h1 = m_capability.screenHeight - h2 - h3;

                    m_btns[0].Bounds = new Rectangle(x, 0, w, h1);
                    m_btns[1].Bounds = new Rectangle(x, h1, w, h2);
                    m_btns[2].Bounds = new Rectangle(x, h1 + h2, w, h3);
                }

                m_btns[0].Text = "OK";
                m_btns[1].Text = "Clear";
                m_btns[2].Text = "Cancel";
                m_btns[0].Click = btnOk_Click;
                m_btns[1].Click = btnClear_Click;
                m_btns[2].Click = btnCancel_Click;
                clearCanvas(canvas, ctx);
                drawButtons();
            }

            function drawButtons() {
            // This application uses the same bitmap for both the screen and client (window).

            ctx.save();
            ctx.setTransform(1, 0, 0, 1, 0, 0);

            ctx.beginPath();
            ctx.lineWidth = 1;
            ctx.strokeStyle = 'black';
            ctx.font = "30px Arial";

            // Draw the buttons
            for (var i = 0; i < m_btns.length; ++i) {
                //if (useColor)
                {
                ctx.fillStyle = "lightgrey";
                ctx.fillRect(m_btns[i].Bounds.x, m_btns[i].Bounds.y, m_btns[i].Bounds.width, m_btns[i].Bounds.height);
                }

                ctx.fillStyle = "black";
                ctx.rect(m_btns[i].Bounds.x, m_btns[i].Bounds.y, m_btns[i].Bounds.width, m_btns[i].Bounds.height);
                var xPos = m_btns[i].Bounds.x + ((m_btns[i].Bounds.width / 2) - (ctx.measureText(m_btns[i].Text).width / 2));
                var yOffset;
                if (m_usbDevices[0].idProduct == WacomGSS.STU.ProductId.ProductId_300)
                yOffset = 28;
                else if (m_usbDevices[0].idProduct == WacomGSS.STU.ProductId.ProductId_430)
                yOffset = 26;
                else
                yOffset = 40;
                ctx.fillText(m_btns[i].Text, xPos, m_btns[i].Bounds.y + yOffset);
            }
            ctx.stroke();
            ctx.closePath();

            ctx.restore();
        }

        function clearScreen() {
            clearCanvas(canvas, ctx);
            drawButtons();
            m_penData = new Array();
            tablet.writeImage(m_encodingMode, m_imgData);
        }

        function btnOk_Click() {
            // You probably want to add additional processing here.
            generateImage();
            setTimeout(close, 0);
        }

        function btnCancel_Click() {
            // You probably want to add additional processing here.
            setTimeout(close, 0);
        }

        function btnClear_Click() {
            // You probably want to add additional processing here.
            console.log("clear!");
            clearScreen();
        }

        function distance(a, b) {
            return Math.pow(a.x - b.x, 2) + Math.pow(a.y - b.y, 2);
        }

        function clearCanvas(in_canvas, in_ctx) {
            in_ctx.save();
            in_ctx.setTransform(1, 0, 0, 1, 0, 0);
            in_ctx.fillStyle = "white";
            in_ctx.fillRect(0, 0, in_canvas.width, in_canvas.height);
            in_ctx.restore();
        }

        function processButtons(point, in_canvas) {
            var nextPoint = {};
            nextPoint.x = Math.round(in_canvas.width * point.x / m_capability.tabletMaxX);
            nextPoint.y = Math.round(in_canvas.height * point.y / m_capability.tabletMaxY);
            var isDown2 = (isDown ? !(point.pressure <= m_inkThreshold.offPressureMark) : (point.pressure > m_inkThreshold.onPressureMark));

            var btn = -1;
            for (var i = 0; i < m_btns.length; ++i) {
                if (m_btns[i].Bounds.Contains(nextPoint)) {
                btn = i;
                break;
                }
            }

            if (isDown && !isDown2) {
                if (btn != -1 && m_clickBtn === btn) {
                m_btns[btn].Click();
                }
                m_clickBtn = -1;
            }
            else if (btn != -1 && !isDown && isDown2) {
                m_clickBtn = btn;
            }
            return (btn == -1);
        }

        function processPoint(point, in_canvas, in_ctx) {
            var nextPoint = {};
            nextPoint.x = Math.round(in_canvas.width * point.x / m_capability.tabletMaxX);
            nextPoint.y = Math.round(in_canvas.height * point.y / m_capability.tabletMaxY);
            var isDown2 = (isDown ? !(point.pressure <= m_inkThreshold.offPressureMark) : (point.pressure > m_inkThreshold.onPressureMark));

            if (!isDown && isDown2) {
                lastPoint = nextPoint;
            }

            if ((isDown2 && 10 < distance(lastPoint, nextPoint)) || (isDown && !isDown2)) {
                in_ctx.beginPath();
                in_ctx.moveTo(lastPoint.x, lastPoint.y);
                in_ctx.lineTo(nextPoint.x, nextPoint.y);
                in_ctx.stroke();
                in_ctx.closePath();
                lastPoint = nextPoint;
            }

            isDown = isDown2;
        }

        function generateImage() {
            //signatureImage = document.getElementById("signatureImage");
            var signatureCanvas = document.createElement("canvas");
            signatureCanvas.id = "signatureCanvas";
           
           // signatureCanvas.height = signatureImage.height;
           // signatureCanvas.width = signatureImage.width;
           
          // signatureCanvas.height = "480px";
          // signatureCanvas.width =  "300px";
           
           
            var signatureCtx = signatureCanvas.getContext("2d");

            clearCanvas(signatureCanvas, signatureCtx);
            signatureCtx.lineWidth = 1;
            signatureCtx.strokeStyle = 'black';
            lastPoint = { "x": 0, "y": 0 };
            isDown = false;
            for (var i = 0; i < m_penData.length; i++) {
                processPoint(m_penData[i], signatureCanvas, signatureCtx);
            }
            //signatureImage.src = signatureCanvas.toDataURL("image/jpeg");
            //alert(signatureCanvas.toDataURL("image/jpeg"));

            $.post("src/sjerp.php", { mod: "saveSignature", jsonSignature: signatureCanvas.toDataURL("image/jpeg"), so_no: $("#pe_sono").val(), pid: $("#pe_pid").val(), sid:Math.random() },function(){ 
                    alert("Signature Successfully Saved!");
                    parent.closeDialog("#signaturepad");

                });



        }

        function close() {
            // Clear handler for Device Control App timeout
            WacomGSS.STU.onDCAtimeout = null;

            disconnect();
            document.getElementsByTagName('body')[0].removeChild(modalBackground);
            document.getElementsByTagName('body')[0].removeChild(formDiv);
        }

        function onCanvasClick(event) {
            // Enable the mouse to click on the simulated buttons that we have displayed.

            // Note that this can add some tricky logic into processing pen data
            // if the pen was down at the time of this click, especially if the pen was logically
            // also 'pressing' a button! This demo however ignores any that.

            var posX = event.pageX - formDiv.offsetLeft;
            var posY = event.pageY - formDiv.offsetTop;

            for (var i = 0; i < m_btns.length; i++) {
                if (m_btns[i].Bounds.Contains(new Point(posX, posY))) {
                m_btns[i].Click();
                break;
                }
            }
        }

    </script>
    <style>

        a {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <form name="frmVitals" id="frmVitals">
        <input type="hidden" name="pe_sono" id="pe_sono" value="<?php echo $_REQUEST['so_no']; ?>">
        <input type="hidden" name="pe_pid" id="pe_pid" value="<?php echo $_REQUEST['pid']; ?>">
		<table width=100% cellpadding=0 cellspacing=0 style="boder-collpase: collapse;">
            <tr><td colspan=8 align=center><img src="images/doc-header.jpg" width=85% height=85% align=absmiddle /></td></tr>
			<tr>
				<td colspan=4 align=center class=bebottom>
					<input type="radio" id="pe_type" name="pe_type" value="APE" <?php if($b['pe_type'] == 'APE') { echo "checked"; } ?>>&nbsp;<span class="spadix-l">Annual Physical Examination</span>

				</td>
				<td colspan=4 align=center class=bebottom>
					<input type="radio" id="pe_type" name="pe_type" value="PE" <?php if($b['pe_type'] == 'PE') { echo "checked"; } ?>>&nbsp;<span class="spadix-l">Pre-Employment Requirements</span>
				</td>
			</tr>
			<tr><td height=4></td></tr>
			<tr>
				<td width=8% class="bebottom" >Last Name :</td>
				<td width=17% class="bebottom">	
					<input type="text" name="pe_lname" id="pe_lname" style="border: none; font-size: 11px; font-weight: bold;" value="<?php echo $a['lname']; ?>">
				</td>
				<td width=8% class="bebottom">First Name :</td>
				<td width=17% class="bebottom">	
					<input type="text" name="pe_fname" id="pe_fname" style="border: none; font-size: 11px; font-weight: bold;" value="<?php echo $a['fname']; ?>">
				</td>
				<td width=8% class="bebottom">Middle Name :</td>
				<td width=17% class="bebottom">	
					<input type="text" name="pe_mname" id="pe_mname" style="border: none; font-size: 11px; font-weight: bold;" value="<?php echo $a['mname']; ?>">
				</td>
				<td width=8% class="bebottom">Date :</td>
				<td width=17% class="bebottom">	
					<input type="text" name="pe_date" id="pe_date" style="border: none; font-size: 11px; font-weight: bold;" value="<?php echo date('m/d/Y'); ?>">
				</td>
			</tr>
            <tr>
				<td class="bebottom" >Address :</td>
				<td class="bebottom">	
					<input type="text" name="pe_address" id="pe_address" style="border: none; font-size: 11px;width: 98%; font-weight: bold;" value="<?php echo $myaddress; ?>">
				</td>
				<td class="bebottom">Age :</td>
				<td class="bebottom">	
                <input type="text" name="pe_age" id="pe_age" style="border: none; font-size: 11px;width: 98%; font-weight: bold;" value="<?php echo $d['age']; ?>">
				</td>
				<td class="bebottom">Civil Status :</td>
				<td class="bebottom">	
					<input type="text" name="pe_cstatus" id="pe_cstatus" style="border: none; font-size: 11px; width: 98%; font-weight: bold;" value="<?php echo $cstat; ?>">
				</td>
				<td class="bebottom">Gender :</td>
				<td class="bebottom">	
					<input type="text" name="pe_gender" id="pe_gender" style="border: none; font-size: 11px; width: 98%; font-weight: bold;" value="<?php echo $a['gender']; ?>">
				</td>
			</tr>
            <tr>
				<td class="bebottom" >Place of Birth :</td>
				<td class="bebottom">	
					<input type="text" name="pe_pob" id="pe_pob" style="border: none; font-size: 11px; width: 98%; font-weight: bold;" value="<?php echo $a['birthplace']; ?>">
				</td>
				<td class="bebottom">Date of Birth :</td>
				<td class="bebottom">	
					<input type="text" name="pe_dob" id="pe_dob" style="border: none; font-size: 11px; width: 98%; font-weight: bold;" value="<?php echo $a['birthdate']; ?>">
				</td>
				<td class="bebottom"></td>
				<td class="bebottom" colspan=3>	
					<!--input type="text" name="pe_insurance" id="pe_insurance" style="border: none; font-size: 11px; width: 98%; font-weight: bold;"-->
				</td>
			</tr>
            <tr>
				<td class="bebottom" >Occupation :</td>
				<td class="bebottom">	
					<input type="text" name="pe_occ" id="pe_occ" style="border: none; font-size: 11px; width: 98%; font-weight: bold;" value="<?php echo $a['occupation']; ?>">
				</td>
				<td class="bebottom">Company :</td>
				<td class="bebottom">	
					<input type="text" name="pe_comp" id="pe_comp" style="border: none; font-size: 11px; width: 98%; font-weight: bold;" value="<?php echo $a['employer']; ?>">
				</td>
				<td class="bebottom">Tel/Mobile # :</td>
				<td class="bebottom" colspan=3>	
					<input type="text" name="pe_contact" id="pe_contact" style="border: none; font-size: 11px; width: 98%; font-weight: bold;" value="<?php echo $b['contactno']; ?>">
				</td>
			</tr>
            <tr><td style="padding-top:10px;"></td></tr>
		</table>
        <table width=100% cellpadding=5><tr><td align=center><span style="font-size: 10pt; font-weight: bold;">PHYSICAL EXAMINATION</span></td></tr></table>
        <table width=100% cellspacing=0 cellpadding=3>
            <tr><td style="padding-top:10px;"></td></tr>
            <tr>
                <td class="spandix-l" align=left>
                    Temp: <input type="text" name="pe_temp" id="pe_temp" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 80px;font-weight: bold;" value="<?php echo $b['temp']; ?>"><sup>0</sup>C&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    PR:  <input type="text" name="pe_pr" id="pe_pr" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 80px;font-weight: bold;" value="<?php echo $b['pulse']; ?>">bpm&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    RR:  <input type="text" name="pe_rr" id="pe_rr" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 80px;font-weight: bold;" value="<?php echo $b['rr']; ?>">bpm&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    BP:  <input type="text" name="pe_bp" id="pe_bp" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 80px;font-weight: bold;" value="<?php echo $b['bp']; ?>">mm/HG&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Ht:  <input type="text" name="pe_ht" id="pe_ht" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 80px;font-weight: bold;" value="<?php echo $b['ht']; ?>" onchange="calculateBMI();">cm&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                    Wt:  <input type="text" name="pe_wt" id="pe_wt" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 80px;font-weight: bold;" value="<?php echo $b['wt']; ?>" onchange="calculateBMI();">kgs    
               </td>
               <td class="spandix-l"><input type=radio name="pe_bmitype" id="pe_bmitype" value="Underweight" <?php if($b['bmi_category'] == 'Underweight') { echo "checked"; } ?>>&nbsp;Underweight</td>
               <td class="spandix-l"><input type=radio name="pe_bmitype" id="pe_bmitype" value="Overweight"  <?php if($b['bmi_category'] == 'Overweight') { echo "checked"; } ?>>&nbsp;Overweight</td>
            </tr>
            <tr>
                <td class="spandix-l" align=left>
                    Visual Acuity: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Right Eye:  <input type="text" name="pe_lefteye" id="pe_lefteye" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 80px;font-weight: bold;" value="<?php echo $b['lefteye']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Left Eye:  <input type="text" name="pe_righteye" id="pe_righteye" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 80px;font-weight: bold;" value="<?php echo $b['righteye']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Jaeger Test: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Right Eye:  <input type="text" name="j_lefteye" id="j_lefteye" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 80px;font-weight: bold;" value="<?php echo $b['jaegerleft']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Left Eye:  <input type="text" name="j_righteye" id="j_righteye" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 80px;font-weight: bold;" value="<?php echo $b['jaegerright']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    BMI:  <input type="text" name="pe_bmi" id="pe_bmi" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 80px;font-weight: bold;" value="<?php echo $b['bmi']; ?>" readonly>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
               </td>
               <td class="spandix-l" ><input type=radio name="pe_bmitype" id="pe_bmitype" value="Normal"  <?php if($b['bmi_category'] == 'Normal') { echo "checked"; } ?>>&nbsp;Normal Weight</td>
               <td class="spandix-l"><input type=radio name="pe_bmitype" id="pe_bmitype" value="Obese"  <?php if($b['bmi_category'] == 'Obese') { echo "checked"; } ?>>&nbsp;Obese</td>
            </tr>
            <tr>                    
               <td class="spandix-l"><input type=radio name="pe_glasses" id="pe_glasses" value="Y"  <?php if($b['with_glasses'] == 'Y') { echo "checked"; } ?>>&nbsp;With glasses</td>
            </tr>                    
        </table>
        <table width=100% cellpadding=5><tr><td align=center><span style="font-size: 10pt; font-weight: bold;">MEDICAL HISTORY</span></td></tr></table>
        <table width=100% cellpadding=3>
            <tr>
                <td width=12% class="spandix-l" valign=top>Past Medical History :</td>
                <td align=right>
                    <table width=100% cellpadding=2 cellspacing=0>
                        <tr>
                            <td width=33% valign=top>
                                <?php
                                    $medh1 = $o->dbquery("select id, history from options_medicalhistory order by id limit 0,10");
                                    while($medh1_row = $medh1->fetch_array()) {
                                        
                                        if(in_array($medh1_row[0], $pmh)) { $sltd = 'checked'; } else { $sltd = ''; }

                                        
                                        echo '<input type="checkbox" name="pe_medhistory[]" id="pe_medhistory[]" value="'.$medh1_row[0].'" '.$sltd.'>&nbsp;&nbsp;<span class="spandix-l">'.$medh1_row[1].'</span><br/>';
                                    }
                                ?>
                            </td>
                                      
                            <td width=33% valign=top>
                                 <?php
                                    $medh2 = $o->dbquery("select id, history from options_medicalhistory order by id limit 10,10");
                                    while($medh2_row = $medh2->fetch_array()) {
                                        if(in_array($medh2_row[0], $pmh)) { $sltd = 'checked'; } else { $sltd = ''; }
                                        echo '<input type="checkbox" name="pe_medhistory[]" id="pe_medhistory[]" value="'.$medh2_row[0].'" '.$sltd.'>&nbsp;&nbsp;<span class="spandix-l">'.$medh2_row[1].'</span><br/>';
                                    }
                                ?>      
                            </td>

                            <td width=33% valign=top>
                                <?php
                                    $medh3 = $o->dbquery("select id, history from options_medicalhistory order by id limit 20,10");
                                    while($medh3_row = $medh3->fetch_array()) {
                                        if(in_array($medh3_row[0], $pmh)) { $sltd = 'checked'; } else { $sltd = ''; }
                                        echo '<input type="checkbox" name="pe_medhistory[]" id="pe_medhistory[]" value="'.$medh3_row[0].'" '.$sltd.'>&nbsp;&nbsp;<span class="spandix-l">'.$medh3_row[1].'</span><br/>';
                                    }
                                ?>
                                <input type="text" name="pm_others" id="pm_others" placeholder="Please specify other medical history here" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 250px;font-weight: bold;" value="<?php echo $b['pm_others']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
       
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
            <tr>
                <td class="spandix-l">Family History :</td>
                <td align=right><input type="text" name="pe_famhistory" id="pe_famhistory" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 98%;font-weight: bold;" value="<?php echo $b['fm_history']; ?>"></td>
            </tr>
            <tr>
                <td class="spandix-l">Previous Hospitalization :</td>
                <td align=right><input type="text" name="pe_hospitalization" id="pe_hospitalization" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 98%;font-weight: bold;" value="<?php echo $b['pv_hospitalization']; ?>"></td>
            </tr>
            <tr>
                <td class="spandix-l">Current Medication :</td>
                <td align=right><input type="text" name="pe_current_med" id="pe_current_med" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 98%;font-weight: bold;" value="<?php echo $b['current_med']; ?>"></td>
            </tr>
            <tr>
                <td colspan=2 class="spandix-l">
                    Menstrual History: <input type="text" name="pe_menshistory" id="pe_menshistory" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 160px;font-weight: bold;" value="<?php echo $b['mens_history']; ?>">y.o&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Parity:  <input type="text" name="pe_parity" id="pe_parity" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 160px;font-weight: bold;" value="<?php echo $b['parity']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    LMP:  <input type="text" name="pe_lmp" id="pe_lmp" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 200px;font-weight: bold;" value="<?php echo $b['lmp']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Contraceptive Use:  <input type="text" name="pe_contra" id="pe_contra" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 160px;font-weight: bold;" value="<?php echo $b['contraceptives']; ?>">      
                </td>
            </tr>
            <tr>
                <td colspan=2 class="spandix-l">
                    Smoker: <input type="text" name="pe_smoker" id="pe_smoker" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 160px;font-weight: bold;" value="<?php echo $b['smoker']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Pregnant:  <input type="text" name="pe_pregnant" id="pe_pregnant" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 160px;font-weight: bold;" value="<?php echo $b['pregnant']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Alcoholic Beverage/Drinker:  <input type="text" name="pe_alcoholic" id="pe_alcoholic" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 160px;font-weight: bold;" value="<?php echo $b['alcoholic']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Illicit Drug Use:  <input type="text" name="pe_drugs" id="pe_drugs" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 160px;font-weight: bold;" value="<?php echo $b['drugs']; ?>">      
                </td>
            </tr>
            <tr><td height=10></td></tr>
        </table>
        <table width=100% cellpadding=5 cellspacing=0 style="border-collapse: collapse; font-size: 11px;">
            <tr>
                <td width=15% align=center style="border: 1px solid black; font-weight: bold;">Review of Systems</td>
                <td width=8% align=center style="border: 1px solid black; font-weight: bold;">Status</td>
                <td width=27% align=center style="border: 1px solid black; font-weight: bold;">Findings</td>
                <td width=15% align=center style="border: 1px solid black; font-weight: bold;">Status</td>
                <td width=8% align=center style="border: 1px solid black; font-weight: bold;">Normal</td>
                <td width=27% align=center style="border: 1px solid black; font-weight: bold;">Findings</td>
            </tr>
            <tr>
                <td style="border: 1px solid black;">Head & Scalp</td>
                <td style="border: 1px solid black;" align=center>
                    <select name="pe_hs_normal" id="pe_hs_normal">
                        <option value="N/A" <?php if($b['hs_normal'] == 'N/A') { echo "selected"; } ?>>N/A</option>
                        <option value="Y" <?php if($b['hs_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['hs_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select>
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_hs_findings" id="pe_hs_findings" value="<?php echo $b['hs_findings']; ?>"></td>
                <td style="border: 1px solid black;">Lungs</td>
                <td style="border: 1px solid black;" align=center>
                    
                    <select name="pe_lungs_normal" id="pe_lungs_normal">
                        <option value="N/A" <?php if($b['lungs_normal'] == 'N/A') { echo "selected"; } ?>>N/A</option>
                        <option value="Y" <?php if($b['lungs_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['lungs_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select>
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_lungs_findings" id="pe_lungs_findings" value="<?php echo $b['lungs_findings']; ?>"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black;">Eyes & Ears</td>
                <td style="border: 1px solid black;" align=center>
                   
                    <select name="pe_ee_normal" id="pe_ee_normal">
                        <option value="N/A" <?php if($b['ee_normal'] == 'N/A') { echo "selected"; } ?>>N/A</option>
                        <option value="Y" <?php if($b['ee_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['ee_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select>
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_ee_findings" id="pe_ee_findings" value="<?php echo $b['ee_findings']; ?>"></td>
                <td style="border: 1px solid black;">Heart</td>
                <td style="border: 1px solid black;" align=center>
                  
                    <select name="pe_heart_normal" id="pe_heart_normal">
                        <option value='N/A'>N/A</option>
                        <option value="Y" <?php if($b['heart_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['heart_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select>
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_heart_findings" id="pe_heart_findings" value="<?php echo $b['heart_findings']; ?>"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black;">Skin/Allergy</td>
                <td style="border: 1px solid black;" align=center>
                   
                    <select name="pe_sa_normal" id="pe_sa_normal">
                        <option value='N/A'>N/A</option>
                        <option value="Y" <?php if($b['sa_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['sa_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select>                           
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_sa_findings" id="pe_sa_findings" value="<?php echo $b['sa_findings']; ?>"></td>
                <td style="border: 1px solid black;">Abdomen</td>
                <td style="border: 1px solid black;" align=center>
                 
                    <select name="pe_abdomen_normal" id="pe_abdomen_normal">
                    <option value='N/A'>N/A</option>
                        <option value="Y" <?php if($b['abdomen_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['abdomen_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select>
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_abdomen_findings" id="pe_abdomen_findings" value="<?php echo $b['abdomen_findings']; ?>"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black;">Nose/Sinuses</td>
                <td style="border: 1px solid black;" align=center>
                   
                    <select name="pe_nose_normal" id="pe_nose_normal">
                        <option value='N/A'>N/A</option>
                        <option value="Y" <?php if($b['nose_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['nose_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select>                
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_nose_findings" id="pe_nose_findings" value="<?php echo $b['nose_findings']; ?>"></td>
                <td style="border: 1px solid black;">Genitals</td>
                <td style="border: 1px solid black;" align=center>
                    
                    <select name="pe_genitals_normal" id="pe_genitals_normal">
                        <option value='N/A'>N/A</option>
                        <option value="Y" <?php if($b['genitals_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['genitals_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select>                 
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_genitals_findings" id="pe_genitals_findings" value="<?php echo $b['genitals_findings']; ?>"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black;">Mouth/Teeth/Tongue</td>
                <td style="border: 1px solid black;" align=center>
                   
                    <select name="pe_mouth_normal" id="pe_mouth_normal">
                        <option value='N/A'>N/A</option>
                        <option value="Y" <?php if($b['mouth_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['mouth_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select>                        
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_mouth_findings" id="pe_mouth_findings" value="<?php echo $b['mouth_findings']; ?>"></td>
                <td style="border: 1px solid black;">Extremities</td>
                <td style="border: 1px solid black;" align=center>
                  
                    <select name="pe_extr_normal" id="pe_extr_normal">
                     <option value='N/A'>N/A</option>
                        <option value="Y" <?php if($b['extr_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['extr_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select>               
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_extr_findings" id="pe_extr_findings" value="<?php echo $b['extr_findings']; ?>"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black;">Neck/Thyroid</td>
                <td style="border: 1px solid black;" align=center>
                    
                    <select name="pe_neck_normal" id="pe_neck_normal">
                        <option value='N/A'>N/A</option>
                        <option value="Y" <?php if($b['neck_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['neck_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select>                
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_neck_findings" id="pe_neck_findings" value="<?php echo $b['neck_findings']; ?>"></td>
                <td style="border: 1px solid black;">Reflexes</td>
                <td style="border: 1px solid black;" align=center>
                   
                    <select name="pe_ref_normal" id="pe_ref_normal">
                        <option value='N/A'>N/A</option>
                        <option value="Y" <?php if($b['ref_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['ref_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select> 
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_ref_findings" id="pe_ref_findings" value="<?php echo $b['ref_findings']; ?>"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black;">Breast-Axillla</td>
                <td style="border: 1px solid black;" align=center>
                    <select name="pe_check_normal" id="pe_check_normal">
                        <option value='N/A'>N/A</option>
                        <option value="Y" <?php if($b['check_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['check_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select>
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_check_findings" id="pe_check_findings" value="<?php echo $b['check_findings']; ?>"></td>
                <td style="border: 1px solid black;">BPE</td>
                <td style="border: 1px solid black;" align=center>
                   
                    <select name="pe_bpe_normal" id="pe_bpe_normal">
                        <option value='N/A'>N/A</option>
                        <option value="Y" <?php if($b['bpe_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['bpe_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select>                
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_bpe_findings" id="pe_bpe_findings" value="<?php echo $b['bpe_findings']; ?>"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black;"></td>
                <td style="border: 1px solid black;"></td>
                <td style="border: 1px solid black;"></td>
                <td style="border: 1px solid black;">Rectal</td>
                <td style="border: 1px solid black;" align=center>
                  
                    <select name="pe_rect_normal" id="pe_rect_normal">
                        <option value='N/A'>N/A</option>
                        <option value="Y" <?php if($b['rect_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['rect_normal'] == 'N') { echo "selected"; } ?>>Not Normal</option>
                    </select>                
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_rect_findings" id="pe_rect_findings" value="<?php echo $b['rect_findings']; ?>"></td>
            </tr>
        </table>
        <table style="padding-top:5px;">
            <tr>
                <td><button type=button style="padding:5px; background-color:#dfeffc; color:#1d5987;border-radius:5px;border:1px solid #1d5987;cursor:pointer;" onclick="javascript: marknormal();">Mark Common Systems as "Normal"</button></td>
                <td align=center class="spandix-l" width=20% style="padding-left: 50px;">Pre-Examined By: </td>
                <td>
                    <select name="pe_pre_examined_by" id="pe_pre_examined_by" style="width: 350px;">
                        <option value=''>- Select Pre-Examining Physician</option>
                        <?php
                            $equery = $o->dbquery("SELECT CONCAT(fullname,', ',prefix), id FROM options_doctors WHERE id NOT IN (1,2,3);");
                            while($erow = $equery->fetch_array()) {
                                echo "<option value='$erow[1]' ";
                                if($b['pre_examined_by'] == $erow[1]) { echo "selected"; }
                                echo ">$erow[0]<option>";
                            }
                        ?>
                    </select>                       
                </td>
            </tr>
        
        </table>
        <table width=100% cellpadding=5 cellspacing=0 style="border-collapse: collapse; font-size: 11px; margin-top: 7px;">
            <tr>
                <td width=15% align=center style="border: 1px solid black; font-weight: bold;">Laboratory</td>
                <td width=8% align=center style="border: 1px solid black; font-weight: bold;">Status</td>
                <td width=27% align=center style="border: 1px solid black; font-weight: bold;">Findings</td>
                <td width=15% align=center style="border: 1px solid black; font-weight: bold;">Review of Systems</td>
                <td width=8% align=center style="border: 1px solid black; font-weight: bold;">Status</td>
                <td width=27% align=center style="border: 1px solid black; font-weight: bold;">Findings</td>
            </tr>
            <tr>
                <td style="border: 1px solid black;">Chest X-Ray&nbsp;&nbsp;<a onclick="javascript: checkXray('X012'); checkXray('X001');" title="Click to View X-Ray Result"><img src="images/icons/open-icon.png" width=8 height=8 align=top /></a></td>
                <td style="border: 1px solid black;" align=center>
                    <select name="pe_chest_normal" id="pe_chest_normal">
                        <option value=''>N/A</option>
                        <option value="Y" <?php if($b['chest_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['chest_normal'] == 'N') { echo "selected"; } ?>>With Findings</option>
                    </select>   
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_chest_findings" id="pe_chest_findings" value="<?php echo $b['chest_findings']; ?>"></td>
                <td style="border: 1px solid black;">ECG&nbsp;&nbsp;<a onclick="javascript: openAttachment('O001');" title="Click to View ECG Result"><img src="images/icons/open-icon.png" width=8 height=8 align=top /></a></td>
                <td style="border: 1px solid black;" align=center>
                    <select name="pe_ecg_normal" id="pe_ecg_normal">
                        <option value=''>N/A</option>
                        <option value="Y" <?php if($b['ecg_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['ecg_normal'] == 'N') { echo "selected"; } ?>>With Findings</option>
                    </select>                
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_ecg_findings" id="pe_ecg_findings" value="<?php echo $b['ecg_findings']; ?>"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black;">CBC&nbsp;&nbsp;<a onclick="javascript: checkResult('L010');" title="Click to View CBC Result"><img src="images/icons/open-icon.png" width=8 height=8 align=top /></a></td>
                <td style="border: 1px solid black;" align=center>
                     <select name="pe_cbc_normal" id="pe_cbc_normal">
                        <option value=''>N/A</option>
                        <option value="Y" <?php if($b['cbc_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['cbc_normal'] == 'N') { echo "selected"; } ?>>With Findings</option>
                    </select>                
             
                <td style="border: 1px solid black;">
                 <input type="text" style="width: 100%; border: none;" name="pe_cbc_findings" id="pe_cbc_findings" value="<?php echo $b['cbc_findings']; ?>">
                                   
                </td>
                <td style="border: 1px solid black;">Papsmear&nbsp;&nbsp;<a onclick="javascript: openAttachment('L066');" title="Click to View Papsmear Result"><img src="images/icons/open-icon.png" width=8 height=8 align=top /></a></td>
                <td style="border: 1px solid black;" align=center>
                   
                    <select name="pe_papsmear_normal" id="pe_papsmear_normal">
                        <option value=''>N/A</option>
                        <option value="Y" <?php if($b['pap_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['pap_normal'] == 'N') { echo "selected"; } ?>>With Findings</option>
                    </select>  

                </td>
                <td style="border: 1px solid black;">
                    <input type="text" style="width: 100%; border: none;" name="pe_pap_findings" id="pe_pap_findings" value="<?php echo $b['pap_findings']; ?>">
                                
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid black;">Urinalysis&nbsp;&nbsp;<a onclick="javascript: checkResult('L012');" title="Click to View UA Result"><img src="images/icons/open-icon.png" width=8 height=8 align=top /></a></td>
                <td style="border: 1px solid black;" align=center>
                   
                    <select name="pe_ua_findings_normal" id="pe_ua_findings_normal">
                        <option value=''>N/A</option>
                        <option value="Y" <?php if($b['ua_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['ua_normal'] == 'N') { echo "selected"; } ?>>With Findings</option>
                    </select>  

                </td>
                <td style="border: 1px solid black;">
                    <input type="text" style="width: 100%; border: none;" name="pe_ua_findings" id="pe_ua_findings" value="<?php echo $b['ua_findings']; ?>">
                                
                </td>
                <td style="border: 1px solid black;">OTHER PROCEDURES:</td>
                <td style="border: 1px solid black;"></td>
                <td style="border: 1px solid black;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid black;">Fecalysis&nbsp;&nbsp;<a onclick="javascript: checkResult('L013');" title="Click to View Fecalysis Result"><img src="images/icons/open-icon.png" width=8 height=8 align=top /></a></td>
                <td style="border: 1px solid black;" align=center>
                
            
                    <select name="pe_se_normal" id="pe_se_normal">
                        <option value=''>N/A</option>
                        <option value="Y" <?php if($b['se_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['se_normal'] == 'N') { echo "selected"; } ?>>With Findings</option>
                    </select>                
                    </select>                               
                </td>
                <td style="border: 1px solid black;">
                    <input type="text" style="width: 100%; border: none;" name="pe_se_findings" id="pe_se_findings" value="<?php echo $b['se_findings']; ?>">
                     
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_others1" id="pe_others1" value="<?php echo $b['others1_name']; ?>"></td>
                <td style="border: 1px solid black;" align=center>
                   
                    <select name="pe_others1_normal" id="pe_others1_normal">
                        <option value=''>N/A</option>
                        <option value="Y" <?php if($b['others1_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['others1_normal'] == 'N') { echo "selected"; } ?>>With Findings</option>
                    </select>                
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_others1_findings" id="pe_others1_findings" value="<?php echo $b['others1_findings']; ?>"></td>
            </tr>
            <tr>
            <td style="border: 1px solid black;">Drug Test&nbsp;&nbsp;<a onclick="javascript: openAttachment('L071');" title="Click to View Drug Test Result"><img src="images/icons/open-icon.png" width=8 height=8 align=top /></a></td>
                <td style="border: 1px solid black;" align=center>
                    
                    <select name="pe_dt_normal" id="pe_dt_normal">
                        <option value=''>N/A</option>
                        <option value="Y" <?php if($b['dt_normal'] == 'Y') { echo "selected"; } ?>>POSITIVE</option>
                        <option value="N" <?php if($b['dt_normal'] == 'N') { echo "selected"; } ?>>NEGATIVE</option>
                    </select>                
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_dt_findings" id="pe_dt_findings" value="<?php echo $b['dt_findings']; ?>"></td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_others2" id="pe_others2" value="<?php echo $b['others2_name']; ?>"></td>
                <td style="border: 1px solid black;" align=center>
                    <select name="pe_others2_normal" id="pe_others2_normal">
                        <option value=''>N/A</option>
                        <option value="Y" <?php if($b['others2_normal'] == 'Y') { echo "selected"; } ?>>Normal</option>
                        <option value="N" <?php if($b['others2_normal'] == 'N') { echo "selected"; } ?>>With Findings</option>
                    </select>                       
                </td>
                <td style="border: 1px solid black;"><input type="text" style="width: 100%; border: none;" name="pe_others2_findings" id="pe_others2_findings" value="<?php echo $b['others2_findings']; ?>"></td>
            </tr>
        </table>
        <table width=100% cellpadding=5 cellspacing=0>
            <tr><td colspan=2 class="spandix-l">I Hereby Certify that I have examined and found the employee to be <select class=gridInput name="pe_fit" id="pe_fit"><option value="FIT" <?php if($b['pe_fit'] == 'FIT') { echo "selected"; } ?>>FIT</option><option value="UNFIT" <?php if($b['pe_fit'] == 'UNFIT') { echo "selected"; } ?>>UNFIT</option></select> for employment.<br/><b>CLASSIFICATION:</b></td></tr>                
            <tr>
                <td width=20% style="padding-left: 5%;" class="spandix-l"><input type="radio" name="pe_class" id="pe_class" value="A" <?php if($b['classification'] == 'A') { echo "checked"; } ?>>&nbsp;&nbsp;CLASS A</td>
                <td width=80% class="spandix-l">
                    Physically fit for all types of work
                   </td>
            </tr>
            <tr>
                <td width=20% style="padding-left: 5%;" class="spandix-l" valign=top><input type="radio" name="pe_class" id="pe_class" value="B" <?php if($b['classification'] == 'B') { echo "checked"; } ?>>&nbsp;&nbsp;CLASS B</td>
                <td width=80% class="spandix-l">Physically fit for all types of work
                <br/>
                    Has Minor ailment/defect. Easily curable or offers no handicap to applied.
                    <br/>
                    <input type="radio" name="pe_class_b" id="pe_class_b" value="1" <?php if($b['class_b'] == '1') { echo "checked"; } ?>>&nbsp;&nbsp;Needs Treatment Correction : &nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="pe_class_b_remarks1" id="pe_class_b_remarks1" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width:370px;" value="<?php echo $b['class_b_remarks1']; ?>">
                    <br/>
                    <input type="radio" name="pe_class_b" id="pe_class_b" value="2" <?php if($b['class_b'] == '2') { echo "checked"; } ?>>&nbsp;&nbsp;Treatment Optional For : &nbsp;&nbsp;<input type="text" name="pe_class_b_remarks2" id="pe_class_b_remarks2" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 400px;" value="<?php echo $b['class_b_remarks2']; ?>">
                
                </td>
            </tr>
            <tr>
                <td width=20% style="padding-left: 5%;" class="spandix-l" valign=top><input type="radio" name="pe_class" id="pe_class" value="C" <?php if($b['classification'] == 'C') { echo "checked"; } ?>>&nbsp;&nbsp;CLASS C</td>
                <td width=80% class="spandix-l">Physically fit for less strenous type of work. Has minor ailments/defects.
                <br/>
                    Easily curable or offers no handicap to job applied.
                    <br/>
                    <input type="radio" name="pe_class_c" id="pe_class_c" value="1" <?php if($b['class_c'] == '1') { echo "checked"; } ?>>&nbsp;&nbsp;Needs Treatment Correction : &nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="pe_class_c_remarks1" id="pe_class_c_remarks1" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width:370px;" value="<?php echo $b['class_c_remarks1']; ?>">
                    <br/>
                    <input type="radio" name="pe_class_c" id="pe_class_c" value="2" <?php if($b['class_c'] == '2') { echo "checked"; } ?>>&nbsp;&nbsp;Treatment Optional For : &nbsp;&nbsp;<input type="text" name="pe_class_c_remarks2" id="pe_class_c_remarks2" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 400px;" value="<?php echo $b['class_c_remarks2']; ?>">
                
                </td>
            </tr>
            <tr> 
                <td width=20% style="padding-left: 5%;" class="spandix-l"><input type="radio" name="pe_class" id="pe_class" value="D" <?php if($b['classification'] == 'D') { echo "checked"; } ?>>&nbsp;&nbsp;CLASS D</td>
                <td width=80% class="spandix-l">
                    Employment at the risk and discretion of the management
                </td>
            </tr>
            <tr>
                <td width=20% style="padding-left: 5%;" class="spandix-l"><input type="radio" name="pe_class" id="pe_class" value="E" <?php if($b['classification'] == 'E') { echo "checked"; } ?>>&nbsp;&nbsp;CLASS E</td>
                <td width=80% class="spandix-l">
                    Unfit for Employment
                </td>
            </tr>
            <tr>
                <td width=20% style="padding-left: 5%;" class="spandix-l"><input type="radio" name="pe_class" id="pe_class" value="PENDING" <?php if($b['classification'] == 'PENDING') { echo "checked"; } ?>>&nbsp;&nbsp;PENDING</td>
                <td width=80% class="spandix-l">
                    For further evaluation of: &nbsp;&nbsp;&nbsp;<input type="text" name="pe_eval_remarks" id="pe_eval_remarks" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 380px;" value="<?php echo $b['pending_remarks']; ?>">
                </td>
            </tr>
            <tr><td colspan=2 class="spandix-l">Remarks: <input type="text" name="pe_remarks" id="pe_remarks" style="border-top: none; border-left: none; border-right: none; border-bottom: 1px solid black; font-size: 11px; width: 70%;" value="<?php echo $b['overall_remarks']; ?>"></td></tr>
        </table>
        <table><tr><td height="10"></td></tr></table>
        <!-- <table width=100%>
            <tr>
                <td class="spandix-l" width=15%>Examined By: </td>
                <td >
                    <select name="pe_examined_by" id="pe_examined_by" style="width: 250px;">
                        <option value=''>- Select Examining Physician</option>
                        <?php
                            $equery = $o->dbquery("SELECT CONCAT(fullname,', ',prefix), id FROM options_doctors WHERE id NOT IN (1,2);");
                            while($erow = $equery->fetch_array()) {
                                echo "<option value='$erow[1]' ";
                                if($b['examined_by'] == $erow[1]) { echo "selected"; }
                                echo ">$erow[0]<option>";
                            }
                        ?>
                    </select>                       
                </td>
            </tr>
            <tr>
                <td class="spandix-l">Evaluated By: </td>
                <td >
                    <select name="pe_evaluated_by" id="pe_evaluated_by" style="width: 250px;">
                     <option value=''>- Select Evaluating Physician</option>
                        <?php
                            $equery = $o->dbquery("SELECT CONCAT(fullname,', ',prefix), id FROM options_doctors WHERE id NOT IN (1,2);");
                            while($erow = $equery->fetch_array()) {
                                echo "<option value='$erow[1]' ";
                                if($b['evaluated_by'] == $erow[1]) { echo "selected"; }
                                echo ">$erow[0]<option>";
                            }
                        ?>
                    </select>                       
                </td>
            </tr>
        </table> -->

        <table width=100%>
            <tr style="float:left;">
                <td class="spandix-1" width="25%" style="font-size:12px;">Examined By:</td>
                <td class="spandix-1"><input type="text" style="width: 390px; font-weight:300; padding:10px; font-size:13px;" name="pe_examined_by" id="pe_examined_by" value="<?php echo $docfullname,' ',$docprefix,' ',$c['examin_d8'], ' ',$c['examin_tym']; ?>" readonly></td>
            </tr>
        </table>
	</form>
    <div id="imageAttachment" name="imageAttachment" style="display: none;">
        <p id="fileLocation" name="fileLocation"></p>
    </div>
</body>
</html>