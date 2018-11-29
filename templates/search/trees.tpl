{foreach $trees as $t}
  <h3 class="tree-heading">
    {$t->description}

    <span class="variantList">
      {foreach $t->getPrintableLexemes() as $l}
        <span {if !$l->main}class="text-muted"{/if}>
          {$l->formNoAccent}
        </span>
      {/foreach}
    </span>

    <span class="tagList">
      {foreach $t->getTags() as $tag}
        {include "bits/tag.tpl" t=$tag}
      {/foreach}
    </span>

    {if User::can(User::PRIV_EDIT + User::PRIV_STRUCT)}
      <small>
        <a href="{$wwwRoot}editTree.php?id={$t->id}" class="pull-right">
          <i class="glyphicon glyphicon-pencil"></i>
          {t}edit{/t}
        </a>
      </small>
    {/if}
  </h3>

  <div class="tree-body">
    {include "bits/meaningTree.tpl" meanings=$t->getMeanings()}

    <h4 class="etymology">{t}etymology{/t}:</h4>
    {include "bits/meaningTree.tpl" meanings=$t->getEtymologies() etymologies=true}
  </div>
{/foreach}
