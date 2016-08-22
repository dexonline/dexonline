{assign var="hasUnrecommendedForms" value=$hasUnrecommendedForms|default:false}
{if $lexems}
  {foreach $lexems as $lexem}
    <div class="paraLexem">
      <div class="lexemData">
        <span class="lexemName">{include file="bits/lexemName.tpl" lexem=$lexem}</span>
        {include file="bits/locInfo.tpl" isLoc=$lexem->isLoc}
        {if $sUser && ($sUser->moderator & ($smarty.const.PRIV_EDIT + $smarty.const.PRIV_STRUCT))}
          <a class="paraEdit" href="{$wwwRoot}admin/lexemEdit.php?lexemId={$lexem->id}" title="editează lexemul"></a>
        {/if}
      </div>

      {include file="paradigm/paradigm.tpl" lexem=$lexem}
    </div>
  {/foreach}

  {if $hasUnrecommendedForms}
    <div class="notRecommendedLegend">* Formă nerecomandată</div>
  {/if}
  {if !$onlyParadigm}
    <div><a class="paradigmLink" title="Link către această pagină, dar cu flexiunile expandate!" href="{$paradigmLink}">Link către această paradigmă</a></div>
  {/if}
{/if}
