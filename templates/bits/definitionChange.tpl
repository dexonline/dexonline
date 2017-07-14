{$tagLink=$tagLink|default:true}
<div class="panel panel-default">

  <div class="panel-heading">
    <i class="glyphicon glyphicon-user"></i>
    {$c.user}

    <div class="pull-right">
      <i class="glyphicon glyphicon-calendar"></i>
      {$c.new->createDate|date_format:"%e %B %Y %T"}
    </div>

  </div>

  {if isset($c.diff)}
    <div class="panel-body">
      <p>{$c.diff}</p>
    </div>
  {/if}

  <ul class="list-group">
    {if $c.old->sourceId != $c.new->sourceId}
      <li class="list-group-item">
        <strong>sursa:</strong>
        <span class="label label-danger">{$c.oldSource->shortName|default:"necunoscută"}</span>
        <i class="glyphicon glyphicon-arrow-right"></i>
        <span class="label label-success">{$c.newSource->shortName|default:"necunoscută"}</span>
      </li>
    {/if}
    {if $c.old->status != $c.new->status}
      <li class="list-group-item">
        <strong>starea:</strong>
        <span class="label label-danger">{$c.old->getStatusName()|default:"necunoscută"}</span>
        <i class="glyphicon glyphicon-arrow-right"></i>
        <span class="label label-success">{$c.new->getStatusName()|default:"necunoscută"}</span>
      </li>
    {/if}
    {if $c.old->lexicon != $c.new->lexicon}
      <li class="list-group-item">
        <strong>lexicon:</strong>
        <span class="label label-danger">{$c.old->lexicon}</span>
        <i class="glyphicon glyphicon-arrow-right"></i>
        <span class="label label-success">{$c.new->lexicon}</span>
      </li>
    {/if}
  </ul>

  <div class="panel-footer clearfix">
    etichete:
    {foreach $c.tags as $t}
      {include "bits/tag.tpl"}
    {/foreach}


    {if $tagLink}
      <div class="pull-right">
        <a href="etichete-istorie?id={$c.old->id}">
          etichetează această modificare
        </a>
      </div>
    {/if}
  </div>

</div>
