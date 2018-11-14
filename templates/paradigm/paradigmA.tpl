{* Argument: $lexeme *}
{assign var=ifMap value=$lexeme->loadInflectedFormMap()}
<table class="lexeme">
  <tr>
    <td colspan="2" rowspan="2">
      {include "bits/lexeme.tpl" lexeme=$lexeme}
    </td>
    <td colspan="2" class="inflection">{'masculine'|_}</td>
    <td colspan="2" class="inflection">{'feminine'|_}</td>
  </tr>
  <tr>
    <td class="inflection">{'no article'|_}</td>
    <td class="inflection">{'def. article'|_}</td>
    <td class="inflection">{'no article'|_}</td>
    <td class="inflection">{'def. article'|_}</td>
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
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[9]|default:null}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[13]|default:null}
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
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[11]|default:null}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[15]|default:null}
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
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[10]|default:null}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[14]|default:null}
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
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[12]|default:null}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[16]|default:null}
    </td>
  </tr>
  <tr>
    <td rowspan="2" class="inflection">{'vocative'|_}</td>
    <td class="inflection">{'singular'|_}</td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[17]|default:null}
    </td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[19]|default:null}
    </td>
  </tr>
  <tr>
    <td class="inflection">{'plural'|_}</td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[18]|default:null}
    </td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[20]|default:null}
    </td>
  </tr>
</table>
