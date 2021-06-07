{extends "layout-admin.tpl"}

{block "title"}Alegere sufix{/block}

{block "content"}

  <h3>Alegere sufix pentru etichetare în masă</h3>

  <p>
    Mai întâi, alegeți sufixul de examinat. În paranteză este trecut
    numărul de lexeme care au sufixul ales.
  </p>

  <form class="row row-cols-sm-auto g-2" action="{Router::link('lexeme/bulkLabel')}">
    <div class="col-12">
      <label class="col-form-label">sufix</label>
    </div>

    <div class="col-12">
      <select class="form-select" name="suffix">
        {foreach $stats as $stat}
          <option value="{$stat.0}">{$stat.0} ({$stat.1})</option>
        {/foreach}
      </select>
    </div>

    <div class="col-12">
      <button type="submit" class="btn btn-primary">
        continuă
      </button>
    </div>
  </form>
{/block}
