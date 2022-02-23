{**
   Displays a tab header. Mandatory arguments:
   $activeTab: active tab
   $tab: tab to display
   $target: ID of content element
   $text: tab title
  **}
{$cssClass=$cssClass|default:''}
<li class="nav-item" role="presentation">
  <button
    aria-controls="{$target}"
    aria-selected="{if $tab == $activeTab}true{else}false{/if}"
    class="nav-link {$cssClass} {if $tab == $activeTab}active{/if}"
    data-bs-target="#{$target}"
    data-bs-toggle="tab"
    data-permalink="{Tab::getPermalink($tab)}"
    role="tab"
    type="button">
    {$text}
  </button>
</li>
