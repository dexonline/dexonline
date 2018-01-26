{foreach Source::getAll(Source::SORT_SHORT_NAME) as $source}
  <span>
    <input type="checkbox"
           name="s[]"
           value="{$source->id}"
           id="s_{$source->id}">
    {$source->shortName|escape}
  </span>
{/foreach}
