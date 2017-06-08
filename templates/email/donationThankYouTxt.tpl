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
reclame), aveți nevoie de un cont pe dexonline. Vă puteți autentifica [3]
folosind orice OpenID, care include orice cont de Google sau Yahoo.
Apoi, dați-ne de știre.
{elseif $donor->amount < Donor::AMOUNT_NO_BANNERS}
V-am acordat medalia în contul dumneavoastră [3].
{else}
V-am acordat medalia și am ascuns reclamele în contul dumneavoastră [3].
Dacă încă observați reclame, vă rugăm să vă deconectați și reconectați în cont.
{/if}

{if $donor->amount >= Donor::AMOUNT_STICKER}
Dacă doriți să beneficiați de premiile fizice (autocolante și/sau tricou), vă rugăm
să ne trimiteți adresa pe care doriți să le primiți.

{/if}
Vă mulțumim încă o dată călduros și promitem să folosim la maximum donația dumneavoastră!

Din partea echipei dexonline,
{User::getActive()->name}

----
[1] https://dexonline.ro/doneaza
[2] http://wiki.dexonline.ro/wiki/Modul_confiden%C8%9Bial
{if $donor->user}
[3] https://dexonline.ro/utilizator/{$donor->user->nick|escape:url}
{else}
[3] https://dexonline.ro/auth/login
{/if}
