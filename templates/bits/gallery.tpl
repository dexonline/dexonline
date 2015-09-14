{include file="bits/prototypes.ihtml"}
<div id="gallery">
  {foreach from=$images item=i}
    <a class="gallery" href="{$i->getImageUrl()}" data-visual-id="{$i->id}" title="Imagine: {$i->getTitle()}">
      <img src="{$i->getThumbUrl()}" alt="imagine pentru acest cuvânt"/>
    </a>
  {/foreach}
</div>
