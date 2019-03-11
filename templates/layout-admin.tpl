{extends "layout.tpl"}

{block "banner"}{/block}
{block "bannerHead"}{/block}
{block "search"}{/block}
{block "content"}
	{include "bits/charmap.tpl"}
	{$smarty.block.child}
{/block}
{block "footer"}{/block}
