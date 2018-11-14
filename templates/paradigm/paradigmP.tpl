{* Argument: $lexeme *}
{assign var=ifMap value=$lexeme->loadInflectedFormMap()}
<table class="lexeme">
  <tr>
    <td colspan="2">
      {include "bits/lexeme.tpl" lexeme=$lexeme}
    </td>
    <td class="inflection">{'masculine'|_}</td>
    <td class="inflection">{'feminine'|_}</td>
  </tr>
  <tr>
    <td rowspan="2" class="inflection">{'nominative-accusative'|_}</td>
    <td class="inflection">{'singular'|_}</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[1]|default:null}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[5]|default:null}
    </td>
  </tr>
  <tr>
    <td class="inflection">{'plural'|_}</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[3]|default:null}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[7]|default:null}
    </td>
  </tr>
  <tr>
    <td rowspan="2" class="inflection">{'genitive-dative'|_}</td>
    <td class="inflection">{'singular'|_}</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[2]|default:null}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[6]|default:null}
    </td>
  </tr>
  <tr>
    <td class="inflection">{'plural'|_}</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[4]|default:null}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[8]|default:null}
    </td>
  </tr>
</table>
