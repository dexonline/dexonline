{foreach $images as $i}
  <a class="gallery"
    href="{$i->getImageUrl()}"
    data-tag-info="{$i->getTagInfo()|escape}"
    title="Imagine: {$i->getTitle()}">
    <img src="{$i->getThumbUrl()}" alt="imagine pentru acest cuvânt" class="p-1">
  </a>
{/foreach}

<p class="text-muted mt-2">
  {t}click any image for details{/t}
</p>
