{*
* 2007-2019 PrestaShop SA and Contributors
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to https://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA and Contributors
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
  <div class="panel-heading"> 
    <img src="../modules/bayonet/logo.png" height="16" width="16" /> Bayonet
  </div>
  {if $not_consulting_order}
    <div class="alert alert-warning">
	  This order was not processed by the consulting API.
	</div>
  {elseif $unprocessed_order}
    <div class="alert alert-warning">
	  This order is not processed by Bayonet.
	</div>
  {else}
    <div class="table-responsive">
	  <table class="table" width="100%" cellspacing="0" cellpadding="0">
	    <tr>
		  <td valign="top" align="center">
		    <p>
			  <span style="font-size:1.5em;font-weight:bold;color:#f00">{$decision}</span>
			</p>
		  </td>
		  <td>
		    <p>
			  <strong>Bayonet Tracking ID:</strong>
			</p>
		    <p>
			  {$bayonet_tracking_id}
			</p>
		    <p>
			  <strong>Consulting API Response:</strong>
			</p>
			<p>
			  {$api_response}
			</p>
			<p>
			  <strong>Rules Triggered:</strong>
			</p>
		    <p>
			  {$rules_triggered}
			</p>
		  </td>
		</tr>
	  </table>
	  <br/>
	  <div style="text-align:right; width:100%; padding:0;">
	    {if $idBlockList == 0}
	      <button class="btn btn-success" id="btn-addWhite" data-mail="{$mailCustomer}" data-customer="{$idCustomer}" data-id="{$idBlockList}" data-whitelist="{$whitelist}" data-blacklist="{$blacklist}">Add Customer to White List</button>
          <button class="btn btn-danger" id="btn-addBlack" data-mail="{$mailCustomer}" data-customer="{$idCustomer}" data-id="{$idBlockList}" data-whitelist="{$whitelist}" data-blacklist="{$blacklist}">Add Customer to Black List</button>
        {elseif $idBlockList > 0}
          {if $whitelist == 1}
            <button class="btn btn-success" id="btn-removeWhite" data-mail="{$mailCustomer}" data-customer="{$idCustomer}" data-id="{$idBlockList}" data-whitelist="{$whitelist}" data-blacklist="{$blacklist}">Remove Customer from White List</button>
            <button class="btn btn-danger" id="btn-addBlack" data-mail="{$mailCustomer}" data-customer="{$idCustomer}" data-id="{$idBlockList}" data-whitelist="{$whitelist}" data-blacklist="{$blacklist}">Add Customer to Black List</button>
          {elseif $blacklist == 1}
            <button class="btn btn-success" id="btn-addWhite" data-mail="{$mailCustomer}" data-customer="{$idCustomer}" data-id="{$idBlockList}" data-whitelist="{$whitelist}" data-blacklist="{$blacklist}">Add Customer to White List</button>
            <button class="btn btn-danger" id="btn-removeBlack" data-mail="{$mailCustomer}" data-customer="{$idCustomer}" data-id="{$idBlockList}" data-whitelist="{$whitelist}" data-blacklist="{$blacklist}">Remove Customer from Black List</button>
          {elseif $whitelist == 0 && blocklist == 0}
            <button class="btn btn-success" id="btn-addWhite" data-mail="{$mailCustomer}" data-customer="{$idCustomer}" data-id="{$idBlockList}" data-whitelist="{$whitelist}" data-blacklist="{$blacklist}">Add Customer to White List</button>
            <button class="btn btn-danger" id="btn-addBlack" data-mail="{$mailCustomer}" data-customer="{$idCustomer}" data-id="{$idBlockList}" data-whitelist="{$whitelist}" data-blacklist="{$blacklist}">Add Customer to Black List</button>
          {/if}
        {/if}
      </div>
	</div>
  {/if}
</div>
