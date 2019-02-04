<div id="pgWrapper" style="height: 90px">

  <script>
    var pgWrapper = document.getElementById('pgWrapper');
    var clientWidth = getWidth();
  </script>

  {foreach Config::BANNER_PG as $r}
    <div
      id='{$r.divId}'
      style='height:{$r.height}px; width:{$r.width}px; margin: 0 auto;'>
      <script>
        var d = document.getElementById('{$r.divId}');
        if ((clientWidth >= {$r.clientMinWidth}) &&
            (clientWidth <= {$r.clientMaxWidth})) {
          googletag.cmd.push(function() { googletag.display('{$r.divId}'); });
          pgWrapper.style.height = '{$r.height}px';
        } else {
          d.style.display = 'none';
        }
      </script>
    </div>
  {/foreach}

</div>
