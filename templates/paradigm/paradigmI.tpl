{* Argument: $lexeme *}
{$ifMap=$lexeme->loadInflectedFormMap()}

<table class="lexeme">
  <tr>
    <td>
      {include "bits/lexeme.tpl" lexeme=$lexeme}
    </td>
    <td class="form">
      {include "bits/ifArray.tpl" ifArray=$ifMap[1]|default:null}
    </td>
  </tr>
</table>
