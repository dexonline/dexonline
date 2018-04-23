{$labelEdited=$labelEdited|default:'default'}
{strip}
<tr id="{$row->id}">
  <td>
    <span class="label label-{$labelEdited}">{$row->id}</span>
  </td>
  {* define the function *}
  {function name=prop}
    {foreach $props as $checked}
      {$label = ($checked) ? 'success' : 'primary'}
      {$icon = ($checked) ? 'ok' : 'minus'}
      <td>
        <label class="label label-{$label}">
          <i class="glyphicon glyphicon-{$icon}" data-checked="{$checked}"></i>
        </label>
      </td>
    {/foreach}
  {/function}
  {* create an array of properties *}
  {$props = [$row->enforced, $row->ambiguous, $row->caseSensitive]}
  {* run the array through the function *}
  {call prop data=$props} 
  <td>{$row->short}</td>
  <td>{$row->internalRep}</td>
  {if User::can(User::PRIV_ADMIN)}
  <td>
    <div class="btn-group btn-group">
      <button type="button" class="btn btn-xs btn-default" name="btn-edit" data-row-id="{$row->id}">
        <i class="glyphicon glyphicon-edit"></i>
      </button>
      <button type="button" class="btn btn-xs btn-default" data-row-id="{$row->id}">
        <i class="glyphicon glyphicon-trash"></i>
      </button>
    </div>
  </td>
  {/if}
</tr>
{/strip}
