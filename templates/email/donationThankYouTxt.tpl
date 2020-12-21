{* Keep this in sync with donationThankYouHtml.tpl *}
Bună ziua,

Vă mulțumim pentru donația dumneavoastră generoasă. Asociația dexonline va
folosi acești bani pentru unul dintre proiectele noastre [1].

Dorim să răsplătim gestul dumneavoastră după cum urmează:

{if $donor->needsReward(Donor::RANGE_TEE)}
* un tricou cu dexonline;
{/if}
{if $donor->needsReward(Donor::RANGE_LAPEL_PIN)}
* o insignă cu dexonline;
{/if}
{if $donor->needsReward(Donor::RANGE_STICKER)}
* trei autocolante cu dexonline;
{/if}
{if $donor->needsReward(Donor::RANGE_NO_BANNERS)}
* pagini fără reclame și preferință pentru modul confidențial [2] timp de un an;
{/if}
* o medalie (virtuală) pentru donatori.

{if !$donor->user}
Dacă doriți să beneficiați de premiile virtuale (medalie și/sau pagini fără
reclame), aveți nevoie de un cont pe dexonline [3].
{elseif $donor->needsReward(Donor::RANGE_NO_BANNERS)}
V-am acordat medalia și am ascuns reclamele în contul dumneavoastră [3].  Dacă
preferați ca donația dumneavoastră să fie anonimă, vă rugăm să ne contactați.
{else}
V-am acordat medalia în contul dumneavoastră [3]. Dacă preferați ca donația
dumneavoastră să fie anonimă, vă rugăm să ne contactați.
{/if}

{if $donor->needsMaterialReward()}
Dacă doriți să beneficiați de premiile fizice (tricou, insignă și/sau
autocolante), vă rugăm să ne trimiteți adresa pe care doriți să le primiți,
eventual și un număr de telefon astfel încît curierul să vă poată suna. Pentru
tricou, spuneți-ne și măsura dorită (XS/S/M/L/XL).

{/if}
Vă mulțumim încă o dată călduros și promitem să folosim la maximum donația dumneavoastră!

Din partea echipei dexonline,
{User::getActive()->name}

----
[1] {Router::link('donation/donate', true)}
{if $donor->needsReward(Donor::RANGE_NO_BANNERS)}
[2] https://wiki.dexonline.ro/wiki/Modul_confiden%C8%9Bial
{/if}
{if $donor->user}
[3] {Router::link('user/view', true)}/{$donor->user->nick|escape:url}
{else}
[3] {Router::link('auth/login', true)}
{/if}
