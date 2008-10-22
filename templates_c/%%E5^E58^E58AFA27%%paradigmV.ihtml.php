<?php /* Smarty version 2.6.19, created on 2008-06-11 11:04:32
         compiled from common/bits/paradigmV.ihtml */ ?>
<?php $this->assign('baseInflId', @INFL_V_OFFSET); ?>
<?php $this->assign('baseTenseId', @INFL_V_PREZ_OFFSET); ?>

<table class="lexem">
  <tr>
    <td colspan="2" rowspan="3">
      <span class="lexemName"
        ><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/lexemName.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></span>
      &nbsp; <?php echo $this->_tpl_vars['title']; ?>

      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/locInfo.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="inflection">infinitiv</td>
    <td class="inflection">infinitiv lung</td>
    <td class="inflection">participiu</td>
    <td class="inflection">gerunziu</td>
    <td colspan="2" class="inflection">imperativ pers. a II-a</td>
  </tr>
  <tr>
    <td rowspan="2" class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId'])); ?>
      <?php if (count ( $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']] ) > 0): ?>(a)<?php endif; ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td rowspan="2" class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+1)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td rowspan="2" class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+3)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td rowspan="2" class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+4)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="inflection">singular</td>
    <td class="inflection">plural</td>
  </tr>
  <tr>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseInflId']+2)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+10)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
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
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+0)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+6)); ?>
      <?php if (count ( $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']] ) > 0): ?>(să)<?php endif; ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+12)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+18)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td colspan="2" class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+24)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
  </tr>
  <tr>
    <td class="inflection person">a II-a (tu)</td>    
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+1)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+7)); ?>
      <?php if (count ( $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']] ) > 0): ?>(să)<?php endif; ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+13)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+19)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td colspan="2" class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+25)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
  </tr>
  <tr>
    <td class="inflection person">a III-a (el, ea)</td>    
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+2)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+8)); ?>
      <?php if (count ( $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']] ) > 0): ?>(să)<?php endif; ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+14)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+20)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td colspan="2" class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+26)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
  </tr>
  <tr>
    <td rowspan="3" class="inflection">plural</td>
    <td class="inflection person">I (noi)</td>    
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+3)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+9)); ?>
      <?php if (count ( $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']] ) > 0): ?>(să)<?php endif; ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+15)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+21)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td colspan="2" class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+27)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
  </tr>
  <tr>
    <td class="inflection person">a II-a (voi)</td>    
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+4)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+10)); ?>
      <?php if (count ( $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']] ) > 0): ?>(să)<?php endif; ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+16)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+22)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td colspan="2" class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+28)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
  </tr>
  <tr>
    <td class="inflection person">a III-a (ei, ele)</td>    
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+5)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+11)); ?>
      <?php if (count ( $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']] ) > 0): ?>(să)<?php endif; ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+17)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+23)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
    <td colspan="2" class="form">
      <?php $this->assign('inflId', ($this->_tpl_vars['baseTenseId']+29)); ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/wlArray.ihtml", 'smarty_include_vars' => array('wlArray' => $this->_tpl_vars['wlMap'][$this->_tpl_vars['inflId']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </td>
  </tr>
</table>