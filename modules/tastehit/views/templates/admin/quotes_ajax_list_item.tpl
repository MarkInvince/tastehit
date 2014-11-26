<h3><i class="icon-legal"></i> {l s='Current quotes list' mod="quotes"} <span class="badge">{$totalQuotes}</span></h3>
{if $totalQuotes < 1}
    <div class="alert alert-warning">{l s='No quotes found' mod="quotes"}</div>
{else}
    <table class="table">
        <thead>
        <tr>
            <td class="text-center">{l s="ID" mod="quotes"}</td>
            <td class="text-center">{l s="Quote name" mod="quotes"}</td>
            <td class="text-center">{l s="Customer" mod="quotes"}</td>
            <td class="text-center">{l s="Total Products" mod="quotes"}</td>
            <td class="text-center">{l s="Date add" mod="quotes"}</td>
            <td class="text-center">{l s="Status" mod="quotes"}</td>
            <td class="text-center"><i class="icon-cogs"></i></td>
        </tr>
        </thead>
        <tbody id="quotes_list">
        {foreach $quotes as $quote}
            {include file="$tpl_dir./quotes_list_item.tpl"}
        {/foreach}
        </tbody>
    </table>
{/if}