{extends "layout.tpl"}

{block "title"}Scrabble{/block}

{block "search"}{/block}

{block "content"}
  <div class="panel panel-default">
    <div class="panel-heading">{t}Scrabble lookup{/t}</div>

    <div class="panel-body">
      {assign var="form" value=$form|default:""}

      <p>
        {t}Are you playing Scrabble and your friends do not believe that <em>kwyjibo</em>
        is a perfectly legal word? Type in your word to show them who the boss is.{/t}
      </p>

      <form id="scrabbleForm">

        <div class="scrabbleSearchDiv">
          <div class="form-group {$class|default:''}">
            <input
              class="scrabbleSearchField form-control"
              type="text"
              name="form"
              placeholder="kwyjibo"
              value="{$form|default:''|escape}"
              autofocus>
            <span id="scrabble-feedback-glyph">
              {if isset($answer)}
                {if $answer}
                  <span class="form-control-feedback glyphicon glyphicon-ok"></span>
                {else}
                  <span class="form-control-feedback glyphicon glyphicon-remove"></span>
                {/if}
              {/if}
            </span>
          </div>

          <div class="form-inline text-center">
            {t}in version{/t}
            <select name="version" class="form-control">
              {foreach $versions as $v}
                <option
                  value="{$v->name}"
                  {if $v->name == $version}selected{/if}>
                  {$v->name|escape}
                </option>
              {/foreach}
            </select>

            <input type="submit" value="{t}look up{/t}" class="btn btn-primary">
          </div>
        </div>
      </form>
      <br>
      <div id="scrabble-results">{include "bits/scrabbleResults.tpl"}</div>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">{t}Official Scrabble word list (LOC){/t}</div>

    <div class="panel-body">
      <table class="table table-striped-column-even">
        <thead>
          <tr>
            <th>{t}version{/t}</th>
            <th>{t}publication date{/t}</th>
            <th>{t}reduced forms{/t}</th>
            <th>{t}base forms{/t}</th>
            <th>{t}inflected forms{/t}</th>
          </tr>
        </thead>
        <tbody>
          {foreach $versions as $v}
            <tr>
              <td>{$v->name|escape}</td>
              <td>{$v->freezeTimestamp|date_format:"%d %B %Y"}</td>
              <td><a href="{$v->getReducedFormUrl()}">{t}download{/t}</a></td>
              <td><a href="{$v->getBaseFormUrl()}">{t}download{/t}</a></td>
              <td><a href="{$v->getInflectedFormUrl()}">{t}download{/t}</a></td>
            </tr>
          {/foreach}
        </tbody>
      </table>

      <dl class="dl-horizontal">
        <dt>{t}reduced forms{/t}</dt>
        <dd>{t}a list of words between 2 and 15 letters, without diacritics{/t}</dd>
        <dt>{t}base forms{/t}</dt>
        <dd>
          {t}word list in alphabetical order{/t}
          (<a href="https://wiki.dexonline.ro/wiki/Preciz%C4%83ri_privind_LOC"
             target="_blank">{t}legend of notations{/t}</a>)
        </dd>
        <dt>{t}inflected forms{/t}</dt>
        <dd>{t}list of words and their conjugations/declensions{/t}</dd>
      </dl>

      <div class="alert alert-warning">
        {t}The last official LOC version is 5.0. Version 6.0 contains changes to
        LOC made using the dexonline interface, but not included in the official
        version.{/t}
      </div>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">{t}Differences between versions{/t}</div>

    <div class="panel-body">
      <p>
        {t}Find out what changed between two versions of LOC.{/t}
      </p>

      <form class="form-inline" action="{Router::link('games/scrabbleLocDifferences')}">
        <div class="form-group">
          {cap}{t}compare{/t}{/cap}
          <select class="form-control" name="list">
            <option value="base">{t}base forms{/t}</option>
            <option value="inflected">{t}inflected forms{/t}</option>
            <option value="reduced">{t}reduced forms{/t}</option>
          </select>
        </div>

        <div class="form-group">
          {t}between{/t}

          <select class="form-control" name="versions">
            {foreach $versions as $i => $old}
              {foreach $versions as $j => $new}
                {if $i > $j}
                  <option value="{$old->name|escape},{$new->name|escape}">
                    {t 1=$old->name 2=$new->name}version %1 and version %2{/t}
                  </option>
                {/if}
              {/foreach}
            {/foreach}
          </select>
        </div>
        <input type="submit" value="{t}compare{/t}" class="btn btn-primary">
      </form>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">{t}Inflection models{/t}</div>

    <div class="panel-body">
      <p>
        {t 1="Stradă, cadă, ladă" 2="ogradă" 3="baladă" 4="livadă"}
        <em>%1</em> and <em>%2</em> have similar declensions... but not
        <em>%3</em> and <em>%4</em>? Here is why.{/t}
      </p>

      <form class="form-inline" action="modele-flexiune">
        {t}Show models for{/t}

        <select class="form-control" name="modelType">
          {foreach $canonicalModelTypes as $mt}
            <option value="{$mt->code}">{$mt->code} ({$mt->description})</option>
          {/foreach}
        </select>

        <input class="btn btn-primary" type="submit" value="{t}show{/t}">
      </form>
    </div>
  </div>
{/block}
