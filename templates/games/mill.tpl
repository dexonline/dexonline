{extends "layout.tpl"}

{block "title"}{cap}{t}word mill{/t}{/cap}{/block}

{block "search"}{/block}

{block "content"}
  <div class="panel panel-default millArea voffset3">
    <div class="panel-heading">
      <h3 class="panel-title">{cap}{t}word mill{/t}{/cap}</h3>
    </div>
    <div class="panel-body">
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
          <label>{t}The correct definition of <span class="word"></span>:{/t}</label>
          {section name=choices start=0 loop=4}
            <button
              class="btn btn-default btn-block btn-lg"
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

  <div id="defPanel" class="panel panel-default millArea">
    <div class="panel-heading">
      <h3 class="panel-title">{cap}{t}definitions{/t}{/cap}</h3>
    </div>
    <div id="definitionsSection" class="panel-body">
    </div>
  </div>
{/block}
