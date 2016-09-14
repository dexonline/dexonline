{extends "layout-admin.tpl"}

{block "title"}Alegere sufix{/block}

{block "content"}

  <h3>Alegere sufix pentru etichetare în masă</h3>

  <p>
    Mai întâi, alegeți sufixul de examinat. În paranteză este trecut
    numărul de lexeme care au sufixul ales.
  </p>

  <form class="form-inline" action="bulkLabel.php">
    <div class="form-group">
      <label>sufix</label>

      <select class="form-control" name="suffix">
        {foreach $stats as $stat}
          <option value="{$stat.0}">{$stat.0} ({$stat.1})</option>
        {/foreach}
      </select>

      <button type="submit" class="btn btn-primary" name="ignored">
        continuă
      </button>
    </div>
  </form>
{/block}
