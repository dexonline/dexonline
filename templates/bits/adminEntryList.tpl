<ul class="list-inline bulletList">
  {foreach $entries as $e}
    <li class="list-inline-item">
      {include "bits/entry.tpl" entry=$e editLink=true}
    </li>
  {/foreach}
</ul>
