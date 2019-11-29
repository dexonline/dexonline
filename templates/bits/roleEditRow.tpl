{* a SourceRole to be used on the role edit page *}
{$id=$id|default:''}
{$role=$role|default:null}

<tr {if $id}id="{$id}" hidden{/if}>
  <td class="vcenter">
    <input type="hidden" name="roleId[]" value="{$role->id|default:''}">
    <i class="glyphicon glyphicon-move"></i>
  </td>

  <td>
    <input
      type="text"
      name="roleNameSingular[]"
      value="{$role->nameSingular|escape|default:''}"
      class="form-control">
  </td>

  <td>
    <input
      type="text"
      name="roleNamePlural[]"
      value="{$role->namePlural|escape|default:''}"
      class="form-control">
  </td>

  <td>
    <input
      type="number"
      name="rolePriority[]"
      value="{$role->priority|default:1}"
      class="form-control"
      size="3">
  </td>

  <td>
    <button
      type="button"
      class="btn btn-danger deleteButton"
      {if $role && $role->isInUse()}disabled{/if}>
      <i class="glyphicon glyphicon-trash"></i>
    </button>
  </td>
</tr>
