{extends "layout.tpl"}

{block "title"}Dicționar explicativ al limbii române{/block}

{block "banner"}{/block}
{block "search"}{/block}

{block "content"}
  <header>
    <div class="siteIdentity">
      {$svgLogo}
      <div class="tagline">{t}Dictionaries of the Romanian language{/t}</div>
    </div>
  </header>

  {include "bits/searchForm.tpl"}
  {include "banner/banner.tpl"}

  <section class="row widgets">
    {if Config::SKIN_WIDGETS}
      {foreach $widgets as $params}
        {if $params.enabled}
          <div class="col-md-4">{include "widgets/`$params.template`"}</div>
        {/if}
      {/foreach}
    {/if}
    <div class="col-md-12">
      <a
        class="btn btn-link customise-widgets float-end"
        href="{Router::link('user/preferences')}">
        {include "bits/icon.tpl" i=settings class="text-muted"}
        {t}customize widgets{/t}
      </a>
    </div>
  </section>

  <div class="website-statement mt-2 text-center">
    <i>dexonline</i> {t}digitizes prestigious dictionaries of the Romanian language.{/t}
    {t}The project is maintained by a team of volunteers.{/t}
    {t}Much of the data can be downloaded freely under the GNU General Public License.{/t}
    {t 1=$wordsTotal|nf 2=$wordsLastMonth|nf}
    Current status: %1 definitions, of which %2 learned last month.
    {/t}
  </div>

{/block}
