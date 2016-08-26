{extends file="layout.tpl"}

{block name=title}Editare model{/block}

{block name=content}
  {assign var="adjModels" value=$adjModels|default:null}
  {assign var="participles" value=$participles|default:null}
  {assign var="regenTransforms" value=$regenTransforms|default:null}

  <h3>Editare model {$m->modelType}{$m->number}</h3>

  <form class="form-horizontal" id="modelForm" method="post">
    <input type="hidden" name="id" value="{$m->id}"/>

    <div class="panel panel-default">
      <div class="panel-heading">
        Proprietăți
      </div>

      <div class="panel-body">
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
          <th>flexiune</th>
          <th></th>
          <th>forme</th>
          <th>LOC</th>
          <th>recom.</th>
        </tr>

        {foreach $forms as $inflId => $f}
          <tr>
            <td>{$inflectionMap[$inflId]->description|escape}</td>
            <td>
              <a class="addFormLink" href="#" data-infl-id="{$inflId}">
                <i class="glyphicon glyphicon-plus"></i>
              </a>
            </td>
            <td>
              {foreach $f as $i => $tuple}
                <div>
                  <input class="form-control"
                         type="text"
                         name="forms_{$inflId}_{$i}"
                         value="{$tuple.form|escape}"
                         {if $tuple.isLoc && !$locPerm}disabled{/if}>
                </div>
              {/foreach}
            </td>
            <td>
              {foreach $f as $i => $tuple}
                <div>
                  <input class="checkbox"
                         type="checkbox"
                         name="isLoc_{$inflId}_{$i}"
                         value="1"
                         {if $tuple.isLoc}checked{/if}
                         {if !$locPerm}disabled{/if}>
                </div>
              {/foreach}
            </td>
            <td>
              {foreach $f as $i => $tuple}
                <div>
                  <input class="checkbox"
                         type="checkbox"
                         name="recommended_{$inflId}_{$i}"
                         value="1"
                         {if $tuple.recommended}checked{/if}>
                </div>
              {/foreach}
            </td>
          </tr>
        {/foreach}
      </table>
    </div>

    <input id="shortList" type="checkbox" name="shortList" value="1" {if $shortList}checked{/if}>
    <label for="shortList">Testează modificările pe maxim 10 lexeme</label>
    <div class="flexExplanation">
      Toate lexemele vor fi salvate, dar numai (maxim) 10 vor fi testate și
      afișate. Aceasta poate accelera mult pasul de testare.
    </div>

    {if $previewPassed}
      <h3>Schimbări globale:</h3>

      <ul>
        {if $m->number != $om->number}
          <li>Număr de model nou: {$m->number|escape}</li>
        {/if}
        {if $m->exponent != $om->exponent}
          <li>Exponent nou: {$m->exponent|escape}</li>
        {/if}
        {if $m->description != $om->description}
          <li>Descriere nouă: {$m->description|escape}</li>
        {/if}
        {if $pm && ($pm->adjectiveModel != $opm->adjectiveModel)}
          <li>Model nou de participiu: A{$pm->adjectiveModel|escape}</li>
        {/if}
      </ul>

      {if count($regenTransforms)}
        <h3>Lista de flexiuni afectate ({$regenTransforms|@count}):</h3>
        <ol>
          {foreach from=$regenTransforms item=ignored key=inflId}
            <li>{$inflectionMap[$inflId]->description|escape}</li>
          {/foreach}
        </ol>

        <h3>Lexemele afectate ({$lexems|@count}) și noile lor forme:</h3>

        <table class="changedForms">
          <tr class="header">
            <td class="lexem">Lexem</td>
            <td class="model">Model</td>
            {foreach from=$regenTransforms item=ignored key=ignored2}
              <td class="forms">{counter name="otherCounter"}.</td>
            {/foreach}
          </tr>
          <tr class="exponent">
            <td class="lexem">{$m->exponent}</td>
            <td class="model">exponent</td>
            {foreach from=$regenTransforms item=ignored key=inflId}
              {assign var="variantArray" value=$forms[$inflId]}
              <td class="forms">
                {strip}
                {foreach from=$variantArray item=tuple key=i}
                  {if $i}, {/if}
                  {$tuple.form|escape}
                {/foreach}
              {/strip}
              {if !count($variantArray)}&mdash;{/if}
              </td>
            {/foreach}
          </tr>
          {foreach $lexems as $lIndex => $l}
            {assign var="inflArray" value=$regenForms[$lIndex]}
            <tr>
              <td class="lexem">{$l->form|escape}</td>
              <td class="model">{$l->modelType}{$l->modelNumber}</td>
              {foreach from=$inflArray item=variantArray key=inflId}
                <td class="forms">
                  {strip}
                  {foreach from=$variantArray item=form key=i}
                    {if $i}, {/if}
                    {$form|escape}
                  {/foreach}
                  {if !count($variantArray)}&mdash;{/if}
                {/strip}
                </td>
              {/foreach}
            </tr>
          {/foreach}
        </table>
      {/if}
    {/if}

    {if count($participles) && !count($flashMessages)}
      <h3>Participii regenerate conform modelului A{$pm->adjectiveModel|escape}:</h3>

      {foreach from=$participles item=p key=i}
        {include file="paradigm/paradigm.tpl" lexem=$p}
      {/foreach}
    {/if}

    <br/>
    <input type="submit" name="previewButton" value="Testează"/>
    <!-- We want to disable the button on click, but still submit a value -->
    {if $previewPassed}
      <input type="submit" name="confirmButton" value="Salvează"/>
    {/if}
  </form>
{/block}
