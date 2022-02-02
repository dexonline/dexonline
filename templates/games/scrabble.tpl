{extends "layout.tpl"}

{block "title"}Scrabble{/block}

{block "search"}{/block}

{block "content"}
  <div class="card mb-3">
    <div class="card-header">{t}Scrabble lookup{/t}</div>

    <div class="card-body pb-0">
      {assign var="form" value=$form|default:""}

      <p>
        {t}Are you playing Scrabble and your friends do not believe that <em>kwyjibo</em>
        is a perfectly legal word? Type in your word to show them who the boss is.{/t}
      </p>

      <form id="scrabbleForm">

        <div class="scrabbleSearchDiv">
          {if !isset($answer)}
            {$class=""}
          {else if $answer}
            {$class="is-valid"}
          {else}
            {$class="is-invalid"}
          {/if}
          <input
            class="scrabbleSearchField form-control {$class}"
            type="text"
            name="form"
            placeholder="kwyjibo"
            value="{$form|default:''|escape}"
            autocomplete="off"
            autofocus>

          <div class="d-flex justify-content-center mt-3">
            <label class="col-form-label">{t}in version{/t}</label>

            <div class="mx-2">
              <select name="version" class="form-select">
                {foreach $versions as $v}
                  <option
                    value="{$v->name}"
                    {if $v->name == $version}selected{/if}>
                    {$v->name|escape}
                  </option>
                {/foreach}
              </select>
            </div>

            <button type="submit" class="btn btn-primary">
              {t}look up{/t}
            </button>
          </div>
        </div>
      </form>

      <div id="scrabble-results" class="mt-3">
        {include "bits/scrabbleResults.tpl"}
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">{t}Official Scrabble word list (LOC){/t}</div>

    <div class="card-body">
      <table class="table">
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
              <td>{$v->freezeTimestamp|date:'d MMMM yyyy'}</td>
              <td><a href="{$v->getReducedFormUrl()}">{t}download{/t}</a></td>
              <td><a href="{$v->getBaseFormUrl()}">{t}download{/t}</a></td>
              <td><a href="{$v->getInflectedFormUrl()}">{t}download{/t}</a></td>
            </tr>
          {/foreach}
        </tbody>
      </table>

      <dl class="row">
        <dt class="col-md-3">{t}reduced forms{/t}</dt>
        <dd class="col-md-9">{t}a list of words between 2 and 15 letters, without diacritics{/t}</dd>

        <dt class="col-md-3">{t}base forms{/t}</dt>
        <dd class="col-md-9">
          {t}word list in alphabetical order{/t}
          (<a href="https://wiki.dexonline.ro/wiki/Preciz%C4%83ri_privind_LOC"
             target="_blank">{t}legend of notations{/t}</a>)
        </dd>

        <dt class="col-md-3">{t}inflected forms{/t}</dt>
        <dd class="col-md-9">{t}list of words and their conjugations/declensions{/t}</dd>
      </dl>

      {notice type="info"}
        {t}The last official LOC version is 5.0. Version 6.0 contains changes to
        LOC made using the dexonline interface, but not included in the official
        version.{/t}
      {/notice}
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">{t}Differences between versions{/t}</div>

    <div class="card-body">
      <p>
        {t}Find out what changed between two versions of LOC.{/t}
      </p>

      <form
        class="d-flex"
        action="{Router::link('games/scrabbleLocDifferences')}">
        <label class="col-form-label me-2">{cap}{t}compare{/t}{/cap}</label>

        <div class="me-2">
          <select class="form-select" name="list">
            <option value="base">{t}base forms{/t}</option>
            <option value="inflected">{t}inflected forms{/t}</option>
            <option value="reduced">{t}reduced forms{/t}</option>
          </select>
        </div>

        <label class="col-form-label me-2">{t}between{/t}</label>

        <div class="me-2">
          <select class="form-select" name="versions">
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

        <button type="submit" class="btn btn-primary">
          {t}compare{/t}
        </button>
      </form>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">{t}Inflection models{/t}</div>

    <div class="card-body">
      <p>
        {t 1="Stradă, cadă, ladă" 2="ogradă" 3="baladă" 4="livadă"}
        <em>%1</em> and <em>%2</em> have similar declensions... but not
        <em>%3</em> and <em>%4</em>? Here is why.{/t}
      </p>

      <form class="d-flex" action="{Router::link('model/list')}">
        <label class="col-form-label me-2">
          {t}Show models for{/t}
        </label>

        <div class="me-2">
          <select class="form-select" name="modelType">
            {foreach $canonicalModelTypes as $mt}
              <option value="{$mt->code}">{$mt->code} ({$mt->description})</option>
            {/foreach}
          </select>
        </div>

        <button class="btn btn-primary" type="submit">
          {t}show{/t}
        </button>
      </form>
    </div>
  </div>
{/block}
