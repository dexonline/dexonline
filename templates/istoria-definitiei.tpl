{extends "layout.tpl"}

{block "title"}Istoria definiției {$def->lexicon}{/block}

{block "content"}
  <h3>Istoria definiției <a href="{$wwwRoot}definitie/{$def->id}">{$def->lexicon}</a></h3>

  {foreach $changeSets as $c}
    <div class="panel panel-default">

      <div class="panel-heading">
        <i class="glyphicon glyphicon-user"></i>
        {$c.new.munick|default:"necunoscut"}

        <div class="pull-right">
          <i class="glyphicon glyphicon-calendar"></i>
          {$c.new.NewDate|date_format:"%e %B %Y %T"}
        </div>

      </div>

      {if isset($c.diff)}
        <div class="panel-body">
          <p>{$c.diff}</p>
        </div>
      {/if}

      <ul class="list-group">
        {if $c.old.UserId != $c.new.UserId}
          <li class="list-group-item">
            <strong>utilizator:</strong>
            <span class="label label-danger">{$c.old.unick|default:"necunoscut"}</span>
            <i class="glyphicon glyphicon-arrow-right"></i>
            <span class="label label-success">{$c.new.unick|default:"necunoscut"}</span>
          </li>
        {/if}
        {if $c.old.SourceId != $c.new.SourceId}
          <li class="list-group-item">
            <strong>sursa:</strong>
            <span class="label label-danger">{$c.old.shortName|default:"necunoscută"}</span>
            <i class="glyphicon glyphicon-arrow-right"></i>
            <span class="label label-success">{$c.new.shortName|default:"necunoscută"}</span>
          </li>
        {/if}
        {if $c.old.Status != $c.new.Status}
          <li class="list-group-item">
            <strong>starea:</strong>
            <span class="label label-danger">{$c.OldStatusName|default:"necunoscută"}</span>
            <i class="glyphicon glyphicon-arrow-right"></i>
            <span class="label label-success">{$c.NewStatusName|default:"necunoscută"}</span>
          </li>
        {/if}
        {if $c.old.Lexicon != $c.new.Lexicon}
          <li class="list-group-item">
            <strong>lexicon:</strong>
            <span class="label label-danger">{$c.old.Lexicon}</span>
            <i class="glyphicon glyphicon-arrow-right"></i>
            <span class="label label-success">{$c.new.Lexicon}</span>
          </li>
        {/if}
      </ul>

    </div>
  {foreachelse}
    <p>Nu există modificări la această definiție.</p>
  {/foreach}
{/block}
