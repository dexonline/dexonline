{assign var="cuv" value=$cuv|default:''}
{assign var="typewriterEnabled" value=$cfg.global.typewriterEnabled|default:false}
{assign var="onHomePage" value=$onHomePage|default:false}
<!DOCTYPE html>
<html>

  <head>
    <title>{block "title"}Dicționare ale limbii române{/block} | dexonline</title>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes" name="viewport">
    {block "pageDescription"}{/block}
    {block "openGraph"}
      <meta property="og:image" content="{$imgRoot}/logo/logo-og.png">
      <meta property="og:type" content="website">
      <meta property="og:title" content="dexonline">
      <link rel="image_src" href="{$imgRoot}/logo/logo-og.png">
    {/block}
    {if $privateMode}
      <link href="{$wwwRoot}css/opensans.css" rel="stylesheet" type="text/css">
    {else}
      <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,b,i,bi"
            rel="stylesheet" type="text/css">
    {/if}
    <link href="{$cssFile.path}?v={$cssFile.date}" rel="stylesheet" type="text/css">
    <script async src="{$jsFile.path}?v={$jsFile.date}"></script>
    <link rel="search" type="application/opensearchdescription+xml" href="https://dexonline.ro/download/dex.xml" title="Căutare dexonline.ro">
    <link href="https://plus.google.com/100407552237543221945" rel="publisher">
    <link rel="alternate" type="application/rss+xml" title="Cuvântul zilei" href="https://dexonline.ro/rss/cuvantul-zilei">
    <link rel="apple-touch-icon" href="{$imgRoot}/apple-touch-icon.png">
    {if $typewriterEnabled}
        <link rel="prefetch" href="{$wwwRoot}typewriter/font/FuckinGwenhwyfar.ttf">
        <link rel="stylesheet" type="text/css" href="{$wwwRoot}typewriter/run.css">
    {/if}
  </head>

  <body>

    {if isset($callToAction)}
      {include $cfg.global.callToAction}
    {/if}

    <header>
      {block "banner"}
        {include "bits/banner.tpl"}
      {/block}
      {include "bits/navmenu.tpl"}
      {include "bits/recentlyVisited.tpl"}
    </header>
    <div class="container">
      <main class="row">
        <div class="col-md-12 main-content">
          {include "bits/flashMessages.tpl"}
          {block "search"}
            {include "bits/searchForm.tpl"}
          {/block}
          {block "content"}{/block}
        </div>
      </main>
      <footer class="row footer">
        <div class="col-md-12">

          {block "footer"}
            {if $skinVariables.fbLarge && !$privateMode}
              <hr>
              {include "bits/facebook.tpl"}
              <hr>
            {/if}
          {/block}

          <div class="text-center">
            <ul class="list-inline">
              <li>Copyright (C) 2004-{$currentYear} dexonline (https://dexonline.ro)</li>
              <li class="licenceLink"><a href="{$wwwRoot}licenta">Licență</a></li>
              {if $cfg.global.hostedBy}
                <li class="hostingLink">{include "hosting/`$cfg.global.hostedBy`.tpl"}</li>
              {/if}
            </ul>
          </div>
        </div>
      </footer>
    </div>
    {include "bits/analytics.tpl"}
    {include "bits/debugInfo.tpl"}
  </body>

  {if $typewriterEnabled}
    <script src="{$wwwRoot}typewriter/typewriter.js"></script>
    <script
        id="aprilFools"
        src="{$wwwRoot}typewriter/run.js"
        data-sound="{$wwwRoot}typewriter/sound/"></script>
    <script>typewriter.run({$onHomePage});</script>
  {/if}

</html>
