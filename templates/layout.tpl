{assign var="cuv" value=$cuv|default:''}
{assign var="pageType" value=$pageType|default:'other'}
<!DOCTYPE html>
<html>

  <head>
    <title>{block "title"}Dicționare ale limbii române{/block} | dexonline</title>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes" name="viewport">
    {block "pageDescription"}{/block}
    {block "openGraph"}
      <meta property="og:image" content="{Config::URL_PREFIX}img/logo/logo-og.png">
      <meta property="og:type" content="website">
      <meta property="og:title" content="dexonline">
      <link rel="image_src" href="{Config::URL_PREFIX}img/logo/logo-og.png">
    {/block}
    {if !$privateMode}
      <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,b,i,bi"
        rel="stylesheet" type="text/css">
    {/if}
    <link href="{$cssFile.path}?v={$cssFile.date}" rel="stylesheet" type="text/css">
    <script src="{$jsFile.path}?v={$jsFile.date}"></script>
    <link
      rel="search"
      type="application/opensearchdescription+xml"
      href="{Config::STATIC_URL}download/dex.xml"
      title="Căutare dexonline.ro">
    <link href="https://plus.google.com/100407552237543221945" rel="publisher">
    <link rel="alternate" type="application/rss+xml" title="Cuvântul zilei" href="https://dexonline.ro/rss/cuvantul-zilei">
    <link rel="apple-touch-icon" href="{Config::URL_PREFIX}img/apple-touch-icon.png">
    {Plugin::notify('htmlHead')}
    {block "bannerHead"}
      {include "banner/bannerHead.tpl"}
    {/block}
  </head>

  <body class="{$pageType}">

    {Plugin::notify('bodyStart')}

    <header>
      {block "banner"}
        {include "banner/banner.tpl"}
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
            {if Config::SKIN_FACEBOOK && !$privateMode && !User::can(User::PRIV_ANY)}
              <hr>
              {include "bits/facebook.tpl"}
              <hr>
            {/if}
          {/block}

          <div class="text-center">
            <ul class="list-inline">
              <li>Copyright © 2004-{$currentYear} dexonline (https://dexonline.ro)</li>
            </ul>
          </div>

          <div id="footerLinks" class="text-center">
            <ul class="list-inline">
              <li><a href="{Config::URL_PREFIX}licenta">{t}license{/t}</a></li>
              <li><a href="https://wiki.dexonline.ro/wiki/Principii_de_confiden%C8%9Bialitate_dexonline.ro">{t}privacy{/t}</a></li>

              {$host=Config::SKIN_HOSTED_BY}
              {if $host}
                <li>{include "hosting/$host.tpl"}</li>
              {/if}
              {* disabled for now as quantcast provides their own overlay *}
              {* <li> *}
              {*  <a href="javascript:window.__cmp(&quot;displayConsentUi&quot;)"> *}
              {*    Modifică consimțământul pentru anunțuri *}
              {*  </a> *}
              {* </li> *}
            </ul>
          </div>
        </div>
      </footer>
    </div>
    {include "bits/analytics.tpl"}
    {include "bits/debugInfo.tpl"}
  </body>

  {Plugin::notify('afterBody')}

</html>
