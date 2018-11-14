{assign var="name" value=$name|default:'source'}
{assign var="skipAnySource" value=$skipAnySource|default:''}
{assign var="sourceId" value=$sourceId|default:null}
{assign var="urlName" value=$urlName|default:false}
{assign var="width" value=$width|default:'100%'}
{assign var="autosubmit" value=$autosubmit|default:false}
<select name="{$name}"
        id="sourceDropDown"
        class="form-control sourceDropDown"
        style="width: {$width}"
        {if $autosubmit}onchange="this.form.submit();"{/if}>
  {if !$skipAnySource}
    <option value="">{'All dictionaries'|_}</option>
  {/if}
  {foreach Source::getAll(Source::SORT_SEARCH) as $source}
    {if $urlName}
      {assign var="submitValue" value=$source->urlName}
    {else}
      {assign var="submitValue" value=$source->id}
    {/if}
    {if ($source->type != Source::TYPE_HIDDEN) ||
        User::can(User::PRIV_VIEW_HIDDEN)}
      <option value="{$submitValue}" {if $sourceId == $source->id}selected{/if}>
        {* All the select2-searchable text must go here, not in data-* attributes *}
        ({$source->shortName|escape})
        {$source->name|escape}
      </option>
    {/if}
  {/foreach}
</select>
