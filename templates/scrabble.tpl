{extends "layout.tpl"}

{block "title"}Scrabble{/block}

{block "search"}{/block}

{block "content"}
  <div class="panel panel-default">
    <div class="panel-heading">Verificare Scrabble</div>

    <div class="panel-body">
      {assign var="form" value=$form|default:""}

      <p>
        Joci Scrabble și prietenii tăi nu cred că <em>kwyjibo</em> este un
        cuvânt perfect legal? Tastează cuvântul tău ca să le arăți cine e
        șeful.
      </p>

      <form action="scrabble" method="get">

        <div class="scrabbleSearchDiv">
          <div class="form-group {$class|default:''}">
            <input
              class="scrabbleSearchField form-control"
              type="text"
              name="form"
              placeholder="kwyjibo"
              value="{$form|default:''|escape}"
              autofocus>
            <span id="scrabble-feedback-glyph">
              {if isset($answer)}
                {if $answer}
                  <span class="form-control-feedback glyphicon glyphicon-ok"></span>
                {else}
                  <span class="form-control-feedback glyphicon glyphicon-remove"></span>
                {/if}
              {/if}
            </span>
          </div>

          <div class="form-inline text-center">
            în versiunea
            <select name="version" class="form-control">
              {foreach $versions as $v}
                <option
                  value="{$v->name}"
                  {if $v->name == $version}selected{/if}>
                  {$v->name|escape}
                </option>
              {/foreach}
            </select>

            <input type="submit" value="verifică" class="btn btn-primary">
          </div>
        </div>
      </form>
      <br>
      <div id="scrabble-results">{include "bits/scrabbleResults.tpl"}</div>
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
          {foreach $versions as $v}
            <tr>
              <td>{$v->name|escape}</td>
              <td>{$v->freezeTimestamp|date_format:"%d %B %Y"}</td>
              <td><a href="{$v->getReducedFormUrl()}">descarcă</a></td>
              <td><a href="{$v->getBaseFormUrl()}">descarcă</a></td>
              <td><a href="{$v->getInflectedFormUrl()}">descarcă</a></td>
            </tr>
          {/foreach}
        </tbody>
      </table>

      <dl class="dl-horizontal">
        <dt>forme reduse</dt>
        <dd>lista de cuvinte între 2 și 15 litere, fără diacritice</dd>
        <dt>forme de bază</dt>
        <dd>
          lista de cuvinte ordonată alfabetic
          (<a href="https://wiki.dexonline.ro/wiki/Preciz%C4%83ri_privind_LOC"
             target="_blank">legenda notațiilor</a>)
        </dd>
        <dt>forme flexionare</dt>
        <dd>lista de cuvinte cu conjugările/declinările lor</dd>
      </dl>

      <div class="alert alert-warning">
        Ultima versiune oficială a LOC este 5.0. Versiunea 6.0 conține
        modificări aduse LOC în dexonline, dar neîncorporate în versiunea
        oficială.
      </div>
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
          <select class="form-control" name="list">
            <option value="base">formele de bază</option>
            <option value="inflected">formele flexionare</option>
            <option value="reduced">formele reduse</option>
          </select>
        </div>

        <div class="form-group">
          între

          <select class="form-control" name="versions">
            {foreach $versions as $i => $old}
              {foreach $versions as $j => $new}
                {if $i > $j}
                  <option value="{$old->name|escape},{$new->name|escape}">
                    versiunea {$old->name|escape} și versiunea {$new->name|escape}
                  </option>
                {/if}
              {/foreach}
            {/foreach}
          </select>
        </div>
        <input type="submit" value="compară" class="btn btn-primary">
      </form>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">Modele de flexionare</div>

    <div class="panel-body">
      <p>
        <em>Stradă, cadă, ladă</em> și <em>ogradă</em> se declină la fel...
        dar <em>baladă</em> și <em>livadă</em> nu? Iată de ce.
      </p>

      <form class="form-inline" action="modele-flexiune">
        Arată modelele pentru

        <select class="form-control" name="modelType">
          {foreach $canonicalModelTypes as $mt}
            <option value="{$mt->code}">{$mt->code} ({$mt->description})</option>
          {/foreach}
        </select>

        <input class="btn btn-primary" type="submit" value="arată">
      </form>
    </div>
  </div>
{/block}
