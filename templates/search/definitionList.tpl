{$categories=$categories|default:true}
{$displayedUnofficial=false}
{$displayedSpec=false}

{foreach $results as $i => $row}

  {if $categories}
    {if $row->sources[0]->type == Source::TYPE_SPECIALIZED && !$displayedSpec}
      <br>
      <div class="callout callout-info">
        <h3>{t}Definitions from specialized dictionaries{/t}</h3>
        <p class="text-muted">
          {t}These definitions could explain only certain meanings of words.{/t}
        </p>
      </div>
      {$displayedSpec=true}
    {elseif $row->sources[0]->type == Source::TYPE_UNOFFICIAL && !$displayedUnofficial}
      <br>
      <div class="callout callout-info">
        <h3>{t}Definitions from unofficial dictionaries{/t}</h3>
        <p class="text-muted">
          {t}Since they are not made by lexicographers, these definitions may
          contain errors, so we advise you to look at other dictionaries as
          well.{/t}
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
