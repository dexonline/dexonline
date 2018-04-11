<div id="pgWrapper" style="height: 90px">

  <script>
    var pgWrapper = document.getElementById('pgWrapper');
    var clientWidth = getWidth();
  </script>

  {foreach $cfg.banner.pgDivId as $i => $divId}
    {$width=$cfg.banner.pgWidth.$i}
    {$height=$cfg.banner.pgHeight.$i}
    {$clientMinWidth=$cfg.banner.pgClientMinWidth.$i}
    {$clientMaxWidth=$cfg.banner.pgClientMaxWidth.$i}
    <div
      id='{$divId}'
      style='height:{$height}px; width:{$width}px; margin: 0 auto;'>
      <script>
        var d = document.getElementById('{$divId}');
        if ((clientWidth >= {$clientMinWidth}) &&
            (clientWidth <= {$clientMaxWidth})) {
          googletag.cmd.push(function() { googletag.display('{$divId}'); });
          pgWrapper.style.height = '{$height}px';
        } else {
          d.style.display = 'none';
        }
      </script>
    </div>
  {/foreach}

</div>
