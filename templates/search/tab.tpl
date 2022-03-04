{**
   Displays a tab header. Mandatory arguments:
   $activeTab: active tab
   $prominent: whether to make the title more prominent
   $tab: tab to display
  **}
<li class="nav-item" role="presentation">
  <button
    aria-controls="tab_{$tab}"
    aria-selected="{if $tab == $activeTab}true{else}false{/if}"
    class="nav-link {if $prominent}nav-notice{/if} {if $tab == $activeTab}active{/if}"
    data-bs-target="#tab_{$tab}"
    data-bs-toggle="tab"
    data-permalink="{Tab::getPermalink($tab)}"
    role="tab"
    type="button">

    {$title}

    {if $count}({$count}){/if}
  </button>
</li>
