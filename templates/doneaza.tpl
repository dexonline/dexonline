{extends "layout-admin.tpl"}

{block "title"}Donează{/block}

{block "content"}
  <div>
    <h3>Donează</h3>

    <p>
      Dacă ați ajuns pe această pagină, probabil că știți deja ce este <i>dexonline</i>
      (dacă nu, puteți afla din <a href="http://wiki.dexonline.ro/wiki/Informa%C8%9Bii"
                                   target="_blank">pagina de informații</a>).
      Puteți contribui la dezvoltarea proiectului <i>dexonline</i> și prin donarea unei sume de bani.
    </p>

    {if $haveEuPlatescCredentials}
      <div class="row">
        <div class="col-md-4">
          <div class="panel panel-info paymentSection">
            <div class="panel-heading" title="Comision fix de 3,5%">
              Donează online
              <i class="glyphicon glyphicon-info-sign"></i>
            </div>
            <div class="panel-body">
              <form id="donateOnline" action="doneaza-euplatesc.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                  <label class="donate-labels">Suma</label>
                  <select name="amount" class="form-control select-donated-sum">
                    <option value="10">10 lei</option>
                    <option value="20">20 lei</option>
                    <option value="50" selected>50 lei</option>
                    <option value="100">100 lei</option>
                    <option value="150">150 lei</option>
                    <option value="200">200 lei</option>
                  </select>
                </div>
                <div class="form-group">
                  <label title="e-mailul este necesar pentru trimiterea confirmării plății" class="donate-labels">E-mail *</label>
                  <input id="donateOnlineEmail" type="text" name="email" value="{$defaultEmail}" class="form-control donor-email">
                </div>
                <div class="text-center">
                  <input type="submit" name="Doneaza" value="" class="onlineDonationButton btn">
                </div>
              </form>
            </div>
          </div>
        </div>
    {/if}


    <div class="col-md-4">
      <div class="panel panel-info paymentSection">
        <div class="panel-heading" title="Comision mediu 6,5% (10% la donații de 5€, 4% la 25€)">
          Donează prin PayPal
          <i class="glyphicon glyphicon-info-sign"></i>
        </div>
        <div class="panel-body text-center">
          <form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="payPal">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
                                                         ">
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="panel panel-info paymentSection">
        <div class="panel-heading" title="Comisionul este oprit la trimitere">
          Donează prin transfer bancar
          <i class="glyphicon glyphicon-info-sign"></i>
        </div>
        <div class="panel-body bankTransfer">
          <div class="form-group">
            <label>Beneficiar</label>Asociația dexonline
          </div>
          <div class="form-group">
            <label>CIF</label>30855345
          </div>
          <div class="form-group">
            <label>Adresa</label>strada Remetea nr. 20, București, sector 2
          </div>
          <div class="form-group">
            <label>Cont</label>Banca Transilvania, sucursala Obor
          </div>
          <div class="form-group">
            <label>RON</label>RO96 BTRL 0440 1205 M306 19XX
          </div>
        </div>
      </div>
    </div>
      </div>

      <div class="clearfix"></div>
      {include "bits/doneaza-la-ce-folosim.tpl"}
      {include "bits/doneaza-doi-la-suta.tpl"}
      {include "bits/doneaza-firme.tpl"}
      {include "bits/doneaza-rasplata.tpl"}
  </div>
{/block}
