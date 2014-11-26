<div class="panel">
    <ul class="list-group">
        <li class="list-group-item"><i class="icon-remove color-red btn"></i> {l s="Not submited quotes" mod="quotes"}</li>
        <li class="list-group-item"><i class="icon-ok-circle btn color-green"></i> {l s="Submited quotes" mod="quotes"}</li>
        <li class="list-group-item"><i class="icon-mail-forward btn color-green"></i> {l s="Submited and transorm into prestashop order quotes" mod="quotes"}</li>
    </ul>
</div>
<div class="panel" id="quotes_panel">
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
</div>