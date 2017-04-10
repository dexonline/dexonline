{extends "layout.tpl"}

{block "title"}Dicționar explicativ al limbii române{/block}

{block "banner"}{/block}
{block "search"}{/block}

{block "content"}
  <header>
    <div class="siteIdentity">
      <img class='siteLogo' src='{$wwwRoot}img/svg/logo-dexonline.svg' alt='dexonline'/>
      <div class="tagline">Dicționare ale limbii române</div>
    </div>
  </header>

  {include "bits/searchForm.tpl"}
  {include "bits/banner.tpl"}

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
      <i>dexonline</i> transpune pe Internet dicționare de prestigiu ale limbii române. Proiectul este întreținut de un colectiv de voluntari.
      O parte din definiții pot fi descărcate liber și gratuit sub Licența Publică Generală GNU.<br>
      Starea curentă: {$words_total} de definiții, din care {$words_last_month} învățate în ultima lună.
    </p>
  </div>

{/block}

{block "footer"}{/block}
