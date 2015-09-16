{extends file="layout.tpl"}

{block name=title}Donează{/block}

{block name=content}
  <script>
   <!--
   function formCheck(formobj) {
     var email = formobj.elements['email'].value;
     if (email) {
       return true;
     } else {
       alert('Vă rugăm să completați adresa de email.');
       return false;
     }
   }
   // -->
  </script>

  <div class="donateContent">
    Dacă ați ajuns pe această pagină, probabil că știți deja ce este <i>dexonline</i> (dacă nu, puteți afla din
    <a href="http://wiki.dexonline.ro/wiki/Informa%C8%9Bii" target="_blank">pagina de informații</a>).<br />

    Puteți contribui la dezvoltarea proiectului <i>dexonline</i> și prin donarea unei sume de bani.

    <h2>Donează</h2>

    {if $haveEuPlatescCredentials}
      <div class="paymentSection">
        <h3>
          Donează online
          <span title="Comision fix de 3,5%" class="tooltip2">&nbsp;</span>
        </h3>
        <form action="doneaza-euplatesc.php" method="post" enctype="multipart/form-data"  onsubmit="return formCheck(this);">
          <label>Suma</label>
          <select name="amount">
            <option value="10">10 lei</option>
            <option value="20">20 lei</option>
            <option value="50" selected>50 lei</option>
            <option value="100">100 lei</option>
            <option value="150">150 lei</option>
            <option value="200">200 lei</option>
          </select><br/>
          <label title="e-mailul este necesar pentru trimiterea confirmării plății">E-mail *</label>
          <input id="donateOnlineEmail" type="text" name="email" value="{$defaultEmail}"/> <br/>
          <input type="submit" name="Doneaza" value="" class="onlineDonationButton btn"/>
          <label class="tipText">* necesar pentru trimiterea confirmării plății</label>
        </form>
      </div>
      <script>
       $(function() {
         $('#donateOnlineEmail').focus();
       });
      </script>
    {/if}

    <div class="paymentSection paypal">
      <h3>
        Donează prin PayPal
        <span title="Comision mediu 6,5% (10% la donații de 5€, 4% la 25€)" class="tooltip2">&nbsp;</span>
      </h3>
      <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
                                                     ">
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" class="payPalButton" />
        <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" />
      </form>
    </div>

    <div class="paymentSection bankTransfer">
      <h3>
        Donează prin transfer bancar
        <span title="Comisionul este oprit la trimitere" class="tooltip2">&nbsp;</span>
      </h3>
      <ul>
        <li>Beneficiar<span class="bankAccount"> Asociația dexonline</span></li>
        <li>CIF<span class="bankAccount"> 30855345</span></li>
        <li>Adresa<span class="bankAccount"> strada Remetea nr. 20, <br />București, sector 2</span></li>
        <li>Cont<span class="bankAccount"> Banca Transilvania, sucursala Obor</span></li>
        <li>RON<span class="bankAccount"> RO96 BTRL 0440 1205 M306 19XX</span></li>
      </ul>
    </div>

    <br /><br />

    {include file="bits/doneaza-la-ce-folosim.tpl"}
    {include file="bits/doneaza-doi-la-suta.tpl"}
    {include file="bits/doneaza-firme.tpl"}
    {include file="bits/doneaza-rasplata.tpl"}
  </div>


  <script>
   $(function() {
     $('.donateDetailLink').click(function() {
       $(this).parent().next().slideToggle();
       return false;
     });
   });
  </script>
{/block}
