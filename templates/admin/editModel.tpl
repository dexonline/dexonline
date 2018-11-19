{extends "layout-admin.tpl"}

{block "title"}Editare model{/block}

{block "content"}
  {assign var="adjModels" value=$adjModels|default:null}
  {assign var="participles" value=$participles|default:null}
  {assign var="regenTransforms" value=$regenTransforms|default:null}

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
                        name="apocope_{$inflId}_{$i}"
                        value="1"
                        {if $tuple.apocope}checked{/if}>
                    </div>
                  </div>
                {/foreach}
              </div>
            </td>
          </tr>
        {/foreach}
      </table>
    </div>

    {if $previewPassed}
      {if count($regenTransforms)}
        <div class="panel panel-default">
          <div class="panel-heading">
            Lexeme afectate ({$lexemes|@count})
          </div>

          <table class="table table-condensed table-striped">

            <tr>
              <th>lexem</th>
              <th>model</th>
              {foreach $regenTransforms as $inflId => $ignored}
                <th>{$inflectionMap[$inflId]->description|escape}</th>
              {/foreach}
            </tr>

            <tr>
              <th>{$m->exponent}</th>
              <th>exponent</th>
              {foreach $regenTransforms as $inflId => $ignored}
                {assign var="variantArray" value=$forms[$inflId]}
                <th>
                  {strip}
                  {foreach $variantArray as $i => $tuple}
                    {if $i}, {/if}
                    {$tuple.form|escape}
                  {/foreach}
                  {if !count($variantArray)}&mdash;{/if}
                  {/strip}
                </th>
              {/foreach}
            </tr>

            {foreach $lexemes as $lIndex => $l}
              {assign var="inflArray" value=$regenForms[$lIndex]}
              <tr>
                <td>{$l->form|escape}</td>
                <td>{$l->modelType}{$l->modelNumber}</td>
                {foreach $inflArray as $variantArray}
                  <td>
                    {', '|implode:$variantArray|escape}
                    {if !count($variantArray)}&mdash;{/if}
                  </td>
                {/foreach}
              </tr>
            {/foreach}
          </table>
        </div>
      {/if}
    {/if}

    {if !empty($participles) && !FlashMessage::hasErrors()}
      <div class="panel panel-default">
        <div class="panel-heading">
          Participii regenerate conform modelului A{$pm->adjectiveModel|escape}
        </div>

        <div class="panel-body">
          {foreach $participles as $i => $p}
            {include "paradigm/paradigm.tpl" lexeme=$p}
          {/foreach}
        </div>
      </div>
    {/if}

    <div class="panel panel-default">
      <div class="panel-heading">
        Acțiuni
      </div>

      <div class="panel-body">
        <div class="checkbox">
          <label class="control-label">
            <input class="checkbox" type="checkbox" name="shortList" value="1"
              {if $shortList}checked{/if}>
            testează modificările pe maximum 10 lexeme
          </label>

          <p class="text-muted">
            Toate lexemele vor fi salvate, dar numai (maximum) 10 vor fi
            testate și afișate. Aceasta poate accelera mult pasul de testare.
          </p>
        </div>

        <div class="form-group">

          <button class="btn btn-primary" type="submit" name="previewButton">
            testează
          </button>

          {if $previewPassed}
            <button class="btn btn-success" type="submit" name="saveButton">
              <i class="glyphicon glyphicon-floppy-disk"></i>
              <u>s</u>alvează
            </button>
          {/if}

        </div>
      </div>
    </div>
  </form>
{/block}
