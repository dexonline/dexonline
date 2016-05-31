{* Argument: $lexem *}
{assign var=ifMap value=$lexem->loadInflectedFormMap()}
<table class="lexem">
  <tr>
    <td colspan="2">
      {include "bits/lexem.tpl" lexem=$lexem}
    </td>
    <td class="inflection">nearticulat</td>
    <td class="inflection">articulat</td>
  </tr>
  <tr>
    <td rowspan="2" class="inflection">nominativ-acuzativ</td>
    <td class="inflection">singular</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[1]|default:null}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[5]|default:null}
    </td>
  </tr>
  <tr>
    <td class="inflection">plural</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[3]|default:null}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[7]|default:null}
    </td>
  </tr>
  <tr>
    <td rowspan="2" class="inflection">genitiv-dativ</td>
    <td class="inflection">singular</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[2]|default:null}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[6]|default:null}
    </td>
  </tr>
  <tr>
    <td class="inflection">plural</td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[4]|default:null}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[8]|default:null}
    </td>
  </tr>
  <tr>
    <td rowspan="2" class="inflection">vocativ</td>
    <td class="inflection">singular</td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[9]|default:null}
    </td>
  </tr>
  <tr>
    <td class="inflection">plural</td>
    <td colspan="2" class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[10]|default:null}
    </td>
  </tr>
</table>
