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

let interval = '';
$(document).ready(function(){
  interval = setInterval(getStatus, 2000);
  $('#initiate').on('click', function(){
    $.ajax({
      url: urlBackfill,
	  type: 'post',
	  data: {mode:'initiate'},
	  dataType: 'json',
	  success: function(data) {
	    if(data.error == 0) {
	  	  $.ajax({
	        url: urlBackfill,
		    type: 'post',
		    data: {mode:'execute'},
		    dataType: 'json',
		    success: function(result) {
		    }
		  });
		  location.reload();
	    } else {
	  	  $('#error-msg').html('<div class="alert alert-danger" id="error-msg">Unable to initiate history backfill please try again</div>');
	    }
	  }
	});
  });

  $('#stop').on('click', function(){
    $.ajax({
  	  url: urlBackfill,
	  type: 'post',
	  data: {mode:'stop'},
	  dataType: 'json',
	  success: function(data) {
	    if(data.error == 0) {
		  location.reload();
		} else {
	      $('#error-msg').html('<div class="alert alert-danger" id="error-msg">Unable to stop history backfill please try again</div>');
	    }
	  }
	});
  });
});

/**
 * Gets the current status of the backfill process.
 */
function getStatus(){
  $.ajax({
    url: urlBackfill,
	type: 'post',
	data: {mode:'status'},
	dataType: 'json',
	success: function(data) {
	  if(data.error == 0) {
	    $('.progress-bar').attr('aria-valuenow',data.percentage);
		$('.progress-bar').css('width',data.percentage+'%');
		$('.progress-bar').text(data.percentage+'%');
	  } else {
	  	$('.progress-bar').attr('aria-valuenow',data.percentage);
		$('.progress-bar').css('width',data.percentage+'%');
		$('.progress-bar').text(data.percentage+'%');
		clearInterval(interval);
	  }
	}
  });
}
