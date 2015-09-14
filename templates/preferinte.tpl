{if $sUser}
  <form action="editare-avatar" method="post" enctype="multipart/form-data">
    <p class="paragraphTitle">Imagine</p>
    {include file="bits/avatar.ihtml" user=$sUser}
    <label for="avatarFileName">Fișier:</label>
    <input id="avatarFileName" type="file" name="avatarFileName">
    <input id="avatarSubmit" type="submit" name="submit" value="Editează" disabled="disabled">
    {* TODO: Hide this when the user does not have an avatar *}
    <a href="salvare-avatar?delete=1" onclick="return confirm('Confirmați ștergerea imaginii?');">șterge imaginea</a>

    <div class="describeOption">
      Imaginea profilului dumneavoastră are rezoluția de 48x48 pixeli. Pe ecranul următor puteți edita poza încărcată.
    </div>
  </form>
{/if}

<form method="post" action="preferinte" name="accountForm">
  {if $sUser}
    <p class="paragraphTitle">Date personale</p>
    <input type="checkbox" id="detailsVisible" name="detailsVisible" value="1" {if $detailsVisible}checked="checked"{/if}/>
    <label for="detailsVisible">Datele mele sunt vizibile public</label>
    <div class="describeOption">
      Identitatea OpenID, numele și adresa de email furnizate de OpenID vor apărea în <a href="{$wwwRoot}utilizator/{$sUser->nick}">profilul dumneavoastră</a>.<br/>
      <i>dexonline</i> nu permite editarea directă a acestor date. Ele sunt preluate din identitatea OpenID.
    </div>
  {/if}

  <p class="paragraphTitle">Preferințe</p>
  {foreach from=$userPrefs key=value item=i}
    <input type="checkbox" name="userPrefs[]" id="cb_{$value}" value="{$value}" class="cbOption" {if $i.checked}checked="checked"{/if}/>
    <label for="cb_{$value}" class="labelOption">{$i.label}</label>
    <div class="describeOption">{$i.comment}</div>
  {/foreach}

  <p class="paragraphTitle">Design</p>
  <select name="skin" id="skinsList">
    {foreach from=$availableSkins item=i}
      <option value="{$i}" {if $i == $skin}selected="selected"{/if}>{$i|capitalize}</option>
    {/foreach}
  </select>
  <span class="describeOption">Notă: Numai designul Zepu este ținut la zi cu cele mai noi funcții.</span>

  <p class="paragraphTitle">Elemente în pagina principală</p>

  {foreach from=$widgets item=w key=value}
    <input type="checkbox" name="widgets[]" id="widget{$value}" value="{$value}" {if $w.enabled}checked="checked"{/if}/>
    <label for="widget{$value}">{$w.name}</label><br/>
  {/foreach}

  {if $sUser && $sUser->moderator > 0}
    <p class="paragraphTitle">Privilegii</p>

    <ul>
      {section name="bit" loop=$smarty.const.NUM_PRIVILEGES}
        {math equation="1 << x" x=$smarty.section.bit.index assign="mask"}
        {if $sUser->moderator & $mask}
          <li>{$privilegeNames[$smarty.section.bit.index]}</li>
        {/if}
      {/section}
    </ul>
  {/if}

  <br/>
  <input type="submit" name="send" value="Salvează" id="saveButton"/>
  {if $sUser}
    <a href="{$wwwRoot}utilizator/{$sUser->nick|escape}">renunță</a>
  {/if}
</form>

<script type="text/javascript">
{literal}
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
{/literal}
</script>
