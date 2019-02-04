<p class="entryList">
  {foreach $entries as $e}
    <span>
      <a href="{Config::URL_PREFIX}intrare/{$e->getShortDescription()}/{$e->id}">
        {$e->description|escape}
      </a>
    </span>
  {/foreach}
</p>
