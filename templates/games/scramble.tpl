{extends "layout.tpl"}

{block "title"}
  {cap}{t}word scramble{/t}{/cap}
{/block}

{block "search"}{/block}

{block "content"}
  <div id="mainMenu" class="card">
    <div class="card-header">
      {cap}{t}word scramble{/t}{/cap}
    </div>

    <div class="card-body">
      <form>

        <div class="mb-3">
          <button id="startGameButton" class="btn btn-success" type="button" disabled>
            {include "bits/icon.tpl" i=play_arrow}
            {t}start{/t}
          </button>

          <a
            class="btn btn-light"
            data-bs-toggle="collapse"
            href="#optionsDiv"
            role="button"
            aria-expanded="false"
            aria-controls="optionsDiv">
            {include "bits/icon.tpl" i=settings}
            {t}options{/t}
          </a>
        </div>

        <div id="optionsDiv" class="collapse mb-3">

          <div class="row mb-3">
            <label class="col-md-2 col-form-label">{t}object{/t}</label>
            <div class="col-md-10">

              <div class="btn-group" role="group">
                <input id="radioMode0" type="radio" class="btn-check" name="mode" value="0" checked>
                <label for="radioMode0" class="btn btn-outline-secondary">
                  {t}all words{/t}
                </label>

                <input id="radioMode1" type="radio" class="btn-check" name="mode" value="1">
                <label for="radioMode1" class="btn btn-outline-secondary">
                  {t}one anagram{/t}
                </label>

                <input id="radioMode2" type="radio" class="btn-check" name="mode" value="2">
                <label for="radioMode2" class="btn btn-outline-secondary">
                  {t}all anagrams{/t}
                </label>
              </div>

            </div>
          </div>

          <div class="row mb-3">
            <label class="col-md-2 col-form-label">{t}level{/t}</label>
            <div class="col-md-10">

              <div class="btn-group" role="group">
                <input id="radioLevel0" type="radio" class="btn-check" name="level" value="0">
                <label for="radioLevel0" class="btn btn-outline-secondary">
                  4 {t}letters{/t}
                </label>

                <input id="radioLevel1" type="radio" class="btn-check" name="level" value="1" checked>
                <label for="radioLevel1" class="btn btn-outline-secondary">
                  5 {t}letters{/t}
                </label>

                <input id="radioLevel2" type="radio" class="btn-check" name="level" value="2">
                <label for="radioLevel2" class="btn btn-outline-secondary">
                  6 {t}letters{/t}
                </label>

                <input id="radioLevel3" type="radio" class="btn-check" name="level" value="3">
                <label for="radioLevel3" class="btn btn-outline-secondary">
                  7 {t}letters{/t}
                </label>
              </div>

            </div>
          </div>

          <div class="row mb-3">
            <label class="col-md-2 col-form-label">{t}diacritics{/t}</label>
            <div class="col-md-10">

              <div class="btn-group" role="group">
                <input id="radioDia0" type="radio" class="btn-check" name="useDiacritics" value="0" checked>
                <label for="radioDia0" class="btn btn-outline-secondary">
                  {t}no{/t}
                </label>

                <input id="radioDia1" type="radio" class="btn-check" name="useDiacritics" value="1">
                <label for="radioDia1" class="btn btn-outline-secondary">
                  {t}yes{/t}
                </label>
              </div>

            </div>
          </div>

          <div class="row mb-3">
            <label class="col-md-2 col-form-label">{t}time{/t}</label>
            <div class="col-md-10">

              <div class="btn-group" role="group">
                {for $time=1 to 5}
                  <input
                    id="radioTime{$time-1}"
                    type="radio"
                    class="btn-check"
                    name="seconds"
                    value="{$time-1}"
                    {if $time == 3}checked{/if}>
                  <label for="radioTime{$time-1}" class="btn btn-outline-secondary">
                    {t count=$time 1=$time plural="%1 minutes"}one minute{/t}
                  </label>
                {/for}
              </div>

            </div>
          </div>
        </div>

        <h3>{t}Object of the game{/t}</h3>
        <p>
          {t}Find as many words as you can using the given letters, before the
          time runs out.{/t}
        </p>

        <p>
          {t}There are three game types:{/t}
        </p>

        <ol>
          <li>
            <b>{cap}{t}all words{/t}{/cap}:</b>
            {t}find all words having at least three letters.{/t}
          </li>
          <li>
            <b>{cap}{t}one anagram{/t}{/cap}:</b>
            {t}find a word using all the letters, then receive a new set of letters.{/t}
          </li>
          <li>
            <b>{cap}{t}all anagrams{/t}{/cap}:</b>

            {t 1="tanti, titan, țintă"}find all words using all the letters,
            then receive a new set of letters. For example, for the set AINTT
            you must guess all three anagrams (<i>%1</i>) to receive the next
            set.{/t}
          </li>
        </ol>

        <h3>{t}Instructions{/t}</h3>
        <p>
          {t}When using a keyboard, simply type the letters. When using a
          mouse, click on the letters, then click on the magnifier to check a
          word.{/t}
        </p>

        <p>
          {t 1="ĂÂÎȘȚ" 2="AIST"}Legal words include conjugations (such as <i>lucrez</i>)
          and declensions (such as <i>verzi</i>). If you opted for diacritics,
          then the letters %1 cannot be interchanged with %2.{/t}
        </p>

        <p>
          {t}Special keys{/t}:
        </p>

        <ul>
          <li><i>Enter</i> {t}checks the word{/t};</li>
          <li><i>Backspace</i> {t}deletes the last letter{/t};</li>
          <li><i>Escape</i> {t}deletes all the letters{/t};</li>
          <li><i>slash (/)</i> {t}shuffles the letters{/t}.</li>
        </ul>

        <p>
          {t}Below the game area there is{/t}:
        </p>

        <ul>
          <li>
            {include "bits/icon.tpl" i=hourglass_empty}
            {t}time remaining{/t};
          </li>
          <li>
            {include "bits/icon.tpl" i=visibility}
            {t}number of words found / total number of words{/t};
          </li>
          <li>
            {include "bits/icon.tpl" i=savings}
            {t}score (longer words earn more points){/t}.
          </li>
        </ul>

      </form>
    </div>
  </div>

  <div id="gamePanel" class="card mb-3">
    <div class="card-header">
      {cap}{t}word scramble{/t}{/cap}
    </div>

    <div class="card-body">
      <div id="canvasWrap">
      </div>

      <div id="gameStats" class="d-flex justify-content-between">
        <div>
          {include "bits/icon.tpl" i=hourglass_empty}
          <span id="timer"></span>
        </div>

        <span id="wordCountDiv">
          {include "bits/icon.tpl" i=visibility}
          <span id="foundWords"></span> /
          <span id="maxWords"></span>
        </span>

        <div>
          {include "bits/icon.tpl" i=savings}
          <span id="score"></span>
        </div>
      </div>

      <div class="text-center mt-2">
        <button id="restartGameButton" class="btn btn-success" type="button">
          {include "bits/icon.tpl" i=repeat}
          {t}play again{/t}
        </button>
      </div>
    </div>
  </div>

  <div id="wordListPanel" class="card mb-3">
    <div class="card-header">
      {t}Legal words{/t}
    </div>

    <div class="card-body">
      <div id="legalWords" class="row">
        <div id="wordStem" class="col-6 col-sm-3 col-md-2">
          <a href="definitie/" target="_blank" class="text-danger">
            {include "bits/icon.tpl" i=clear}
            <span></span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div id="gameIdPanel" class="card mb-3">
    <div class="card-body">
      Permalink:
      <a id="permalink" href="#">
        {include "bits/icon.tpl" i=link}
        <span></span>
      </a>

      <p class="form-text">
        {t}You can share this link with your friends to compare scores on the
        same letter sets.{/t}
      </p>
    </div>
  </div>
{/block}
