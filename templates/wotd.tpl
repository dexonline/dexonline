{extends "layout.tpl"}

{block "title"}
  {cap}{t}word of the day{/t}{/cap} ({$day} {$monthName} {$year}):
  {$searchResult->definition->lexicon}
{/block}

{block "pageDescription"}
  {$lexicon=$searchResult->definition->lexicon}
  <meta name="description"
        content="Cuvântul zilei de {$day} {$monthName} {$year} la dexonline: {$lexicon}">
{/block}

{block "openGraph"}
  {* Nothing -- so crawlers index the image of the day instead. *}
{/block}

{block "content"}
  {assign var="reason" value=$reason|default:''}

  <h3 class="clearfix">
    {t 1=$day 2=$monthName 3=$year}Word of the day for %2 %1, %3{/t}

    {if Config::SKIN_WOTD_SUBSCRIBE}
      <div id="wotdSocialMedia" class="pull-right">
        <div>
          <a href="#toggleContents" data-toggle="collapse">
            <img src="{Config::URL_PREFIX}img/social-media/email-29.png" alt="iconiță email"
          ></a>
          <a type="application/rss+xml" href="{Config::URL_PREFIX}rss/cuvantul-zilei">
            <img src="{Config::URL_PREFIX}img/social-media/rss-29.png" alt="iconiță RSS"
          ></a>
          <a href="https://www.facebook.com/dexonline">
            <img src="{Config::URL_PREFIX}img/social-media/facebook-29.png" alt="iconiță Facebook"
          ></a>
        </div>
      </div>
    {/if}
  </h3>

  <div id="toggleContents" class="collapse voffset2">
    <div class="panel panel-default">
      <div class="panel-body">
        {t
          1="http://www.google.com/search?q=rss+by+email"
          2="https://ifttt.com/recipes/147561-rss-feed-to-email"
        }<i>dexonline</i> does not directly offer the word of the day by email.
        However, there are <a href="%1">many sites</a> that do this for any RSS feed.
        We recommend <a href="%2">IFTTT</a>.{/t}
      </div>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <div class="wotd-navigation pull-left">
        {if $prevDay}
          <a href="{Config::URL_PREFIX}cuvantul-zilei/{$prevDay}">
            <span class="glyphicon glyphicon-chevron-left"></span>
          </a>
        {/if}
      </div>
      <div class="wotd-navigation pull-right">
        {if $nextDay}
          <a href="{Config::URL_PREFIX}cuvantul-zilei/{$nextDay}">
            <span class="glyphicon glyphicon-chevron-right pull-right">
            </span>
          </a>
        {/if}
      </div>
    </div>
    <div class="panel-body">
      <a {if $wotd->url}href="{$wotd->url}"{/if} target="_blank">
        <img class="img-responsive center-block"
          src="{$wotd->getLargeThumbUrl()}"
          alt="{$searchResult->definition->lexicon}"
          title="{$searchResult->definition->lexicon}">
      </a>
      <div class="text-muted pull-right">
        {$wotd->getArtist()->credits|default:''}
      </div>

      {if $wotd->sponsor}
        <div style="clear: both"></div>
        {include "wotd-sponsors/{$wotd->sponsor}"}
      {/if}

      {include "bits/definition.tpl"
        row=$searchResult
        showBookmark=1
        showCourtesyLink=1
        showFlagTypo=1
        showHistory=1}

    </div>
    {if $reason}
      <div class="panel-footer">
        <b>{t}Chosen because:{/t}</b> {$reason}
      </div>
    {/if}
  </div>

  {if Config::SKIN_WOTD_ARCHIVE}
    <br>
    <h3>{t}Word of the day archive{/t}</h3>

    <div id="wotdArchive" class="wotdArchive"></div>
    <script>
      loadAjaxContent(
        '{Config::URL_PREFIX}arhiva/cuvantul-zilei/{$year}/{$month}',
        '#wotdArchive');
    </script>

    <h3>
      {t 1=$day 2=$monthName}Word of the day for %2 %1 in other years:{/t}
    </h3>
    {foreach $otherYears as $r}
      <div class="panel panel-default">
        <div class="panel-body">
          <img class="pull-right"
               src="{$r.wotd->getMediumThumbUrl()}"
               alt="iconița cuvântului zilei">
          <p>
            <strong>{$r.wotd->displayDate|date_format:'%Y'}</strong>:
            <a href="{Config::URL_PREFIX}cuvantul-zilei/{$r.wotd->getUrlDate()}">
              {$r.word}
            </a>
          </p>
          {$r.wotd->description}
        </div>
      </div>
    {/foreach}
  {/if}

  <h3>{t}Comments{/t}</h3>
  <fb:comments href="https://dexonline.ro/cuvantul-zilei/{$year}/{$month}/{$day}"></fb:comments>
{/block}
