{extends "layout-admin.tpl"}

{block "title"}Definiție din DE{/block}

{block "content"}
  <h3>Definiție din Dicționarul enciclopedic: {$def->lexicon} ({$def->id})</h3>

  <form class="form-horizontal" method="post">
    <div class="form-group">
      <label class="col-sm-2 control-label">sari la prefixul</label>
      <div class="col-sm-3">
        <input type="text" class="form-control" name="jumpPrefix">
      </div>
    </div>
  </form>

  <div class="panel panel-default">
    <div class="panel-body">
      {$def->htmlRep}
      <a href="definitionEdit?definitionId={$def->id}">
        <i class="glyphicon glyphicon-pencil"></i>
        editează
      </a>
    </div>
  </div>

  <form method="post">
    <input type="hidden" name="definitionId" value="{$def->id}">

    <table id="lexemsTable">
      <tr>
        <th>lexem</th>
        <th>model</th>
        <th>scurtături</th>
      </tr>
      <tr id="stem">
        <td>
          <select class="lexem" name="lexemId[]" style="width: 300px;">
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
      {foreach $lexemIds as $i => $l}
        <tr>
          <td>
            <select class="lexem" name="lexemId[]" style="width: 300px;">
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
    </table>
    <a id="addRow" href="#">adaugă o linie</a>
    <br><br>

    <div class="checkbox">
      <label>
        <input id="capitalize" type="checkbox" name="capitalize" value="1"
               {if $capitalize}checked{/if}>
        scrie cu majusculă lexemele I3 și SP*
      </label>
    </div>
    <div class="checkbox">
      <label>
        <input id="deleteOrphans" type="checkbox" name="deleteOrphans" value="1"
               {if $deleteOrphans}checked{/if}>
        șterge lexemele și intrările care devin neasociate
      </label>
    </div>

    <div class="form-group">
      <button type="submit" class="btn btn-default" name="butPrev">
        <i class="glyphicon glyphicon-chevron-left"></i>
        anterioara
      </button>

      <button id="refreshButton"
              type="submit"
              name="refreshButton"
              class="btn btn-primary">
        <i class="glyphicon glyphicon-refresh"></i>
        <u>r</u>eafișează
      </button>

      <button type="submit"
              name="saveButton"
              class="btn btn-success"
              {if !$passedTests}disabled{/if}>
        <i class="glyphicon glyphicon-floppy-disk"></i>
        <u>s</u>alvează
      </button>

      <button type="submit" class="btn btn-default" name="butNext">
        <i class="glyphicon glyphicon-chevron-right"></i>
        următoarea
      </button>

    </div>
  </form>

  <div class="alert alert-info alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">
      <span>&times;</span>
    </button>

    <p><strong>Note:</strong></p>

    <ul>
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
