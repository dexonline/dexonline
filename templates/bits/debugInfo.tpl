{if DebugInfo::isEnabled()}
  <hr>

  <div class="container">
    <p>
      Pagină generată în <strong>{DebugInfo::getRunningTimeInMillis()} ms.</strong>
    </p>

    <div class="card mb-3">
      <div class="card-header">Interogări executate cu DB::execute()</div>
      <div class="card-body">
        {foreach DebugInfo::$debugInfo as $line}
          {$line|escape}<br>
        {/foreach}
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header">Interogări Idiorm ({ORM::get_query_log()|@count})</div>
      <div class="card-body">
        {foreach ORM::get_query_log() as $query}
          Idiorm query: {$query}<br>
          {foreachelse}
            Nu există interogări Idiorm. Ați decomentat linia
            <code>ORM::configure('logging', true);</code> în <code>lib/DB.php</code>?
          {/foreach}
      </div>
    </div>
  </div>
{/if}
