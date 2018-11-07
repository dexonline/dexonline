{extends "layout.tpl"}

{block "title"}Dicționar explicativ al limbii române{/block}

{block "banner"}{/block}
{block "search"}{/block}

{block "content"}
  <header>
    <div class="siteIdentity">
      <img class='siteLogo' src='{$wwwRoot}img/svg/logo-dexonline.svg' alt='dexonline'>
      <div class="tagline">{'Dictionaries of the Romanian language'|_}</div>
    </div>
  </header>

  {include "bits/searchForm.tpl"}
  {include "banner/banner.tpl"}

  <section class="row widgets">
    <div class="col-md-12">
      {if $numEnabledWidgets && $skinVariables.widgets}
        {foreach $widgets as $params}
          {if $params.enabled}
            <div class="col-sm-4 col-xs-12">{include "widgets/`$params.template`"}</div>
          {/if}
        {/foreach}
      {/if}
    </div>
    <div class="col-md-12">
      <a class="btn btn-link customise-widgets pull-right" href="preferinte"><span class="glyphicon glyphicon-cog"></span>personalizare elemente</a>
    </div>
  </section>


  <div class="col-md-6 col-md-offset-3 website-statement text-center">
    <p>

      <i>dexonline</i> {'digitizes prestigious dictionaries of the Romanian language.'|_}
      {'The project is maintained by a team of volunteers.'|_}
      {'Much of the data can be downloaded freely under the GNU General Public License.'|_}

      <br>

      {'Current status: %s definitions, of which %s learned last month.'|_|sprintf
      :{Locale::number($wordsTotal)}
      :{Locale::number($wordsLastMonth)}}

    </p>
  </div>

{/block}

{block "footer"}{/block}
