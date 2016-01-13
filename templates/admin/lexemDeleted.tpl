Lexemul {include file="bits/lexemName.tpl" lexem=$lexem} a fost șters. Puteți vizita unul dintre omonimele listate mai jos sau merge înapoi
la <a href="../admin">pagina moderatorului</a>.
<br/><br/>

{foreach from=$homonyms item=h key=i}
  {if $i}|{/if}
  {include file="bits/lexemLink.tpl" lexem=$h}
{/foreach}
