{* argument: $ifArray: array of InflectedForm *}

{if empty($ifArray)}
  &mdash;
{else}
  <ul class="commaList">
    {foreach $ifArray as $i => $if}
      {strip}
      <li class="{$if->getHtmlClasses()}" title="{$if->getHtmlTitles()}">
        {$if->getHtmlForm()}
      </li>
      {/strip}
    {/foreach}
  </ul>
{/if}
