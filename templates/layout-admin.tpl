{extends "layout.tpl"}

{block "banner"}{/block}
{block "bannerHead"}{/block}
{block "search"}{/block}
{block "content"}
	{include "admin/charmap.tpl"}
	{$smarty.block.child}
{/block}
{block "footer"}{/block}
