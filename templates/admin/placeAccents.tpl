{extends file="admin/layout.tpl"}

{block name=title}Plasare accente{/block}

{block name=headerTitle}Plasare accente{/block}

{block name=content}
  Dați clic pe litera care trebuie accentuată sau bifați „Nu
  necesită accent” pentru lexemele care nu necesită accent (cuvinte
  compuse, cuvinte latinești etc.). Lexemele sunt alese la
  întâmplare dintre toate cele neaccentuate. Dacă nu știți ce să
  faceți cu un lexem, săriți-l (nu bifați nimic).

  <form action="placeAccents.php" method="post">
    {foreach from=$lexems item=l}
      {assign var=lexemId value=$l->id}
      {assign var=charArray value=$chars[$lexemId]}
      {assign var=srArray value=$searchResults[$lexemId]}

      <div>
        <input type="hidden" name="position_{$l->id}" value="-1"/>

        <span class="apLexemForm">
          {foreach from=$charArray item=char key=cIndex}
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
          <a class="apDefLink" href="#" data-other-text="ascunde definițiile">arată definițiile</a>
        </span>
      </div>

      <div class="apDefs">
        {foreach from=$srArray item=row}
          <div>
            {$row->definition->htmlRep}<br/>
            <span class="defDetails">
              Sursa: {$row->source->shortName|escape} |
              Starea: {$row->definition->getStatusName()}
            </span>
          </div>
        {/foreach}
      </div>
    {/foreach}

    <input type="submit" name="submitButton" value="Trimite"/>
  </form>
{/block}
