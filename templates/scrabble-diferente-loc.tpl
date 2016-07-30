{extends file="layout.tpl"}

{block name=title}Lista Oficială de Cuvinte{/block}

{block name=content}
  <h3>
    Diferențe între LOC {$locVersions.0} și LOC {$locVersions.1} ({$listType})
  </h3>

  <p>
    <a class="btn btn-default" href="scrabble">
      <i class="glyphicon glyphicon-chevron-left"></i>
      înapoi
    </a>
    <a class="btn btn-default" href="{$zipUrl}">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      descarcă
    </a>
  </p>

  {strip}
  <pre class="locDiff">
    {foreach from=$diff item=rec}
      <div class="{if $rec.0 == 'ins'}text-success{else}text-danger{/if}">
        {$rec.1}
      </div>
    {/foreach}
  </pre>
  {/strip}
{/block}
