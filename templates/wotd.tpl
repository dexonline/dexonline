{extends "layout.tpl"}

{block "title"}
  Cuvântul zilei ({$timestamp|date_format:'%e %B %Y'}): {$searchResult->definition->lexicon}
{/block}

{block "pageDescription"}
  <meta name="description"
        content="Cuvântul zilei de {$timestamp|date_format:'%e %B %Y'} la dexonline: {$searchResult->definition->lexicon}"/>
{/block}

{block "openGraph"}
  {* Nothing -- so crawlers index the image of the day instead. *}
{/block}

{block "content"}
  {assign var="nextday" value=$nextday|default:false}
  {assign var="prevday" value=$prevday|default:false}
  {assign var="reason" value=$reason|default:''}

  {if $skinVariables.wotdSubscribe}
    <div id="wotdSocialMedia">
      <a href="#toggleContents" data-toggle="collapse"><img src="{$imgRoot}/social-media/email-29.png" alt="iconiță email"/></a>
      <a type="application/rss+xml" href="https://dexonline.ro/rss/cuvantul-zilei"><img src="{$imgRoot}/social-media/rss-29.png" alt="iconiță RSS"/></a>
      <a href="https://www.facebook.com/dexonline"><img src="{$imgRoot}/social-media/facebook-29.png" alt="iconiță Facebook"/></a>
    </div>
    <div id="toggleContents" class="collapse">
      <br />
      <div class="panel panel-default">
        <div class="panel-body">
          <i>dexonline</i> nu oferă cuvântul zilei direct prin email. Există însă
          <a href="http://www.google.com/search?q=rss+by+email">numeroase site-uri</a>
          care fac acest lucru pentru orice RSS. Vă recomandăm
          <a href="https://ifttt.com/recipes/147561-rss-feed-to-email">IFTTT</a> (RSS feed to email).
        </div>
      </div>
    </div>
  {/if}

  <br />

  <div class="panel panel-default">
    <div class="panel-heading">
      <div class="panel-title">
        <span>Cuvântul zilei, {$timestamp|date_format:'%e %B %Y'}</span>
        <span class="pull-right">
          <a href="{$wwwRoot}cuvantul-zilei/{$prevday}"><span class="glyphicon glyphicon-chevron-left"></span></a>
          <a href="{$wwwRoot}cuvantul-zilei/{$nextday}"><span class="glyphicon glyphicon-chevron-right"></span></a>
        </span>
      </div>
    </div>
    <div class="panel-body">
      {include "bits/definition.tpl" row=$searchResult}
      {if $imageUrl}
        <img class="img-responsive center-block" src="{$imageUrl}" alt="{$searchResult->definition->lexicon}" title="{$searchResult->definition->lexicon}"/>
        <div class="text-muted pull-right">
          {$artist->credits|default:''}
        </div>
      {/if}
    </div>
    {if $reason}
      <div class="panel-footer">
        <b>Cheia alegerii:</b> {$reason|escape:'html'}
      </div>
    {/if}
  </div>

  {if $skinVariables.wotdArchive}
    <br />
    <h3>Arhiva cuvintelor zilei</h3>

    <div id="wotdArchive" class="wotdArchive"></div>
    <script>loadAjaxContent('{$wwwRoot}arhiva/cuvantul-zilei/{$timestamp|date_format:'%Y/%m'}','#wotdArchive')</script>

    <h3>Cuvântul zilei de {$timestamp|date_format:'%e %B'} în alți ani:</h3>
    {foreach $otherYears as $r}
      <div class="panel panel-default">
        <div class="panel-body">
          <img class="pull-right"
               src="{$r.wotd->getSmallThumbUrl()}"
               alt="iconița cuvântului zilei" />
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

  {include "bits/typoForm.tpl"}
{/block}
