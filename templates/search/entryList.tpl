<p class="entryList">
  {foreach $entries as $e}
    <span>
      <a href="{$wwwRoot}intrare/{$e->getShortDescription()}/{$e->id}">
        {$e->description|escape}
      </a>
    </span>
  {/foreach}
</p>
