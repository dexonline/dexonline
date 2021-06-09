<ul class="list-inline list-inline-bullet list-inline-bullet-sm">
  {foreach $entries as $e}
    <li class="list-inline-item">
      {include "bits/entry.tpl" entry=$e editLink=true}
    </li>
  {/foreach}
</ul>
