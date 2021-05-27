{$badgeEdited=$badgeEdited|default:'secondary'}
<tr id="{$row->id}" data-row-id="{$row->id}">
  <td>
    <span class="badge bg-{$badgeEdited}">{$row->id}</span>
  </td>
  <td class="text-center">
    {if $row->enforced}
      {include "bits/icon.tpl" i=done}
    {/if}
  </td>
  <td class="text-center">
    {if $row->ambiguous}
      {include "bits/icon.tpl" i=done}
    {/if}
  </td>
  <td class="text-center">
    {if $row->caseSensitive}
      {include "bits/icon.tpl" i=done}
    {/if}
  </td>
  <td>{$row->short}</td>
  <td>{HtmlConverter::convert($row)}</td>
  {if User::can(User::PRIV_ADMIN)}
    <td>
      <button type="button" class="btn btn-sm btn-light me-1" name="btn-edit">
        {include "bits/icon.tpl" i=edit}
      </button>
      <button type="button" class="btn btn-sm btn-danger" name="btn-trash">
        {include "bits/icon.tpl" i=delete}
      </button>
    </td>
  {/if}
</tr>
