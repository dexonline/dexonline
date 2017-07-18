{extends "layout.tpl"}

{block "title"}
  Cuvântul zilei ({$day} {$monthName} {$year}): {$searchResult->definition->lexicon}
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

  <h3>
    Cuvântul zilei, {$day} {$monthName} {$year}

    {if $skinVariables.wotdSubscribe}
      <div id="wotdSocialMedia" class="pull-right">
        <div>
          <a href="#toggleContents" data-toggle="collapse"><img src="{$imgRoot}/social-media/email-29.png" alt="iconiță email"></a>
          <a type="application/rss+xml" href="https://dexonline.ro/rss/cuvantul-zilei"><img src="{$imgRoot}/social-media/rss-29.png" alt="iconiță RSS"></a>
          <a href="https://www.facebook.com/dexonline"><img src="{$imgRoot}/social-media/facebook-29.png" alt="iconiță Facebook"></a>
        </div>
      </div>
    {/if}
  </h3>

  <div id="toggleContents" class="collapse voffset2">
    <div class="panel panel-default">
      <div class="panel-body">
        <i>dexonline</i> nu oferă cuvântul zilei direct prin email. Există însă
        <a href="http://www.google.com/search?q=rss+by+email">numeroase site-uri</a>
        care fac acest lucru pentru orice RSS. Vă recomandăm
        <a href="https://ifttt.com/recipes/147561-rss-feed-to-email">IFTTT</a> (RSS feed to email).
      </div>
    </div>
  </div>

  {if $wotd->sponsor}
    {include "wotd-sponsors/{$wotd->sponsor}"}
  {/if}

  <div class="container panel panel-default">
    <div class="row panel-heading">
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 wotd-navigation">
        {if $prevDay}
          <a href="{$wwwRoot}cuvantul-zilei/{$prevDay}">
            <span class="glyphicon glyphicon-chevron-left"></span>
          </a>
        {/if}
      </div>
      <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 wotd-navigation">
        {if $nextDay}
          <a href="{$wwwRoot}cuvantul-zilei/{$nextDay}">
            <span class="glyphicon glyphicon-chevron-right pull-right">
            </span>
          </a>
        {/if}
      </div>
    </div>
    <div class="row panel-body">

      {include "bits/definition.tpl"
      row=$searchResult
      showBookmark=1
      showCourtesyLink=1
      showFlagTypo=1
      showHistory=1}

      {if $imageUrl}
        <a {if $wotd->url}href="{$wotd->url}"{/if} target="_blank">
          <img class="img-responsive center-block"
               src="{$imageUrl}" alt="{$searchResult->definition->lexicon}"
               title="{$searchResult->definition->lexicon}">
        </a>
        <div class="text-muted pull-right">
          {$artist->credits|default:''}
        </div>
      {/if}
    </div>
    {if $reason}
      <div class="row panel-footer">
        <b>Cheia alegerii:</b> {$reason|escape:'html'}
      </div>
    {/if}
  </div>

  {if $skinVariables.wotdArchive}
    <br>
    <h3>Arhiva cuvintelor zilei</h3>

    <div id="wotdArchive" class="wotdArchive"></div>
    <script>loadAjaxContent('{$wwwRoot}arhiva/cuvantul-zilei/{$year}/{$month}','#wotdArchive')</script>

    <h3>Cuvântul zilei de {$day} {$monthName} în alți ani:</h3>
    {foreach $otherYears as $r}
      <div class="panel panel-default">
        <div class="panel-body">
          <img class="pull-right"
               src="{$r.wotd->getSmallThumbUrl()}"
               alt="iconița cuvântului zilei">
          <p>
            <strong>{$r.wotd->displayDate|date_format:'%Y'}:</strong>
            <a href="{$wwwRoot}cuvantul-zilei/{$r.wotd->displayDate|date_format:'%Y/%m/%d'}">
              {$r.word}
            </a>
          </p>
          {$r.wotd->description}
        </div>
      </div>
    {/foreach}
  {/if}

  <h3>Comentarii</h3>
  <fb:comments href="https://dexonline.ro/cuvantul-zilei/{$year}/{$month}/{$day}"></fb:comments>
{/block}
