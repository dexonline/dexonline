{$slotKey="dfp_slot_`$pageType`"}
{$adUnitKey="dfp_id_`$pageType`"}
{$slot=$cfg.banner.$slotKey}
{$adUnit=$cfg.banner.$adUnitKey}

<script async='async' src='https://www.googletagservices.com/tag/js/gpt.js'></script>
<script>
  var googletag = googletag || {};
  googletag.cmd = googletag.cmd || [];
</script>

<script>
  googletag.cmd.push(function() {
    googletag.defineSlot(
      '{$slot}',
      [[320, 100], [468, 60], [728, 90]],
      'div-gpt-ad-{$adUnit}-0'
    ).addService(googletag.pubads());
    googletag.pubads().enableSingleRequest();
    googletag.enableServices();
  });
</script>

<div id='div-gpt-ad-{$adUnit}-0'>
  <script>
    googletag.cmd.push(function() { googletag.display('div-gpt-ad-{$adUnit}-0'); });
  </script>
</div>
