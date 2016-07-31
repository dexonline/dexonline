{extends file="layout.tpl"}

{block name=title}Parolă uitată{/block}

{block name=content}
  <h3>Parolă uitată</h3>

  Pentru a vă recupera parola, introduceți adresa de e-mail asociată cu contul <i>dexonline</i>. Vă vom trimite un e-mail cu instrucțiuni pentru recuperarea
  parolei.<br/><br/>

  <form method="post" action="{$wwwRoot}auth/parola-uitata">
    Adresa de e-mail:
    <input type="text" name="email" value="{$email}" size="30"/>
    <input type="hidden" name="identity" value="{$identity}"/>
    <input type=submit id="login" name="submitButton" value="Trimite"/>  
  </form>
{/block}
