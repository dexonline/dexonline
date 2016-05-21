{extends file="widgets/layout.tpl"}

{block name="widget-header"}
  Cuvânt aleator
{/block}

{block name="widget-body"}
  <img alt="cuvânt aleator" src="{$cfg.static.url}img/wotd/thumb/misc/aleator.jpg" class="commonShadow" />
  {include file="bits/randomWord.tpl"}
{/block}
