{assign var="locParadigm" value=$locParadigm|default:false}
{* argument: $ifArray: array(InflectedForm) *}

{if count($ifArray) == 0}
  &mdash;
{else}
  {strip}
    {foreach $ifArray as $i => $if}
        {assign var="form" value=$if->getHtmlForm()}
        {if $i}, {/if}
        {if !$if->recommended}
          <span class="notRecommended" title="formă nerecomandată">{$form}*</span>
        {elseif !$if->isLoc && $locParadigm}
          <span class="notInLoc" title="formă neacceptată la scrabble">{$form}*</span>
        {else}
          {$form}
        {/if}
    {/foreach}
  {/strip}
{/if}
