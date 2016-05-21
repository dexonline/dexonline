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
      <meta property="og:image" content="{$imgRoot}/logo-dexonline-2.png" />
      <meta property="og:type" content="website" />
      <meta property="og:title" content="dexonline" />
      <link rel="image_src" href="{$imgRoot}/logo-dexonline-2.png" />
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
          <nav class="navbar navbar-default">
            <div class="container-fluid">
              <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navMenu" aria-expanded="false">
                  <span class="sr-only">Navigare</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                </button>
                <button type="button" class="navbar-toggle collapsed pull-right" data-toggle="collapse" data-target="#navMenuMobileLogin" aria-expanded="false">
                  <span class="glyphicon glyphicon-user"></span>
                </button>
              </div>
              <div class="collapse navbar-collapse" id="navMenu">
                <!-- Collect the nav links, forms, and other content for toggling -->
                <ul class="nav navbar-nav">
                  <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Despre noi <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li><a href="http://wiki.dexonline.ro/wiki/Informa%C8%9Bii" target="_blank">Informații</a></li>
                      <li><a href="{$wwwRoot}contact">Contact</a></li>
                      <li><a href="http://dexonline.blogspot.ro">Blogul nostru</a></li>
                    </ul>
                  </li>
                  <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Implică-te <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li><a href="{$wwwRoot}contribuie">Contribuie cu definiții</a></li>
                      <li><a href="{$wwwRoot}top">Topul voluntarilor</a></li>
                      <li><a href="http://wiki.dexonline.ro/wiki/Informa%C8%9Bii_pentru_programatori">Informații pentru programatori</a></li>
                    </ul>
                  </li>
                  <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Articole și resurse <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li><a href="http://wiki.dexonline.ro/wiki/Abrevieri" target="_blank">Abrevieri folosite</a></li>
                      <li><a href="{$wwwRoot}articole">Articole lingvistice</a></li>
                      <li><a href="{$wwwRoot}articol/Ghid_de_exprimare_corect%C4%83">Ghid de exprimare</a></li>
                      <li><a href="{$wwwRoot}cuvantul-zilei">Cuvântul zilei</a></li>
                      <li><a href="{$wwwRoot}cuvantul-lunii">Cuvântul lunii</a></li>
                      <li><a href="{$wwwRoot}cuvinte-aleatoare">Cuvinte aleatoare</a></li>
                      <li><a href="{$wwwRoot}scrabble">Scrabble</a></li>
                      <li><a href="{$wwwRoot}unelte">Unelte</a></li>
                      <li><a href="{$wwwRoot}legaturi">Legături externe</a></li>
                    </ul>
                  </li>
                </ul>
              </div>
              <div class="collapse navbar-collapse" id="navMenuMobileLogin">
                {if !$cfg.global.mirror}
                  <ul class="nav navbar-nav">
                    {if $sUser && $sUser->moderator}
                      <li><a href="{$wwwRoot}admin">Pagina moderatorului</a></li>
                    {/if}
                    <li><a href="{$wwwRoot}preferinte">Preferințe</a></li>
                    {if $sUser}
                      <li><a href="{$wwwRoot}utilizator/{$sUser->nick}">Profilul meu</a></li>
                      <li><a href="{$wwwRoot}cuvinte-favorite">Cuvinte favorite</a></li>
                      <li><a href="{$wwwRoot}auth/logout">Închide sesiunea</a></li>
                    {else}
                      <li><a href="{$wwwRoot}auth/login">Autentificare cu OpenID</a></li>
                    {/if}
                  </ul>
                {/if}
              </div>
              <div class="collapse navbar-collapse hidden-xs" id="navMenuLogin">
                <ul class="nav navbar-nav">
                  {if !$cfg.global.mirror}
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown"
                         role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="glyphicon glyphicon-user"></span>
                        {$nick|escape}
                      </a>
                      <ul class="dropdown-menu">
                        {if $sUser && $sUser->moderator}
                          <li><a href="{$wwwRoot}admin">Pagina moderatorului</a></li>
                        {/if}
                        <li><a href="{$wwwRoot}preferinte">Preferințe</a></li>
                        {if $sUser}
                          <li><a href="{$wwwRoot}utilizator/{$sUser->nick}">Profilul meu</a></li>
                          <li><a href="{$wwwRoot}cuvinte-favorite">Cuvinte favorite</a></li>
                          <li><a href="{$wwwRoot}auth/logout">Închide sesiunea</a></li>
                        {else}
                          <li><a href="{$wwwRoot}auth/login">Autentificare cu OpenID</a></li>
                        {/if}
                      </ul>
                    </li>
                  {/if}
                  {if !$suggestHiddenSearchForm}
                    <li class="hidden-xs">
                      <a class="donateLink" href="{$wwwRoot}doneaza">
                        <span class="glyphicon glyphicon-credit-card"></span>
                        Donează
                      </a>
                    </li>
                  {/if}
                </ul>
              </div>
            </div>
          </nav>
        </div>
      </header>
      <main class="row">
        <div class="col-md-12 main-content">
          {block name="before-content"}{/block}
          {block name="content"}{/block}
          {block name="after-content"}{/block}
        </div>
      </main>
      <footer class="row">
        <div class="col-md-12">
          <ul>
            <li>Copyright (C) 2004-{$currentYear} dexonline (https://dexonline.ro)</li>
            <li class="licenceLink"><a href="{$wwwRoot}licenta">Licență</a></li>
            {if $cfg.global.hostedBy}
              <li class="hostingLink">{include file="hosting/`$cfg.global.hostedBy`.tpl"}</li>
            {/if}
          </ul>
        </div>
      </footer>
    </div>
    {include file="bits/analytics.tpl"}
    {getDebugInfo}
  </body>
</html>
