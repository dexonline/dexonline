Definiția conține caractere alăturate din alfabete diferite, evidențiate cu
roșu mai jos. Vă rugăm să încercați să folosiți litere din alfabetul potrivit.

<ul class="voffset3">
  {foreach $conflicts as $c}
    <li>
      litera <strong>{$c.glyph}</strong> din alfabetul {$c.script}
    </li>
  {/foreach}
</ul>

