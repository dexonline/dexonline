{$badgeEdited=$badgeEdited|default:'secondary'}
<tr
  id="{$row->id}"
  data-row-id="{$row->id}"
  {if $row->enforced}data-enforced="1"{/if}
  {if $row->ambiguous}data-ambiguous="1"{/if}
  {if $row->caseSensitive}data-case-sensitive="1"{/if}
  {if $row->html}data-html="1"{/if}>
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
  <td class="text-center">
    {if $row->html}
      {include "bits/icon.tpl" i=done}
    {/if}
  </td>
  <td>{$row->short}</td>
  <td>{$row->internalRep|escape}</td>
  {if User::can(User::PRIV_ADMIN)}
    <td>
      <button type="button" class="btn btn-sm btn-outline-secondary me-1" name="btn-edit">
        {include "bits/icon.tpl" i=edit}
      </button>
      <button type="button" class="btn btn-sm btn-danger" name="btn-trash">
        {include "bits/icon.tpl" i=delete}
      </button>
    </td>
  {/if}
</tr>
