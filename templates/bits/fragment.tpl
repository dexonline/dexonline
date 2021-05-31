{* a fragment for compound lexemes *}
{$id=$id|default:''}
{$fragment=$fragment|default:null}
{$declension=$fragment->declension|default:0}
{$capitalized=$fragment->capitalized|default:0}
{$accented=$fragment->accented|default:0}

<div
  {if $id}id="{$id}"{/if}
  class="row row-cols-lg-auto g-1 mb-1 align-items-center fragmentWrapper">

  <div class="col">
    <select name="partIds[]" class="form-select fragment">
      {if $fragment}
        <option value="{$fragment->partId}"></option>
      {/if}
    </select>
  </div>

  <div class="col">
    <select name="declensions[]" class="form-select form-select-sm">
      {foreach Fragment::DEC_NAMES as $key => $value}
        <option value="{$key}"
          {if $key == $declension}selected{/if}>
          {$value}
        </option>
      {/foreach}
    </select>
  </div>

  <input type="hidden"
    name="capitalized[]"
    value="{$capitalized}">

  <div class="form-check mx-2">
    <label class="form-check-label" title="cu literă mare">
      <input type="checkbox"
        class="form-check-input capitalized"
        value="1"
        {if $capitalized}checked{/if}>
      A
    </label>
  </div>

  <input type="hidden"
    name="accented[]"
    value="{$accented}">

  <div class="form-check">
    <label
      class="form-check-label"
      title="preia accentul (dacă există; nu are niciun efect dacă fragmentul nu are accent)">
      <input type="checkbox"
        class="form-check-input accented"
        value="1"
        {if $accented}checked{/if}>
      acc.
    </label>
  </div>

  <button type="button" class="btn btn-link btn-sm editFragmentButton">
    {include "bits/icon.tpl" i=edit}
  </button>

  <button type="button" class="btn btn-link btn-sm deleteFragmentButton">
    {include "bits/icon.tpl" i=delete}
  </button>
</div>
