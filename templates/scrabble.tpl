{extends "layout.tpl"}

{block "title"}Scrabble{/block}

{block "search"}{/block}

{block "content"}
  <div class="panel panel-default">
    <div class="panel-heading">Verificare Scrabble</div>

    <div class="panel-body">
      {assign var="form" value=$form|default:""}
      {assign var="selectedLocVersion" value=$selectedLocVersion|default:null}

      <p>
        Joci Scrabble și prietenii tăi nu cred că <em>kwyjibo</em> este un cuvânt perfect legal? Tastează cuvântul tău ca să le arăți cine e șeful.
      </p>

      <form action="scrabble" method="get">

        <div class="scrabbleSearchDiv">
          {if isset($data)}
            <div class="form-group has-feedback {if count($data)}has-success{else}has-error{/if}">
          {else}
            <div class="form-group">
          {/if}
          <input class="scrabbleSearchField form-control" type="text" name="form" placeholder="kwyjibo" value="{$form|default:""|escape}" autofocus>
            <span id="scrabble-feedback-glyph">
            {if isset($data)}
              {if count($data)}
                <span class="form-control-feedback glyphicon glyphicon-ok"></span>
              {else}
                <span class="form-control-feedback glyphicon glyphicon-remove"></span>
              {/if}
            {/if}
            </span>
          </div>

          <div class="form-inline text-center">
            în versiunea
            <select name="locVersion" class="form-control">
              {foreach $locVersions as $lv}
                <option value="{$lv->name|escape}" {if $lv->name == $selectedLocVersion}selected{/if}>
                  {$lv->name|escape} ({$lv->freezeTimestamp|date_format:"%d %B %Y"|default:"în lucru"})
                </option>
              {/foreach}
            </select>

            <input type="submit" name="submitButton" value="verifică" class="btn btn-primary">
          </div>
        </div>
      </form>
      <br>
      <div id="scrabble-results">{include "scrabble-results.tpl"}</div>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">Lista Oficială de Cuvinte admise la Scrabble</div>

    <div class="panel-body">
      <table class="table table-striped-column-even">
        <thead>
          <tr>
            <th>versiunea</th>
            <th>data publicării</th>
            <th>forme reduse</th>
            <th>forme de bază</th>
            <th>forme flexionare</th>
          </tr>
        </thead>
        <tbody>
          {foreach $locVersions as $lv}
            <tr>
              <td>{$lv->name|escape}</td>
              <td>{$lv->freezeTimestamp|date_format:"%d %B %Y"|default:"în lucru"}</td>
              <td><a href="{$cfg.static.url}download/scrabble/loc-reduse-{$lv->name}.zip">descarcă</a></td>
              <td><a href="{$cfg.static.url}download/scrabble/loc-baza-{$lv->name}.zip">descarcă</a></td>
              <td><a href="{$cfg.static.url}download/scrabble/loc-flexiuni-{$lv->name}.zip">descarcă</a></td>
            </tr>
          {/foreach}
        </tbody>
      </table>

      <dl class="dl-horizontal">
        <dt>forme reduse</dt>
        <dd>lista de cuvinte între 2 și 15 litere, fără diacritice</dd>
        <dt>forme de bază</dt>
        <dd>lista de cuvinte ordonată alfabetic (<a href="https://wiki.dexonline.ro/wiki/Preciz%C4%83ri_privind_LOC" target="_blank">legenda notațiilor</a>)</dd>
        <dt>forme flexionare</dt>
        <dd>lista de cuvinte cu conjugările/declinările lor</dd>
      </dl>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">Diferențe între versiuni</div>

    <div class="panel-body">
      <p>
        Află ce s-a schimbat între două versiuni ale LOC.
      </p>

      <form class="form-inline" action="scrabble-diferente-loc" method="get">
        <div class="form-group">
          Compară
          <select id="formeleDeBaza" class="form-control" name="list">
            <option value="base">formele de bază</option>
            <option value="inflected">formele flexionare</option>
            <option value="reduced">formele reduse</option>
          </select>
        </div>

        <div class="form-group">
        între

        <select class="form-control" name="locVersions">
          {foreach $locVersions as $i => $old}
            {foreach $locVersions as $j => $new}
              {if $i > $j}
                <option value="{$old->name|escape},{$new->name|escape}">
                  versiunea {$old->name|escape} și versiunea {$new->name|escape}
                </option>
              {/if}
            {/foreach}
          {/foreach}
        </select>
        </div>
        <input type="submit" name="submitButton" value="compară" class="btn btn-primary">
      </form>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">Modele de flexionare</div>

    <div class="panel-body">
      <p>
        <em>Stradă, cadă, ladă</em> și <em>ogradă</em> se declină la fel... dar <em>baladă</em> și <em>livadă</em> nu? Iată de ce.
      </p>

      <form class="form-inline" action="modele-flexiune" method="get">
        <div class="form-group">
          <span data-model-dropdown>

            Arată modelele pentru

            <select class="form-control" name="modelType" data-model-type data-verbose="1" data-selected="">
            </select>


            în versiunea

            <select class="form-control" name="locVersion" data-loc-version>
              {foreach $locVersions as $lv}
                <option value="{$lv->name|escape}">
                  {$lv->name|escape}
                </option>
              {/foreach}
            </select>

          </span>
        </div>

        <input class="btn btn-primary" type="submit" name="submitButton" value="arată">
      </form>
    </div>
  </div>
{/block}
