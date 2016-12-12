{assign var="locParadigm" value=$locParadigm|default:false}
{* argument: $ifArray: array(InflectedForm) *}

{if count($ifArray) == 0}
  &mdash;
{else}
  {strip}
    {foreach $ifArray as $i => $if}
        {assign var="form" value=$if->getHtmlForm()}
        {if !$if->recommended}
          <span class="notRecommended toggleOff" title="formă nerecomandată">{if $i}, {/if}{$form}*</span>
        {elseif !$if->isLoc && $locParadigm}
          <span class="notInLoc" title="formă neacceptată la scrabble">{if $i}, {/if}{$form}*</span>
        {else}
            {if $i}, {/if}{$form}
        {/if}
    {/foreach}
  {/strip}
{/if}
