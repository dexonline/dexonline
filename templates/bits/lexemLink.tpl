{assign var="accent" value=$accent|default:false}
{strip}
  <a href="lexemEdit.php?lexemId={$lexem->id}" title="editează">
    {include file="bits/lexemName.tpl"}
  </a>
{/strip}
