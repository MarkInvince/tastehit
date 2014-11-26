<!-- MODULE Quotes cart -->
{if $active_overlay == 0}
    <script type="text/javascript">
        var quotesCart = "{$actionAddQuotes}";
        {if $PS_CATALOG_MODE}
        var catalogMode = true;
        {else}
        var catalogMode = false;
        {/if}

    </script>
<div class="clearfix col-sm-3">
    <div class="row quotes_cart">
        <a href="{$quotesCart}" rel="nofollow" id="quotes-cart-link">
            <b>{l s='Quotes' mod='quotes'}</b>
            <span class="ajax_cart_quantity{if $cartTotalProducts == 0} unvisible{/if}">{$cartTotalProducts}</span>
            <span class="ajax_cart_product_txt{if $cartTotalProducts != 1} unvisible{/if}">{l s='Product' mod='quotes'}</span>
            <span class="ajax_cart_product_txt_s{if $cartTotalProducts < 2} unvisible{/if}">{l s='Products' mod='quotes'}</span>
            <span class="ajax_cart_no_product{if $cartTotalProducts > 0} unvisible{/if}">{l s='(empty)' mod='quotes'}</span>
        </a>
        <div class="col-sm-12 quotes_cart_block exclusive" id="box-body" style="display:none;">
            <div class="block_content">
                <div class="row product-list" id="product-list">
                    {if $cartTotalProducts > 0}
                        <dl class="products" id="quotes-products">
                            {foreach $products as $key=>$product}
                                {if is_numeric($key)}
                                    <dt class="item">
                                        <a class="cart-images" href="{$product.link}" title="{$product.title}">
                                            <img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'cart_default')}" alt="{$product.title}">
                                        </a>
                                        <div class="cart-info">
                                            <div class="product-name">
                                                <span class="quantity-formated"><span class="quantity">{$product.quantity}</span>&nbsp;x&nbsp;</span><a class="cart_block_product_name" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.title|escape:'html':'UTF-8'}">{$product.title|truncate:20:'...'|escape:'html':'UTF-8'}</a>
                                            </div>
                                            <span class="price">
                                                {$product.unit_price}
                                            </span>
                                            <div class="remove-wrap">
                                                <hr/>
                                                <a href="javascript:void(0);" rel="{$product.id}_{$product.id_attribute}" class="remove-quote">{l s="Remove"}</a>
                                            </div>
                                        </div>
                                    </dt>
                                {/if}
                            {/foreach}
                        </dl>
                        <div class="quotes-cart-prices">
                            <div class="row">
                                <span class="col-xs-12 col-lg-6 text-center">{l s="Total:" mod="quotes"}</span>
                                <span class="col-xs-12 col-lg-6 text-center">{$cart.total}</span>

                            </div>
                        </div>
                    {else}
                        <div class="alert">
                            {l s="No products to quote"}
                        </div>
                    {/if}
                </div>
                <p class="cart-buttons">
                    {if isset($isLogged) && $isLogged > 0}
                        <a id="button_order_cart" class="btn btn-default button button-small submit_quote" href="javascript:void(0);" title="{l s='Submit quote' mod='quotes'}" rel="nofollow">
                            <span>
                                {l s='Submit now' mod='quotes'}<i class="icon-chevron-right right"></i>
                            </span>
                        </a>
                    {else}
                        <a id="button_order_cart" class="btn btn-default button button-small" href="{$quotesCart}" title="{l s='Submit quote' mod='quotes'}" rel="nofollow">
                            <span>
                                {l s='Check out' mod='quotes'}<i class="icon-chevron-right right"></i>
                            </span>
                        </a>
                    {/if}
                </p>
            </div>
        </div>
    </div>
</div>

{elseif $active_overlay == 1}
    <div id="quotes_layer_cart">
        <div class="clearfix">
            <div class="quotes_layer_cart_product col-xs-12 col-md-6">
                <span class="cross" title="{l s='Close window' mod='quotes'}"></span>
                <h2>
                    <i class="icon-ok-circle"></i>{l s='Product successfully added to your shopping cart' mod='quotes'}
                </h2>
                <!--<div class="product-image-container layer_cart_img">
                </div>
                <div class="quotes_layer_cart_product_info">
                    <span id="quotes_layer_cart_product_title" class="product-name"></span>
                    <span id="quotes_layer_cart_product_attributes"></span>
                    <div>
                        <strong class="dark">{l s='Quantity' mod='quotes'}</strong>
                        <span id="quotes_layer_cart_product_quantity"></span>
                    </div>
                    <div>
                        <strong class="dark">{l s='Total' mod='quotes'}</strong>
                        <span id="layer_cart_product_price"></span>
                    </div>
                </div>-->
            </div>
            <div class="quotes_layer_cart_cart col-xs-12 col-md-6">
                <!--<h2>
					<span class="ajax_cart_product_txt_s {if $total_count < 2} unvisible{/if}">
						{l s='There are [1]%d[/1] items in your cart.' mod='quotes' sprintf=[$total_count] tags=['<span class="ajax_cart_quantity">']}
					</span>
					<span class="ajax_cart_product_txt {if $total_count > 1} unvisible{/if}">
						{l s='There is 1 item in your cart.' mod='quotes'}
					</span>
                </h2>-->
                <br/>
                <hr/>
                <!--<div class="layer_cart_row">
                    <strong class="dark">
                        {l s='Total' mod='quotes'}
                    </strong>
					<span class="ajax_block_products_total">
						ok2
					</span>
                </div>
                <div class="layer_cart_row">
                    <strong class="dark">
                        {l s='Total' mod='quotes'}
                    </strong>
					<span class="ajax_block_cart_total">
						{if $total_count > 0}
                            {$total}
                        {/if}
					</span>
                </div>-->
                <div class="button-container">
					<span class="continue btn btn-default button exclusive-medium" title="{l s='Continue shopping' mod='quotes'}">
						<span>
							<i class="icon-chevron-left left"></i>{l s='Continue shopping' mod='quotes'}
						</span>
					</span>
                    <a class="btn btn-default button button-medium"	href="{$link->getModuleLink('quotes','QuotesCart')}" title="{l s='Proceed to checkout' mod='quotes'}" rel="nofollow">
						<span>
							{l s='Proceed to checkout' mod='quotes'}<i class="icon-chevron-right right"></i>
						</span>
                    </a>
                </div>
                <hr/>
            </div>
        </div>
        <div class="crossseling"></div>
    </div> <!-- #layer_cart -->
    <div class="quotes_layer_cart_overlay"></div>
{/if}
<!-- /MODULE Quotes cart -->