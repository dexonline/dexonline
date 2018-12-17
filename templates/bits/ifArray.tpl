{* argument: $ifArray: array of InflectedForm *}

{if empty($ifArray)}
  &mdash;
{else}
  {strip}
  <ul class="commaList">
    {foreach $ifArray as $i => $if}
      <li class="{$if->getHtmlClasses()}" title="{$if->getHtmlTitles()}">
        {$if->getHtmlForm()}
      </li>
    {/foreach}
  </ul>
  {/strip}
{/if}
