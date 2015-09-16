{foreach from=$cssFiles item=cssFile}
  <link href="{$wwwRoot}styles/{$cssFile}" rel="stylesheet" type="text/css"/>
{/foreach}
{foreach from=$jsFiles item=jsFile}
  <script src="{$wwwRoot}js/{$jsFile}"></script>
{/foreach}
