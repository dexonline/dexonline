{extends "layout-admin.tpl"}

{block "title"}Plasare accente{/block}

{block "content"}
  <h3>Plasare accente</h3>

  <div class="alert alert-info">
    Dați clic pe litera care trebuie accentuată sau bifați „Nu necesită
    accent” pentru lexemele care nu necesită accent (cuvinte compuse, cuvinte
    latinești etc.). Lexemele sunt alese la întâmplare dintre toate cele
    neaccentuate. Dacă nu știți ce să faceți cu un lexem, săriți-l (nu bifați
    nimic).
  </div>


  <form method="post">
    {foreach $lexemes as $l}
      {assign var=lexemeId value=$l->id}
      {assign var=charArray value=$chars[$lexemeId]}
      {assign var=srArray value=$searchResults[$lexemeId]}

      <div>
        <input type="hidden" name="position_{$l->id}" value="-1">

        <span class="apLexemeForm">
          {foreach $charArray as $cIndex => $char}
            <span class="apLetter" data-order="{$cIndex}">{$char}</span>
          {/foreach}
        </span>

        <span>
          <label>
            <input type="checkbox"
              name="noAccent_{$l->id}" value="X">
            Nu necesită accent
          </label>
        </span>

        <span>
          <a
            class="btn btn-link"
            data-bs-toggle="collapse"
            href="#defs-{$l->id}"
            role="button"
            aria-expanded="false"
            aria-controls="defs-{$l->id}">
            {include "bits/icon.tpl" i=description}
            definiții
          </a>
          <a
            class="btn btn-link"
            href="{Router::link('lexeme/edit')}?lexemeId={$l->id}"
            target="_blank">
            {include "bits/icon.tpl" i=edit}
            editează
          </a>
        </span>
      </div>

      <div class="collapse card-admin" id="defs-{$l->id}">
        {foreach $srArray as $row}
          {include "bits/definition.tpl" showDropup=0 showId=0 showStatus=1 showUser=0}
        {/foreach}
      </div>
    {/foreach}

    <button type="submit" class="btn btn-primary" name="saveButton">
      {include "bits/icon.tpl" i=save}
      <u>s</u>alvează
    </button>
  </form>
{/block}
