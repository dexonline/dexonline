{$labelEdited=$labelEdited|default:'default'}
{strip}
  <tr id="{$row->id}" data-row-id="{$row->id}">
    <td>
      <span class="label label-{$labelEdited}">{$row->id}</span>
    </td>
    <td>{$row->volume}</td>
    <td>{$row->page}</td>
    <td>{$row->word}</td>
    <td>{$row->number}</td>
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
