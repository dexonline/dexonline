{extends "layout-admin.tpl"}

{block "title"}
  Pagină PDF
{/block}

{block "content"}
  <form method="get" class="form-inline text-center">
    {if isset($pdfBase64)}
      <a class="btn btn-default pull-left"
         href="?sourceId={$sourceId}&amp;volume={$volume}&amp;page={$page-1}">
        <i class="glyphicon glyphicon-chevron-left"></i>
      </a>
    {/if}

    <div class="form-group">
      <label>sursa:</label>
      {include "bits/sourceDropDown.tpl"
      name="sourceId"
      sourceId="{$sourceId}"
      width="200px"
      skipAnySource=1}
    </div>

    <div class="form-group">
      <label>cuvântul:</label>
      <input id="text" type="text" name="word" value="{$word}" class="form-control">
    </div>

    <button class="btn btn-primary" type="submit" name="saveButton">
      <i class="glyphicon glyphicon-search"></i>
      caută
    </button>

    {if isset($pdfBase64)}
      <a class="btn btn-default pull-right"
         href="?sourceId={$sourceId}&amp;volume={$volume}&amp;page={$page+1}">
        <i class="glyphicon glyphicon-chevron-right"></i>
      </a>
    {/if}

  </form>

  {if isset($pdfBase64)}
    <div class="embed-responsive voffset2" style="padding-bottom: 142%">
      <iframe src="data:application/pdf;base64,{$pdfBase64}"></iframe>
    </div>
  {/if}
{/block}
