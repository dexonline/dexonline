{if isset($extra.numDefinitions) && ($extra.numDefinitions > count($results))}
  <p>
    <a href="{$smarty.server.REQUEST_URI}/expandat" class="btn btn-default">
      {t}show all definitions{/t}
    </a>
  </p>
{/if}
