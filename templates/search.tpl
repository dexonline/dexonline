{$wikiArticles=$wikiArticles|default:[]}

{extends "layout.tpl"}

{block "title"}
  {$cuv=$cuv|escape}
  {$cuv} - {t}definition{/t}
  {if count($sourceList) == 1}{$sourceList[0]}{/if}
  {if $searchParams.paradigm}{t}and paradigm{/t}{/if}
{/block}

{block "pageDescription"}
  {if isset($pageDescription)}
    <meta name="description" content="{$pageDescription}">
  {/if}
{/block}

{* we'll move these around *}
{block "banner"}{/block}
{block "flashMessages"}{/block}

{block "content"}
  {include "banner/banner.tpl"}
  {include "bits/flashMessages.tpl"}

  <ul class="nav nav-tabs" role="tablist">
    {foreach $tabs as $tab => $info}
      {include "search/tab.tpl"
        count=$info.count
        prominent=$info.prominent
        emphasize=$info.emphasize
        tab=$tab
        icon=$info.icon
        title=$info.title}
    {/foreach}

    {if count($trees)}
      {* don't advertise the synthesis tab unless it exists *}
      <li class="align-self-center ms-2">
        <a id="tabAdvertiser" href="#">
          {include "bits/icon.tpl" i=info}
        </a>
      </li>
    {/if}

    {* only special people are allowed ;) *}
    {* if User::can(User::PRIV_PLUGIN) *}
    {if $cuv == 'empatie'}
      {* don't advertise the expert explanatinon tab unless it exists *}
      <li class="align-self-center ms-2">
        {include "bits/expert.tpl"}
      </li>
    {/if}
    {* /if *}

    {* only special people are allowed ;) *}
    {* if User::can(User::PRIV_PLUGIN) *}
    {if $cuv == 'limbă de lemn'}
      <li class="align-self-center ms-2">
          {include "bits/limba-de-lemn.tpl"}
      </li>
    {/if}
    {* /if *}
  </ul>

  <div class="tab-content">
    {* 20260115: temporary change all h3 from this template to h5 *}

    {* results tab *}
    <div
      role="tabpanel"
      class="tab-pane {if $activeTab == Tab::T_RESULTS}show active{/if}"
      id="tab_{Tab::T_RESULTS}">

      {* definition ID search *}
      {if $searchType == $smarty.const.SEARCH_DEF_ID}
        <h5>{t}Definition with ID{/t} {$results|array_keys|implode}:</h5>

        {include "search/definitionList.tpl"}

        {* full-text search *}
      {elseif $searchType == $smarty.const.SEARCH_FULL_TEXT}
        {if isset($extra.fullTextLock)}
          {include "search/fullTextLock.tpl"}
        {else}
          <h5>
            {if count($results)}
              {t
                count=$extra.numDefinitionsFullText
                1=$extra.numDefinitionsFullText
                plural="%1 definitions contain all the words"}
              One definition contains all the words{/t}
              {if $extra.numDefinitionsFullText > count($results)}
                {t 1=count($results)}(at most %1 shown){/t}
              {/if}
            {else}
              {t}No definitions contain all the words{/t}
            {/if}
          </h5>

          {if !empty($extra.stopWords)}
            <p class="text-warning">
              {t}The following words were ignored because they are too common:{/t}
              <strong>
                {' '|implode:$extra.stopWords|escape}
              </strong>
            </p>
          {/if}

          {include "search/definitionList.tpl" categories=false}
        {/if}

        {* entry ID search *}
      {elseif $searchType == $smarty.const.SEARCH_ENTRY_ID}

        {if !count($entries)}
          <h5>{t}There is no entry with the given ID.{/t}</h5>
        {else}

          <h5>
            {capture "entryText"}
              {include "bits/entry.tpl" entry=$entries[0] tagList=true}
            {/capture}

            {if count($results)}
              {t
                count=count($results)
                1=count($results)
                2=$smarty.capture.entryText
                plural="%1 definitions for %2"}
              One definition for %2{/t}
            {else}
              {t 1=$smarty.capture.entryText}No definitions for %1{/t}
            {/if}
          </h5>

          {include "search/sourceTypes.tpl"}
          {include "search/missingDefinitionWarnings.tpl"}
          {include "search/definitionList.tpl"}
        {/if}

        {* regular expression search *}
      {elseif $searchType == $smarty.const.SEARCH_REGEXP}
        {capture "common"}
        {t}for{/t} <strong>{$cuv}</strong>
        {/capture}

        <h5>
          {if count($lexemes)}
            {t
              count=$extra.numLexemes
              1=$extra.numLexemes
              2=$cuv
              plural="%1 results for <strong>%2</strong>"}
            One result for <strong>%2</strong>{/t}
          {else}
            {t 1=$cuv}No results for <strong>%1</strong>{/t}
          {/if}

          {if $extra.numLexemes > count($lexemes)}
            {t 1=count($lexemes)}(at most %1 shown){/t}
          {/if}
        </h5>

        {if !count($lexemes) && $sourceId}
          {include "search/extendToAllSources.tpl"}
        {/if}

        {include "search/lexemeList.tpl"}

        {* normal search (inflected form search) *}
      {elseif $searchType == $smarty.const.SEARCH_INFLECTED}

        {if count($entries) > 1}
          <h5>
            {* this is always plural, but still needs to be localized *}
            {t count=count($entries) 1=count($entries) plural="%1 entries"}
            One entry{/t}
          </h5>

          {include "search/entryToc.tpl"}
        {else}
          {capture "entryText"}
          {include "bits/entry.tpl" entry=$entries[0] tagList=true}
          {/capture}

          <h5>
            {if count($results)}
              {t
                count=count($results)
                1=count($results)
                2=$smarty.capture.entryText
                plural="%1 definitions for %2"}
              One definition for %2{/t}
            {else}
              {t 1=$smarty.capture.entryText}No definitions for %1{/t}
            {/if}
          </h5>
          {include "search/sourceTypes.tpl"}

          {if !count($results) && count($entries) && $sourceId}
            {include "search/extendToAllSources.tpl"}
          {/if}
        {/if}

        {* another <h5> for the definition list, if needed *}
        {if (count($entries) > 1) && count($results)}
          <h5>
            {t
              count=count($results)
              1=count($results)
              plural="%1 definitions"}
            One definition{/t}
          </h5>
          {include "search/sourceTypes.tpl"}
        {/if}

        {include "search/missingDefinitionWarnings.tpl"}

        {include "search/definitionList.tpl"}

        {* multiword search *}
      {elseif $searchType == $smarty.const.SEARCH_MULTIWORD}
        <h5>
          {if count($results)}
            {t
              count=count($results)
              1=count($results)
              plural="%1 definitions match at least two words"}
            One definition matches at least two words{/t}
          {else}
            {t}No definitions match at least two words{/t}
          {/if}
        </h5>

        <p class="text-warning">
          {t}If the results are inadequate, you can look up individual words or you can search{/t}
          <a href="{Config::URL_PREFIX}text/{$cuv|escape:url}">{t}full-text{/t}</a>.
        </p>

        {if !empty($extra.ignoredWords)}
          <p class="text-warning">
            {t}At most 5 words are allowed. The following words were ignored:{/t}
            <strong>
              {' '|implode:$extra.ignoredWords|escape}
            </strong>
          </p>
        {/if}

        {include "search/definitionList.tpl" categories=false}

        {* approximate search *}
      {elseif $searchType == $smarty.const.SEARCH_APPROXIMATE}
        {if count($entries)}
          <h5>
            {t escape="0" 1=$cuv}The word <strong>%1</strong> is not in the dictionary.
            Here are some suggestions:{/t}
          </h5>

          {include "search/entryList.tpl"}
        {else}
          <h5>{t escape="no" 1=$cuv}No results for <strong>%1</strong>{/t}</h5>
        {/if}

      {/if}

    </div>

    {* paradigm tab *}
    {if $searchParams.paradigm}
      <div
        role="tabpanel"
        class="tab-pane {if $activeTab == Tab::T_PARADIGM}show active{/if}"
        id="tab_{Tab::T_PARADIGM}">

        {foreach $entries as $e}
          {include "bits/multiParadigm.tpl" entry=$e}
        {/foreach}

        {if $hasUnrecommendedForms}
          <div class="notRecommendedLegend">
            * {t}unrecommended or incorrect form{/t} –
            <a id="toggleNotRecommended"
              href="#"
              class="doubleText"
              data-other-text="({t}hide{/t})">
              ({t}show{/t})
            </a>
          </div>
        {/if}

        {if $hasElisionForms}
          {if User::can(User::PRIV_EDIT)}
            {capture "text1"}{t}hide{/t}{/capture}
            {capture "text2"}{t}show{/t}{/capture}
          {else}
            {capture "text1"}{t}show{/t}{/capture}
            {capture "text2"}{t}hide{/t}{/capture}
          {/if}
          <div class="elisionLegend">
            * {t}elisions and long verb forms{/t} –
            <a id="toggleElision"
              href="#"
              class="doubleText"
              data-other-text="({$smarty.capture.text2})">
              ({$smarty.capture.text1})
            </a>
          </div>
        {/if}
      </div>
    {/if}

    {* tree tab *}
    {if count($trees)}
      <div
        role="tabpanel"
        class="tab-pane {if $activeTab == Tab::T_TREES}show active{/if}"
        id="tab_{Tab::T_TREES}">
        {include "search/trees.tpl"}
      </div>
    {/if}

    {* gallery tab *}
    {if count($images)}
      <div
        role="tabpanel"
        class="tab-pane {if $activeTab == Tab::T_GALLERY}show active{/if}"
        id="tab_{Tab::T_GALLERY}">
        {include "search/gallery.tpl"}
      </div>
    {/if}

    {* articles tab *}
    {if count($wikiArticles)}
      <div
        role="tabpanel"
        class="tab-pane {if $activeTab == Tab::T_ARTICLES}show active{/if}"
        id="tab_{Tab::T_ARTICLES}">
        {include "search/wikiArticles.tpl"}
      </div>
    {/if}

    {* pronunciation tab *}
    {if count($pronunciations)}
    {* only special people are allowed ;) *}
    {* if User::can(User::PRIV_PLUGIN) *}
      <div
        role="tabpanel"
        class="tab-pane {if $activeTab == Tab::T_PRONUNCIATION}show active{/if}"
        id="tab_{Tab::T_PRONUNCIATION}">
        {include "search/pronunciation.tpl" searchTerm=$searchTerm}
      </div>
    {* /if *}
    {/if}
  </div>

  <div id="tabAdvertiserContent" style="display: none">
    {t 1=Router::link('user/preferences')}
    <b>The Synthesis tab</b> shows a condensed list of definitions compiled by
    the dexonline team. The original definitions are available on the <b>Definitions</b>
    tab. You can reorder tabs on your <a href="%1">preferences</a> page.
    {/t}
  </div>
{/block}
