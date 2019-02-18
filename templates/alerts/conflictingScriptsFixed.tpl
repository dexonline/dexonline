Am întîlnit caractere alăturate din alfabete diferite pe care le-am înlocuit
cu succes astfel:

<ul class="voffset3">
  {foreach $fixedConflicts as $c}
    <li>
      litera <strong>{$c.glyphFrom}</strong> din alfabetul {$c.scriptFrom}
      cu litera <strong>{$c.glyphTo}</strong> din alfabetul {$c.scriptTo}
      în contextul <strong>{$c.context}</strong>
    </li>
  {/foreach}
</ul>
