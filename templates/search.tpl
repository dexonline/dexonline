{extends "layout.tpl"}

{block "title"}
  {$cuv|escape} - definiție
  {if count($sourceList) == 1}{$sourceList[0]}{/if}
  {if $showParadigm}și paradigmă{/if}
{/block}

{block "pageDescription"}
  {if isset($pageDescription)}
    <meta name="description" content="{$pageDescription}"/>
  {/if}
{/block}

{block "content"}
  {assign var="declensionText" value=$declensionText|default:null}
  {assign var="excludeUnofficial" value=$excludeUnofficial|default:false}
  {assign var="showParadigm" value=$showParadigm|default:false}
  {assign var="wikiArticles" value=$wikiArticles|default:null}
  {assign var="allDefinitions" value=$allDefinitions|default:null}

  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" {if !$showParadigm}class="active"{/if}>
      <a href="#resultsTab" aria-controls="resultsTab" role="tab" data-toggle="tab">
        rezultate
      </a>
    </li>

    {if $searchParams.paradigm}
      <li role="presentation" {if $showParadigm}class="active"{/if}>
        <a href="#paradigmTab" aria-controls="paradigmTab" role="tab" data-toggle="tab">
          {$declensionText}
        </a>
      </li>
    {/if}
  </ul>

  <div class="tab-content">
    {* results tab *}
    <div role="tabpanel" class="tab-pane {if !$showParadigm}active{/if}" id="resultsTab">
      {* definition ID search *}
      {if $searchType == $smarty.const.SEARCH_DEF_ID}
        {if count($results)}
          <h3>
            Definiția cu ID-ul
            {foreach $results as $key => $ignored}
              {$key}:
            {/foreach}
          </h3>
        {else}
          <h4>
            Nu există nicio definiție cu ID-ul căutat.
          </h4>
        {/if}

      {* full-text search *}
      {elseif $searchType == $smarty.const.SEARCH_FULL_TEXT}
        {if isset($extra.fullTextLock)}
          <h3>Căutare dezactivată</h3>

          <p>
            Momentan nu puteți căuta prin textul definițiilor, deoarece indexul este
            în curs de reconstrucție. Această operație durează de obicei circa 10 minute.
            Ne cerem scuze pentru neplăcere.
          </p>
        {else}
          <h3>
            {include "bits/count.tpl"
            displayed=count($results)
            total=$extra.numDefinitions
            none="Nicio definiție nu cuprinde"
            one="O definiție cuprinde"
            many="definiții cuprind"
            common="toate cuvintele căutate"}
          </h3>

          {if count($extra.stopWords)}
            <p class="text-warning">
              Următoarele cuvinte au fost ignorate deoarece sunt prea comune:
              <strong>
                {foreach $extra.stopWords as $word}
                  {$word|escape}
                {/foreach}
              </strong>
              </span>
          {/if}
        {/if}

      {* entry ID search *}
      {elseif $searchType == $smarty.const.SEARCH_ENTRY_ID}
        <h3>
          {if count($entries)}
            {include "bits/count.tpl"
            displayed=count($results)
            none="Nicio definiție"
            one="O definiție"
            many="definiții"
            common="pentru"}

            <strong>{$entries[0]->description}</strong>
          {else}
            Nu există nicio intrare cu ID-ul căutat.
          {/if}
        </h3>

      {* regular expression search *}
      {elseif $searchType == $smarty.const.SEARCH_REGEXP}
        {capture name="common"}
          pentru <strong>{$cuv|escape}</strong>
        {/capture}

        <h3>
          {include "bits/count.tpl"
          displayed=count($lexems)
          total=$extra.numLexems|default:0
          none="Niciun rezultat"
          one="Un rezultat"
          many="rezultate"
          common=$smarty.capture.common}
        </h3>

      {* normal search (inflected form search) *}
      {elseif $searchType == $smarty.const.SEARCH_INFLECTED}
        <h3>
          {if empty($results) && $sourceId}
            Nu am găsit în acest dicționar definiția lui
          {else}
            {include "bits/count.tpl"
            displayed=count($results)
            total=$extra.numDefinitions
            none="Nicio definiție"
            one="O definiție"
            many="definiții"
            common=""}
          {/if}
        </h3>

        <ul>
          {foreach $entries as $e}
            <li>
              {* If there is exactly one entry, do not link to the entry page, because
                 it would print an almost exact duplicate of this page. *}
              {if count($entries) > 1}
                <a href="{$wwwRoot}intrare/{$e->description}/{$e->id}">
                  {$e->description}
                </a>
              {else}
                <strong>{$e->description}</strong>
              {/if}

              <span class="variantList">
                {foreach $e->getPrintableLexems() as $l}
                  <span {if !$l->main}class="text-muted"{/if}>
                    {include "bits/lexemName.tpl" lexem=$l}
                  </span>
                {/foreach}
              </span>
            </li>
          {/foreach}
        </ul>

        {if $extra.numDefinitions > count($results)}
          <p>
            <a href="{$smarty.server.REQUEST_URI}/expandat">
              arată toate definițiile
            </a>
          </p>
        {/if}

      {* multiword search *}
      {elseif $searchType == $smarty.const.SEARCH_MULTIWORD}
        <h3>
          {include "bits/count.tpl"
          displayed=count($results)
          total=$extra.numDefinitions
          none="Nicio definiție nu se potrivește"
          one="O definiție se potrivește"
          many="definiții se potrivesc"
          common="cu cel puțin doi dintre termenii căutați."}
        </h3>

        <p class="text-warning">
          Dacă rezultatele nu sunt mulțumitoare, puteți căuta cuvintele separat sau puteți căuta

          <a href="{$wwwRoot}text/{$cuv|escape:url}">
            în tot corpul definițiilor
          </a>.
        </p>

        {if $extra.ignoredWords}
          <p class="text-warning">
            Sunt permise maximum 5 cuvinte. Următoarele cuvinte au fost ignorate:
            <strong>
              {foreach $extra.ignoredWords as $w}
                {$w|escape}
              {/foreach}
            </strong>
          </p>
        {/if}

      {* approximate search *}
      {elseif $searchType == $smarty.const.SEARCH_APPROXIMATE}
        <h3>
          {if count($entries)}
            Cuvântul <strong>{$cuv|escape}</strong> nu este în dicționar. Iată câteva sugestii:
          {else}
            Niciun rezultat pentru <strong>{$cuv|escape}</strong>
          {/if}
        </h3>

      {/if}

      {* various warnings and subtitles *}
      {if !count($results) && count($entries) && $sourceId}
        <p>
          Repetați căutarea <a href="{$wwwRoot}definitie/{$cuv|escape}">în toate dicționarele</a>.
        </p>
      {/if}

      {if !count($results) && isset($extra.unofficialHidden)}
        <p class="text-warning">
          Există definiții din dicționare neoficiale, pe care ați ales
          <a href="{$wwwRoot}preferinte">să le ascundeți</a>.
        </p>
      {/if}

      {if !count($results) && isset($extra.sourcesHidden)}
        <p class="text-warning">
          Există definiții din dicționare pentru care dexonline nu are drepturi de redistribuire:
        </p>

        <ul>
          {foreach $extra.sourcesHidden as $sh}
            <li>{$sh->name}, {$sh->publisher}, {$sh->year}</li>
          {/foreach}
        </ul>
      {/if}

      <div id="resultsWrapper" class="txt">
        {* image gallery *}
        {if !empty($images)}
          {include "bits/gallery.tpl" images=$images}
        {/if}

        {* wiki articles *}
        {if $wikiArticles}
          <div class="panel panel-default">
            <div class="panel-heading">Articole pe această temă:</div>
            <div class="panel-body">
              <ul>
                {foreach $wikiArticles as $wa}
                  <li>
                    <a href="{$wwwRoot}articol/{$wa->getUrlTitle()}">{$wa->title}</a>
                  </li>
                {/foreach}
              </ul>
            </div>
          </div>
        {/if}

        {* definitions and categories *}
        {$displayedUnofficial=false}
        {$displayedSpec=false}
        {foreach $results as $i => $row}

          {if $searchParams.categories}
            {if $row->source->type == Source::TYPE_SPECIALIZED && !$displayedSpec}
              <br/>
              <div class="callout callout-info">
                <h3>Definiții din dicționare specializate</h3>
                <p class="text-muted">
                  Aceste definiții pot explica numai anumite înțelesuri ale cuvintelor.
                </p>
              </div>
              {$displayedSpec=true}
            {elseif $row->source->type == Source::TYPE_UNOFFICIAL && !$displayedUnofficial}
              <br/>
              <div class="callout callout-info">
                <h3>Definiții din dicționare neoficiale</h3>
                <p class="text-muted">
                  Deoarece nu sunt editate de lexicografi, aceste definiții pot conține erori,
                  deci e preferabilă consultarea altor dicționare în paralel.
                </p>
              </div>
              {$displayedUnofficial=true}
            {/if}
          {/if}

          {include "bits/definition.tpl"
          showBookmark=1
          showCourtesyLink=1
          showFlagTypo=1
          showHistory=1
          showWotd=1}

        {/foreach}

        {* entry list *}
        {if $searchParams.entryList}
          <span class="entryList">
            {foreach $entries as $e}
              <span>
                <a href="{$wwwRoot}intrare/{$e->getShortDescription()}/{$e->id}">
                  {$e->description|escape}
                </a>
              </span>
            {/foreach}
          </span>
        {/if}

        {* lexem list *}
        {if count($lexems)}
          <span class="entryList">
            {foreach $lexems as $l}
              <span>
                <a href="{$wwwRoot}lexem/{$l->formNoAccent}/{$l->id}">
                  {include "bits/lexemName.tpl" lexem=$l}
                </a>
              </span>
            {/foreach}
          </span>
        {/if}

        {include "bits/typoForm.tpl"}
      </div>
    </div>

    {* paradigm tab *}
    {if $searchParams.paradigm}
      <div role="tabpanel" class="tab-pane {if $showParadigm}active{/if}" id="paradigmTab">
        {foreach $entries as $e}
          {include "bits/multiParadigm.tpl" entry=$e}
        {/foreach}

        {if $hasUnrecommendedForms}
          <div class="notRecommendedLegend">* Formă nerecomandată</div>
        {/if}

        <div>
          <a class="paradigmLink"
             title="Link către această pagină, dar cu flexiunile expandate"
             href="{$paradigmLink}">
            Link către această paradigmă
          </a>
        </div>
      </div>
    {/if}
  </div>

{/block}
