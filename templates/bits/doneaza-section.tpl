<div class="card card-collapse mt-3">
  <div
    class="card-header collapsed"
    data-bs-toggle="collapse"
    href="#collapse{$id}">

    {include "bits/icon.tpl" i=expand_less class=chevron}

    {$title}
  </div>

  <div id="collapse{$id}" class="card-body collapse">
    {$body}
  </div>
</div>
