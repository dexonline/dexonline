{extends file="admin/layout.tpl"}

{block name=title}Editare model{/block}

{block name=headerTitle}
  Editare model {$m->modelType}{$m->number}
{/block}

{block name=content}
  {assign var="adjModels" value=$adjModels|default:null}
  {assign var="participles" value=$participles|default:null}
  {assign var="regenTransforms" value=$regenTransforms|default:null}

  <p id="stem">
    <input class="fieldColumn" type="text" name="" value="">
    <input class="checkboxColumn"
           type="checkbox"
           name=""
           value="1"
           {if !$locPerm}disabled{/if}>
    <input class="checkboxColumn" type="checkbox" name="" value="1" checked="checked">
  </p>

  <form id="modelForm" method="post">
    <input type="hidden" name="id" value="{$m->id}"/>

    <table class="editModel">
      <tr>
        <td>
          Număr model
          <span class="small">(poate conține orice caractere)</span>
        </td>
        <td></td>
        <td class="input">
          <input type="text" name="number" value="{$m->number|escape}"/>

          <span class="tooltip2" title="Aici puteți edita exponentul ales pentru un model și formele pentru diversele flexiuni. Folosiți accente unde
                                        doriți. Dacă o flexiune nu are forme, lăsați câmpul vid. Dacă o flexiune are mai multe forme, apăsați semnul + pentru a obține câte câmpuri
                                        doriți. Pentru a șterge o formă, ștergeți conținutul câmpului respectiv. Dacă bifați/debifați o formă pentru LOC, rezultatul se va aplica
                                        tuturor formelor corespunzătoare ale lexemelor din acest model, dar nu și la alte modele. Tipul modelului nu este editabil, dar numărul
                                        este.">&nbsp;</span>

        </td>
      </tr>
      <tr>
        <td>Descriere</td>
        <td></td>
        <td class="input">
          <input type="text" name="description" value="{$m->description|escape}"/>
        </td>
      </tr>
      {if $adjModels}
        <tr>
          <td>Model de participiu</td>
          <td></td>
          <td class="input">
            <select name="participleNumber">
              {foreach from=$adjModels item=am}
                <option value="{$am->number}"
                        {if $pm && $pm->adjectiveModel == $am->number}selected="selected"{/if}
                        >{$am->number} ({$am->exponent})
                </option>
              {/foreach}
            </select>
          </td>
        </tr>
      {/if}
      <tr class="exponent">
        <td>Exponent</td>
        <td></td>
        <td class="input">
          <input type="text" name="exponent" value="{$m->exponent|escape}"/>
        </td>
      </tr>

      <tr>
        <th>Flexiune</th>
        <th></th>
        <th class="input">
          <span class="fieldColumn">Forme</span>
          <span class="checkboxColumn">LOC</span>
          <span class="checkboxColumn">Recom.</span>
        </th>
      </tr>

      {foreach from=$forms item=f key=inflId}
        <tr class="{cycle values="odd,even"}">
          <td>{$inflectionMap[$inflId]->description|escape}</td>
          <td class="addSign">
            <a class="noBorder addFormLink" href="#" data-infl-id="{$inflId}">
              <i class="glyphicon glyphicon-plus"></i>
            </a>
          </td>
          <td class="input">
            {foreach from=$f item=tuple key=i}
              <p>
                <input class="fieldColumn"
                       type="text"
                       name="forms_{$inflId}_{$i}"
                       value="{$tuple.form|escape}"
                       {if $tuple.isLoc && !$locPerm}disabled{/if}>
                <input class="checkboxColumn"
                       type="checkbox"
                       name="isLoc_{$inflId}_{$i}"
                       value="1"
                       {if $tuple.isLoc}checked{/if}
                       {if !$locPerm}disabled{/if}>
                <input class="checkboxColumn" type="checkbox" name="recommended_{$inflId}_{$i}" value="1" {if $tuple.recommended}checked="checked"{/if}/>
              </p>
            {/foreach}
          </td>
        </tr>
      {/foreach}
    </table>
    <br/>

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
