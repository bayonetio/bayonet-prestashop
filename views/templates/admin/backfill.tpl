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
	<h3><i class="icon icon-cogs"></i> {l s='History Backfill' mod='bayonet'}</h3>
	<p>{l s='The history backfill is a process we perfom in order to get to know your store in a better way. We check all the orders in your store prior to this module\'s installation, in this way, we can detect patterns from your customers and how your store behaves in general. Doing this helps the module to have a better accuracy when analysing the new orders in your store for fraud.'  mod='bayonet'}</p>
	<br />
	<div class="text-center row">
		<div class="col-md-12">
			{if $backfill_enable == 1}
				{if $backfill_mode == 0}
					<div class="col-md-offset-2 col-md-8 col-sm-12">
						<div id="error-msg"></div>
					</div>
					<div class="col-md-12">
						<button class="btn btn-primary" id="initiate">{l s='Initiate Backfill' mod='bayonet'}</button>
					</div>
				{else}
					<div class="col-md-2 col-sm-12">
						<label class="control-label">
							{l s='Backfill Status' mod='bayonet'}
						</label>
					</div>
					<div class="col-md-8 col-sm-12">
						<div class="progress">
						  <div class="progress-bar progress-bar-striped active" role="progressbar"
						  aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width:0%">
						    0% 
						  </div>
						</div>
					</div>
					<div class="col-md-12">
						<button class="btn btn-primary" id="stop">{l s='Stop Backfill' mod='bayonet'}</button>
					</div>
				{/if}
			{else}
				<div class="col-md-12">
				  <div class="alert alert-danger">{l s='Please enter your live API keys to enable this feature' mod='bayonet'}</div>
					<p></p>
				</div>
			{/if}
		</div>
	</div>
</div>