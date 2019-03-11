{extends "layout.tpl"}

{block "title"}Dicționar explicativ al limbii române{/block}

{block "banner"}{/block}
{block "search"}{/block}

{block "content"}
  <header>
    <div class="siteIdentity">
      <img class='siteLogo' src='img/svg/logo-dexonline.svg' alt='dexonline'>
      <div class="tagline">{t}Dictionaries of the Romanian language{/t}</div>
    </div>
  </header>

  {include "bits/searchForm.tpl"}
  {include "banner/banner.tpl"}

  <section class="row widgets">
    {if Config::SKIN_WIDGETS}
      <div class="col-md-12">
        {foreach $widgets as $params}
          {if $params.enabled}
            <div class="col-sm-4 col-xs-12">{include "widgets/`$params.template`"}</div>
          {/if}
        {/foreach}
      </div>
    {/if}
    <div class="col-md-12">
      <a
        class="btn btn-link customise-widgets pull-right"
        href="{Router::link('user/preferences')}">
        <i class="glyphicon glyphicon-cog"></i>
        {t}customize widgets{/t}
      </a>
    </div>
  </section>


  <div class="col-md-6 col-md-offset-3 website-statement text-center">
    <p>

      <i>dexonline</i> {t}digitizes prestigious dictionaries of the Romanian language.{/t}
      {t}The project is maintained by a team of volunteers.{/t}
      {t}Much of the data can be downloaded freely under the GNU General Public License.{/t}

      <br>

      {t 1=LocaleUtil::number($wordsTotal) 2=LocaleUtil::number($wordsLastMonth)}
      Current status: %1 definitions, of which %2 learned last month.{/t}
    </p>
  </div>

{/block}

{block "footer"}{/block}
