<div class="accordion-item">
  <h2 class="accordion-header" id="heading-{$id}">
    <button
      class="accordion-button collapsed"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#collapse-{$id}"
      aria-expanded="false"
      aria-controls="collapse-{$id}">

      {$title}
    </button>
  </h2>

  <div
    id="collapse-{$id}"
    class="accordion-collapse collapse"
    aria-labelledby="heading-{$id}"
    data-bs-parent="#accordion">
    <div class="accordion-body">
      {$body}
    </div>
  </div>
</div>
