{extends "layout.tpl"}

{block "title"}Moara cuvintelor{/block}

{block "search"}{/block}

{block "content"}
  <div class="panel panel-default millArea">
    <div class="panel-heading">
      <h3 class="panel-title">Moara cuvintelor</h3>
    </div>
    <div class="panel-body">
      <div id="mainPage">
        <form id="main" action="">
          <label>Nivel:</label>
          <button class="btn btn-info" type="button" value="1">Ușor</button>
          <button class="btn btn-info" type="button" value="2">Mediu</button>
          <button class="btn btn-info" type="button" value="3">Greu</button>
          <button class="btn btn-info" type="button" value="4">Foarte greu</button>
        </form>

        <p class="text-info">
          Se poate juca și cu tastatura folosind tastele: 1, 2, 3, 4.
        </p>
      </div>

      <div id="questionPage">
        {section name=round start=1 loop=11} {* Yes, Smarty, 11 means 10 *}
          <span class="questionImage">
            <img id="statusImage{$smarty.section.round.index}"
                 src="{$imgRoot}/mill/pending.png"
                 alt="imagine pentru runda {$smarty.section.round.index}"/>
            <span class="questionNumber">{$smarty.section.round.index}</span>
          </span>
        {/section}

        <form id="mill" action="">
          <label>Definiția corectă pentru <span class="word"></span>:</label>
          {section name=choices start=1 loop=5}
            <button class="btn btn-default btn-block btn-lg"
                    type="button"
                    value="{$smarty.section.choices.index}">
              <strong>
                {$smarty.section.choices.index}.
              </strong>
              <span class="def"></span>
            </button>
          {/section}
        </form>
      </div>

      <div id="resultsPage">
        <div class="alert alert-success">
          Felicitări! Ai răspuns corect la <b id="answeredCorrect">0</b> definiții din 10.
        </div>
        <a href="moara" class="btn btn-info">Joc nou</a>
        <button id="definitionsButton" class="btn btn-info">Vezi toate definițiile</button>
      </div>
    </div>
  </div>

  <div id="defPanel" class="panel panel-default millArea">
    <div class="panel-heading">
      <h3 class="panel-title">Definiții</h3>
    </div>
    <div id="definitionsSection" class="panel-body">
    </div>
  </div>
{/block}
