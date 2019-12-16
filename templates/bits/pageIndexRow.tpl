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
        <div class="btn-toolbar">
          <button type="button" class="btn btn-xs btn-warning" name="btn-edit">
            <i class="glyphicon glyphicon-edit"></i>
          </button>
          <button type="button" class="btn btn-xs btn-danger" name="btn-trash">
            <i class="glyphicon glyphicon-trash"></i>
          </button>
        </div>
      </td>
    {/if}
  </tr>
{/strip}
