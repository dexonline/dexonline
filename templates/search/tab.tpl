{**
   Displays a tab header. Mandatory arguments:
   $activeTab: active tab
   $tab: tab to display
   $target: ID of content element
  **}
{$count=$count|default:0}    {* if non-zero, display it in parentheses *}
{$notice=$notice|default:''} {* make the nav slightly more prominent *}
{$title=$title|default:''}   {* override tab name *}
<li class="nav-item" role="presentation">
  <button
    aria-controls="{$target}"
    aria-selected="{if $tab == $activeTab}true{else}false{/if}"
    class="nav-link {if $notice}nav-notice{/if} {if $tab == $activeTab}active{/if}"
    data-bs-target="#{$target}"
    data-bs-toggle="tab"
    data-permalink="{Tab::getPermalink($tab)}"
    role="tab"
    type="button">

    {if $title}
      {$title}
    {else}
      {Tab::getName($tab)}
    {/if}

    {if $count}({$count}){/if}
  </button>
</li>
