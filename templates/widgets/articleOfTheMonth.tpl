<a
  href="{Router::link('article/view')}/{$articleTitle}"
  class="widget aotm d-flex flex-md-column flex-xl-row">
  <div class="flex-grow-1">
    <h4>{t}article of the month{/t}</h4><br>
    <span class="widget-value">{$articleTitle|urldecode|replace:'_':' '}</span>
  </div>
  <div>
    <img
      alt="{t}article of the month{/t}"
      src="{Config::STATIC_URL}img/wotd/thumb88/misc/papirus.png"
      class="widget-icon">
  </div>
</a>
