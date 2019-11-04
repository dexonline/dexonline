{* a fragment for compound lexemes *}
{$id=$id|default:''}
{$fragment=$fragment|default:null}
{$declension=$fragment->declension|default:0}
{$capitalized=$fragment->capitalized|default:0}
{$accented=$fragment->accented|default:0}

<div {if $id}id="{$id}"{/if} class="form-inline input-group-sm fragmentWrapper">
  <select name="partIds[]" class="fragment">
    {if $fragment}
      <option value="{$fragment->partId}"></option>
    {/if}
  </select>

  <select name="declensions[]" class="form-control">
    {foreach Fragment::DEC_NAMES as $key => $value}
      <option value="{$key}"
        {if $key == $declension}selected{/if}>
        {$value}
      </option>
    {/foreach}
  </select>

  <input type="hidden"
    name="capitalized[]"
    value="{$capitalized}">

  <label title="cu literă mare">
    <input type="checkbox"
      class="capitalized"
      value="1"
      {if $capitalized}checked{/if}>
    <i class="glyphicon glyphicon-font"></i>
  </label>

  <input type="hidden"
    name="accented[]"
    value="{$accented}">

  <label title="preia accentul (dacă există; nu are niciun efect dacă fragmentul nu are accent)">
    <input type="checkbox"
      class="accented"
      value="1"
      {if $accented}checked{/if}>
    acc.
  </label>

  <button type="button" class="btn btn-link btn-xs editFragmentButton">
    <i class="glyphicon glyphicon-pencil"></i>
  </button>

  <button type="button" class="btn btn-link btn-xs deleteFragmentButton">
    <i class="glyphicon glyphicon-trash"></i>
  </button>
</div>
