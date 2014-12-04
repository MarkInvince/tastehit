{extends file="helpers/form/form.tpl"}

{block name="input"}
    {if $input.type == 'export_button'}
        <div class="clearfix export_views">
            <button id="export_button"  class="btn button export_button col-lg-3">
                <i class="icon-upload"></i><span>{l s='Export now' mod='tastehit'}</span>
            </button>
            <div class="module_confirmation conf confirm alert alert-success col-lg-8">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {l s='Export was finished successfully' mod='tastehit'}
            </div>
            <div class="module_confirmation conf confirm alert alert-danger col-lg-8">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {l s='Export was not finished' mod='tastehit'}
            </div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="script"}
    $(document).ready(function(){
        $('#export_button').click(function(e){
            e.preventDefault();
            $.ajax({
                method:'post',
                data: 'action=exportNow',
                dataType:'json',
                success: function(data) {
                    console.log(data);
                    if(data.ajax == 'ok')
                        $('.export_views .alert-success').fadeIn(500);
                    else
                        $('.export_views .alert-danger').fadeIn(500);
                }
            });
        });
    });
{/block}