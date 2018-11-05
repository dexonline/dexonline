{$modelTypeName=$modelTypeName|default:'modelType'}
{$modelNumberName=$modelNumberName|default:'modelNumber'}
{$selectedModelType=$selectedModelType|default:''}
{$selectedModelNumber=$selectedModelNumber|default:''}
{$allOption=$allOption|default:''} {* format: display_value|submit_value *}
{* additional arguments: $modelTypes *}

<span data-model-dropdown>
  <select
    name="{$modelTypeName}"
    class="form-control"
    data-model-type>
    {foreach $modelTypes as $mt}
      <option
        value="{$mt->code}"
        {if $mt->code == $selectedModelType}selected{/if}>
        {$mt->code}
      </option>
    {/foreach}
  </select>

  <select
    name="{$modelNumberName}"
    class="form-control"
    data-model-number
    data-selected="{$selectedModelNumber}"
    data-all-option="{$allOption}">
  </select>
</span>
