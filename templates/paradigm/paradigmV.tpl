{* Argument: $lexeme *}
{$ifMap=$lexeme->loadInflectedFormMap()}
<table class="lexeme">
  <tr>
    <td colspan="2" rowspan="3">
      {include "bits/lexeme.tpl" lexeme=$lexeme}
    </td>
    <td class="inflection">{t}infinitive{/t}</td>
    <td class="inflection">{t}long infinitive{/t}</td>
    <td class="inflection">{t}participle{/t}</td>
    <td class="inflection">{t}gerund{/t}</td>
    <td colspan="2" class="inflection">{t}imperative 2nd person{/t}</td>
  </tr>
  <tr>
    <td rowspan="2" class="form">
      {if count($ifMap[1]|default:[]) > 0}(a){/if}
      {include "bits/ifArray.tpl" ifArray=$ifMap[1]|default:[]}
    </td>
    <td rowspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[2]|default:[]}
    </td>
    <td rowspan="2" class="form">
      {$short=$ifMap[5]|default:[]}
      {$long=$ifMap[6]|default:[]}
      {$participles=array_merge($short, $long)}
      {include "bits/ifArray.tpl" ifArray=$participles}
    </td>
    <td rowspan="2" class="form">
      {$short=$ifMap[7]|default:[]}
      {$long=$ifMap[8]|default:[]}
      {$gerunds=array_merge($short, $long)}
      {include "bits/ifArray.tpl" ifArray=$gerunds}
    </td>
    <td class="inflection">{t}singular{/t}</td>
    <td class="inflection">{t}plural{/t}</td>
  </tr>
  <tr>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[3]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[4]|default:[]}
    </td>
  </tr>

  <tr>
    <td colspan="8" class="spacer"></td>
  </tr>

  <tr>
    <td class="inflection">{t}number{/t}</td>
    <td class="inflection">{t}person{/t}</td>
    <td class="inflection">{t}present{/t}</td>
    <td class="inflection">{t}present subjunctive{/t}</td>
    <td class="inflection">{t}imperfect{/t}</td>
    <td class="inflection">{t}simple perfect{/t}</td>
    <td colspan="2" class="inflection">{t}pluperfect{/t}</td>
  </tr>
  <tr>
    <td rowspan="3" class="inflection">{t}singular{/t}</td>
    <td class="inflection person">{t}1st (eu){/t}</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[9]|default:[]}
    </td>
    <td class="form">
      {if count($ifMap[15]|default:[]) > 0}(să){/if}
      {include "bits/ifArray.tpl" ifArray=$ifMap[15]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[21]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[27]|default:[]}
    </td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[33]|default:[]}
    </td>
  </tr>
  <tr>
    <td class="inflection person">{t}2nd (tu){/t}</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[10]|default:[]}
    </td>
    <td class="form">
      {if count($ifMap[16]|default:[]) > 0}(să){/if}
      {include "bits/ifArray.tpl" ifArray=$ifMap[16]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[22]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[28]|default:[]}
    </td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[34]|default:[]}
    </td>
  </tr>
  <tr>
    <td class="inflection person">{t}3rd (el, ea){/t}</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[11]|default:[]}
    </td>
    <td class="form">
      {if count($ifMap[17]|default:[]) > 0}(să){/if}
      {include "bits/ifArray.tpl" ifArray=$ifMap[17]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[23]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[29]|default:[]}
    </td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[35]|default:[]}
    </td>
  </tr>
  <tr>
    <td rowspan="3" class="inflection">{t}plural{/t}</td>
    <td class="inflection person">{t}1st (noi){/t}</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[12]|default:[]}
    </td>
    <td class="form">
      {if count($ifMap[18]|default:[]) > 0}(să){/if}
      {include "bits/ifArray.tpl" ifArray=$ifMap[18]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[24]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[30]|default:[]}
    </td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[36]|default:[]}
    </td>
  </tr>
  <tr>
    <td class="inflection person">{t}2nd (voi){/t}</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[13]|default:[]}
    </td>
    <td class="form">
      {if count($ifMap[19]|default:[]) > 0}(să){/if}
      {include "bits/ifArray.tpl" ifArray=$ifMap[19]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[25]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[31]|default:[]}
    </td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[37]|default:[]}
    </td>
  </tr>
  <tr>
    <td class="inflection person">{t}3rd (ei, ele){/t}</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[14]|default:[]}
    </td>
    <td class="form">
      {if count($ifMap[20]|default:[]) > 0}(să){/if}
      {include "bits/ifArray.tpl" ifArray=$ifMap[20]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[26]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[32]|default:[]}
    </td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[38]|default:[]}
    </td>
  </tr>
</table>
