<select name="{$modelTypes.vars.name}"
  {if $modelTypes.vars.id}id="{$modelTypes.vars.id}"{/if}
  class="form-control"
  data-model-type
  {if $modelTypes.vars.multiple}multiple{/if}>
  {foreach $modelTypes.resultSet as $model}
  <option
    value="{$model->$modelTypes.vars.submitValue}"
    {if $modelTypes.vars.selectedValue == $model->$modelTypes.vars.submitValue}selected{/if}
    data-canonical={$model->canonical}>
      {$model->code|escape}
  </option>
  {/foreach}
</select>