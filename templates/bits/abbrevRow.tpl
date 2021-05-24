{$labelEdited=$labelEdited|default:'default'}
{strip}
<tr id="{$row->id}" data-row-id="{$row->id}">
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
  <td class="internalRep">{$row->internalRep}</td>
  <td>{HtmlConverter::convert($row)}</td>
  {if User::can(User::PRIV_ADMIN)}
    <td>
      <button type="button" class="btn btn-sm btn-secondary me-1" name="btn-edit">
        {include "bits/icon.tpl" i=edit}
      </button>
      <button type="button" class="btn btn-sm btn-danger" name="btn-trash">
        {include "bits/icon.tpl" i=delete}
      </button>
    </td>
  {/if}
</tr>
{/strip}
