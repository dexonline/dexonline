{extends file="layout.tpl"}

{block name=title}Dicționar explicativ al limbii române{/block}

{block name=content}
  <header class="row">
    <div class="col-md-12 siteIdentity">
      <div class="siteLogo"></div>
      <div class="tagline">Dicționare ale limbii române</div>
    </div>
  </header>

  {include file="bits/searchForm.tpl" advancedSearch=0}

  {if !$suggestNoBanner}
    {include file="bits/banner.tpl" id="mainPage" width="1024" height="90"}
  {/if}

  <section class="row widgets">
    <div class="col-md-12">
      {if $numEnabledWidgets && $skinVariables.widgets}
        {foreach from=$widgets item=params}
          {if $params.enabled}
            <div class="col-sm-4 col-xs-12">{include file="widgets/`$params.template`"}</div>
          {/if}
        {/foreach}

        <div class="widgetsPreferences">
          <a href="preferinte">personalizare elemente</a>
        </div>
      {/if}
    </div>
  </section>

  <footer class="row" id="missionStatement">
    <div class="col-md-12">
      <p>
        <i>dexonline</i> transpune pe Internet dicționare de prestigiu ale limbii române. Proiectul este întreținut de un colectiv de voluntari.
        O parte din definiții pot fi descărcate liber și gratuit sub Licența Publică Generală GNU.<br>
        Starea curentă: {$words_total} de definiții, din care {$words_last_month} învățate în ultima lună.
      </p>
    </div>
  </footer>

{/block}
