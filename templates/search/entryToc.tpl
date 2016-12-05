{* when there are multiple entries, display a ToC-style list of entries *}
{if count($entries) > 1}
  <ul>
    {foreach $entries as $e}
      <li>

        {* If there is exactly one entry, do not link to the entry page, because
           it would print an almost exact duplicate of this page. *}
        {include "bits/entry.tpl" entry=$e link=(count($entries) > 1) variantList=true}


      </li>
    {/foreach}
  </ul>
{/if}

