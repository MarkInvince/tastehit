<h1>{l s='Tastehit products'}</h1>
{if $page_name == 'index'}
    {if $th_display_home}
        <div id="thRecommendations"></div>
    {/if}
{/if}
{if $page_name == 'product'}
    {if $th_display_product}
        <div id="thRecommendations"></div>
    {/if}
{/if}
{if $page_name == 'category'}
    {if $th_display_category}
        <div id="thRecommendations"></div>
    {/if}
{/if}
