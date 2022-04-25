{$withCharmap=$withCharmap|default:true}
{extends "layout.tpl"}

{block "containerClasses"}{/block}
{block "banner"}{/block}
{block "search"}{/block}
{block "content"}
  {if $withCharmap}
    {include "bits/charmap.tpl"}
  {/if}
  {$smarty.block.child}
{/block}
