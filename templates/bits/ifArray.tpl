{* argument: $ifArray: array of InflectedForm *}

{if empty($ifArray)}
  &mdash;
{else}
  {strip}
    {foreach $ifArray as $i => $if}
        {assign var="form" value=$if->getHtmlForm()}
        {if !$if->recommended}
          <span class="notRecommended" title="formă nerecomandată">{if $i}, {/if}{$form}*</span>
        {else}
            {if $i}, {/if}{$form}
        {/if}
    {/foreach}
  {/strip}
{/if}
