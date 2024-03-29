{* Argument: $lexeme *}
{$ifMap=$lexeme->loadInflectedFormMap()}
<table class="lexeme">
  <tr>
    <td colspan="2">
      {include "bits/lexeme.tpl" lexeme=$lexeme}
    </td>
    <td class="inflection">{t}masculine{/t}</td>
    <td class="inflection">{t}feminine{/t}</td>
  </tr>
  <tr>
    <td rowspan="2" class="inflection">{t}nominative-accusative{/t}</td>
    <td class="inflection">{t}singular{/t}</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[1]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[5]|default:[]}
    </td>
  </tr>
  <tr>
    <td class="inflection">{t}plural{/t}</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[3]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[7]|default:[]}
    </td>
  </tr>
  <tr>
    <td rowspan="2" class="inflection">{t}genitive-dative{/t}</td>
    <td class="inflection">{t}singular{/t}</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[2]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[6]|default:[]}
    </td>
  </tr>
  <tr>
    <td class="inflection">{t}plural{/t}</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[4]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[8]|default:[]}
    </td>
  </tr>
</table>
