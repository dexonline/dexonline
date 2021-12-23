{* argument: $source (possibly null) *}
{$sourceId=$source->id|default:null}

{* a hidden input with the submit value *}
<input
  id="source-field-hidden"
  name="source"
  type="hidden"
  value="{$source->urlName|default:null}">

<div class="dropdown" id="source-field">

  {* the dropdown trigger is a button styled like a select *}
  <button
    class="btn btn-sm dropdown-toggle w-100"
    data-bs-toggle="dropdown"
    type="button">
    {if isset($source)}
      {include "bits/sourceItem.tpl" src=$source}
    {else}
      {t}All dictionaries{/t}
    {/if}
  </button>

  <div class="dropdown-menu w-100">
    {* a filter input, not scrollable *}
    <div class="dropdown-item">
      <input
        class="form-control w-100"
        placeholder="{t}choose a dictionary...{/t}"
        type="text">
    </div>

    {* the source options, scrollable *}
    <div class="source-scrollable">
      <a class="dropdown-item" href="#">{t}All dictionaries{/t}</a>

      {$sources=Source::getAll(Source::SORT_SEARCH)}
      {foreach $sources as $src}
        {if !$src->hidden || User::can(User::PRIV_VIEW_HIDDEN)}
          <a
            class="dropdown-item"
            data-value="{$src->urlName}"
            href="#"
            title="{$src->name|escape}">
            {include "bits/sourceItem.tpl"}
          </a>
        {/if}
      {/foreach}
    </div>
  </div>
</div>
