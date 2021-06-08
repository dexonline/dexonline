{extends "layout-admin.tpl"}

{block "title"}Cuvinte{/block}

{block "content"}
  {$results=$results|default:[]}

  <form method="post">

    <div class="row row-cols-sm-auto gx-2 mb-3">
      <div class="col-12">
        <label class="col-form-label">De la:</label>
      </div>
      <div class="col-12">
        <input type="text" class="form-control" name="i" value="{$i|escape}" size="10">
      </div>
      <div class="col-12">
        <label class="col-form-label">la:</label>
      </div>
      <div class="col-12">
        <input type="text" class="form-control" name="e" value="{$e|escape}" size="10">
      </div>
    </div>

    <div class="row mb-3">
      {foreach Source::getAll(Source::SORT_SHORT_NAME) as $source}
        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
          {include "bs/checkbox.tpl"
            name="s[]"
            label=$source->shortName|escape
            checked=isset($s[$source->id])
            value=$source->id}
        </div>
      {/foreach}
    </div>

    <button type="submit" class="btn btn-primary">
      {include "bits/icon.tpl" i=search}
      caută
    </button>

  </form>

  {foreach $results as $row}
    {include "bits/definition.tpl"}
  {/foreach}
{/block}
