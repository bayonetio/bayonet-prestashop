/**
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
*/

$(document).ready(function() {
  if (1 === parseInt(bayonet_enabled)) {
  $.getScript('https://cdn.bayonet.io/fingerprinting-2.0.min.js', function() {
  	initBayonet();
  });
}
});

/**
 * Initializes the device fingerprint script and executes
 * the callback function to get the generated token.
 */
function initBayonet() {
  _bayonet.init({
    js_key: bayonet_af_js_key,
    callback_function: "getResponse"
  });
  _bayonet.track();
}


/**
 * Gets the response from the fingerprint script and sends the
 * fingerprint token to the php fingerprint controller via an ajax call.
 *
 * @param {Object} response
 */
function getResponse(response) {
  if(response.bayonet_fingerprint_token) {
    $.ajax({
      url: urlFingerprint,
      type: 'post',
      data: {
        fingerprint: response.bayonet_fingerprint_token,
        apiMode: bayonet_api_mode
      },
      dataType: 'json'
    });
  }
}
