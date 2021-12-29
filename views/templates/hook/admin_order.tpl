{*
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<script type="text/javascript" src="{$path|escape:'htmlall':'UTF-8'}views/js/blocklist.js"></script>
<script type="text/javascript">
    var urlBlocklist = decodeURIComponent("{$urlBlocklist|escape:'htmlall':'UTF-8'}");    
</script>

<div class="panel">
  <div class="panel-heading"> 
    <img src="../modules/bayonet/logo.png" height="16" width="16" /> {l s='Bayonet Anti-Fraud Details' mod='bayonetantifraud'}
  </div>

  {if $not_consulting_order}
    <div class="alert alert-warning">
	  {l s='This order was not processed by the consulting API.' mod='bayonetantifraud'}
	</div>
  {elseif $unprocessed_order}
    <div class="alert alert-warning">
	  {l s='This order is not processed by Bayonet.' mod='bayonetantifraud'}
	</div>
  {else}
    <div class="table-responsive">
	  <table class="table" width="100%" border="0" cellspacing="0" cellpadding="3" style="border-collapse:collapse">
	    <col width="40%">
		<col width="40%">
		<col width="20%">
		<tr>
		  <td rowspan="5" colspan="3" valign="center" align="center" style="vertical-align:center">
		    <p><span style="font-size:1.5em;font-weight:bold;color:#f00">{$decision|escape:'html':'UTF-8'|htmlspecialchars_decode:3}</span><div style="font-size:15px;color:#4b4b4b"><strong>{$decision_message|escape:'html':'UTF-8'|htmlspecialchars_decode:3}</strong></div></p>
		  </td>
		  <td>&nbsp;</td>
		</tr>
		<tr></tr>
		<tr></tr>
		<tr></tr>
		<tr></tr>
		<tr></tr>
		<tr>
		  <td valign="top" align="center" style="vertical-align:center" ><strong>Bayonet Tracking ID</strong><div style="font-size:10px;color:#4b4b4b">{l s='Bayonet internal ID to track the status change on this order' mod='bayonetantifraud'}</div></td>
		  <td colspan="2" valign="top" align="left" style="vertical-align:center" >{$bayonet_tracking_id|escape:'htmlall':'UTF-8'}</td>
		  <td>&nbsp;</td>
		</tr>
		<tr>
		  <td valign="top" align="center" style="vertical-align:center" ><strong>{l s='API Call Status' mod='bayonetantifraud'}</strong><div style="font-size:10px;color:#4b4b4b">{l s='The response obtained from Bayonet API after performing the analysis request' mod='bayonetantifraud'}</div></td>
		  <td colspan="2" valign="top" align="left" style="vertical-align:center" >
		    - {l s='Code: ' mod='bayonetantifraud'} {$reason_code|escape:'htmlall':'UTF-8'}
		    <br />
		    - {l s='API Call: ' mod='bayonetantifraud'} {$reason_message|escape:'htmlall':'UTF-8'}
		  </td>
		  <td>&nbsp;</td>
		</tr>
		{if strlen($triggered_rules) > 0}
		  <tr>
		    <td valign="top" align="center" style="vertical-align:top" ><strong>{l s='Triggered Rules' mod='bayonetantifraud'}</strong><div style="font-size:10px;color:#4b4b4b">{l s='The rules that were triggered in the analysis and led to this decision' mod='bayonetantifraud'}</div></td>
		    <td colspan="2" valign="top" align="left" style="vertical-align:top">{$triggered_rules|escape:'html':'UTF-8'|htmlspecialchars_decode:3}</td>
		    <td>&nbsp;</td>
		  </tr>
		{/if}
		<tr>
		  <td valign="top" align="center" style="vertical-align:center" ><strong>{l s='Blocklist Status' mod='bayonetantifraud'}</strong><div style="font-size:10px;color:#4b4b4b">{l s='The current status of the customer\'s email in Bayonet Blocklist' mod='bayonetantifraud'}</div></td>
		  {if (int)$blocklist_live === 1}
		    <td valign="top" align="center" style="vertical-align:center" ><strong>{l s='Added' mod='bayonetantifraud'}</strong></td>
		  {elseif (int)$blocklist_live === 0}
		    <td valign="top" align="center" style="vertical-align:center" ><strong>{l s='Not Added' mod='bayonetantifraud'}</strong></td>
		  {/if}
		  <td valign="top" align="left" style="vertical-align:center" >
			- {l s='Code: ' mod='bayonetantifraud'} {$reason_code_blocklist_live|escape:'htmlall':'UTF-8'}
			<br />
			- {l s='API Call: ' mod='bayonetantifraud'} {$reason_message_blocklist_live|escape:'htmlall':'UTF-8'}
			<br />
			- {l s='Attempted action: ' mod='bayonetantifraud'} {$attempted_action_blocklist_live|escape:'htmlall':'UTF-8'}
		  </td>
		  <td>&nbsp;</td>
		</tr>
		  <td valign="top" align="center" style="vertical-align:center" ><strong>{l s='Whitelist Status' mod='bayonetantifraud'}</strong><div style="font-size:10px;color:#4b4b4b">{l s='The current status of the customer\'s email in Bayonet Whitelist' mod='bayonetantifraud'}</div></td>
		  {if (int)$whitelist_live === 1}
		    <td valign="top" align="center" style="vertical-align:center" ><strong>{l s='Added' mod='bayonetantifraud'}</strong></td>
		  {elseif (int)$whitelist_live === 0}
		    <td valign="top" align="center" style="vertical-align:center" ><strong>{l s='Not Added' mod='bayonetantifraud'}</strong></td>
		  {/if}
		  <td valign="top" align="left" style="vertical-align:center" >
			- {l s='Code: ' mod='bayonetantifraud'} {$reason_code_whitelist_live|escape:'htmlall':'UTF-8'}
			<br />
			- {l s='API Call: ' mod='bayonetantifraud'} {$reason_message_whitelist_live|escape:'htmlall':'UTF-8'}
			<br />
			- {l s='Attempted action: ' mod='bayonetantifraud'} {$attempted_action_whitelist_live|escape:'htmlall':'UTF-8'}
		  </td>
		  <td>&nbsp;</td>
		</tr>
	  </table>
	  <div style="text-align:right; width:100%; padding:0;">
	    <p>
		  {if $no_keys}
		    <div style="font-size:10px;color:#4b4b4b">{l s='The API key has not been added yet, please add it to enable the blocklist/whitelist buttons' mod='bayonetantifraud'}</div>
		  {/if}
		</p>
	    {if (int)$current_api_mode === 1}
	      {if (int)$blocklist_id_live > 0}
            {if (int)$whitelist_live === 1}
			  {if $no_keys}
			    <button class="btn btn-success" disabled id="btn-removeWhite" data-mail="{$customer_email|escape:'htmlall':'UTF-8'}" data-customer="{$customer_id|escape:'htmlall':'UTF-8'}" data-id="{$blocklist_id_live|escape:'htmlall':'UTF-8'}" data-whitelist="{$whitelist_live|escape:'htmlall':'UTF-8'}" data-blocklist="{$blocklist_live|escape:'htmlall':'UTF-8'}" data-mode="{$current_api_mode|escape:'htmlall':'UTF-8'}">{l s='Remove Customer\'s Email from Bayonet Whitelist' mod='bayonetantifraud'}</button>
                <button class="btn btn-danger" disabled id="btn-addBlock" data-mail="{$customer_email|escape:'htmlall':'UTF-8'}" data-customer="{$customer_id|escape:'htmlall':'UTF-8'}" data-id="{$blocklist_id_live|escape:'htmlall':'UTF-8'}" data-whitelist="{$whitelist_live|escape:'htmlall':'UTF-8'}" data-blocklist="{$blocklist_live|escape:'htmlall':'UTF-8'}" data-mode="{$current_api_mode|escape:'htmlall':'UTF-8'}">{l s='Add Customer\'s Email to Bayonet Blocklist' mod='bayonetantifraud'}</button>
			  {else}
			    <button class="btn btn-success" id="btn-removeWhite" data-mail="{$customer_email|escape:'htmlall':'UTF-8'}" data-customer="{$customer_id|escape:'htmlall':'UTF-8'}" data-id="{$blocklist_id_live|escape:'htmlall':'UTF-8'}" data-whitelist="{$whitelist_live|escape:'htmlall':'UTF-8'}" data-blocklist="{$blocklist_live|escape:'htmlall':'UTF-8'}" data-mode="{$current_api_mode|escape:'htmlall':'UTF-8'}">{l s='Remove Customer\'s Email from Bayonet Whitelist' mod='bayonetantifraud'}</button>
                <button class="btn btn-danger" id="btn-addBlock" data-mail="{$customer_email|escape:'htmlall':'UTF-8'}" data-customer="{$customer_id|escape:'htmlall':'UTF-8'}" data-id="{$blocklist_id_live|escape:'htmlall':'UTF-8'}" data-whitelist="{$whitelist_live|escape:'htmlall':'UTF-8'}" data-blocklist="{$blocklist_live|escape:'htmlall':'UTF-8'}" data-mode="{$current_api_mode|escape:'htmlall':'UTF-8'}">{l s='Add Customer\'s Email to Bayonet Blocklist' mod='bayonetantifraud'}</button>
			  {/if}
            {elseif (int)$blocklist_live === 1}
			  {if $no_keys}
			    <button class="btn btn-success" disabled id="btn-addWhite" data-mail="{$customer_email|escape:'htmlall':'UTF-8'}" data-customer="{$customer_id|escape:'htmlall':'UTF-8'}" data-id="{$blocklist_id_live|escape:'htmlall':'UTF-8'}" data-whitelist="{$whitelist_live|escape:'htmlall':'UTF-8'}" data-blocklist="{$blocklist_live|escape:'htmlall':'UTF-8'}" data-mode="{$current_api_mode|escape:'htmlall':'UTF-8'}">{l s='Add Customer\'s Email to Bayonet Whitelist' mod='bayonetantifraud'}</button>
                <button class="btn btn-danger" disabled id="btn-removeBlock" data-mail="{$customer_email|escape:'htmlall':'UTF-8'}" data-customer="{$customer_id|escape:'htmlall':'UTF-8'}" data-id="{$blocklist_id_live|escape:'htmlall':'UTF-8'}" data-whitelist="{$whitelist_live|escape:'htmlall':'UTF-8'}" data-blocklist="{$blocklist_live|escape:'htmlall':'UTF-8'}" data-mode="{$current_api_mode|escape:'htmlall':'UTF-8'}">{l s='Remove Customer\'s Email from Bayonet Blocklist' mod='bayonetantifraud'}</button>
			  {else}
			    <button class="btn btn-success" id="btn-addWhite" data-mail="{$customer_email|escape:'htmlall':'UTF-8'}" data-customer="{$customer_id|escape:'htmlall':'UTF-8'}" data-id="{$blocklist_id_live|escape:'htmlall':'UTF-8'}" data-whitelist="{$whitelist_live|escape:'htmlall':'UTF-8'}" data-blocklist="{$blocklist_live|escape:'htmlall':'UTF-8'}" data-mode="{$current_api_mode|escape:'htmlall':'UTF-8'}">{l s='Add Customer\'s Email to Bayonet Whitelist' mod='bayonetantifraud'}</button>
                <button class="btn btn-danger" id="btn-removeBlock" data-mail="{$customer_email|escape:'htmlall':'UTF-8'}" data-customer="{$customer_id|escape:'htmlall':'UTF-8'}" data-id="{$blocklist_id_live|escape:'htmlall':'UTF-8'}" data-whitelist="{$whitelist_live|escape:'htmlall':'UTF-8'}" data-blocklist="{$blocklist_live|escape:'htmlall':'UTF-8'}" data-mode="{$current_api_mode|escape:'htmlall':'UTF-8'}">{l s='Remove Customer\'s Email from Bayonet Blocklist' mod='bayonetantifraud'}</button>
			  {/if}
            {elseif (int)$whitelist_live === 0 && (int)blocklist_live === 0}
			  {if $no_keys}
			    <button class="btn btn-success" disabled id="btn-addWhite" data-mail="{$customer_email|escape:'htmlall':'UTF-8'}" data-customer="{$customer_id|escape:'htmlall':'UTF-8'}" data-id="{$blocklist_id_live|escape:'htmlall':'UTF-8'}" data-whitelist="{$whitelist_live|escape:'htmlall':'UTF-8'}" data-blocklist="{$blocklist_live|escape:'htmlall':'UTF-8'}" data-mode="{$current_api_mode|escape:'htmlall':'UTF-8'}">{l s='Add Customer\'s Email to Bayonet Whitelist' mod='bayonetantifraud'}</button>
                <button class="btn btn-danger" disabled id="btn-addBlock" data-mail="{$customer_email|escape:'htmlall':'UTF-8'}" data-customer="{$customer_id|escape:'htmlall':'UTF-8'}" data-id="{$blocklist_id_live|escape:'htmlall':'UTF-8'}" data-whitelist="{$whitelist_live|escape:'htmlall':'UTF-8'}" data-blocklist="{$blocklist_live|escape:'htmlall':'UTF-8'}" data-mode="{$current_api_mode|escape:'htmlall':'UTF-8'}">{l s='Add Customer\'s Email to Bayonet Blocklist' mod='bayonetantifraud'}</button>
			  {else}
			    <button class="btn btn-success" id="btn-addWhite" data-mail="{$customer_email|escape:'htmlall':'UTF-8'}" data-customer="{$customer_id|escape:'htmlall':'UTF-8'}" data-id="{$blocklist_id_live|escape:'htmlall':'UTF-8'}" data-whitelist="{$whitelist_live|escape:'htmlall':'UTF-8'}" data-blocklist="{$blocklist_live|escape:'htmlall':'UTF-8'}" data-mode="{$current_api_mode|escape:'htmlall':'UTF-8'}">{l s='Add Customer\'s Email to Bayonet Whitelist' mod='bayonetantifraud'}</button>
                <button class="btn btn-danger" id="btn-addBlock" data-mail="{$customer_email|escape:'htmlall':'UTF-8'}" data-customer="{$customer_id|escape:'htmlall':'UTF-8'}" data-id="{$blocklist_id_live|escape:'htmlall':'UTF-8'}" data-whitelist="{$whitelist_live|escape:'htmlall':'UTF-8'}" data-blocklist="{$blocklist_live|escape:'htmlall':'UTF-8'}" data-mode="{$current_api_mode|escape:'htmlall':'UTF-8'}">{l s='Add Customer\'s Email to Bayonet Blocklist' mod='bayonetantifraud'}</button>
			  {/if}
            {/if}
          {/if}
	    {/if}
      </div>
	</div>
  {/if}
</div>