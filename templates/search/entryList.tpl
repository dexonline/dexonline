<ul class="list-inline list-inline-bullet list-inline-bullet-sm">
  {foreach $entries as $e}
    <li class="list-inline-item">
      <a href="{Config::URL_PREFIX}intrare/{$e->getShortDescription()}/{$e->id}">
        {$e->description|escape}
      </a>
    </li>
  {/foreach}
</ul>
