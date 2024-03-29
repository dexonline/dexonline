{assign var="cuv" value=$cuv|default:''}
{assign var="pageType" value=$pageType|default:'other'}
<!DOCTYPE html>
<html>

  <head>
    <title>{block "title"}Dicționare ale limbii române{/block} | dexonline</title>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=3.0, user-scalable=yes" name="viewport">
    {include "bits/colorScheme.tpl"}
    {block "pageDescription"}{/block}
    {block "openGraph"}
      <meta property="og:image" content="{Config::URL_PREFIX}img/logo/logo-og.png">
      <meta property="og:type" content="website">
      <meta property="og:title" content="dexonline">
      <link rel="image_src" href="{Config::URL_PREFIX}img/logo/logo-og.png">
    {/block}
    <link href="{$cssFile.path}?v={$cssFile.date}" rel="stylesheet" type="text/css">
    <script src="{$jsFile.path}?v={$jsFile.date}"></script>
    <link
      rel="search"
      type="application/opensearchdescription+xml"
      href="{Config::STATIC_URL}download/dex.xml"
      title="Căutare dexonline.ro">
    <link href="https://plus.google.com/100407552237543221945" rel="publisher">
    <link
      rel="alternate"
      type="application/rss+xml"
      title="Cuvântul zilei"
      href="{Router::link('wotd/view', true)}">
    {foreach Router::getRelAlternate() as $lang => $url}
      <link rel="alternate" hreflang="{$lang}" href="{$url}">
    {/foreach}
    <link rel="icon" type="image/svg+xml" href="{Config::URL_PREFIX}img/favicon.svg">
    <link rel="apple-touch-icon" href="{Config::URL_PREFIX}img/apple-touch-icon.png">
    {Plugin::notify('htmlHead')}
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
    <div class="container {block "containerClasses"}container-narrow{/block}">
      <main class="row">
        <div class="col-md-12">
          {block "flashMessages"}
            {include "bits/flashMessages.tpl"}
          {/block}
          {block "search"}
            {include "bits/searchForm.tpl"}
          {/block}
          {block "content"}{/block}
        </div>
      </main>

      <footer>
        <div class="text-center">
          Copyright © 2004-{$currentYear} dexonline (https://dexonline.ro)
        </div>

        <ul class="text-center list-inline list-inline-bullet mt-2">
          <li class="list-inline-item">
            <a href="{Router::link('simple/license')}">{t}license{/t}</a>
          </li>
          <li class="list-inline-item">
            <a href="https://wiki.dexonline.ro/wiki/Principii_de_confiden%C8%9Bialitate_dexonline.ro">{t}privacy{/t}</a>
          </li>

          {$host=Config::SKIN_HOSTED_BY}
          {if $host}
            <li class="list-inline-item">{include "hosting/$host.tpl"}</li>
          {/if}
        </ul>
      </footer>
    </div>
    {include "bits/debugInfo.tpl"}
  </body>

  {Plugin::notify('afterBody')}

</html>
