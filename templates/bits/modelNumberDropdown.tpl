<select name="{$modelNumbers.vars.name}"
  {if $modelNumbers.vars.id}id="{$modelNumbers.vars.id}"{/if}
  class="form-control"
  data-model-number
  data-all-option="{$modelNumbers.vars.allOption}"
  {if $modelNumbers.vars.compoundLexeme}style="display: none"{/if}>
  {if $modelNumbers.vars.allOption}
  <option value="">{$modelNumbers.vars.allOption|escape}</option>
  {/if}
  {foreach $modelNumbers.resultSet as $model}
  <option
    value="{$model->number}"
    {if $modelNumbers.vars.selectedValue == $model->$modelNumbers.vars.submitValue}selected{/if}>
      {$model->number} ({$model->exponent|escape})
  </option>
  {/foreach}
</select>
