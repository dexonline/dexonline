<script
  type="text/javascript"
  src="//dsh7ky7308k4b.cloudfront.net/publishers/dexonlinero.min.js"
></script>

<script async='async' src='https://www.googletagservices.com/tag/js/gpt.js'></script>
<script>
  var googletag = googletag || {};
  googletag.cmd = googletag.cmd || [];

  {foreach $cfg.banner.pgDivId as $i => $divId}
  {$slot=$cfg.banner.pgSlot.$i}
  {$width=$cfg.banner.pgWidth.$i}
  {$height=$cfg.banner.pgHeight.$i}
  googletag.cmd.push(function() {
    googletag.defineSlot('{$slot}',
                         [{$width}, {$height}],
                         '{$divId}')
             .addService(googletag.pubads());
    googletag.enableServices();
  });
  {/foreach}
</script>
