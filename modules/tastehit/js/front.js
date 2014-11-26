/**
* 2007-2014 PrestaShop
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
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/
function setHiddenShow(elem_id) {
	document.getElementById(elem_id).value = 1;
}

$(document).ready(function(){
	// Quote details and bargains
	$('.show_quote_details').on('click', function() {

		var $id_quote = $(this).data('id');

		$.ajax({
			url: submitedQuotes,
			method:'post',
			data:
			{
				action : 'showQuoteDetails',
				id_quote : $id_quote
			},
			dataType:'json',
			success: function(data) {
				$('.ajax_item').fadeOut(500);
				$('.ajax_item').remove();

				$('#quotes-list .quote_' + $id_quote).after('<tr class="item ajax_item" style="display: none"><td colspan="7">' + data.details + '</td></tr>');

				$('#quotes-list .ajax_item').fadeIn(500);
			}
		});
		return false;
	});

	$('#submit-added').fancybox();
	// Change quote name
	$( ".quote_name" ).on( "click", function() {
		var $thisQuoteName = $(this);
		var $oldName = $thisQuoteName.text();
		if($oldName == ''){
			var $myInput = $($thisQuoteName).find('.changed_name');
		}else {
			$($thisQuoteName).html('<input class="changed_name" type="text" value="' + $oldName + '">');
			var $myInput = $($thisQuoteName).find('.changed_name');
		}
		$myInput.focus();
		$myInput.on( "blur", function() {
			var $id_quote = $thisQuoteName.data('value');
			var $quoteName = $myInput.val();
			$.ajax({
				url: submitedQuotes,
				method:'post',
				data:
				{
					quoteRename: 'rename',
					id_quote : $id_quote,
					quoteName : $quoteName
				},
				dataType:'json',
				success: function(data) {
					if(data.hasError){
							alert(data.message);
							$thisQuoteName.html('<i class="icon-pencil"></i>' + $oldName);
					}
					if(data.renamed){
						$thisQuoteName.html('<i class="icon-pencil"></i>' + data.renamed);
					}
				}
			});
		});
	});

	// Show quote products
	$('body').on('click', '#show_quote_products_info', function (e) {
		e.preventDefault();
		$('#quote_products_info').toggle('slow');
	});

	// Add client bargain message
	$('body').on('click', '#addClientBargain', function() {
		$.ajax({
			url: submitedQuotes,
			method:'post',
			data: $('#client_bargain_txt').serialize(),
			dataType:'json',
			success: function(data) {
				if(data.errors) {
					$.each(data.errors, function (key, value) {
						$('#errors_bargain_message').append('<p>' + key + ": " + value + '</p>');
					});
					$('#errors_bargain_message').fadeIn(500);
				}
				else {
					$('#errors_bargain_message').css('display','none');

					var $out_msg = '<li class="customer_bargain clearfix"><div class="row"><div class="bargain_heading clearfix"><div class="date col-xs-9"><p class="bargain_whos">';
					$out_msg += your_msg;
					$out_msg += '</p></div><div class="date col-xs-3"><strong>';
					$out_msg += added;
					$out_msg += ' </strong>';
					$out_msg += getDateTime();
					$out_msg +='</div></div><div class="bargain_message col-xs-12 box">';
					$out_msg += $('#bargain_text').val();
					$out_msg += '</div></div></li>';
					$('.bargains_list .bargains_list_warning').fadeOut(500);
					$('.bargains_list').prepend($out_msg);

					$('#bargain_text').val('');

					$('#success_bargain_message').fadeIn(500);
					setTimeout(function() {
						$('#success_bargain_message').fadeOut(500);
					}, 2000);
				}
			}
		});
		return false;
	});

	//Submit bargain price
	$('body').on('click', '.rejectBargainOffer, .acceptBargainOffer', function() {
		var $action = $(this).data('action');
		var $id_bargain = $(this).data('id');
		var $id_quote = $(this).data('quote');

		$.ajax({
			url: submitedQuotes,
			method:'post',
			data:
			{
				actionSubmitBargain : $action,
        		id_quote : $id_quote,
				id_bargain : $id_bargain
			},
			dataType:'json',
			success: function(data) {
				if(data.hasError)
					$('#danger_bargain_' + $id_bargain).css('display', 'block');
				else
					$('.burgainSubmitForm').css('display', 'none');

				if(data.submited == 'reject')
					$('#reject_bargain_' + $id_bargain).css('display', 'block');
				if(data.submited == 'accept')
					$('#success_bargain_' + $id_bargain).css('display', 'block');
			}
		});
		return false;
	});


	//input item quantity cart change
	$('body').on('change', '.cart_quantity_input', function(){
		var input = $(this);
		$.ajax({
			url: quotesCart,
			method:'post',
			data: 'action=recount&method=set&item_id='+input.attr('rel')+'&value='+input.val(),
			dataType:'json',
			success: function(response) {
				if(response.hasError == false) {
					$('#quotes-cart-wrapper').empty();
					$('#quotes-cart-wrapper').html(response.data);

					// insert cart header
					$('#quotes-cart-link').empty();
					$('#quotes-cart-link').html(response.header);

					$('#product-list').empty();
					$('#product-list').html(response.products);
				}
				else
					alert(response.data.message);
			}
		});
	});
	// minus item quote cart
	$('body').on('click', '.quote-plus-button', function(){
		var current = $(this).closest('.quotes_cart_quantity').find('.cart_quantity_input');
			if($('#order-detail-content').find('.overlay').length == 0)
				$('#order-detail-content').append('<div class="overlay-wrapper"><div class="overlay"></div></div>');
			var button = $(this);
			$.ajax({
				url: quotesCart,
				method:'post',
				data: 'action=recount&method=add&item_id='+button.attr('rel')+'&value='+current.val()+'&button=1',
				dataType:'json',
				success: function(response) {
					if(response.hasError == false) {
						$('#quotes-cart-wrapper').empty();
						$('#quotes-cart-wrapper').html(response.data);

						// insert cart header
						$('#quotes-cart-link').empty();
						$('#quotes-cart-link').html(response.header);

						$('#product-list').empty();
						$('#product-list').html(response.products);
					}
					else
						alert(response.data.message);
				}
			});
	});
	// plus item quote cart
	$('body').on('click', '.quote-minus-button', function(){
		var current = $(this).closest('.quotes_cart_quantity').find('.cart_quantity_input');
		if(current.val() != 1) {
			if($('#order-detail-content').find('.overlay').length == 0)
				$('#order-detail-content').append('<div class="overlay-wrapper"><div class="overlay"></div></div>');
			var button = $(this);
			$.ajax({
				url: quotesCart,
				method:'post',
				data: 'action=recount&method=remove&item_id='+button.attr('rel')+'&value='+current.val()+'&button=1',
				dataType:'json',
				success: function(response) {
					if(response.hasError == false) {
						$('#quotes-cart-wrapper').empty();
						$('#quotes-cart-wrapper').html(response.data);

						// insert cart header
						$('#quotes-cart-link').empty();
						$('#quotes-cart-link').html(response.header);

						$('#product-list').empty();
						$('#product-list').html(response.products);
					}
					else
						alert(response.data.message);
				}
			});
		}
	});

	$('body').on('click', '.submit_quote', function() {
		$.ajax({
			url: quotesCart,
			method:'post',
			data: 'action=submit',
			dataType:'json',
			success: function(response) {
				console.log(response);
				if(response.hasError == false) {
					window.location = response.redirectUrl;
				}
			}
		});
		return false;
	});
	$('body').on('click', '.remove_quote', function(){
		var elem = $(this);
		$.ajax({
			url: quotesCart,
			method: 'post',
			data: 'action=delete_from_cart&item_id='+ $(this).attr('rel'),
			dataType: 'json',
			success: function(response) {
				if(response.hasError == false) {
					$('#quotes-cart-wrapper').empty();
					$('#quotes-cart-wrapper').html(response.data);

					// insert cart header
					$('#quotes-cart-link').empty();
					$('#quotes-cart-link').html(response.header);

					$('#product-list').empty();
					$('#product-list').html(response.products);
				}
				else
					alert(response.data.message);
			}
		});
	});

    $('body').on('click','.fly_to_quote_cart_button', function(){
		var this_element = $(this);
		if (this_element.closest('form.quote_ask_form').find('.product_list_opt').val() == 1)
			var product_list = 1;
		if (product_list != 1)
			this_element.closest('form.quote_ask_form').find('.ipa').val($('#idCombination').val());

		if(catalogMode == false) {
			if (product_list != 1)
				this_element.closest('form.quote_ask_form').find('.pqty').val(parseInt($('#quantity_wanted').val()));
		}

		var score_x = $('#quotes-cart-link').offset().left;
		var score_y = $('#quotes-cart-link').offset().top;

		if (product_list != 1)
			var image = $("#bigpic");
		else
			var image = this_element.closest('.product-container').find('.product_img_link img');

		// fly to cart animation
		var top =  this_element.offset().top - 150;
		var left = this_element.offset().left;
		var class_name = 'basket_add_indicator_' + new Date().getTime();

		$.ajax({
			url: quotesCart,
			method:'post',
			data: this_element.closest('form.quote_ask_form').serialize(),
			dataType:'json',
			success: function(response) {
				$("body").append('<img src="'+image.attr('src')+'" style="width: 150px;height:150px;position:absolute;z-index: 99999;opacity:0;left:' + left+ 'px;top:' + top + 'px" class="'+class_name+'" alt="" />');

				$('.' + class_name).animate({"opacity" : "1"}, 600, function () {
					$('.' + class_name).animate({
						'left': score_x,
						'top': score_y,
						'width': '20px',
						'height': '20px',
						'opacity' : '0.2'
					}, 800, function () {
						$(this).remove();
						/*if(!$('#box-body').hasClass('expanded'))
							$('#box-body').addClass('expanded');*/
						// insert cart content
						$('#product-list').empty();
						$('#product-list').html(response.products);

						// insert cart header
						$('#quotes-cart-link').empty();
						$('#quotes-cart-link').html(response.header);
					});
				});
			}
		});
		return false;
    });

	//close popup events
	$(document).on('click', '#quotes_layer_cart .cross, #quotes_layer_cart .continue, .quotes_layer_cart_overlay', function(e){
		e.preventDefault();
		$('.quotes_layer_cart_overlay').hide(function(){
			$('.quotes_layer_cart_overlay').remove();
		});

		$('#quotes_layer_cart').fadeOut('fast', function(){
			$(this).remove();
		});
	});
	$('#columns #quotes_layer_cart, #columns .quotes_layer_cart_overlay').detach().prependTo('#columns');
	//add product to quotes with popup
	$('body').on('click','.ajax_add_to_quote_cart_button', function(){
		var this_element = $(this);

		if (this_element.closest('form.quote_ask_form').find('.product_list_opt').val() != 1)
			this_element.closest('form.quote_ask_form').find('.ipa').val($('#idCombination').val());
		//$('#ipa').val($('#idCombination').val());
		$.ajax({
			url: quotesCart,
			method:'post',
			data: this_element.closest('form.quote_ask_form').serialize(),
			dataType:'json',
			success: function(response) {

				$.ajax({
					url: quotesCart,
					method:'post',
					data: this_element.closest('form.quote_ask_form').serialize()+'&showpop&action=popup',
					dataType:'json',
					success: function(response) {
						$('#columns').append(response.popup);
						$('#quotes_layer_cart').css('display', 'block');
						var $scroll = $(window).scrollTop();
						$scroll = $scroll + 'px';
						$('#quotes_layer_cart').css('top', $scroll);

						$('.quotes_layer_cart_overlay').css('display', 'block');
						$('.quotes_layer_cart_overlay').css('width', '100%');
						$('.quotes_layer_cart_overlay').css('height', '100%');
					}
				});

				// insert cart header
				$('#quotes-cart-link').empty();
				$('#quotes-cart-link').html(response.header);

				$('#product-list').empty();
				$('#product-list').html(response.products);
				/*if(!$('#box-body').hasClass('expanded'))
					$('#box-body').addClass('expanded');
				*/
			}
		});
		return false;
	});

	$('body').on('click', '.remove-quote', function() {
		var item = $(this).attr('rel');
		var item_a = $(this);
		$.ajax({
			url: quotesCart,
			method:'post',
			data: 'action=delete&item_id='+item,
			dataType:'json',
			success: function(response) {
				item_a.closest('dt').fadeOut('slow', function(){
					item_a.closest('dt').remove();
				});
				$('#product-list').empty();
				$('#product-list').html(response.products);

				$('#quotes-cart-link').empty();
				$('#quotes-cart-link').html(response.header);
			}
		});
	});

	// quotes cart actions
	var cart_block = new showCart('#header .quotes_cart_block');
	var cart_parent_block = new showCart('#header .quotes_cart');

	$("#header .quotes_cart a:first").hover(
		function(){
				$("#header .quotes_cart_block").stop(true, true).slideDown(450);
		},
		function(){
			setTimeout(function(){
				if (!cart_parent_block.isHoveringOver() && !cart_block.isHoveringOver()) {
					$("#header .quotes_cart_block").stop(true, true).slideUp(450);
					if($('#box-body').hasClass('expanded'))
						$('#box-body').removeClass('expanded');
				}

			}, 200);
		}
	);

	$("#header .cart_block").hover(
		function(){
		},
		function(){
			setTimeout(function(){
				if (!cart_parent_block.isHoveringOver()) {
					$("#header .quotes_cart_block").stop(true, true).slideUp(450);
					if($('#box-body').hasClass('expanded'))
						$('#box-body').removeClass('expanded');
				}
			}, 200);
		}
	);
});
function showCart(selector)
{
	this.hovering = false;
	var self = this;

	this.isHoveringOver = function(){
		return self.hovering;
	}

	$(selector).hover(function(){
		self.hovering = true;
	}, function(){
		self.hovering = false;
	})
}

function getDateTime() {
	var now     = new Date();
	var year    = now.getFullYear();
	var month   = now.getMonth()+1;
	var day     = now.getDate();
	var hour    = now.getHours();
	var minute  = now.getMinutes();
	var second  = now.getSeconds();
	if(month.toString().length == 1) {
		var month = '0'+month;
	}
	if(day.toString().length == 1) {
		var day = '0'+day;
	}
	if(hour.toString().length == 1) {
		var hour = '0'+hour;
	}
	if(minute.toString().length == 1) {
		var minute = '0'+minute;
	}
	if(second.toString().length == 1) {
		var second = '0'+second;
	}
	var dateTime = year+'-'+month+'-'+day+' '+hour+':'+minute+':'+second;
	return dateTime;
}