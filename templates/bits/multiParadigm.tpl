{assign var="hasUnrecommendedForms" value=$hasUnrecommendedForms|default:false}
{assign var="hasElisionForms" value=$hasElisionForms|default:false}

<div class="panel panel-default paradigmDiv voffset3">
  <div class="panel-heading">
    {'entry'|_|cap}: <strong>{$entry->description}</strong>

    {if User::can(User::PRIV_EDIT + User::PRIV_STRUCT)}
      <div class="pull-right">
        <a href="{$wwwRoot}editEntry.php?id={$entry->id}">
          <i class="glyphicon glyphicon-pencil"></i>
          editează
        </a>
      </div>
    {/if}

  </div>

  <div class="panel-body">

    {foreach $entry->getMainLexemes() as $lexeme}
      {include "bits/extendedParadigm.tpl"}
    {/foreach}

    {if $entry->hasVariants()}
      <div class="variantParadigm">
        {foreach $entry->getVariants() as $lexeme}
          {include "bits/extendedParadigm.tpl"}
        {/foreach}

      </div>

      <button
        type="button"
        class="btn btn-default toggleVariantParadigms doubleText"
        data-other-text="{'hide variants'|_}">
        {'show variants'|_}
      </button>
    {/if}

  </div>
</div>
