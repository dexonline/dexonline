{extends file="responsive/responsive-layout.tpl"}

{block name=title}Dicționar explicativ al limbii române{/block}

{block name=content}
  <header class="row">
    <div class="col-md-12 siteIdentity">
      <div class="siteLogo"></div>
      <div class="tagline">Dicționare ale limbii române</div>
    </div>
  </header>

  <section class="row">
    <div class="col-md-9">
      <div class="row">
        <div class="col-md-12">
          <section class="row">
            {include file="responsive/bits/searchForm.tpl" advancedSearch=0}
          </section>

          {if !$suggestNoBanner}
            <div class="row">
              {include file="responsive/bits/banner.tpl" id="mainPage" width="100%" height="90"}
            </div>
          {/if}

          <footer class="row margin-top-25" id="missionStatement">
            <div class="col-md-12">
              <p>
                <i>dexonline</i> transpune pe Internet dicționare de prestigiu ale limbii române. Proiectul este întreținut de un colectiv de voluntari.
                O parte din definiții pot fi descărcate liber și gratuit sub Licența Publică Generală GNU.<br>
                Starea curentă: {$words_total} de definiții, din care {$words_last_month} învățate în ultima lună.
              </p>
            </div>
          </footer>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      {if $numEnabledWidgets && $skinVariables.widgets}
        <section class="widgetBox bendShadow">
          <ul class="widgetList">
            {foreach from=$widgets item=params}
              {if $params.enabled}
                <li>{include file="widgets/`$params.template`"}</li>
              {/if}
            {/foreach}

            <li class="widgetsPreferences">
              <a href="preferinte">personalizare elemente</a>
            </li>
          </ul>
        </section>
      {/if}
    </div>
  </section>
{/block}
