{$config=Config::BANNER_REVIVE}

<ins id="revive-container" data-revive-zoneid="" data-revive-id="{$config.id}"></ins>

<script>
  reviveInit({$config|json_encode});
</script>
