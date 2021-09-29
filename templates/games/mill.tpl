{extends "layout.tpl"}

{block "title"}{cap}{t}word mill{/t}{/cap}{/block}

{block "search"}{/block}

{block "content"}
  <div class="card millArea mb-3">
    <div class="card-header">
      {cap}{t}word mill{/t}{/cap}
    </div>
    <div class="card-body">
      <div id="mainPage">
        <form id="main" action="">
          <label>{t}Level{/t}:</label>
          <button class="btn btn-info" type="button" value="0">{t}easy{/t}</button>
          <button class="btn btn-info" type="button" value="1">{t}medium{/t}</button>
          <button class="btn btn-info" type="button" value="2">{t}hard{/t}</button>
          <button class="btn btn-info" type="button" value="3">{t}expert{/t}</button>
        </form>

        <p class="text-info">
          {t}You can also use the keys 1, 2, 3, 4.{/t}
        </p>

        <div class="alert alert-info">
          {t}We are currently recalibrating the difficulty levels.
          Please excuse any words that are too easy or too hard for the level
          you selected.{/t}
        </div>
      </div>

      <div id="questionPage">
        {section name=round start=0 loop=10}
          {$i=$smarty.section.round.index}
          <div class="questionImage">
            <div>
              <img
                id="statusImage{$i}"
                src="img/mill/pending.png"
                alt="imagine pentru runda {$i+1}">
            </div>
            <div class="questionNumber">{$i+1}</div>
          </div>
        {/section}

        <form id="mill" action="">
          <div class="my-2">
            {t}The correct definition of <span class="word fs-3"></span>:{/t}
          </div>

          {section name=choices start=0 loop=4}
            <button
              class="btn btn-outline-secondary btn-lg border-secondary text-start w-100 mb-2"
              type="button"
              disabled
              value="{$smarty.section.choices.index}">
              <strong>
                {$smarty.section.choices.index+1}.
              </strong>
              <span class="def"></span>
            </button>
          {/section}
        </form>
      </div>

      <div id="resultsPage">
        <div class="alert alert-success">
          {t}Congratulations! You answered <b id="answeredCorrect">0</b> of 10
          questions correctly.{/t}
        </div>
        <a href="{Router::link('games/mill')}" class="btn btn-info">{t}new game{/t}</a>
        <button id="definitionsButton" class="btn btn-info">{t}see all definitions{/t}</button>
      </div>
    </div>
  </div>

  <div id="defCard" class="card millArea">
    <div class="card-header">
      {cap}{t}definitions{/t}{/cap}
    </div>
    <div id="definitionsSection" class="card-body">
    </div>
  </div>
{/block}
