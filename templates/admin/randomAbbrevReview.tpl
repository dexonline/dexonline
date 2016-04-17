{extends file="admin/layout.tpl"}

{block name=title}Examinare abrevieri{/block}

{block name=headerTitle}Examinare abrevieri{/block}

{block name=content}
  {assign var=ambiguities value=$ambiguities|default:null}
  <span id="numAmbiguities" style="display: none">{$ambiguities|@count}</span>

  {if $def}
    <form action="" method="post">
      <input type="hidden" name="definitionId" value="{$def->id}"/>
      {foreach from=$text item=chunk key=i name=iter}
        <span>
          {$chunk}
          {if !$smarty.foreach.iter.last}
            <br/>
            <input type="radio" id="radio_{$i}_a" name="radio_{$i}" value="abbrev" style="display: none"/>
            <label for="radio_{$i}_a" data-order="{$i}" data-answer="0">
              <img src="{$imgRoot}/icons/zoom_out.png" alt="abreviere" title="abreviere"/>
            </label>
            <span id="abrevText_{$i}" data-clicked="">{$ambiguities[$i]}</span>
            <input type="radio" id="radio_{$i}_w" name="radio_{$i}" value="word" style="display: none"/>
            <label for="radio_{$i}_w" data-order="{$i}" data-answer="1">
              <img src="{$imgRoot}/icons/zoom_in.png" alt="cuvânt" title="cuvânt"/>
            </label>
          {/if}
        </span>
      {/foreach}
      <br/>
      <input type="submit" name="submitButton" id="submitButton" value="OK" style="margin-top: 5px;" {if count($ambiguities)}disabled="disabled"{/if}/>
      &nbsp; <a href="definitionEdit.php?definitionId={$def->id}">Editează</a>
    </form>
  {else}
    Nu există definiții de revizuit deocamdată. Ura!<br/>
  {/if}

  <br/>
  <b>Precizare:</b>
  <i>dexonline</i> are un sistem care detectează automat abrevierile. Efectele sunt bune pe termen lung: este bine ca bucățile din text care sunt abrevieri, nu cuvinte propriu-zise, să fie delimitate ca atare.
  Problema cu acest sistem este că unele bucăți din text sunt ambigue; „lat.” poate însemna „limba latină”, dar poate fi și cuvântul „lat” la sfârșitul unei propoziții. În aceeași situație se află „gen.” (genitiv),
  „dat.” (dativ) și altele. Aceste cazuri au nevoie de decizia unui operator uman. Vi se prezintă o definiție la întâmplare, dintre cele cu ambiguități. Pentru fiecare ambiguitate, indicați dacă este o
  abreviere (<img src="{$imgRoot}/icons/zoom_out.png" alt="abreviere" title="abreviere"/>)
  sau un cuvânt propriu-zis (<img src="{$imgRoot}/icons/zoom_in.png" alt="cuvânt" title="cuvânt"/>).
  Trebuie să rezolvați toate ambiguitățile înainte de a putea salva definiția. Sistemul avansează automat la o altă definiție aleatoare.
{/block}
