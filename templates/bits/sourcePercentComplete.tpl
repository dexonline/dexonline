{strip}
  {if $s->isUnknownPercentComplete()}
    {assign var="class" value="sourceComplete0"}
    {assign var="range" value="necunoscut"}
  {elseif $s->percentComplete < 5}
    {assign var="class" value="sourceComplete1"}
    {assign var="range" value="< 5%"}
  {elseif $s->percentComplete < 35}
    {assign var="class" value="sourceComplete2"}
    {assign var="range" value="5-35%"}
  {elseif $s->percentComplete < 65}
    {assign var="class" value="sourceComplete3"}
    {assign var="range" value="35-65%"}
  {elseif $s->percentComplete < 95}
    {assign var="class" value="sourceComplete4"}
    {assign var="range" value="65-95%"}
  {else}
    {assign var="class" value="sourceComplete5"}
    {assign var="range" value="> 95%"}
  {/if}
  <div class="sourceComplete {$class}">{$range}</div>
{/strip}
