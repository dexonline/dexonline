{extends "layout-admin.tpl"}

{block "title"}Definiție din DE{/block}

{block "content"}
  <h3>Definiție din Dicționarul enciclopedic: {$def->lexicon} ({$def->id})</h3>

  <form method="post">
    <div class="row mb-3">
      <label class="col-sm-2 col-form-label">sari la prefixul</label>
      <div class="col-sm-3">
        <input type="text" class="form-control" name="jumpPrefix">
      </div>
    </div>
  </form>

  <div class="card mb-3">
    <div class="card-body">
      {HtmlConverter::convert($def)}
      <a class="ms-2" href="{Router::link('definition/edit')}/{$def->id}">
        {include "bits/icon.tpl" i=edit}
        editează
      </a>
    </div>
  </div>

  <form method="post">
    <input type="hidden" name="definitionId" value="{$def->id}">

    <table class="table" id="lexemesTable">
      <thead>
        <tr>
          <th>lexem</th>
          <th>model</th>
          <th>scurtături</th>
        </tr>
      </thead>
      <tbody>
        <tr id="stem">
          <td>
            <select class="lexeme" name="lexemeId[]" style="width: 300px;">
            </select>
          </td>
          <td>
            <select class="model" name="model[]" style="width: 500px;">
              <option value="I3" selected>I3 (nume proprii)</option>
            </select>
          </td>
          <td>
            <a class="shortcutI3" href="#">I3</a>
          </td>
        </tr>
        {foreach $lexemeIds as $i => $l}
          <tr>
            <td>
              <select class="lexeme" name="lexemeId[]" style="width: 300px;">
                <option value="{$l}" selected></option>
              </select>
            </td>
            <td>
              <select class="model" name="model[]" style="width: 500px;">
                <option value="{$models[$i]}" selected></option>
              </select>
            </td>
            <td>
              <a class="shortcutI3" href="#">I3</a>
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>

    <div class="mb-4">
      <a id="addRow" class="btn btn-light btn-sm" href="#">
        {include "bits/icon.tpl" i=add}
        adaugă o linie
      </a>
    </div>

    {include "bs/checkbox.tpl"
      name=capitalize
      label='scrie cu majusculă lexemele I3 și SP*'
      checked=$capitalize}

    {include "bs/checkbox.tpl"
      name=deleteOrphans
      label='șterge lexemele și intrările care devin neasociate'
      checked=$deleteOrphans}

    <div class="mt-2 mb-4">
      <button type="submit" class="btn btn-light" name="butPrev">
        {include "bits/icon.tpl" i=chevron_left}
        anterioara
      </button>

      <button id="refreshButton"
        type="submit"
        name="refreshButton"
        class="btn {if $passedTests}btn-light{else}btn-primary{/if}">
        {include "bits/icon.tpl" i=refresh}
        <u>r</u>eafișează
      </button>

      <button type="submit"
        name="saveButton"
        class="btn {if $passedTests}btn-primary{else}btn-light{/if}"
        {if !$passedTests}disabled{/if}>
        {include "bits/icon.tpl" i=save}
        <u>s</u>alvează
      </button>

      <button type="submit" class="btn btn-light" name="butNext">
        {include "bits/icon.tpl" i=chevron_right}
        următoarea
      </button>

    </div>
  </form>

  <div class="alert alert-info alert-dismissible fade show">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>

    <p><strong>Note:</strong></p>

    <ul class="mb-0">
      <li>
        Legăturile de pe coloana „scurtături” sunt echivalente cu selectarea
        modelului respectiv. Sunt doar scurtături mai comode.
      </li>
      <li>
        Din această pagină nu puteți adăuga restricții la modelele de flexiune.
      </li>
      <li>
        Transcrierea cu majusculă nu apare la testare, numai la salvare.
      </li>
    </ul>
  </div>

{/block}
