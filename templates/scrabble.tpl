{extends file="layout.tpl"}

{block name=title}Scrabble{/block}

{block name=content}
  <div class="scrabbleSection">
    <div class="title">Verificare Scrabble</div>

    <div class="body">
      {assign var="form" value=$form|default:""}
      {assign var="selectedLocVersion" value=$selectedLocVersion|default:null}

      Joci Scrabble și prietenii tăi nu cred că <i>kwyjibo</i> este un cuvânt perfect legal? Tastează cuvântul tău ca să le arăți cine e șeful.<br><br>

      <form action="scrabble" method="get">

        <div class="scrabbleSearchDiv">
          {if isset($data)}
            <div class="scrabbleVerif {if count($data)}scrabbleVerifYes{else}scrabbleVerifNo{/if}">&nbsp;</div>
          {/if}

          <input class="scrabbleSearchField" type="text" name="form" placeholder="kwyjibo" value="{$form|default:""|escape}" autofocus><br>

          în versiunea
          <select name="locVersion">
            {foreach from=$locVersions item=lv}
              <option value="{$lv->name|escape}" {if $lv->name == $selectedLocVersion}selected="selected"{/if}>
                {$lv->name|escape} ({$lv->freezeTimestamp|date_format:"%d %B %Y"|default:"în lucru"})
              </option>
            {/foreach}
          </select>

          <input type="submit" name="submitButton" value="verifică">
        </div>
      </form>

      {if isset($data)}
        <ul>
          {if !count($data)}
            <li>Niciun cuvânt din LOC {$selectedLocVersion|escape} nu generează forma
              <b>{$form|escape}.</b></li>
          {else}
              {foreach from=$data item=r}
                <li>
                  <b>{$r.inflectedForm|escape}</b> provine din
                  <a href="{$wwwRoot}definitie/{$r.lexemFormNoAccent|escape}">{$r.lexemForm|escape}</a>
                  {$r.modelType}{$r.modelNumber}{$r.restriction}
                  ({$r.inflection|escape})
                </li>
              {/foreach}
          {/if}
        </ul>
      {/if}
    </div>
  </div>

  <div class="scrabbleSection">
    <div class="title">Lista Oficială de Cuvinte admise la Scrabble</div>

    <div class="body">
      <table class="minimalistTable">
        <tr>
          <th>versiunea</th>
          <th>data publicării</th>
          <th>forme reduse</th>
          <th>forme de bază</th>
          <th>forme flexionare</th>
        </tr>
        {foreach from=$locVersions item=lv}
          <tr>
            <td>{$lv->name|escape}</td>
            <td>{$lv->freezeTimestamp|date_format:"%d %B %Y"|default:"în lucru"}</td>
            <td><a href="{$cfg.static.url}download/scrabble/loc-reduse-{$lv->name}.zip">descarcă</a></td>
            <td><a href="{$cfg.static.url}download/scrabble/loc-baza-{$lv->name}.zip">descarcă</a></td>
            <td><a href="{$cfg.static.url}download/scrabble/loc-flexiuni-{$lv->name}.zip">descarcă</a></td>
          </tr>
        {/foreach}
      </table>

      <ul>
        <li><i>forme reduse</i> = lista de cuvinte între 2 și 15 litere, fără diacritice</li>
        <li><i>forme de bază</i> = lista de cuvinte ordonată alfabetic (<a href="http://wiki.dexonline.ro/wiki/Preciz%C4%83ri_privind_LOC" target="_blank">legenda notațiilor</a>)</li>
        <li><i>forme flexionare</i> = lista de cuvinte cu conjugările/declinările lor</li>
      </ul>
    </div>
  </div>
  
  <div class="scrabbleSection">
    <div class="title">Diferențe între versiuni</div>

    <div class="body">
      Află ce s-a schimbat între două versiuni ale LOC.<br><br>

      <form action="scrabble-diferente-loc" method="get">
        Compară

        <select name="list">
          <option value="base">formele de bază</option>
          <option value="inflected">formele flexionare</option>
          <option value="reduced">formele reduse</option>
        </select>

        între

        <select name="locVersions">
          {foreach from=$locVersions item=old key=i}
            {foreach from=$locVersions item=new key=j}
              {if $i > $j}
                <option value="{$old->name|escape},{$new->name|escape}">
                  versiunea {$old->name|escape} și versiunea {$new->name|escape}
                </option>
              {/if}
            {/foreach}
          {/foreach}
        </select>
        <input type="submit" name="submitButton" value="compară">
      </form>
    </div>
  </div>

  <div class="scrabbleSection">
    <div class="title">Modele de flexionare</div>

    <div class="body">
      <i>Stradă, cadă, ladă</i> și <i>ogradă</i> se declină la fel... dar <i>baladă</i> și <i>livadă</i> nu? Iată de ce.<br><br>

      <form action="modele-flexiune" method="get">
        <span data-model-dropdown>
          Arată modelele pentru

          <select name="modelType" data-model-type data-canonical="1" data-verbose="1" data-selected="">
          </select>

          în versiunea

          <select name="locVersion" data-loc-version>
            {foreach from=$locVersions item=lv}
              <option value="{$lv->name|escape}">
                {$lv->name|escape}
              </option>
            {/foreach}
          </select>

          {*
             <select name="modelNumber" data-model-number data-all-option="1" data-selected="">
             </select>
           *}
        </span>

        <input type="submit" name="submitButton" value="arată"
               onclick="return hideSubmitButton(this)"/>
      </form>
    </div>
  </div>
{/block}
