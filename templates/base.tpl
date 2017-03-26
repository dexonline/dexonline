{assign var="cuv" value=$cuv|default:''}
{assign var="aprilFoolsDay" value=$cfg.global.aprilFoolsDay|default:false}
{assign var="onHomePage" value=$onHomePage|default:false}
<!DOCTYPE html>
<html>

  <head>
    <title>{block "title"}Dicționare ale limbii române{/block} | dexonline</title>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes" name="viewport">
    {block "pageDescription"}{/block}
    {block "openGraph"}
      <meta property="og:image" content="{$imgRoot}/logo/logo-og.png" />
      <meta property="og:type" content="website" />
      <meta property="og:title" content="dexonline" />
      <link rel="image_src" href="{$imgRoot}/logo/logo-og.png" />
    {/block}
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,b,i,bi"
          rel="stylesheet" type="text/css">
    {include "bits/cssJs.tpl"}
    <link rel="search" type="application/opensearchdescription+xml" href="https://dexonline.ro/download/dex.xml" title="Căutare dexonline.ro"/>
    <link href="https://plus.google.com/100407552237543221945" rel="publisher" />
    <link rel="alternate" type="application/rss+xml" title="Cuvântul zilei" href="https://dexonline.ro/rss/cuvantul-zilei">
    <link rel="apple-touch-icon" href="{$imgRoot}/apple-touch-icon.png">
    {if $aprilFoolsDay}
        <style>
            #aprilFoolsOverlay {
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
                z-index: 1000;
                background-color: #fff;
            }
            #aprilFoolsOverlay p {
                vertical-align: middle;
                border: 0;
                background-color: transparent;
                font-size: 3em;
                text-align: center;
            }
        </style>
    {/if}
  </head>

  <body>

    {if isset($callToAction)}
      {include $cfg.global.callToAction}
    {/if}

    <header>
      {include "bits/navmenu.tpl"}
      {include "bits/recentlyVisited.tpl"}
    </header>
    <div class="container">
      <main class="row">
        <div class="col-md-12 main-content">
          {include "bits/flashMessages.tpl"}
          {block "before-content"}{/block}
          {block "content"}{/block}
          {block "after-content"}{/block}
        </div>
      </main>
      <footer class="row footer">
        <div class="col-md-12">
          {block "footer"}{/block}
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

  {if $aprilFoolsDay}
    <script src="{$wwwbase}js/third-party/typewriter.js"></script>
    <script
        id="aprilFools"
        src="{$wwwbase}js/aprilFools.js"
        data-sound="{$wwwbase}typewriter/typewriter-keystroke.mp3"></script>
  {/if}

</html>
