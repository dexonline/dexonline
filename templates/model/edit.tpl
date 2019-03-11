{extends "layout-admin.tpl"}

{block "title"}Editare model{/block}

{block "content"}
  {assign var="adjModels" value=$adjModels|default:null}

  <h3>Editare model {$m->modelType}{$m->number}</h3>

  <form id="modelForm" method="post">
    <input type="hidden" name="id" value="{$m->id}">

    <div class="panel panel-default">
      <div class="panel-heading">
        Proprietăți
      </div>

      <div class="panel-body form-horizontal">
        <div class="form-group">
          <label class="col-sm-3 control-label">număr de model</label>
          <div class="col-sm-9">
            <input class="form-control" type="text" name="number" value="{$m->number|escape}">
            <small class="text-muted">poate conține orice caractere</small>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-3 control-label">descriere</label>
          <div class="col-sm-9">
            <input class="form-control" type="text" name="description" value="{$m->description|escape}">
          </div>
        </div>

        {if $adjModels}
          <div class="form-group">
            <label class="col-sm-3 control-label">model de participiu</label>
            <div class="col-sm-9">
              <select class="form-control" name="participleNumber">
                {foreach $adjModels as $am}
                  <option value="{$am->number}"
                    {if $pm && $pm->adjectiveModel == $am->number}selected{/if}
                  >{$am->number} ({$am->exponent})
                  </option>
                {/foreach}
              </select>
            </div>
          </div>
        {/if}

        <div class="form-group">
          <label class="col-sm-3 control-label">exponent</label>
          <div class="col-sm-9">
            <input class="form-control" type="text" name="exponent" value="{$m->exponent|escape}">
          </div>
        </div>

      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">
        Forme
      </div>

      <table class="table table-striped table-condensed">
        <tr>
          <th class="row">
            <div class="col-xs-5">flexiune</div>
            <div class="col-xs-1"></div>
            <div class="col-xs-6 row">
              <div class="col-xs-8">forme</div>
              <div class="col-xs-2">recom</div>
              <div class="col-xs-2">apocopă</div>
            </div>
          </th>
        </tr>

        {foreach $forms as $inflId => $f}
          <tr>
            <td class="row">
              <div class="col-xs-5">
                {$inflectionMap[$inflId]->description|escape}
              </div>
              <div class="col-xs-1 addFormLink" data-infl-id="{$inflId}">
                <a href="#">
                  <i class="glyphicon glyphicon-plus"></i>
                </a>
              </div>
              <div class="col-xs-6 row">
                {foreach $f as $i => $tuple}
                  <div class="fieldWrapper">
                    <div class="col-xs-8">
                      <input class="form-control input-sm"
                        type="text"
                        name="forms_{$inflId}_{$i}"
                        value="{$tuple.form|escape}">
                    </div>
                    <div class="col-xs-2">
                      <input class="checkbox"
                        type="checkbox"
                        name="recommended_{$inflId}_{$i}"
                        value="1"
                        {if $tuple.recommended}checked{/if}>
                    </div>
                    <div class="col-xs-2">
                      <input class="checkbox"
                        type="checkbox"
                        name="hasApocope_{$inflId}_{$i}"
                        value="1"
                        {if $tuple.hasApocope}checked{/if}>
                    </div>
                  </div>
                {/foreach}
              </div>
            </td>
          </tr>
        {/foreach}
      </table>
    </div>

    <div>
      <button class="btn btn-success" type="submit" name="saveButton">
        <i class="glyphicon glyphicon-floppy-disk"></i>
        <u>s</u>alvează
      </button>

      <a href="?id={$m->id}" class="btn btn-link">
        renunță
      </a>
    </div>

    <div class="alert alert-warning voffset3">
      Lexemele nu mai sunt salvate imediat, ci vor apărea în
      <a class="alert-link" href="{Router::link('report/staleParadigms')}">
        raportul de paradigme învechite</a>.
      Dacă în model există erori care fac imposibilă regenerarea paradigmei,
      veți primi acele erori cînd încercați regenerarea paradigmei din raport.
    </div>

  </form>
{/block}
