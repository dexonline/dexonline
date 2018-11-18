{extends "layout.tpl"}

{block "title"}Scrabble{/block}

{block "search"}{/block}

{block "content"}
  <div class="panel panel-default">
    <div class="panel-heading">{'Scrabble lookup'|_}</div>

    <div class="panel-body">
      {assign var="form" value=$form|default:""}

      <p>
        {'Are you playing Scrabble and your friends do not believe that <em>kwyjibo</em> is
        a perfectly legal word? Type in your word to show them who the boss is.'|_}
      </p>

      <form action="scrabble" method="get">

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
            {'in version'|_}
            <select name="version" class="form-control">
              {foreach $versions as $v}
                <option
                  value="{$v->name}"
                  {if $v->name == $version}selected{/if}>
                  {$v->name|escape}
                </option>
              {/foreach}
            </select>

            <input type="submit" value="{'look up'|_}" class="btn btn-primary">
          </div>
        </div>
      </form>
      <br>
      <div id="scrabble-results">{include "bits/scrabbleResults.tpl"}</div>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">{'Official Scrabble word list (LOC)'|_}</div>

    <div class="panel-body">
      <table class="table table-striped-column-even">
        <thead>
          <tr>
            <th>{'version'|_}</th>
            <th>{'publication date'|_}</th>
            <th>{'reduced forms'|_}</th>
            <th>{'base forms'|_}</th>
            <th>{'inflected forms'|_}</th>
          </tr>
        </thead>
        <tbody>
          {foreach $versions as $v}
            <tr>
              <td>{$v->name|escape}</td>
              <td>{$v->freezeTimestamp|date_format:"%d %B %Y"}</td>
              <td><a href="{$v->getReducedFormUrl()}">{'download'|_}</a></td>
              <td><a href="{$v->getBaseFormUrl()}">{'download'|_}</a></td>
              <td><a href="{$v->getInflectedFormUrl()}">{'download'|_}</a></td>
            </tr>
          {/foreach}
        </tbody>
      </table>

      <dl class="dl-horizontal">
        <dt>{'reduced forms'|_}</dt>
        <dd>{'a list of words between 2 and 15 letters, without diacritics'|_}</dd>
        <dt>{'base forms'|_}</dt>
        <dd>
          {'word list in alphabetical order'|_}
          (<a href="https://wiki.dexonline.ro/wiki/Preciz%C4%83ri_privind_LOC"
             target="_blank">{'legend of notations'|_}</a>)
        </dd>
        <dt>{'inflected forms'|_}</dt>
        <dd>{'list of words and their conjugations/declensions'|_}</dd>
      </dl>

      <div class="alert alert-warning">
        {'The last official LOC version is 5.0. Version 6.0 contains changes
        to LOC made using the dexonline interface, but not included in the
        official version.'|_}
      </div>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">{'Differences between versions'|_}</div>

    <div class="panel-body">
      <p>
        {'Find out what changed between two versions of LOC.'|_}
      </p>

      <form class="form-inline" action="scrabble-diferente-loc" method="get">
        <div class="form-group">
          {'compare'|_|cap}
          <select class="form-control" name="list">
            <option value="base">{'base forms'|_}</option>
            <option value="inflected">{'inflected forms'|_}</option>
            <option value="reduced">{'reduced forms'|_}</option>
          </select>
        </div>

        <div class="form-group">
          {'between'|_}

          <select class="form-control" name="versions">
            {foreach $versions as $i => $old}
              {foreach $versions as $j => $new}
                {if $i > $j}
                  <option value="{$old->name|escape},{$new->name|escape}">
                    {'version %s and version %s'|_|sprintf
                    :$old->name
                    :$new->name}
                  </option>
                {/if}
              {/foreach}
            {/foreach}
          </select>
        </div>
        <input type="submit" value="{'compare'|_}" class="btn btn-primary">
      </form>
    </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">{'Inflection models'|_}</div>

    <div class="panel-body">
      <p>
        {"<em>%s</em> and <em>%s</em> have similar declensions... but not
        <em>%s</em> and <em>%s</em>? Here is why."|_|sprintf
        :'Stradă, cadă, ladă'
        :'ogradă'
        :'baladă'
        :'livadă'}
      </p>

      <form class="form-inline" action="modele-flexiune">
        {'Show models for'|_}

        <select class="form-control" name="modelType">
          {foreach $canonicalModelTypes as $mt}
            <option value="{$mt->code}">{$mt->code} ({$mt->description})</option>
          {/foreach}
        </select>

        <input class="btn btn-primary" type="submit" value="{'show'|_}">
      </form>
    </div>
  </div>
{/block}
