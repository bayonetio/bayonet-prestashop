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
 
{$error_msgs|escape:'html':'UTF-8'|htmlspecialchars_decode:3}
<div class="panel">
	<h3><i class="icon icon-warning"></i> {l s='IMPORTANT' mod='bayonetantifraud'}</h3>
	{l s='READ THE MANUAL' mod='bayonetantifraud'}
	<br />
	{l s='Don\'t forget to read this module\'s manual before using it. Reading the manual will help you a lot in understanding how to configure the module and how it works, in this way, navigating throughout its features will be a lot easier for you.' mod='bayonetantifraud'}
	<br />
	<br />
	{l s='Enabling this module\'s features will require for you to enter your API keys, to obtain them you need to log into' mod='bayonetantifraud'} <a href="https://bayonet.io/login" target="_blank" rel="noopener noreferrer">{l s='Bayonet\'s Console' mod='bayonetantifraud'}</a> {l s='with your Bayonet credentials, once you are logged in, go to the ' mod='bayonetantifraud'} <a href="https://bayonet.io/developers/setup" target="_blank" rel="noopener noreferrer">{l s='Setup section' mod='bayonetantifraud'}</a>{l s=', where you can get them or generate them if you haven\'t done that.' mod='bayonetantifraud'}
	{l s='If you haven\'t received your Bayonet credentials yet, please send an email to' mod='bayonetantifraud'} <a href="mailto:prestashop@bayonet.io">prestashop@bayonet.io</a>
	{l s='with your information to provide you with them.' mod='bayonetantifraud'}
	<br />
	<br />
	{l s='In order to be able to use Bayonet in Live Mode properly, you must add both your IP address and your domain to the whitelist in' mod='bayonetantifraud'} <a href="https://bayonet.io/login" target="_blank" rel="noopener noreferrer">{l s='Bayonet\'s Console' mod='bayonetantifraud'}</a>. {l s='This is located in the' mod='bayonetantifraud'} <a href="https://bayonet.io/developers/setup" target="_blank" rel="noopener noreferrer">{l s='Setup section' mod='bayonetantifraud'}</a>, {l s='the same place where you get your API keys.' mod='bayonetantifraud'}
	<br />
	<br />
	{l s='If you need any support regarding this module, you can either send a mail to' mod='bayonetantifraud'} <a href="mailto:prestashop@bayonet.io">prestashop@bayonet.io</a> {l s=' or contact us via Intercom on our ' mod='bayonetantifraud'} <a href="https://bayonet.io/" target="_blank" rel="noopener noreferrer">{l s='Website' mod='bayonetantifraud'}</a>{l s='.' mod='bayonetantifraud'}
</div>
