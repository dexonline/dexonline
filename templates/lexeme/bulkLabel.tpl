{extends "layout-admin.tpl"}

{block "title"}Etichetare sufix -{$suffix}{/block}

{block "content"}
  <h3>Etichetare sufix -{$suffix}</h3>

  <p>
    <a class="btn btn-default" href="{Router::link('lexeme/bulkLabelSelectSuffix')}">
      <i class="glyphicon glyphicon-arrow-left"></i>
      înapoi la lista de sufixe
    </a>
  </p>

  <div class="alert alert-info alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert">
      <span aria-hidden="true">&times;</span>
    </button>

    <ul>
      <li>Sunt prezentate maximum 20 de lexeme pe pagină.</li>
      <li>
        Restricțiile nu sunt luate în considerare în timp real (toate
        formele vor fi afișate chiar dacă indicați unele restricții),
        dar vor fi procesate corect când trimiteți formularul.
      </li>
      <li>
        Dacă ignorați un lexem, el nu va fi modificat și va continua să apară în listă.
      </li>
    </ul>
  </div>

  <form class="form-horizontal" method="post">
    <input type="hidden" name="suffix" value="{$suffix|escape}">
    {foreach $lexemes as $lIter => $l}
      <div class="panel panel-default">

        <div class="panel-heading">
          {$lIter+1}. {$l->formNoAccent|escape}
          <a href="{Router::link('lexeme/edit')}?lexemeId={$l->id}">
            <i class="glyphicon glyphicon-pencil"></i>
            editează
          </a>
        </div>

        <div class="panel-body">
          <div class="form-group">
            <!-- Radio buttons to choose the model. -->
            <label class="col-md-2 control-label">
              model
            </label>

            <div class="col-md-10 form-inline">
              {foreach $models as $i => $m}
                {assign var="mId" value="`$m->modelType`_`$m->number`"}
                <label class="radio">
                  <input class="modelRadio"
                         type="radio"
                         name="lexeme_{$l->id}"
                         value="{$mId}"
                         data-paradigm-id="paradigm_{$lIter}_{$i}">
                  {$m->modelType}{$m->number} ({$m->exponent})
                </label>
              {/foreach}
              <label class="radio">
                <input class="modelRadio"
                       type="radio"
                       name="lexeme_{$l->id}"
                       value="0"
                       checked>
                Ignoră
              </label>
            </div>
          </div>

          <!-- Restriction checkboxes, if applicable -->
          <div class="form-group">
            <label class="col-md-2 control-label">
              restricții
            </label>

            <div class="col-md-10">
              <input type="text" class="form-control" name="restr_{$l->id}">
            </div>
          </div>

          <hr>

          <!-- Definitions -->
          <div class="panel-admin">
            {foreach $searchResults[$lIter] as $row}
              {include "bits/definition.tpl" showDropup=0 showUser=0 showPageLink=false}
            {/foreach}
          </div>

          <!-- Only one paradigm will be visible at any time. -->
          {assign var="lArray" value=$lMatrix[$lIter]}
          {foreach $lArray as $pIter => $l }
            {assign var="m" value=$models[$pIter]}
            {assign var="mt" value=$modelTypes[$pIter]}
            <div id="paradigm_{$lIter}_{$pIter}" class="paradigm">
              {include "paradigm/paradigm.tpl" lexeme=$l}
            </div>
          {/foreach}
        </div>
      </div>
    {/foreach}

    <button type="submit" class="btn btn-success" name="saveButton">
      <i class="glyphicon glyphicon-floppy-disk"></i>
      <u>s</u>alvează
    </button>
  </form>
{/block}
