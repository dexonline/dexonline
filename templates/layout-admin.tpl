{$withCharmap=$withCharmap|default:true}
{extends "layout.tpl"}

{block "banner"}{/block}
{block "bannerHead"}{/block}
{block "search"}{/block}
{block "content"}
  {if $withCharmap}
    {include "bits/charmap.tpl"}
  {/if}
  {$smarty.block.child}
{/block}
{block "footer"}{/block}
