{* when there are multiple entries, display a ToC-style list of entries *}
{if count($entries) > 1}
  <ul class="read-more ms-3" data-read-more-lines="5">
    {foreach $entries as $e}
      <li>
        {include "bits/entry.tpl" entry=$e link=true tagList=true}
      </li>
    {/foreach}
  </ul>
{/if}
