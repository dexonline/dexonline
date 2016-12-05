{if !empty($images)}
  {include "bits/galleryCanvas.tpl"}
  <div id="gallery">
    {foreach $images as $i}
      <a class="gallery"
         href="{$i->getImageUrl()}"
         data-visual-id="{$i->id}"
         title="Imagine: {$i->getTitle()}">
        <img src="{$i->getThumbUrl()}" alt="imagine pentru acest cuvânt">
      </a>
    {/foreach}
  </div>
{/if}
