<a
  href="{Router::link('wote/view')}/{$exprId}"
  class="widget wotd d-flex flex-md-column flex-xl-row">
  <div class="flex-grow-1">
    <h4>Expresii vizuale</h4><br>
      {if $exprTitle}
        <span class="widget-value">{$exprTitle}</span>
      {/if}
  </div>
  <div>
    <img src="{$thumbUrlE}" alt="iconiță cuvântul zilei" class="widget-icon">
  </div>
</a>
