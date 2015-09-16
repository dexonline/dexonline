{extends file="admin/layout.tpl"}

{block name=title}Marcare substantive proprii{/block}

{block name=headerTitle}Marcare substantive proprii{/block}

{block name=content}
  Deocamdată vă sunt oferite lexemele asociate cu definiții din Dicționarul Enciclopedic, cu un tip de model diferit de SP și care
  nu au fost deja bifate ca verificate. Aveți opțiunile:<br/>

  <ul>
    <li>Să indicați un nou model și restricții;</li>
    <li>Să cereți scrierea cu majusculă a lexemului;</li>
    <li>Să adăugați un comentariu;</li>
    <li>Să bifați lexemul ca verificat ca să dispară din listă (dacă îl faceți de tipul SP, el va dispărea oricum).</li>
  </ul>

  <form method="get" action="">
    Filtrează după prefix:
    <input type="text" name="prefix" value="{$prefix}"/>
    <input type="submit" value="Filtrează"/>
  </form>

  <h3>{$lexems|@count} rezultate</h3>

  <form action="" method="post">
    <input type="hidden" name="prefix" value="{$prefix}"/>

    {foreach from=$lexems item=l key=lIter}
      <div class="blLexem">
        <div class="blLexemTitle">
          <span class="name">{$lIter+1}. {$l->form|escape}</span>
          {$l->modelType}{$l->modelNumber}{$l->restriction}
          {strip}
          <a class="noBorder" target="_blank" href="../admin/lexemEdit.php?lexemId={$l->id}">
            <img src={$imgRoot}/icons/pencil.png alt="editează" title="editează lexemul"/>
          </a>
          <a class="noBorder" href="#" onclick="return mlUpdateDefVisibility({$l->id}, 'def_{$l->id}')">
            <img src={$imgRoot}/icons/book_open.png alt="definiții" title="arată definițiile"/>
          </a>
        {/strip}
        </div>
        <div id="def_{$l->id}" class="blDefinitions" style="display:none"></div>
        <div class="blLexemBody">
          Model (tip + număr): <input type="text" name="model_{$l->id}" value="" size="6"/>
          <input type="checkbox" id="singular_{$l->id}" name="singular_{$l->id}" value="1" {if $l->restrS}checked="checked"{/if}/>
          <label for="singular_{$l->id}">Singular</label>
          <input type="checkbox" id="plural_{$l->id}" name="plural_{$l->id}" value="1" {if $l->restrP}checked="checked"{/if}/>
          <label for="plural_{$l->id}">Plural</label>
          <br/>

          <input type="checkbox" id="caps_{$l->id}" name="caps_{$l->id}" value="1"/>
          <label for="caps_{$l->id}">Scrie-l cu majusculă</label><br/>

          <input type="checkbox" id="verifSp_{$l->id}" name="verifSp_{$l->id}" value="1"/>
          <label for="verifSp_{$l->id}">Ignoră</label><br/>

          <input type="checkbox" id="delete_{$l->id}" name="delete_{$l->id}" value="1"/>
          <label for="delete_{$l->id}">Șterge acest lexem</label>
          <input type="checkbox" id="deleteConfirm_{$l->id}" name="deleteConfirm_{$l->id}" value="1"/>
          <label for="deleteConfirm_{$l->id}">Da, confirm ștergerea</label><br/>

          Comentariu: <input type="text" name="comment_{$l->id}" value="{$l->comment}" size="70"/>
        </div>
      </div>
    {/foreach}

    <input type="submit" name="submitButton" value="Salvează"/>
  </form>
{/block}
