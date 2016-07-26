{assign var="cuv" value=$cuv|default:''}
{assign var="onHomePage" value=$onHomePage|default:false}
{assign var="suggestHiddenSearchForm" value=$suggestHiddenSearchForm|default:false}
{assign var="suggestNoBanner" value=$suggestNoBanner|default:false}
<!DOCTYPE html>
<html lang="ro">
  <head>
    <title>{block name=title}Dicționare ale limbii române{/block} | dexonline</title>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes" name="viewport">
    {block name=pageDescription}{/block}
    {block name=openGraph}
      <meta property="og:image" content="{$imgRoot}/logo/logo-og.png" />
      <meta property="og:type" content="website" />
      <meta property="og:title" content="dexonline" />
      <link rel="image_src" href="{$imgRoot}/logo/logo-og.png" />
    {/block}
    {include file="bits/cssJs.tpl"}
    <link rel="search" type="application/opensearchdescription+xml" href="https://dexonline.ro/download/dex.xml" title="Căutare dexonline.ro"/>
    <link href="https://plus.google.com/100407552237543221945" rel="publisher" />
    <link rel="alternate" type="application/rss+xml" title="Cuvântul zilei" href="https://dexonline.ro/rss/cuvantul-zilei">
    <link rel="apple-touch-icon" href="{$imgRoot}/apple-touch-icon.png">
  </head>
  <body>
    <div class="container">
      <header class="row">
        <div class="col-md-12">
          {include file="bits/navmenu.tpl"}
        </div>
      </header>
      <main class="row">
        <div class="col-md-12 main-content">
          {include file="bits/flashMessages.tpl"}
          {block name="before-content"}{/block}
          {block name="content"}{/block}
          {block name="after-content"}{/block}
        </div>
      </main>
      <footer class="row footer">
        <div class="col-md-12">
          {block name="footer"}{/block}
          <div class="text-center">
            <ul class="list-inline">
              <li>Copyright (C) 2004-{$currentYear} dexonline (https://dexonline.ro)</li>
              <li class="licenceLink"><a href="{$wwwRoot}licenta">Licență</a></li>
              {if $cfg.global.hostedBy}
                <li class="hostingLink">{include file="hosting/`$cfg.global.hostedBy`.tpl"}</li>
              {/if}
            </ul>
          </div>
        </div>
      </footer>
    </div>
    {include file="bits/analytics.tpl"}
    {getDebugInfo}
  </body>
</html>
