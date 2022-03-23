<div class="card small mb-3">
  <div class="card-body">
    {include "bs/checkbox.tpl"
      divClass="form-check-inline"
      inputClass="tree-check"
      inputId="tree-check-subtrees"
      label="{t}show secondary meanings{/t}"
      name=""}
    {include "bs/checkbox.tpl"
      divClass="form-check-inline"
      inputClass="tree-check"
      inputId="tree-check-expressions"
      label="{t}show expressions{/t}"
      name=""}
    {include "bs/checkbox.tpl"
      divClass="form-check-inline"
      inputClass="tree-check"
      inputId="tree-check-examples"
      label="{t}show examples{/t}"
      name=""}
    {include "bs/checkbox.tpl"
      divClass="form-check-inline"
      inputClass="tree-check"
      inputId="tree-check-sources"
      label="{t}show sources{/t}"
      name=""}
  </div>
</div>

{foreach $trees as $t}
  <h3 class="tree-heading">
    {$t->description}

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

    {if count($t->getEtymologies())}
      <div class="etymology">
        <h6 class="etymology-heading">{t}etymology{/t}:</h6>

        {include "bits/meaningTree.tpl"
          meanings=$t->getEtymologies()
          etymologies=true
          depth=1}
      </div>
    {/if}
  </div>
{/foreach}

<p class="text-muted">
  {include "bits/icon.tpl" i=info}
  {t 1="tree-tab-link"}
  The full definition list is available on the <a id="%1" href="#">definitions</a> tab.
  {/t}
</p>
