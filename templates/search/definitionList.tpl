{$categories=$categories|default:true}
{$displayedSpec=[]}

{foreach $results as $i => $row}
  {if $categories}
    {$tid=$row->source->sourceTypeId}
    {if $tid > 0  && !isset($displayedSpec[$tid])}
      <br>
      <div class="callout callout-info">
        <h3>{SourceType::getName($tid)}</h3>
        <p class="text-muted">{SourceType::getDescription($tid)}</p>
      </div>
      {$displayedSpec[$tid]=true}
    {/if}
  {/if}

  {include "bits/definition.tpl"
    showBookmark=1
    showCourtesyLink=1
    showFlagTypo=1
    showHistory=1
    showWotd=$showWotd}

{/foreach}
