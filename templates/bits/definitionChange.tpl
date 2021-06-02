{$tagLink=$tagLink|default:true}
<div class="card mb-3">

  <div class="card-header d-flex justify-content-between">
    <span>
      {include "bits/icon.tpl" i=person}
      {$c.user}
    </span>

    <span>
      {include "bits/icon.tpl" i=today}
      {$c.new->createDate|date_format:"%e %B %Y, %T"}
    </span>

  </div>

  <div class="card-body">
    {if isset($c.diff)}
      <p>{$c.diff}</p>

      {include "bits/footnotes.tpl" footnotes=$c.footnotes}
    {/if}

    <dl class="row mb-0">
      {if $c.old->sourceId != $c.new->sourceId}
        <dt class="col-lg-1">sursa:</dt>
        <dd class="col-lg-11">
          <span class="badge bg-danger">{$c.oldSource->shortName|default:"necunoscută"}</span>
          {include "bits/icon.tpl" i=chevron_right}
          <span class="badge bg-success">{$c.newSource->shortName|default:"necunoscută"}</span>
        </dd>
      {/if}
      {if $c.old->status != $c.new->status}
        <dt class="col-lg-1">starea:</dt>
        <dd class="col-lg-11">
          <span class="badge bg-danger">{$c.old->getStatusName()|default:"necunoscută"}</span>
          {include "bits/icon.tpl" i=chevron_right}
          <span class="badge bg-success">{$c.new->getStatusName()|default:"necunoscută"}</span>
        </dd>
      {/if}
      {if $c.old->lexicon != $c.new->lexicon}
        <dt class="col-lg-1">lexicon:</dt>
        <dd class="col-lg-11">
          <span class="badge bg-danger">{$c.old->lexicon}</span>
          {include "bits/icon.tpl" i=chevron_right}
          <span class="badge bg-success">{$c.new->lexicon}</span>
        </dd>
      {/if}
    </dl>
  </div>

  <div class="card-footer d-flex justify-content-between">
    <span>
      etichete:
      {foreach $c.tags as $t}
        {include "bits/tag.tpl"}
      {/foreach}
    </span>

    {if $tagLink}
      <a href="{Router::link('tag/definitionVersion')}?id={$c.old->id}">
        etichetează această modificare
      </a>
    {/if}
  </div>

</div>
