{$dfpData=Config::BANNER_DFP}
{$data=$dfpData[$pageType]}

<script async='async' src='https://www.googletagservices.com/tag/js/gpt.js'></script>
<script>
  var googletag = googletag || {};
  googletag.cmd = googletag.cmd || [];
</script>

<script>
  googletag.cmd.push(function() {
    googletag.defineSlot(
      '{$data.slot}',
      [[320, 100], [468, 60], [728, 90]],
      'div-gpt-ad-{$data.id}-0'
    ).addService(googletag.pubads());
    googletag.pubads().enableSingleRequest();
    googletag.enableServices();
  });
</script>

<div id='div-gpt-ad-{$data.id}-0'>
  <script>
    googletag.cmd.push(function() { googletag.display('div-gpt-ad-{$data.id}-0'); });
  </script>
</div>
