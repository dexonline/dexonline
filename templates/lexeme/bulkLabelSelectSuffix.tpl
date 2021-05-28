{extends "layout-admin.tpl"}

{block "title"}Alegere sufix{/block}

{block "content"}

  <h3>Alegere sufix pentru etichetare în masă</h3>

  <p>
    Mai întâi, alegeți sufixul de examinat. În paranteză este trecut
    numărul de lexeme care au sufixul ales.
  </p>

  <form class="d-flex" action="{Router::link('lexeme/bulkLabel')}">
    <label class="col-form-label">sufix</label>

    <div class="mx-2">
      <select class="form-select" name="suffix">
        {foreach $stats as $stat}
          <option value="{$stat.0}">{$stat.0} ({$stat.1})</option>
        {/foreach}
      </select>
    </div>

    <button type="submit" class="btn btn-primary">
      continuă
    </button>
  </form>
{/block}
