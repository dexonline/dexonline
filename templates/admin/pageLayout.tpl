{assign var=sectionCount value=$sectionCount|default:null}
{assign var=sectionSources value=$sectionSources|default:false}
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8"/>
    {include file="bits/cssJs.tpl"}
    <title>DEX | {$sectionTitle|escape}</title>
  </head>

  <body>
    {if empty($noAdminHeader)}
      {include file="admin/header.tpl" title=$sectionTitle count=$sectionCount showSources=$sectionSources}
    {/if}
    {include file="admin/recentlyVisited.tpl"}
    {include file="admin/flashMessages.tpl"}
    {include file="errorMessage.tpl"}
    {include file="$templateName"}
    {getDebugInfo}
  </body>
</html>
