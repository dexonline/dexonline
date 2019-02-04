{if DebugInfo::isEnabled()}
  <hr>

  <p>
    Pagină generată în <strong>{DebugInfo::getRunningTimeInMillis()} ms.</strong>
  </p>

  <div class="panel panel-default">
    <div class="panel-heading">Interogări executate cu DB::execute()</div>
    <div class="panel-body">
      {foreach DebugInfo::$debugInfo as $line}
        {$line|escape}<br>
      {/foreach}
      </div>
  </div>

  <div class="panel panel-default">
    <div class="panel-heading">Interogări Idiorm</div>
    <div class="panel-body">
      {foreach ORM::get_query_log() as $query}
        Idiorm query: {$query}<br>
      {foreachelse}
        Nu există interogări Idiorm. Ați decomentat linia
        <code>ORM::configure('logging', true);</code> în <code>lib/DB.php</code>?
      {/foreach}
    </div>
  </div>
{/if}
