{* Argument: $lexeme *}
{$ifMap=$lexeme->loadInflectedFormMap()}
<table class="lexeme">
  <tr>
    <td colspan="2" rowspan="2">
      {include "bits/lexeme.tpl" lexeme=$lexeme}
    </td>
    <td colspan="2" class="inflection">{t}masculine{/t}</td>
    <td colspan="2" class="inflection">{t}feminine{/t}</td>
  </tr>
  <tr>
    <td class="inflection">{t}no article{/t}</td>
    <td class="inflection">{t}def. article{/t}</td>
    <td class="inflection">{t}no article{/t}</td>
    <td class="inflection">{t}def. article{/t}</td>
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
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[9]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[13]|default:[]}
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
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[11]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[15]|default:[]}
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
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[10]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[14]|default:[]}
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
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[12]|default:[]}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[16]|default:[]}
    </td>
  </tr>
  <tr>
    <td rowspan="2" class="inflection">{t}vocative{/t}</td>
    <td class="inflection">{t}singular{/t}</td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[17]|default:[]}
    </td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[19]|default:[]}
    </td>
  </tr>
  <tr>
    <td class="inflection">{t}plural{/t}</td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[18]|default:[]}
    </td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[20]|default:[]}
    </td>
  </tr>
</table>
