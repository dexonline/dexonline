{extends "layout.tpl"}

{block "title"}{'Word mill'|_}{/block}

{block "search"}{/block}

{block "content"}
  <div class="panel panel-default millArea">
    <div class="panel-heading">
      <h3 class="panel-title">{'Word mill'|_}</h3>
    </div>
    <div class="panel-body">
      <div id="mainPage">
        <form id="main" action="">
          <label>{'Level'|_}:</label>
          <button class="btn btn-info" type="button" value="1">{'easy'|_}</button>
          <button class="btn btn-info" type="button" value="2">{'medium'|_}</button>
          <button class="btn btn-info" type="button" value="3">{'hard'|_}</button>
          <button class="btn btn-info" type="button" value="4">{'expert'|_}</button>
        </form>

        <p class="text-info">
          {'You can also use the keys 1, 2, 3, 4.'|_}
        </p>
      </div>

      <div id="questionPage">
        {section name=round start=1 loop=11} {* Yes, Smarty, 11 means 10 *}
          <span class="questionImage">
            <img id="statusImage{$smarty.section.round.index}"
                 src="{$imgRoot}/mill/pending.png"
                 alt="imagine pentru runda {$smarty.section.round.index}">
            <span class="questionNumber">{$smarty.section.round.index}</span>
          </span>
        {/section}

        <form id="mill" action="">
          <label>{'The correct definition of <span class="word"></span>:'|_}</label>
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
          {'Congratulations! You answered <b id="answeredCorrect">0</b> of 10
          questions correctly.'|_}
        </div>
        <a href="moara" class="btn btn-info">{'new game'|_}</a>
        <button id="definitionsButton" class="btn btn-info">{'see all definitions'|_}</button>
      </div>
    </div>
  </div>

  <div id="defPanel" class="panel panel-default millArea">
    <div class="panel-heading">
      <h3 class="panel-title">{'Definitions'|_}</h3>
    </div>
    <div id="definitionsSection" class="panel-body">
    </div>
  </div>
{/block}
