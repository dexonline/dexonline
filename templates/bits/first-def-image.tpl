<script>
  document.addEventListener("DOMContentLoaded", () => {
    const wrappers = document.getElementsByClassName("defWrapper");

    var img = '<img class="img-fluid mx-auto d-block" src="{$imgsrc}" alt="{$imgalt}" title="{$imgtit}">';
    {if $imglnk}
    img = '<a href="{$imglnk}">' + img + '</a>';
    {/if}
    const html = '<div class="card-body pb-0">' + img + '</div>';

    wrappers[0].insertAdjacentHTML("afterend", html);

    const container = document.getElementsByClassName("meaningContainer");
    container[0].insertAdjacentHTML("afterend", html);
  });
</script>
