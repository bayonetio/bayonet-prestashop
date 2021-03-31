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
    email = $(this).attr('data-mail');
    idCustomer = $(this).attr('data-customer');
    idBlocklist = $(this).attr('data-id');
    whitelist = $(this).attr('data-whitelist');
    blocklist = $(this).attr('data-blocklist');
    apiMode = $(this).attr('data-mode');

    $.ajax({
      url: urlBlocklist,
      type: 'POST',
      data: {
        mail:email,
        customer:idCustomer,
        id:idBlocklist,
        whitelist:whitelist,
        blocklist:blocklist,
        mode:'addWhite',
        apiMode:apiMode
      },
      dataType: 'json',
      success: function(data) {
        location.reload();
      },
    });
  });

  $('#btn-removeWhite').on('click', function(){
    email = $(this).attr('data-mail');
    idCustomer = $(this).attr('data-customer');
    idBlocklist = $(this).attr('data-id');
    whitelist = $(this).attr('data-whitelist');
    blocklist = $(this).attr('data-blocklist');
    apiMode = $(this).attr('data-mode');

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
      success: function() {
        location.reload();
      },
    });
  });

  $('#btn-addBlock').on('click', function(){
    email = $(this).attr('data-mail');
    idCustomer = $(this).attr('data-customer');
    idBlocklist = $(this).attr('data-id');
    whitelist = $(this).attr('data-whitelist');
    blocklist = $(this).attr('data-blocklist');
    apiMode = $(this).attr('data-mode');

    console.log(email);
    console.log(idCustomer);
    console.log(idBlocklist);
    console.log(whitelist);
    console.log(blocklist);

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
    email = $(this).attr('data-mail');
    idCustomer = $(this).attr('data-customer');
    idBlocklist = $(this).attr('data-id');
    whitelist = $(this).attr('data-whitelist');
    blocklist = $(this).attr('data-blocklist');
    apiMode = $(this).attr('data-mode');

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
      success: function() {
        location.reload();
      },
    });
  });
});