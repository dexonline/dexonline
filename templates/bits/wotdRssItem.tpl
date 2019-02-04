{if $imageUrl}
  <p>
    <img src="{$imageUrl}" alt="{$def->lexicon}">
  </p>
{/if}

<p>{$html}</p>

<p>
  Sursa:
  <a
    class="ref"
    href="{Config::URL_PREFIX}surse"
    title="{$source->name|escape}, {$source->year|escape}">
    {$source->shortName|escape}

    {if $source->year}
      ({$source->year|regex_replace:"/ .*$/":""})
    {/if}
  </a>
</p>

<p>
  Cheia alegerii: {$reason}
</p>
