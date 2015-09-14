<section class="siteIdentity">
  <div class="siteLogo"></div>
  <div class="tagline">Dicționare ale limbii române</div>
</section>

<section id="searchHomePage">
  {include file="bits/searchForm.ihtml" advancedSearch=0}
</section>

{if !$suggestNoBanner}
  {include file="bits/banner.ihtml" id="mainPage" width="728" height="90"}
{/if}

{if $numEnabledWidgets}
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

<section id="missionStatement">
  <i>dexonline</i> transpune pe Internet dicționare de prestigiu ale limbii române. Proiectul este întreținut de un colectiv de voluntari.
  O parte din definiții pot fi descărcate liber și gratuit sub Licența Publică Generală GNU.<br>
  Starea curentă: {$words_total} de definiții, din care {$words_last_month} învățate în ultima lună.
</section>
