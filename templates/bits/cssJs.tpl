{foreach from=$cssFiles item=rec}
  <link href="{$wwwRoot}styles/{$rec.file}?v={$rec.timestamp}" rel="stylesheet" type="text/css"/>
{/foreach}
{foreach from=$jsFiles item=rec}
  <script src="{$wwwRoot}js/{$rec.file}?v={$rec.timestamp}"></script>
{/foreach}
