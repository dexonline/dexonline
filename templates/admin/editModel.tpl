{extends file="layout.tpl"}

{block name=title}Editare model{/block}

{block name=content}
  {assign var="adjModels" value=$adjModels|default:null}
  {assign var="participles" value=$participles|default:null}
  {assign var="regenTransforms" value=$regenTransforms|default:null}

  <h3>Editare model {$m->modelType}{$m->number}</h3>

  <form id="modelForm" method="post">
    <input type="hidden" name="id" value="{$m->id}"/>

    <div class="panel panel-default">
      <div class="panel-heading">
        Proprietăți
      </div>

      <div class="panel-body form-horizontal">
        <div class="form-group">
          <label class="col-sm-3 control-label">număr de model</label>
          <div class="col-sm-9">
            <input class="form-control" type="text" name="number" value="{$m->number|escape}"/>
            <small class="text-muted">poate conține orice caractere</small>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-3 control-label">descriere</label>
          <div class="col-sm-9">
            <input class="form-control" type="text" name="description" value="{$m->description|escape}"/>
          </div>
        </div>

        {if $adjModels}
          <div class="form-group">
            <label class="col-sm-3 control-label">model de participiu</label>
            <div class="col-sm-9">
              <select class="form-control" name="participleNumber">
                {foreach from=$adjModels item=am}
                  <option value="{$am->number}"
                          {if $pm && $pm->adjectiveModel == $am->number}selected="selected"{/if}
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
            <input class="form-control" type="text" name="exponent" value="{$m->exponent|escape}"/>
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
              <div class="col-xs-2">LOC</div>
              <div class="col-xs-2">recom</div>
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
                  <div>
                    <div class="col-xs-8">
                      <input class="form-control input-sm"
                             type="text"
                             name="forms_{$inflId}_{$i}"
                             value="{$tuple.form|escape}"
                             {if $tuple.isLoc && !$locPerm}disabled{/if}>
                    </div>
                    <div class="col-xs-2">
                      <input class="checkbox"
                             type="checkbox"
                             name="isLoc_{$inflId}_{$i}"
                             value="1"
                             {if $tuple.isLoc}checked{/if}
                             {if !$locPerm}disabled{/if}>
                    </div>
                    <div class="col-xs-2">
                      <input class="checkbox"
                             type="checkbox"
                             name="recommended_{$inflId}_{$i}"
                             value="1"
                             {if $tuple.recommended}checked{/if}>
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
            Lexeme afectate ({$lexems|@count})
          </div>

          <table class="table table-condensed table-striped">

            <tr>
              <th>lexem</th>
              <th>Model</th>
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

            {foreach $lexems as $lIndex => $l}
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

    {if count($participles) && !FlashMessage::hasErrors()}
      <div class="panel panel-default">
        <div class="panel-heading">
          Participii regenerate conform modelului A{$pm->adjectiveModel|escape}
        </div>

        <div class="panel-body">
          {foreach from=$participles item=p key=i}
            {include file="paradigm/paradigm.tpl" lexem=$p}
          {/foreach}
        </div>
      </div>
    {/if}

    <div class="panel panel-default">
      <div class="panel-heading">
        Acțiuni
      </div>

      <div class="panel-body">
        <div class="form-group form-inline">
          <label class="control-label">
            <input class="checkbox" type="checkbox" name="shortList" value="1"
                   {if $shortList}checked{/if}>
            testează modificările pe maxim 10 lexeme
          </label>

        </div>

        <p class="text-muted">
          Toate lexemele vor fi salvate, dar numai (maxim) 10 vor fi testate și
          afișate. Aceasta poate accelera mult pasul de testare.
        </p>

        <div class="form-group">

          <button class="btn btn-default" type="submit" name="previewButton">
            testează
          </button>

          {if $previewPassed}
            <button class="btn btn-default" type="submit" name="confirmButton">
              <i class="glyphicon glyphicon-floppy-disk"></i>
              salvează
            </button>
          {/if}

        </div>
      </div>
    </div>
  </form>
{/block}
