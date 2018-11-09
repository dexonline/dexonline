{$categories=$categories|default:true}
{$displayedUnofficial=false}
{$displayedSpec=false}

{foreach $results as $i => $row}

  {if $categories}
    {if $row->source->type == Source::TYPE_SPECIALIZED && !$displayedSpec}
      <br>
      <div class="callout callout-info">
        <h3>{'Definitions from specialized dictionaries'|_}</h3>
        <p class="text-muted">
          {'These definitions could explain only certain meanings of words.'|_}
        </p>
      </div>
      {$displayedSpec=true}
    {elseif $row->source->type == Source::TYPE_UNOFFICIAL && !$displayedUnofficial}
      <br>
      <div class="callout callout-info">
        <h3>{'Definitions from unofficial dictionaries'|_}</h3>
        <p class="text-muted">
          {'Since they are not made by lexicographers, these definitions may
          contain errors, so we advise you to look at other dictionaries as
          well.'|_}
        </p>
      </div>
      {$displayedUnofficial=true}
    {/if}
  {/if}

  {include "bits/definition.tpl"
    showBookmark=1
    showCourtesyLink=1
    showFlagTypo=1
    showHistory=1
    showWotd=$showWotd}

{/foreach}
