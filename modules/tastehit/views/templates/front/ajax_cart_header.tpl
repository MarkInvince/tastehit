<b>{l s='Quotes' mod='quotes'}</b>
<span class="ajax_cart_quantity{if $cartTotalProducts == 0} unvisible{/if}">{$cartTotalProducts}</span>
<span class="ajax_cart_product_txt{if $cartTotalProducts != 1} unvisible{/if}">{l s='Product' mod='quotes'}</span>
<span class="ajax_cart_product_txt_s{if $cartTotalProducts < 2} unvisible{/if}">{l s='Products' mod='quotes'}</span>
<span class="ajax_cart_no_product{if $cartTotalProducts > 0} unvisible{/if}">{l s='(empty)' mod='quotes'}</span>
