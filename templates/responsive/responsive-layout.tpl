{assign var="cuv" value=$cuv|default:''}
{assign var="onHomePage" value=$onHomePage|default:false}
{assign var="suggestHiddenSearchForm" value=$suggestHiddenSearchForm|default:false}
{assign var="suggestNoBanner" value=$suggestNoBanner|default:false}
<!DOCTYPE html>
<html>
  <head>
    <title>{block name=title}Dicționare ale limbii române{/block} | dexonline</title>
    <meta charset="utf-8" />
    <meta content="initial-scale=1.0, maximum-scale=3.0, user-scalable=yes" name="viewport">
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
    <h1>Responsive AF</h1>
  </body>
</html>
