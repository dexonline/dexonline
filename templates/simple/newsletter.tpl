{extends "layout.tpl"}

{block "title"}
    {cap}newsletter{/cap}
{/block}

{block "content"}

  <h3>Abonează-te la newsletter</h3>

  <p class="fs-4 ps-3">
  {include "bits/newsletterForm.tpl"}
  <p>
  <br/>
  <h3>{cap}Contact{/cap}</h3>

  <p class="fs-4 ps-3">
    <a href="mailto:newsletter@dexonline.ro">
        {include "bits/icon.tpl" i=email}
        newsletter@dexonline.ro
    </a>
  <p>
  <h3>Arhiva newsletterului</h3>

  <p class="ps-3">
    <span class="fs-5">&#x2022; 9 septembrie 2024 <a href="https://blog.dexonline.ro/?mailpoet_router&endpoint=view_in_browser&action=view&data=WzI1LCJkZTNhNDA0ZGE3MTciLDQsIjNlYjljZCIsMTE3NSwxXQ">
      bliț-răvaș / septembrie 24</a></span> <span clas="fs-6">(Ziua Limbii Române, concurs, premii)</span>
    </br>
    <span class="fs-5">&#x2022; 3 septembrie 2024 <a href="https://blog.dexonline.ro/?mailpoet_router&endpoint=view_in_browser&action=view&data=WzIzLCJiNmQxYzEwOTVkMDciLDQsIjNlYjljZCIsMTE1NiwxXQ">
      jurnal de dicționar / septembrie 24</a></span> <span clas="fs-6">(Ziua Limbii Române, școală, pampers, Radu Borza)</span>
    </br>
   <span class="fs-5">&#x2022; 3 august 2024 <a href="https://blog.dexonline.ro/?mailpoet_router&endpoint=view_in_browser&action=view&data=WzIwLCIzMGNlMThjNDk0MmEiLDQsIjNlYjljZCIsMTA1MiwxXQ">
      jurnal de dicționar / august 24</a></span> <span clas="fs-6">(concurs, Ziua Limbii Române, panseluțe, oameni, BBC, jaluzea, caniculă, cuvinte aleatorii, Anca Alexandru)</span>
    </br>
  <span class="fs-5">&#x2022; 3 iulie 2024 <a href="https://blog.dexonline.ro/?mailpoet_router&endpoint=view_in_browser&action=view&data=WzE2LCI1YjdmZGU3YzNmNjQiLDQsIjNlYjljZCIsODgyLDFd">
      jurnal de dicționar / iulie 24</a></span> <span clas="fs-6">(cifre romane, bacalaureat, Dicționar enciclopedic ilustrat CADE,
      greșeli dexonline, interviu playtech, Oana Ciobancan)</span>
  </br>
  <span class="fs-5">&#x2022; 3 iunie 2024 <a href="https://blog.dexonline.ro/?mailpoet_router&endpoint=view_in_browser&action=view&data=WzEzLCI2ZjQwZjM1ZWNlOTMiLDQsIjNlYjljZCIsNjA0LDFd">
      jurnal de dicționar / iunie 24</a></span> <span clas="fs-6">(Mircea Miclea, empatie, principii dexonline, învederat-inveterat, Cătălin Frâncu)</span>
  <br/>
    <span class="fs-5">&#x2022; 3 mai 2024 <a href="https://blog.dexonline.ro/?mailpoet_router&endpoint=view_in_browser&action=view&data=WzQsIjI1MDQ2NGViNDQ0MCIsNCwiM2ViOWNkIiwyLDFd">
        jurnal de dicționar / mai 24</a></span> <span clas="fs-6">(Leonardo da Vinci, ortografie, ca și, Matei Gall)</span>
  </p>

{/block}
