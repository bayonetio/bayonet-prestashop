<div class="panel">
	<div class="panel-heading"> 
		<img src="../modules/bayonet/logo.png" height="16" width="16" /> Bayonet
	</div>
	
	{if $unprocessed_order}
	<div class="alert alert-warning">
		This order is not processed by Bayonet.
	</div>
	{else}
	<div class="table-responsive">
		<table class="table" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td valign="top" align="center">
				<p><span style="font-size:1.5em;font-weight:bold;color:#f00">{$decision}</span></p>
				</td>
				<td>
					<p><strong>Bayonet Tracking ID:</strong></p>
					<p>{$bayonet_tracking_id}</p>
					<p><strong>Consulting API Response:</strong></p>
					<p>{$api_response}</p>
					<p><strong>Rules Triggered:</strong></p>
					<p>{$rules_triggered}</p>
				</td>
			</tr>
		</table>
	</div>
	{/if}
</div>