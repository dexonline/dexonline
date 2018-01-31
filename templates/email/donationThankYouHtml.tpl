{* Keep this in sync with donationThankYouTxt.tpl *}
<p>
  Bună ziua,
</p>

<p>
  Vă mulțumim pentru donația dumneavoastră generoasă. Asociația dexonline va folosi acești bani
  pentru unul dintre <a href="{$wwwRoot}doneaza">proiectele noastre</a>.
</p>

<p>
  Dorim să răsplătim gestul dumneavoastră după cum urmează:
</p>

<ul>
  <li>
    {Donor::AMOUNT_MEDAL} de lei -- medalii (virtuale) pentru donatori;
  </li>
  <li {if $donor->amount < Donor::AMOUNT_NO_BANNERS}style="color: #999"{/if}>
    {Donor::AMOUNT_NO_BANNERS} de lei -- în plus, pagini fără reclame și preferință pentru
    <a href="https://wiki.dexonline.ro/wiki/Modul_confiden%C8%9Bial">modul confidențial</a>
    timp de un an;
  </li>
  <li {if $donor->amount < Donor::AMOUNT_STICKER}style="color: #999"{/if}>
    {Donor::AMOUNT_STICKER} de lei -- în plus, trei autocolante cu dexonline;
  </li>
  <li {if $donor->amount < Donor::AMOUNT_TEE}style="color: #999"{/if}>
    {Donor::AMOUNT_TEE} de lei -- în plus, un tricou cu dexonline.
  </li>
</ul>

<p>
  {if !$donor->user}
    Dacă doriți să beneficiați de premiile virtuale (medalie și/sau pagini fără reclame),
    aveți nevoie de un cont pe dexonline. Vă puteți
    <a href="{$wwwRoot}auth/login">autentifica</a> folosind orice OpenID,
    care include orice cont de Google sau Yahoo.
  {elseif $donor->amount < Donor::AMOUNT_NO_BANNERS}
    V-am acordat medalia în
    <a href="{$wwwRoot}utilizator/{$donor->user->nick|escape:url}"
    >contul dumneavoastră</a>.
    Dacă preferați ca donația dumneavoastră să fie anonimă, vă rugăm să ne
    contactați.
  {else}
    V-am acordat medalia și am ascuns reclamele în
    <a href="{$wwwRoot}utilizator/{$donor->user->nick|escape:url}"
    >contul dumneavoastră</a>.
    Dacă preferați ca donația dumneavoastră să fie anonimă, vă rugăm să ne
    contactați.
  {/if}
</p>

{if $donor->amount >= Donor::AMOUNT_STICKER}
  <p>
    Dacă doriți să beneficiați de premiile fizice (autocolante și/sau tricou), vă rugăm
    să ne trimiteți adresa pe care doriți să le primiți.
  </p>
{/if}

<p>
  Vă mulțumim încă o dată călduros și promitem să folosim la maximum donația dumneavoastră!
</p>

<p>
  Din partea echipei dexonline,<br>
  {User::getActive()->name}
</p>
