{foreach $sources as $source}
  <div>
    <input type="checkbox"
           name="s[]"
           value="{$source->id}"
           id="s_{$source->id}">
    {$source->shortName|escape}
  </div>
{/foreach}
