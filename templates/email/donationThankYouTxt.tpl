{* Keep this in sync with donationThankYouHtml.tpl *}
Bună ziua,

Vă mulțumim pentru donația dumneavoastră generoasă. Asociația dexonline va
folosi acești bani pentru unul dintre proiectele noastre [1].

Dorim să răsplătim gestul dumneavoastră după cum urmează:

* {Donor::AMOUNT_MEDAL} de lei -- medalii (virtuale) pentru donatori;
* {Donor::AMOUNT_NO_BANNERS} de lei -- în plus, pagini fără reclame și preferință pentru
  modul confidențial [2] timp de un an;
* {Donor::AMOUNT_STICKER} de lei -- în plus, trei autocolante cu dexonline;
* {Donor::AMOUNT_TEE} de lei -- în plus, un tricou cu dexonline.

{if !$donor->user}
Dacă doriți să beneficiați de premiile virtuale (medalie și/sau pagini fără
reclame), aveți nevoie de un cont pe dexonline [3].
{elseif $donor->amount < Donor::AMOUNT_NO_BANNERS}
V-am acordat medalia în contul dumneavoastră [3]. Dacă preferați ca
donația dumneavoastră să fie anonimă, vă rugăm să ne contactați.
{else}
V-am acordat medalia și am ascuns reclamele în contul dumneavoastră [3].
Dacă preferați ca donația dumneavoastră să fie anonimă, vă rugăm să ne
contactați.
{/if}

{if $donor->amount >= Donor::AMOUNT_STICKER}
Dacă doriți să beneficiați de premiile fizice (autocolante și/sau tricou), vă rugăm
să ne trimiteți adresa pe care doriți să le primiți.

{/if}
Vă mulțumim încă o dată călduros și promitem să folosim la maximum donația dumneavoastră!

Din partea echipei dexonline,
{User::getActive()->name}

----
[1] {Router::link('donation/donate', true)}
[2] https://wiki.dexonline.ro/wiki/Modul_confiden%C8%9Bial
{if $donor->user}
[3] {Router::link('user/view', true)}/{$donor->user->nick|escape:url}
{else}
[3] {Router::link('auth/login', true)}
{/if}
