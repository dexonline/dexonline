<div class="defWrapper">
  <p>
    <strong>{$m->breadcrumb}</strong>
    {HtmlConverter::convert($m)}
    <div class="small text-muted">
      <a href="{Router::link('tree/edit')}?id={$m->treeId}">
        editează
      </a>
    </div>
  </p>
</div>
