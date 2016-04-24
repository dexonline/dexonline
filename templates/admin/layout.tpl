<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8"/>
    {include file="bits/cssJs.tpl"}
    <title>{block name=title}{/block} | admin dexonline</title>
  </head>

  <body>
    {block name=adminHeader}
      <div class="header">
        <div class="title">
          {block name=headerTitle}{/block}
        </div>

        {block name=headerSources}{/block}

        <div class="links">
          <a href="../">dexonline</a> |
          <a href="../admin/">Pagina moderatorului</a>
        </div>
        <div style="clear: both;"></div>
      </div>
    {/block}
    {include file="admin/recentlyVisited.tpl"}
    {include file="bits/flashMessages.tpl"}
    {block name=content}{/block}
    {block name=stats}{/block}
    {getDebugInfo}
  </body>
</html>
