{* Argument: $isLoc *}
{assign var="locParadigm" value=$locParadigm|default:false}
{assign var="admin" value=$sUser && ($sUser->moderator & $smarty.const.PRIV_LOC)}
{if $isLoc && ($admin || $locParadigm)}
  <span class="isLoc" title="Inclus în lista oficială de cuvinte a jocului de scrabble (versiunea în lucru)">[LOC]</span>
{/if}      
