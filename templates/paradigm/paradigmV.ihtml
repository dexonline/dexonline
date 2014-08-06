{* Argument: $lexemModel *}
{assign var=ifMap value=$lexemModel->loadInflectedFormMap()}
<table class="lexemModel">
  <tr>
    <td colspan="2" rowspan="3">
      {include file="bits/lexemModel.ihtml" lexemModel=$lexemModel}
    </td>
    <td class="inflection">infinitiv</td>
    <td class="inflection">infinitiv lung</td>
    <td class="inflection">participiu</td>
    <td class="inflection">gerunziu</td>
    <td colspan="2" class="inflection">imperativ pers. a II-a</td>
  </tr>
  <tr>
    <td rowspan="2" class="form">
      {if count($ifMap[1]|default:null) > 0}(a){/if}
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[1]|default:null}
    </td>
    <td rowspan="2" class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[2]|default:null}
    </td>
    <td rowspan="2" class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[5]|default:null}
    </td>
    <td rowspan="2" class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[6]|default:null}
    </td>
    <td class="inflection">singular</td>
    <td class="inflection">plural</td>
  </tr>
  <tr>
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[3]|default:null}
    </td>
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[4]|default:null}
    </td>
  </tr>

  <tr>
    <td colspan="8" class="spacer"></td>
  </tr>

  <tr>
    <td class="inflection">numărul</td>
    <td class="inflection">persoana</td>
    <td class="inflection">prezent</td>
    <td class="inflection">conjunctiv prezent</td>
    <td class="inflection">imperfect</td>
    <td class="inflection">perfect simplu</td>
    <td colspan="2" class="inflection">mai mult ca perfect</td>
  </tr>
  <tr>
    <td rowspan="3" class="inflection">singular</td>
    <td class="inflection person">I (eu)</td>    
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[7]|default:null}
    </td>
    <td class="form">
      {if count($ifMap[13]|default:null) > 0}(să){/if}
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[13]|default:null}
    </td>
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[19]|default:null}
    </td>
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[25]|default:null}
    </td>
    <td colspan="2" class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[31]|default:null}
    </td>
  </tr>
  <tr>
    <td class="inflection person">a II-a (tu)</td>    
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[8]|default:null}
    </td>
    <td class="form">
      {if count($ifMap[14]|default:null) > 0}(să){/if}
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[14]|default:null}
    </td>
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[20]|default:null}
    </td>
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[26]|default:null}
    </td>
    <td colspan="2" class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[32]|default:null}
    </td>
  </tr>
  <tr>
    <td class="inflection person">a III-a (el, ea)</td>    
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[9]|default:null}
    </td>
    <td class="form">
      {if count($ifMap[15]|default:null) > 0}(să){/if}
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[15]|default:null}
    </td>
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[21]|default:null}
    </td>
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[27]|default:null}
    </td>
    <td colspan="2" class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[33]|default:null}
    </td>
  </tr>
  <tr>
    <td rowspan="3" class="inflection">plural</td>
    <td class="inflection person">I (noi)</td>    
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[10]|default:null}
    </td>
    <td class="form">
      {if count($ifMap[16]|default:null) > 0}(să){/if}
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[16]|default:null}
    </td>
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[22]|default:null}
    </td>
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[28]|default:null}
    </td>
    <td colspan="2" class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[34]|default:null}
    </td>
  </tr>
  <tr>
    <td class="inflection person">a II-a (voi)</td>    
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[11]|default:null}
    </td>
    <td class="form">
      {if count($ifMap[17]|default:null) > 0}(să){/if}
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[17]|default:null}
    </td>
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[23]|default:null}
    </td>
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[29]|default:null}
    </td>
    <td colspan="2" class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[35]|default:null}
    </td>
  </tr>
  <tr>
    <td class="inflection person">a III-a (ei, ele)</td>    
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[12]|default:null}
    </td>
    <td class="form">
      {if count($ifMap[18]|default:null) > 0}(să){/if}
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[18]|default:null}
    </td>
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[24]|default:null}
    </td>
    <td class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[30]|default:null}
    </td>
    <td colspan="2" class="form">
      {include file="bits/ifArray.ihtml" ifArray=$ifMap[36]|default:null}
    </td>
  </tr>
</table>
