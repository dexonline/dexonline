{extends "layout-admin.tpl"}

{block "title"}Dicționarul vizual{/block}

{block "content"}

  <div class="mb-2 d-flex align-items-center">
    <h3 class="me-3">
      Dicționarul vizual
    </h3>

    <a href="https://wiki.dexonline.ro/wiki/Ghidul_dic%C8%9Bionarului_vizual">
      {include "bits/icon.tpl" i=help}
      ajutor
    </a>
  </div>

  <div id="fileManager"></div>

{/block}
