{assign var="name" value=$name|default:'source'}
{assign var="skipAnySource" value=$skipAnySource|default:''}
{assign var="src_selected" value=$src_selected|default:null}
{assign var="urlName" value=$urlName|default:false}
{assign var="autosubmit" value=$autosubmit|default:false}
<select name="{$name}"
        id="sourceDropDown"
        class="form-control"
        {if $autosubmit}onchange="this.form.submit();"{/if}>
  {if !$skipAnySource}
    <option value="">Toate sursele</option>
  {/if}
  {foreach from=$sources item=source}
    {if $urlName}
      {assign var="submitValue" value=$source->urlName}
    {else}
      {assign var="submitValue" value=$source->id}
    {/if}
    {if $source->isOfficial != 3}
      <option value="{$submitValue}"
              {if $src_selected == $source->id}selected="selected"{/if}
              >{$source->shortName|escape}</option>
    {else}
      {if $sUser}
        {if $sUser->moderator & $smarty.const.PRIV_VIEW_HIDDEN}
          <option value="{$submitValue}"
                  {if $src_selected == $source->id}selected="selected"{/if}
                  >{$source->shortName|escape}</option>
        {/if}
      {/if}
    {/if}
  {/foreach}
</select>
