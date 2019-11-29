{* a SourceAuthor to be used on the source edit page *}
{$id=$id|default:''}
{$author=$author|default:null}

<tr {if $id}id="{$id}" hidden{/if}>
  <td class="vcenter">
    <input type="hidden" name="authorId[]" value="{$author->id|default:''}">
    <i class="glyphicon glyphicon-move"></i>
  </td>

  <td>
    <input
      type="text"
      name="authorTitle[]"
      value="{$author->title|escape|default:''}"
      class="form-control"
      size="3">
  </td>

  <td>
    <input
      type="text"
      name="authorName[]"
      value="{$author->name|escape|default:''}"
      class="form-control">
  </td>

  <td>
    <input
      type="text"
      name="authorAcademicRank[]"
      value="{$author->academicRank|default:''}"
      class="form-control">
  </td>

  <td>
    <select name="authorRoleId[]" class="form-control">
      {foreach SourceRole::getAll() as $role}
        <option
          value="{$role->id}"
          {if $author && $author->sourceRoleId == $role->id}selected{/if}
        >
          {$role->nameSingular|escape}
        </option>
      {/foreach}
    </select>
  </td>

  <td>
    <button type="button" class="btn btn-danger deleteButton">
      <i class="glyphicon glyphicon-trash"></i>
    </button>
  </td>
</tr>
