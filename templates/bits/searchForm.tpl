{assign var="advancedSearch" value=$advancedSearch|default:false}
{assign var="cuv" value=$cuv|default:''}
{assign var="text" value=$text|default:false}

<section class="row search">
  <div class="col-md-12">
    <form action="{$wwwRoot}search.php"
          name="frm"
          onsubmit="return searchSubmit()"
          id="searchForm">
      <div class="row">
        <div class="col-sm-12 col-sm-11">
          <div class="input-group">
            <div class="form-group has-feedback">
              <input type="text"
                     class="form-control"
                     name="cuv"
                     placeholder="{t}word{/t}"
                     id="searchField"
                     value="{$cuv|escape}"
                     maxlength="50">
              <span id="searchClear"
                    class="glyphicon glyphicon-remove form-control-feedback">
              </span>
            </div>
            <span class="input-group-btn">
              <button type="submit" value="caută" id="searchButton" class="btn btn-primary">
                <span class="glyphicon glyphicon-search"></span>
                {t}search{/t}
              </button>
            </span>
          </div>
        </div>
        <div class="col-sm-0 col-md-1">
          <a href="#" id="advancedAnchor" onclick="return toggle('advSearch')">
            {t}options{/t}
          </a>
        </div>
      </div>

      <div class="row" id="advSearch" {if !$advancedSearch}style="display: none"{/if}>

        <div class="col-md-6">
          {include "bits/sourceDropDown.tpl" urlName=1}
        </div>

        <div class="checkbox col-md-6">
          <label>
            <input type="checkbox"
                   name="text"
                   {if $text}checked{/if}>
            {t}Full-text search{/t}
          </label>

          <a href="https://wiki.dexonline.ro/wiki/Ajutor_pentru_căutare"
             class="pull-right"
             target="_blank">
            <i class="glyphicon glyphicon-question-sign"></i>
            {t}help{/t}
          </a>
        </div>
      </div>
    </form>

    {if $cfg.search.acEnable}
      <div id="autocompleteEnabled" data-min-chars="{$cfg.search.acMinChars}"></div>
    {/if}
  </div>
</section>
