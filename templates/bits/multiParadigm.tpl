{assign var="hasUnrecommendedForms" value=$hasUnrecommendedForms|default:false}
{assign var="hasElisionForms" value=$hasElisionForms|default:false}

<div class="card mb-3 paradigmDiv">
  <div class="card-header">
    {cap}{t}entry{/t}{/cap}: <strong>{$entry->description}</strong>

    {if User::can(User::PRIV_EDIT + User::PRIV_STRUCT)}
      <div class="float-end">
        <a href="{Router::link('entry/edit')}?id={$entry->id}">
          {include "bits/icon.tpl" i=edit}
          editează
        </a>
      </div>
    {/if}

  </div>

  <div class="card-body">

    {foreach $entry->getMainLexemes() as $lexeme}
      {include "bits/extendedParadigm.tpl"}
    {/foreach}

    {if $entry->hasVariants()}
      <div
        id="variant-paradigm-{$entry->id}"
        class="collapse">
        {foreach $entry->getVariants() as $lexeme}
          {include "bits/extendedParadigm.tpl"}
        {/foreach}
      </div>

      <a
        class="btn btn-light doubleText"
        data-other-text="{t}hide variants{/t}"
        data-bs-toggle="collapse"
        href="#variant-paradigm-{$entry->id}"
        role="button"
        aria-expanded="false"
        aria-controls="variant-paradigm-{$entry->id}">
        {t}show variants{/t}
      </a>
    {/if}

  </div>
</div>
