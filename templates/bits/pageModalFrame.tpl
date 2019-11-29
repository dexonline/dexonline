{if !isset($PAGE_MODAL_ONCE)}
  {$PAGE_MODAL_ONCE=1 scope="global"}
  <script>
    const URL_PATTERN = '{Config::STATIC_URL}' + '{Config::PAGE_URL_PATTERN}';
  </script>

  <div id="pageModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">

    </div>
  </div>

{/if}
