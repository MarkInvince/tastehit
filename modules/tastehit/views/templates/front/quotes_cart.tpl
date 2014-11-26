{capture name=path}{l s='Ask for a Quote' mod='quotes'}{/capture}
<div class="block">
    <h4 class="title_block">
        {l s='Your quotes cart' mod='quotes'}
    </h4>
</div>
<div id="quotes-cart-wrapper">
    {if isset($products) && count($products) > 0}
        {include file="$tpl_path/quote_product_list.tpl"}
        {if isset($isLogged) && $isLogged == 1 && count($products) > 0}
            <a class="btn btn-success submit_quote" href="javascript:void(0);" title="{l s='Submit now' mod='quotes'}">
            <span>
                {l s='Submit now' mod='quotes'}
                <i class="icon-chevron-right right"></i>
            </span>
            </a>
        {else}
            {include file="$tpl_path/quotes_new_account.tpl"}
        {/if}
        <div {if isset($userRegistry) && $userRegistry==1}style="display: block;"{/if} id="quote_account_saved" class="alert alert-success">
            {l s='Account information saved successfully' mod='quotes'}
        </div>
    {else}
        <p class="alert alert-warning">{l s='No quotes' mod='quotes'}</p>
    {/if}
</div>

{strip}
    {addJsDef authenticationUrl=$link->getPageLink("authentication", true)|escape:'quotes':'UTF-8'}
    {addJsDefL name=txtThereis}{l s='There is' js=1}{/addJsDefL}
    {addJsDefL name=txtErrors}{l s='Error(s)' js=1}{/addJsDefL}
    {addJsDef quoteCartUrl=$link->getModuleLink('quotes', 'QuotesCart', array(), true)|escape:'html':'UTF-8'}
    {addJsDef guestCheckoutEnabled=$PS_GUEST_QUOTES_ENABLED|intval}
    {addJsDef isGuest=$isGuest|intval}
    {addJsDef addressEnabled=$ADDRESS_ENABLED}
{/strip}