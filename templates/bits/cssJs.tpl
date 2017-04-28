{foreach $cssFiles as $rec}
  <link href="{$wwwRoot}css/{$rec.file}?v={$rec.date}" rel="stylesheet" type="text/css">
{/foreach}
{foreach $jsFiles as $rec}
  <script src="{$wwwRoot}js/{$rec.file}?v={$rec.date}"></script>
{/foreach}
