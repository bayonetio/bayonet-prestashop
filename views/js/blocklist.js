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

$(document).ready(function(){
  $('#btn-addWhite').on('click', function(){
    var email = $(this).attr('data-mail');
    var idCustomer = $(this).attr('data-customer');
    var idBlocklist = $(this).attr('data-id');
    var whitelist = $(this).attr('data-whitelist');
    var blocklist = $(this).attr('data-blocklist');
    var apiMode = $(this).attr('data-mode');

    if ($('#btn-addBlock').length) {
      $('#btn-addBlock').prop("disabled", true);
    } else if ($('#btn-removeBlock').length) {
      $('#btn-removeBlock').prop("disabled", true);
    }

    $.ajax({
      url: urlBlocklist,
      type: 'POST',
      data: {
        mail: email,
        customer: idCustomer,
        id: idBlocklist,
        whitelist: whitelist,
        blocklist: blocklist,
        mode:'addWhite',
        apiMode: apiMode
      },
      dataType: 'json',
      success: function(data) {
        location.reload();
      },
    });
  });

  $('#btn-removeWhite').on('click', function(){
    var email = $(this).attr('data-mail');
    var idCustomer = $(this).attr('data-customer');
    var idBlocklist = $(this).attr('data-id');
    var whitelist = $(this).attr('data-whitelist');
    var blocklist = $(this).attr('data-blocklist');
    var apiMode = $(this).attr('data-mode');

    if ($('#btn-addBlock').length) {
      $('#btn-addBlock').prop("disabled", true);
    }

    $.ajax({
      url: urlBlocklist,
      type: 'post',
      data: {
        mail: email,
        customer: idCustomer,
        id: idBlocklist,
        whitelist: whitelist,
        blocklist: blocklist,
        mode: 'removeWhite',
        apiMode: apiMode
      },
      dataType: 'json',
      success: function(data) {
        location.reload();
      },
    });
  });

  $('#btn-addBlock').on('click', function(){
    var email = $(this).attr('data-mail');
    var idCustomer = $(this).attr('data-customer');
    var idBlocklist = $(this).attr('data-id');
    var whitelist = $(this).attr('data-whitelist');
    var blocklist = $(this).attr('data-blocklist');
    var apiMode = $(this).attr('data-mode');

    if ($('#btn-addWhite').length) {
      $('#btn-addWhite').prop("disabled", true);
    } else if ($('#btn-removeWhite').length) {
      $('#btn-removeWhite').prop("disabled", true);
    }

    $.ajax({
      url: urlBlocklist,
      type:'post',
      data: {
        mail: email,
        customer: idCustomer,
        id: idBlocklist,
        whitelist: whitelist,
        blocklist: blocklist,
        mode: 'addBlock',
        apiMode: apiMode
      },
      dataType: 'json',
      success: function(data) {
        location.reload();
      },
    });
  });

  $('#btn-removeBlock').on('click', function(){
    var email = $(this).attr('data-mail');
    var idCustomer = $(this).attr('data-customer');
    var idBlocklist = $(this).attr('data-id');
    var whitelist = $(this).attr('data-whitelist');
    var blocklist = $(this).attr('data-blocklist');
    var apiMode = $(this).attr('data-mode');

    if ($('#btn-addWhite').length) {
      $('#btn-addWhite').prop("disabled", true);
    }

    $.ajax({
      url: urlBlocklist,
      type: 'post',
      data: {
        mail: email,
        customer: idCustomer,
        id: idBlocklist,
        whitelist: whitelist,
        blocklist: blocklist,
        mode: 'removeBlock',
        apiMode: apiMode
      },
      dataType: 'json',
      success: function(data) {
        location.reload();
      },
    });
  });
});
