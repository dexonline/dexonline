{if !$thumbUrl}
  {assign var="thumbUrl" value="wotd/thumb/generic.jpg"}
{/if}
<img src="{$thumbUrl}" alt="iconiță cuvântul zilei" class="commonShadow" />
<span>
  <label>Cuvântul zilei</label><br />
  {if $wotdDef}
    {include file="bits/wotdurl.tpl" linkText=$wotdDef->lexicon}
  {/if}
</span>
