<div class="card small mb-3" id="tree-checks">
  <div class="card-body bg-surface">
    <div class="form-check form-check-inline ps-0">
      {t}show{/t}:
    </div>
    {include "bs/checkbox.tpl"
      divClass="form-check-inline"
      inputClass="tree-check"
      inputId="tree-check-subtrees"
      label="{t}secondary meanings{/t}"
      name=""}
    {include "bs/checkbox.tpl"
      divClass="form-check-inline"
      inputClass="tree-check"
      inputId="tree-check-expressions"
      label="{t}expressions{/t}"
      name=""}
    {include "bs/checkbox.tpl"
      divClass="form-check-inline"
      inputClass="tree-check"
      inputId="tree-check-examples"
      label="{t}examples{/t}"
      name=""}
    {include "bs/checkbox.tpl"
      divClass="form-check-inline"
      inputClass="tree-check"
      inputId="tree-check-sources"
      label="{t}sources{/t}"
      name=""}
  </div>
</div>

{foreach $trees as $t}
  {if User::can(User::PRIV_EDIT + User::PRIV_STRUCT)}
    <a href="{Router::link('tree/edit')}?id={$t->id}" class="btn btn-sm btn-link float-end">
      {include "bits/icon.tpl" i=edit}
      {t}edit{/t}
    </a>
  {/if}

  <h3 class="tree-heading">

    {$printable=$t->getPrintableLexemes()}
    {if count($printable)}
      {foreach $printable as $rec}
        <div>
          {strip}
          {foreach $rec.lexemes as $i => $l}
            {if $i} / {/if}
            {Str::highlightAccent($l->form)}
            {$if=$l->getSampleInflectedForm()}
            {if $if}
              <span class="tree-inflected-form">
                , {$if->getHtmlForm()}
              </span>
            {/if}
          {/foreach}

          <span class="tree-pos-info">
            {foreach $rec.tags as $i => $t}
              {if $i}, {/if}
              {$t->value}
            {/foreach}
          </span>
          {/strip}
        </div>
      {/foreach}
    {else}
      {$t->description}
    {/if}
  </h3>

  <div class="tree-body">
    {if count($t->getTags())}
      {foreach $t->getTags() as $tag}
        {include "bits/tag.tpl" t=$tag}
      {/foreach}
    {/if}

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
