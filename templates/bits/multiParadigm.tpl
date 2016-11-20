{assign var="hasUnrecommendedForms" value=$hasUnrecommendedForms|default:false}

<div class="panel panel-default paradigmDiv voffset3">
  <div class="panel-heading">
    Intrare: <strong>{$entry->description}</strong>

    {if $sUser && ($sUser->moderator & ($smarty.const.PRIV_EDIT + $smarty.const.PRIV_STRUCT))}
      <div class="pull-right">
        <a href="{$wwwRoot}editEntry.php?id={$entry->id}">
          <i class="glyphicon glyphicon-pencil"></i>
          editează
        </a>
      </div>
    {/if}

  </div>

  <div class="panel-body">

    {foreach $entry->getLexems() as $lexem}
      <div class="paraLexem">
        <div class="lexemData">
          <span class="lexemName">{include "bits/lexemName.tpl" lexem=$lexem}</span>

          {foreach $lexem->getTags() as $t}
            <label class="label label-info">{$t->value}</label>
          {/foreach}

          {include "bits/locInfo.tpl" isLoc=$lexem->isLoc}

          {if $sUser && ($sUser->moderator & ($smarty.const.PRIV_EDIT + $smarty.const.PRIV_STRUCT))}
            <a class="btn btn-link" href="{$wwwRoot}admin/lexemEdit.php?lexemId={$lexem->id}">
              <i class="glyphicon glyphicon-pencil"></i>
              editează
            </a>
          {/if}
        </div>

        {include "paradigm/paradigm.tpl" lexem=$lexem}
      </div>
    {/foreach}

  </div>
</div>
