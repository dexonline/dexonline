{$categories=$categories|default:true}
{$displayedUnofficial=false}
{$displayedSpec=false}

{foreach $results as $i => $row}
  {if $categories}
    {if $row->source->sourceTypeId > 0  && !$displayedSpec[$row->source->sourceTypeId]}
      <br>
      <div class="callout callout-info">
        <h3>{t}{$sourceTypes['label'][$row->source->sourceTypeId]}{/t}</h3>
        <p class="text-muted">
          {t}{$sourceTypes['desc'][$row->source->sourceTypeId]}{/t}
        </p>
      </div>
      {$displayedSpec[$row->source->sourceTypeId]=true}
    {/if}
  {/if}

  {include "bits/definition.tpl"
    showBookmark=1
    showCourtesyLink=1
    showFlagTypo=1
    showHistory=1
    showWotd=$showWotd}

{/foreach}
