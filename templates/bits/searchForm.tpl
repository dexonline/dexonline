{assign var="cuv" value=$cuv|default:''}
{assign var="text" value=$text|default:false}

<section class="search">
  <form
    action="{Config::URL_PREFIX}search.php"
    name="frm"
    onsubmit="return searchSubmit()"
    class="mb-4"
    id="searchForm">

    <div class="d-flex align-items-end align-items-md-center flex-column flex-md-row">
      <div class="input-group dropdown">
        <input
          autocomplete="off"
          {* no autofocus here: Chrome complains it conflicts with the focus+select in dex.js *}
          class="dropdown-toggle form-control"
          data-bs-offset="0,0"
          data-bs-toggle="dropdown"
          id="searchField"
          maxlength="50"
          name="cuv"
          placeholder="{t}word{/t}"
          type="text"
          value="{$cuv|escape}">

        {* will be populated in Javascript *}
        {if Config::SEARCH_AC_ENABLED}
          <ul
            class="dropdown-menu"
            data-limit="{Config::SEARCH_AC_LIMIT}"
            data-min-chars="{Config::SEARCH_AC_MIN_CHARS}"
            id="search-autocomplete">
          </ul>
        {/if}

        <button
          id="searchClear"
          class="btn btn-link {if !$cuv}d-none{/if}"
          type="button">
          {include "bits/icon.tpl" i=clear}
        </button>

        <button type="submit" value="caută" id="searchButton" class="btn btn-primary">
          {include "bits/icon.tpl" i=search}
          {t}search{/t}
        </button>
      </div>

      <a href="#" id="advancedAnchor" class="ms-3" onclick="return toggle('advSearch')">
        {t}options{/t}
      </a>
    </div>

    <div
      class="row my-2"
      id="advSearch"
      {if !$advancedSearch}style="display: none"{/if}>

      <div class="col-12 col-lg-6">
        {include "bits/sourceField.tpl"}
      </div>

      <div class="col-12 col-lg-6 d-flex align-items-lg-center">
        {capture "fullTextMessage"}{t}Full-text search{/t}{/capture}
        {include "bs/checkbox.tpl"
          name='text'
          label=$smarty.capture.fullTextMessage
          checked=$text
          divClass='flex-grow-1'}

        <a href="https://wiki.dexonline.ro/wiki/Ajutor_pentru_căutare"
          target="_blank">
          {include "bits/icon.tpl" i=help}
          {t}help{/t}
        </a>
      </div>

    </div>
  </form>

</section>

{Plugin::notify('afterSearch')}
