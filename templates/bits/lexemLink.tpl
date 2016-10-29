{assign var="accent" value=$accent|default:false}
{strip}
  <a href="{$wwwRoot}admin/lexemEdit.php?lexemId={$lexem->id}" title="editează">
    {include "bits/lexemName.tpl"}
  </a>
{/strip}
