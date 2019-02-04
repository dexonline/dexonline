<div class="defWrapper">
  <p>
    <strong>{$m->breadcrumb}</strong>
    {HtmlConverter::convert($m)}
    <div class="small text-muted">
      <a href="{Config::URL_PREFIX}editTree.php?id={$m->treeId}">
        editează
      </a>
    </div>
  </p>
</div>
