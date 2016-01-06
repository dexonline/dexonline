{extends file="admin/layout.tpl"}

{block name=title}Forme interzise{/block}

{block name=headerTitle}Forme interzise{/block}

{block name=content}
  <form class="inline" action="#" method="get">
    <input id="inflectedForm" type="text" name="inflectedForm"/>
    <button class="forbiddenFormsAction" data-action="deny">
      <img src="{$imgRoot}/icons/cross.png">
      <span>interzi</span>
    </button>
    <button class="forbiddenFormsAction" data-action="allow">
      <img src="{$imgRoot}/icons/check.png">
      <span>permite</span>
    </button>
  </form>
  <div id="ff-flash">
  </div>

  <script>
    $(forbiddenFormsInit);
  </script>
{/block}
