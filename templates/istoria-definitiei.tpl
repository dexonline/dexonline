{extends "layout.tpl"}

{block "title"}Istoria definiției {$def->lexicon}{/block}

{block "content"}
  <h3>Istoria definiției <a href="{$wwwRoot}definitie/{$def->id}">{$def->lexicon}</a></h3>

  {foreach $changeSets as $c}
    <div class="panel panel-default">

      <div class="panel-heading">
        <i class="glyphicon glyphicon-user"></i>
        {$c.NewModUserNick|default:"necunoscut"}

        <div class="pull-right">
          <i class="glyphicon glyphicon-calendar"></i>
          {$c.NewDate|date_format:"%e %B %Y %T"}
        </div>

      </div>

      {if isset($c.diff)}
        <div class="panel-body">
          <p>{$c.diff}</p>
        </div>
      {/if}

      <ul class="list-group">
        {if $c.OldUserId != $c.NewUserId}
          <li class="list-group-item">
            <strong>utilizator:</strong>
            <span class="label label-danger">{$c.OldUserNick|default:"necunoscut"}</span>
            <i class="glyphicon glyphicon-arrow-right"></i>
            <span class="label label-success">{$c.NewUserNick|default:"necunoscut"}</span>
          </li>
        {/if}
        {if $c.OldSourceId != $c.NewSourceId}
          <li class="list-group-item">
            <strong>sursa:</strong>
            <span class="label label-danger">{$c.OldSourceName|default:"necunoscută"}</span>
            <i class="glyphicon glyphicon-arrow-right"></i>
            <span class="label label-success">{$c.NewSourceName|default:"necunoscută"}</span>
          </li>
        {/if}
        {if $c.OldStatus != $c.NewStatus}
          <li class="list-group-item">
            <strong>starea:</strong>
            <span class="label label-danger">{$c.OldStatusName|default:"necunoscută"}</span>
            <i class="glyphicon glyphicon-arrow-right"></i>
            <span class="label label-success">{$c.NewStatusName|default:"necunoscută"}</span>
          </li>
        {/if}
        {if $c.OldLexicon != $c.NewLexicon}
          <li class="list-group-item">
            <strong>lexicon:</strong>
            <span class="label label-danger">{$c.OldLexicon}</span>
            <i class="glyphicon glyphicon-arrow-right"></i>
            <span class="label label-success">{$c.NewLexicon}</span>
          </li>
        {/if}
      </ul>

    </div>
  {foreachelse}
    <p>Nu există modificări la această definiție.</p>
  {/foreach}
{/block}
