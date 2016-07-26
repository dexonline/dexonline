{extends file="layout.tpl"}

{block name=title}Preferințe{/block}

{block name=content}
  {if $sUser}
    <div class="panel panel-default">
      <div class="panel-heading">Imagine</div>
      <div class="panel-body">
        <form action="editare-avatar" method="post" enctype="multipart/form-data">
          {include file="bits/avatar.tpl" user=$sUser}
          <br />
          <br />
          <div class="form-group">
            <label for="avatarFileName">Fișier:</label>
            <input id="avatarFileName" type="file" name="avatarFileName">
          </div>
          <input id="avatarSubmit" class="btn btn-default" type="submit" name="submit" value="Editează" disabled="disabled">
          {* TODO: Hide this when the user does not have an avatar *}
          <a href="salvare-avatar?delete=1" onclick="return confirm('Confirmați ștergerea imaginii?');">șterge imaginea</a>

          <p class="text-muted">
            Imaginea profilului dumneavoastră are rezoluția de 48x48 pixeli. Pe ecranul următor puteți edita poza încărcată.
          </p>
        </form>
      </div>
    </div>
  {/if}


  <form method="post" action="preferinte" name="accountForm">
    {if $sUser}
      <div class="panel panel-default">
        <div class="panel-heading">Date personale</div>
        <div class="panel-body">
          <div class="checkbox">
            <label>
              <input type="checkbox" id="detailsVisible" name="detailsVisible" value="1" {if $detailsVisible}checked="checked"{/if} />
              Datele mele sunt vizibile public
              <span class="help-block">
                Identitatea OpenID, numele și adresa de email furnizate de OpenID vor apărea în <a href="{$wwwRoot}utilizator/{$sUser->nick}">profilul dumneavoastră</a>.
                <em>dexonline</em> nu permite editarea directă a acestor date. <br />Ele sunt preluate din identitatea OpenID.
              </span>
            </label>
          </div>
        </div>
      </div>
    {/if}

    <div class="panel panel-default">
      <div class="panel-heading">Preferințe</div>
      <div class="panel-body">
        {foreach from=$userPrefs key=value item=i}
          <div class="checkbox">
            <label>
              <input type="checkbox" name="userPrefs[]" id="cb_{$value}" value="{$value}" class="cbOption" {if $i.checked}checked="checked"{/if}/>
              {$i.label}
              <span class="help-block">{$i.comment}</span>
            </label>
          </div>
        {/foreach}
      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">Design</div>
      <div class="panel-body">
        <div class="form-group">
          <select class="form-control" name="skin" id="skinsList">
            {foreach from=$availableSkins item=i}
              <option value="{$i}" {if $i == $skin}selected="selected"{/if}>{$i|capitalize}</option>
            {/foreach}
          </select>
          <span class="help-block">Notă: Numai designul Responsive este ținut la zi cu cele mai noi funcții.</span>
        </div>
      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">Elemente în pagina principală</div>
      <div class="panel-body">

        {foreach from=$widgets item=w key=value}
          <div class="checkbox">
            <label>
              <input type="checkbox" name="widgets[]" id="widget{$value}" value="{$value}" {if $w.enabled}checked="checked"{/if}/>
              {$w.name}
            </label>
          </div>
        {/foreach}
      </div>
    </div>

    {if $sUser && $sUser->moderator > 0}
      <div class="panel panel-default">
        <div class="panel-heading">Privilegii</div>
        <div class="panel-body">
          <ul>
            {section name="bit" loop=$smarty.const.NUM_PRIVILEGES}
              {math equation="1 << x" x=$smarty.section.bit.index assign="mask"}
              {if $sUser->moderator & $mask}
                <li>{$privilegeNames[$smarty.section.bit.index]}</li>
              {/if}
            {/section}
          </ul>
        </div>
      </div>
    {/if}

    <input class="btn btn-primary" type="submit" name="send" value="Salvează" id="saveButton"/>
    {if $sUser}
      <a class="btn btn-link" href="{$wwwRoot}utilizator/{$sUser->nick|escape}">renunță</a>
    {/if}

  </form>

  <script>
   $('#avatarFileName').change(function() {
     var error = '';
     var allowedTypes = ['image/gif', 'image/jpeg', 'image/png'];
     if (this.files[0].size > (1 << 21)) {
       error = 'Dimensiunea maximă admisă este 2 MB.';
     } else if (allowedTypes.indexOf(this.files[0].type) == -1) {
       error = 'Sunt permise doar imagini jpeg, png sau gif.';
     }
     if (error) {
       $('#avatarFileName').val('');
       $('#avatarSubmit').attr('disabled', 'disabled');
       alert(error);
     } else {
       $('#avatarSubmit').removeAttr('disabled');
     }
     return false;
   });
  </script>
{/block}
