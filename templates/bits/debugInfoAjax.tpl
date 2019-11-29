{if DebugInfo::isEnabled()}
  <hr>

  <p>
    Segment ajax generat în <strong>{DebugInfo::getRunningTimeInMillis()} ms.</strong>
    <span class="pull-right text-muted small">{$smarty.now|date_format:"%d.%m.%Y %H:%M:%S"}</span>
  </p>

  <div class="panel panel-info">
    <div class="panel-heading">Interogări executate cu DB::execute()</div>
    <div class="panel-body">
      {foreach DebugInfo::$debugInfo as $line}
        {$line|escape}<br>
      {/foreach}
      </div>
  </div>

  <div class="panel panel-info">
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
