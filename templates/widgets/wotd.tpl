{if !$thumbUrl}
  {assign var="thumbUrl" value="wotd/thumb/generic.jpg"}
{/if}
<img src="{$thumbUrl}" alt="iconiță cuvântul zilei" class="commonShadow" />
<span>
  <label>Cuvântul zilei</label><br />
  {include file="bits/wotdurl.tpl" title=$title}
</span>
