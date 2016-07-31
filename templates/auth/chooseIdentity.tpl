{extends file="layout.tpl"}

{block name=title}Autentificare cu OpenID{/block}

{block name=content}
  <h3>Autentificare cu OpenID</h3>

  Toți utilizatorii necesită un nume de cont; puteți alege una din opțiunile de mai jos.<br/>

  <form id="claimIdForm" method="post" action="{$wwwRoot}auth/revendicare">
    <input type="radio" name="loginType" value="0" id="claimAccount" {if $loginType == 0}checked="checked"{/if}/>
    <label for="claimAccount">Un cont existent pe <i>dexonline</i></label>
    <div class="radioButtonIndent">
      Numele de cont sau adresa de email:
      <input type="text" name="nickOrEmail" value="{$data.email|default:''}" size="20"
             onclick="document.getElementById('claimAccount').checked = true"/>
      Parola:
      <input type="password" name="password" value="{$password|default:''}" size="20"
             onclick="document.getElementById('claimAccount').checked = true"/>
      <br/>

      Dacă aveți un cont pe <i>dexonline,</i> dar v-ați uitat parola,
      <a href="{$wwwRoot}auth/parola-uitata?identity={$randString}{if isset($data.email)}&amp;email={$data.email|escape:'url'}{/if}">click aici</a>.
    </div>

    {if isset($data.fullname)}
      <input type="radio" name="loginType" value="1" id="useFullName" {if $loginType == 1}checked="checked"{/if}/>
      <label for="useFullName">Numele întreg ({$data.fullname})</label><br/>
    {/if}

    {if isset($data.nickname)}
      <input type="radio" name="loginType" value="2" id="useNickname" {if $loginType == 2}checked="checked"{/if}/>
      <label for="useNickname">Porecla OpenID ({$data.nickname})</label><br/>
    {/if}

    <input type="radio" name="loginType" value="3" id="chooseNickname" {if $loginType == 3}checked="checked"{/if}/>
    <label for="chooseNickname">Un nume la alegere:</label>
    <input type="text" name="nick" value="{$chosenNick|default:''}" size="20"
           onclick="document.getElementById('chooseNickname').checked = true"/><br/>

    <input type="hidden" name="randString" value="{$randString}"/>
    <input type=submit id="login" name="submitButton" value="Autentificare"/>  
  </form>
{/block}
