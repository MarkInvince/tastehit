<div class="ask_offer clearfix">
    <form class="quote_ask_form" action="{$actionAddQuotes}" method="post">
        <input type="hidden" name="action" value="add" />
        <input type="hidden" name="ajax" value="true" />
        <input type="hidden" name="pid" value="{$product.id_product}" />
        <input type="hidden" name="ipa" class="ipa" value="" />
        <input type="hidden" class="pqty" name="pqty" value="1" />
        <input type="hidden" class="product_list_opt" name="product_list_opt" value="1" />
        {if isset($enableAnimation) AND $enableAnimation}
            <button class="fly_to_quote_cart_button btn btn-primary">
                <span>{l s='Ask for a quote' mod='quotes'}</span>
            </button>
        {else}
            <a class="ajax_add_to_quote_cart_button"  title="{l s='Ask for a quote' mod='quotes'}" >
                <span>{l s='Ask for a quote' mod='quotes'}</span>
            </a>
        {/if}
    </form>
</div>