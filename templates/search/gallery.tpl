{if !empty($images)}
  {include "bits/galleryCanvas.tpl"}

  <div id="gallery">
    <div class="card">
      <div class="card-header">{t}images{/t}</div>
      <div class="card-body">
        {foreach $images as $i}
          <a class="gallery"
            href="{$i->getImageUrl()}"
            data-visual-id="{$i->id}"
            data-tag-info="{$i->getTagInfo()|escape}"
            title="Imagine: {$i->getTitle()}">
            <img src="{$i->getThumbUrl()}" alt="imagine pentru acest cuvânt">
          </a>
        {/foreach}
      </div>
    </div>
  </div>

{/if}
