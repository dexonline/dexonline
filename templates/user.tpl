{extends file="layout.tpl"}

{block name=title}Utilizator: {$user->nick}{/block}

{block name=content}
  <div class="userProfileHeader">
    {include file="bits/avatar.tpl" user=$user}
    <h3>
      Utilizator: {$user->nick|escape}
      {if $sUser && $user->id == $sUser->id}
        <a class="profileEditLink" href="{$wwwRoot}preferinte">editează profilul</a>
      {/if}
    </h3>
  </div>

  {if $user->detailsVisible}
    <fieldset class="userProfileSection">
      <legend>Date personale</legend>
      {if $user->identity}
        <label class="userFields">OpenID</label>
        <a href="{$user->identity}">{$user->identity}</a>
        <br/>
      {/if}

      {if $user->detailsVisible && $user->name}
        <label class="userFields">Nume</label>
        {$user->name|escape}
        <br/>
      {/if}

      {if $user->detailsVisible && $user->email}
        <label class="userFields">Adresă de e-mail</label>
        {$user->email|escape}
        <br/>
      {/if}
    </fieldset>
  {/if}

  <fieldset class="userProfileSection">
    <legend>Contribuții</legend>
    <span class="userFields">Definiții trimise</span>
    {$userData.num_words|default:0}
    {if isset($userData.num_words)}(locul {$userData.rank_words}){/if}
    <br/>

    <span class="userFields">Lungime totală</span>
    {$userData.num_chars|default:0} caractere
    {if isset($userData.num_chars)}(locul {$userData.rank_chars}){/if}
    <br/>
  </fieldset>

  <fieldset class="userProfileSection">
    <legend>Medalii</legend>
    {if $sUser && $sUser->moderator & $smarty.const.PRIV_ADMIN}
      <span class="sectionEdit"><a onclick="$('#medalEditDiv').slideToggle(); return false;" href="#">editează</a></span>
      <form id="medalEditDiv" method="post">
        <div class="medalCheckboxes">
          <input type="hidden" name="userId" value="{$user->id}"/>
          {foreach from=$allMedals key=mask item=params}
            <input type="checkbox" name="medalsGranted[]" id="cb_{$mask}" value="{$mask}" {if array_key_exists($mask, $medals)}checked="checked"{/if}/>
            <label for="cb_{$mask}">{$params.name} {$params.description}</label><br/>
          {/foreach}
        </div>
        <input type="submit" name="medalSaveButton" value="Salvează"/>
      </form>
    {/if}

    {if $medals}
      {foreach from=$medals item=params}
        <img src="{$imgRoot}/medals/{$params.pic}" alt="{$params.name}" title="{$params.name} {$params.description}"/>
      {/foreach}
    {else}
      <span class="userNoMedals">Utilizatorul {$user->nick|escape} nu are medalii.</span>
    {/if}
  </fieldset>
{/block}
