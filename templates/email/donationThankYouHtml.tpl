{* Keep this in sync with donationThankYouTxt.tpl *}
<p>
  Bună ziua,
</p>

<p>
  Vă mulțumim pentru donația dumneavoastră generoasă. Asociația dexonline va folosi acești bani
  pentru unul dintre <a href="{Router::link('donation/donate', true)}">proiectele noastre</a>.
</p>

<p>
  Dorim să răsplătim gestul dumneavoastră după cum urmează:
</p>

<ul>
  {if $donor->needsReward(Donor::RANGE_TEE)}
    <li>
      un tricou cu dexonline;
    </li>
  {/if}
  {if $donor->needsReward(Donor::RANGE_LAPEL_PIN)}
    <li>
      o insignă cu dexonline;
    </li>
  {/if}
  {if $donor->needsReward(Donor::RANGE_STICKER)}
    <li>
      trei autocolante cu dexonline;
    </li>
  {/if}
  {if $donor->needsReward(Donor::RANGE_NO_BANNERS)}
    <li>
      pagini fără reclame și preferință pentru
      <a href="https://wiki.dexonline.ro/wiki/Modul_confiden%C8%9Bial">modul confidențial</a>
      timp de un an;
    </li>
  {/if}
  <li>
    o medalie (virtuală) pentru donatori.
  </li>
</ul>

<p>
  {if !$donor->user}
    Dacă doriți să beneficiați de premiile virtuale (medalie și/sau pagini fără reclame),
    aveți nevoie de <a href="{Router::link('auth/login', true)}">un cont pe dexonline</a>.
  {elseif $donor->needsReward(Donor::RANGE_NO_BANNERS)}
    V-am acordat medalia și am ascuns reclamele în
    <a href="{Router::link('user/view', true)}/{$donor->user->nick|escape:url}"
    >contul dumneavoastră</a>.
    Dacă preferați ca donația dumneavoastră să fie anonimă, vă rugăm să ne
    contactați.
  {else}
    V-am acordat medalia în
    <a href="{Router::link('user/view', true)}/{$donor->user->nick|escape:url}"
    >contul dumneavoastră</a>.
    Dacă preferați ca donația dumneavoastră să fie anonimă, vă rugăm să ne
    contactați.
  {/if}
</p>

{if $donor->needsMaterialReward()}
  <p>
    Dacă doriți să beneficiați de premiile fizice (tricou, insignă și/sau
    autocolante), vă rugăm să ne trimiteți adresa pe care doriți să le
    primiți, eventual și un număr de telefon astfel încît curierul să vă poată
    suna. Pentru tricou, spuneți-ne și măsura dorită (XS/S/M/L/XL).
  </p>
{/if}

<p>
  Vă mulțumim încă o dată călduros și promitem să folosim la maximum donația dumneavoastră!
</p>

<p>
  Din partea echipei dexonline,<br>
  {User::getActive()->name}
</p>
