{$name=$name|default:'source'}
{$skipAnySource=$skipAnySource|default:''}
{$sourceId=$sourceId|default:null}
{$urlName=$urlName|default:false}
{$width=$width|default:'100%'}
{$autosubmit=$autosubmit|default:false}
{$sources=$sources|default:Source::getAll(Source::SORT_SEARCH)}
<select name="{$name}"
  id="sourceDropDown{$sdPos}"
  class="form-control sourceDropDown"
  style="width: {$width}"
  {if $autosubmit}onchange="this.form.submit();"{/if}>
  {if !$skipAnySource}
    <option value="">{t}All dictionaries{/t}</option>
  {/if}
  {foreach $sources as $source}
    {if $urlName}
      {assign var="submitValue" value=$source->urlName}
    {else}
      {assign var="submitValue" value=$source->id}
    {/if}
    {if !$source->hidden || User::can(User::PRIV_VIEW_HIDDEN)}
      <option value="{$submitValue}" {if $sourceId == $source->id}selected{/if}>
        {* All the select2-searchable text must go here, not in data-* attributes *}
        ({$source->shortName|escape})
        {$source->name|escape}
      </option>
    {/if}
  {/foreach}
</select>
