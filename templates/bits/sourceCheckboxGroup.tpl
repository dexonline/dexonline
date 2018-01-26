{foreach Source::getAll() as $source}
  <span>
    <input type="checkbox"
           name="s[]"
           value="{$source->id}"
           id="s_{$source->id}">
    {$source->shortName|escape}
  </span>
{/foreach}
