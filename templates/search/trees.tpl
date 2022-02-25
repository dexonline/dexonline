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

    <span class="ms-2">
      {foreach $t->getTags() as $tag}
        {include "bits/tag.tpl" t=$tag}
      {/foreach}
    </span>

    {if User::can(User::PRIV_EDIT + User::PRIV_STRUCT)}
      <a href="{Router::link('tree/edit')}?id={$t->id}" class="btn btn-link float-end">
        {include "bits/icon.tpl" i=edit}
        {t}edit{/t}
      </a>
    {/if}
  </h3>

  <div class="tree-body">
    {include "bits/meaningTree.tpl" meanings=$t->getMeanings()}

    <h4 class="etymology">{t}etymology{/t}:</h4>
    {include "bits/meaningTree.tpl" meanings=$t->getEtymologies() etymologies=true}
  </div>
{/foreach}

<p class="text-muted">
  {include "bits/icon.tpl" i=info}
  {t 1="tree-tab-link"}
  The full definition list is available on the <a id="%1" href="#">results</a> tab.
  {/t}
</p>
