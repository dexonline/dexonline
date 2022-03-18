{if count($sources)}
  <span class="meaning-sources tag-group">
    {foreach $sources as $s}
      <span class="badge badge-source" title="{$s->name}, {$s->year}">
        {$s->shortName}
      </span>
    {/foreach}
  </span>
{/if}
