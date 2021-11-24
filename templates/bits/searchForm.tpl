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
      <div class="input-group">
        <input
          type="text"
          class="form-control"
          name="cuv"
          placeholder="{t}word{/t}"
          id="searchField"
          autofocus
          value="{$cuv|escape}"
          maxlength="50">

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

      <div class="col-lg">
        {include "bits/sourceDropDown.tpl" urlName=1}
      </div>

      <div class="col-lg d-flex align-items-lg-center">
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

  {if Config::SEARCH_AC_ENABLED}
    <div
      id="autocompleteEnabled"
      data-limit="{Config::SEARCH_AC_LIMIT}"
      data-min-chars="{Config::SEARCH_AC_MIN_CHARS}">
    </div>
  {/if}
</section>

{Plugin::notify('afterSearch')}
