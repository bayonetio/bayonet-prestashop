/**
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
 */

$(document).ready(function(){
  $('#btn-addWhite').on('click', function(){
    email = $(this).attr('data-mail');
    idCustomer = $(this).attr('data-customer');
    idBlockList = $(this).attr('data-id');
    whitelist = $(this).attr('data-whitelist');
    blacklist = $(this).attr('data-blacklist');

    $.ajax({
      url: urlBlockList,
      type: 'post',
      data: {
        mail:email,
        customer:idCustomer,
        id:idBlockList,
        whitelist:whitelist,
        blacklist:blacklist,
        mode:'addWhite'
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
    idBlockList = $(this).attr('data-id');
    whitelist = $(this).attr('data-whitelist');
    blacklist = $(this).attr('data-blacklist');

    $.ajax({
      url: urlBlockList,
      type: 'post',
      data: {
        mail:email,
        customer:idCustomer,
        id:idBlockList,
        whitelist:whitelist,
        blacklist:blacklist,
        mode:'removeWhite'
      },
      dataType: 'json',
      success: function(data) {
        location.reload();
      },
    });
  });

  $('#btn-addBlack').on('click', function(){
    email = $(this).attr('data-mail');
    idCustomer = $(this).attr('data-customer');
    idBlockList = $(this).attr('data-id');
    whitelist = $(this).attr('data-whitelist');
    blacklist = $(this).attr('data-blacklist');

    $.ajax({
      url: urlBlockList,
      type:'post',
      data: {
        mail:email,
        customer:idCustomer,
        id:idBlockList,
        whitelist:whitelist,
        blacklist:blacklist,
        mode:'addBlack'
      },
      dataType: 'json',
      success: function(data) {
        location.reload();
      },
    });
  });

  $('#btn-removeBlack').on('click', function(){
    email = $(this).attr('data-mail');
    idCustomer = $(this).attr('data-customer');
    idBlockList = $(this).attr('data-id');
    whitelist = $(this).attr('data-whitelist');
    blacklist = $(this).attr('data-blacklist');

    $.ajax({
      url: urlBlockList,
      type: 'post',
      data: {
        mail:email,
        customer:idCustomer,
        id:idBlockList,
        whitelist:whitelist,
        blacklist:blacklist,
        mode:'removeBlack'
      },
      dataType: 'json',
      success: function(data) {
        location.reload();
      },
    });
  });
});