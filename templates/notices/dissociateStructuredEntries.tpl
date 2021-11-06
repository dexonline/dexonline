Nu aveți dreptul să disociați intrările structurate:

{foreach $entries as $i => $e}
  {if $i}|{/if}
  {include "bits/entry.tpl" entry=$e editLink=true editLinkClass="alert-link"}
{/foreach}
