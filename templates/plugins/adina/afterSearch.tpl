{if $pageType == 'home'}
  <div id="adina-alert" class="alert alert-warning alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>

    <p>
      Ca urmare a Ordonanței de Urgență nr. 420/2021 emisă de Guvernul României,
      dexonline a revenit la scrierea cu <b>Î</b> din <b>I</b>. Subliniem că
      dexonline protestează la această ordonanță abuzivă, ruptă de realitățile
      lingvistice și patriotice.
    </p>

    {if $adinaDetailsUrl}
      <p>
        <a href="{$adinaDetailsUrl}" class="alert-link">detalii aici</a>
      </p>
    {/if}
  </div>
{/if}
