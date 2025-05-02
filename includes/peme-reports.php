<div id="pemeReportMain" style="display: none;">
	<div style="padding: 20px;">
		<div style="height: 10px;"></div>
		    <div class="fileObjects" onclick="parent.showMaxicareCensus()"><a href="#"><img src="images/icons/inventory.png" width=60 height=60 /><br/><br/>Maxicare Census</a></div>
	</div>
</div>
<div id="mxcensus" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100%>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="mx_dtf" class="gridInput" style="width: 80%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=40%><span class="spandix-l"></span></td>
			<td>
				<input type="text" id="mx_dt2" class="gridInput" style="width: 80%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
	</table>
</div>