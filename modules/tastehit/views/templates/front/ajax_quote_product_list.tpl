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