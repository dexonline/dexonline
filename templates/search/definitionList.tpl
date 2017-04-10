{$categories=$categories|default:true}
{$displayedUnofficial=false}
{$displayedSpec=false}

{foreach $results as $i => $row}

  {if $categories}
    {if $row->source->type == Source::TYPE_SPECIALIZED && !$displayedSpec}
      <br/>
      <div class="callout callout-info">
        <h3>Definiții din dicționare specializate</h3>
        <p class="text-muted">
          Aceste definiții pot explica numai anumite înțelesuri ale cuvintelor.
        </p>
      </div>
      {$displayedSpec=true}
    {elseif $row->source->type == Source::TYPE_UNOFFICIAL && !$displayedUnofficial}
      <br/>
      <div class="callout callout-info">
        <h3>Definiții din dicționare neoficiale</h3>
        <p class="text-muted">
          Deoarece nu sunt editate de lexicografi, aceste definiții pot conține erori,
          deci e preferabilă consultarea altor dicționare în paralel.
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

{include "bits/typoForm.tpl"}
