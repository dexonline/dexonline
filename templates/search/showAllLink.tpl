{if isset($extra.numDefinitions) && ($extra.numDefinitions > count($results))}
  <p>
    <a href="{$smarty.server.REQUEST_URI}/expandat" class="btn btn-default">
      arată toate definițiile
    </a>
  </p>
{/if}
