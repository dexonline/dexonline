<?php /* Smarty version 2.6.18, created on 2007-10-05 14:59:50
         compiled from flex/editModel.ihtml */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'flex/editModel.ihtml', 37, false),array('modifier', 'count', 'flex/editModel.ihtml', 104, false),array('function', 'counter', 'flex/editModel.ihtml', 118, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link href="../styles/flex.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $this->_tpl_vars['cssRoot']; ?>
/paradigm.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="../js/dex.js"></script>
    <title>DEX | Editare model: <?php echo $this->_tpl_vars['modelType']; ?>
<?php echo $this->_tpl_vars['modelNumber']; ?>
</title>
  </head>
  
  <body>
    <?php $this->assign('title', "Editare model: ".($this->_tpl_vars['modelType']).($this->_tpl_vars['modelNumber'])); ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/header.ihtml", 'smarty_include_vars' => array('title' => ($this->_tpl_vars['title']))));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/recentlyVisited.ihtml", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/errorMessage.ihtml", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

    <?php if ($this->_tpl_vars['wasPreviewed'] && count ( $this->_tpl_vars['errorMessage'] ) == 0): ?>
      Examinaţi modificările afişate mai jos (dacă există) şi, dacă totul
      arată normal, apăsaţi butonul "Salvează". Dacă nu, continuaţi editarea
      şi apăsaţi din nou butonul "Testează".
      <br/><br/>
    <?php endif; ?>

    <form action="editModel.php" method="post">
      <input type="hidden" name="modelType" value="<?php echo $this->_tpl_vars['modelType']; ?>
"/>
      <input type="hidden" name="modelNumber" value="<?php echo $this->_tpl_vars['modelNumber']; ?>
"/>

      <table class="editModel">
        <tr>
          <td>
            Număr model
            <span class="small">(poate conţine orice caractere)</span>
          </td>
          <td class="input">
            <input type="text" name="newModelNumber"
                   value="<?php echo ((is_array($_tmp=$this->_tpl_vars['newModelNumber'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"/>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'modelEdit')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td>Descriere</td>
          <td class="input">
            <input type="text" name="newDescription"
                   value="<?php echo ((is_array($_tmp=$this->_tpl_vars['newDescription'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"/>
          </td>
        </tr>
        <?php if ($this->_tpl_vars['adjModels']): ?>
          <tr>
            <td>Model de participiu</td>
            <td class="input">
              <select name="newParticipleNumber">
                <?php $_from = $this->_tpl_vars['adjModels']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['m']):
?>
                  <option value="<?php echo $this->_tpl_vars['m']->number; ?>
"
                    <?php if ($this->_tpl_vars['m']->number == $this->_tpl_vars['newParticipleNumber']): ?>selected="selected"<?php endif; ?>
                    ><?php echo $this->_tpl_vars['m']->number; ?>
<?php if (! $this->_tpl_vars['m']->id): ?>*<?php endif; ?> (<?php echo $this->_tpl_vars['m']->exponent; ?>
)
                  </option>
                <?php endforeach; endif; unset($_from); ?>
              </select>
            </td>
          </tr>
        <?php endif; ?>
        <tr class="exponent">
          <td>Exponent</td>
          <td class="input">
            <input type="text" name="newExponent" value="<?php echo $this->_tpl_vars['newExponent']; ?>
"/>
          </td>
        </tr>
        <?php $_from = $this->_tpl_vars['inflections']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['infl']):
?>
          <tr>
  	        <td><?php echo $this->_tpl_vars['infl']->description; ?>
</td>
  	        <td class="input">
  	          <input type="text" name="forms_<?php echo $this->_tpl_vars['infl']->id; ?>
"
  		               value="<?php echo $this->_tpl_vars['inputValues'][$this->_tpl_vars['i']]; ?>
"/>
  	        </td>
  	      </tr>
        <?php endforeach; endif; unset($_from); ?>
      </table>

      <?php if ($this->_tpl_vars['wasPreviewed']): ?>
        <?php if ($this->_tpl_vars['newModelNumber'] != $this->_tpl_vars['modelNumber'] || $this->_tpl_vars['newExponent'] != $this->_tpl_vars['exponent'] || $this->_tpl_vars['newDescription'] != $this->_tpl_vars['description'] || $this->_tpl_vars['newParticipleNumber'] != $this->_tpl_vars['participleNumber']): ?>
          <h3>Schimbări globale:</h3>

          <ul>
            <?php if ($this->_tpl_vars['newModelNumber'] != $this->_tpl_vars['modelNumber']): ?>
              <li>Număr de model nou: <?php echo $this->_tpl_vars['newModelNumber']; ?>
</li>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['newExponent'] != $this->_tpl_vars['exponent']): ?>
              <li>Exponent nou: <?php echo $this->_tpl_vars['newExponent']; ?>
</li>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['newDescription'] != $this->_tpl_vars['description']): ?>
              <li>Descriere nouă: <?php echo ((is_array($_tmp=$this->_tpl_vars['newDescription'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</li>
            <?php endif; ?>
            <?php if ($this->_tpl_vars['newParticipleNumber'] != $this->_tpl_vars['participleNumber']): ?>
              <li>Model nou de participiu: A<?php echo $this->_tpl_vars['newParticipleNumber']; ?>
</li>
            <?php endif; ?>
          </ul>
        <?php endif; ?>

        <?php if (count ( $this->_tpl_vars['regenTransforms'] )): ?>
          <h3>Lista de inflexiuni afectate (<?php echo count($this->_tpl_vars['regenTransforms']); ?>
):</h3>
          <ol>
            <?php $_from = $this->_tpl_vars['regenTransforms']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['inflId'] => $this->_tpl_vars['ignored']):
?>
              <li><?php echo $this->_tpl_vars['inflectionMap'][$this->_tpl_vars['inflId']]->description; ?>
</li>
            <?php endforeach; endif; unset($_from); ?>
          </ol>

          <h3>Lexemele afectate (<?php echo count($this->_tpl_vars['lexems']); ?>
) şi noile lor forme:</h3>
    
          <table class="changedForms">
            <tr class="header">
              <td class="lexem">Lexem</td>
              <td class="model">Model</td>
              <?php $_from = $this->_tpl_vars['regenTransforms']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['ignored2'] => $this->_tpl_vars['ignored']):
?>
                <td class="forms"><?php echo smarty_function_counter(array('name' => 'otherCounter'), $this);?>
.</td>
              <?php endforeach; endif; unset($_from); ?>
            </tr>
            <tr class="exponent">
              <td class="lexem"><?php echo $this->_tpl_vars['newExponent']; ?>
</td>
              <td class="model">exponent</td>
              <?php $_from = $this->_tpl_vars['regenTransforms']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['inflId'] => $this->_tpl_vars['ignored']):
?>
                <?php $this->assign('variantArray', ($this->_tpl_vars['newForms'][$this->_tpl_vars['inflId']])); ?>
                <td class="forms">
                  <?php $_from = $this->_tpl_vars['variantArray']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['form']):
?><?php if ($this->_tpl_vars['i']): ?>, <?php endif; ?><?php echo $this->_tpl_vars['form']; ?>
<?php endforeach; endif; unset($_from); ?>
                  <?php if (! count ( $this->_tpl_vars['variantArray'] )): ?>&mdash;<?php endif; ?>
                </td>
              <?php endforeach; endif; unset($_from); ?>
            </tr>
            <?php $_from = $this->_tpl_vars['lexems']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['lIndex'] => $this->_tpl_vars['l']):
?>
              <tr>
                <td class="lexem"><?php echo $this->_tpl_vars['l']->form; ?>
</td>
                <td class="model"><?php echo $this->_tpl_vars['l']->modelType; ?>
<?php echo $this->_tpl_vars['l']->modelNumber; ?>
</td>
                <?php $this->assign('inflArray', ($this->_tpl_vars['regenForms'][$this->_tpl_vars['lIndex']])); ?>
                <?php $_from = $this->_tpl_vars['inflArray']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['inflId'] => $this->_tpl_vars['variantArray']):
?>
                  <td class="forms">
                    <?php $_from = $this->_tpl_vars['variantArray']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['form']):
?><?php if ($this->_tpl_vars['i']): ?>, <?php endif; ?><?php echo $this->_tpl_vars['form']; ?>
<?php endforeach; endif; unset($_from); ?>
                    <?php if (! count ( $this->_tpl_vars['variantArray'] )): ?>&mdash;<?php endif; ?>
                  </td>
                <?php endforeach; endif; unset($_from); ?>
              </tr>
            <?php endforeach; endif; unset($_from); ?>
          </table>
        <?php endif; ?>
      <?php endif; ?>

      <?php if (count ( $this->_tpl_vars['participles'] )): ?>
        <h3>Participii regenerate conform modelului
          A<?php echo $this->_tpl_vars['newParticipleNumber']; ?>
:</h3>

        <?php $_from = $this->_tpl_vars['participles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['p']):
?>
          <?php if ($this->_tpl_vars['participleParadigms'][$this->_tpl_vars['i']]): ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/paradigm.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['p'],'wlMap' => $this->_tpl_vars['participleParadigms'][$this->_tpl_vars['i']],'modelType' => 'A')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          <?php endif; ?>
        <?php endforeach; endif; unset($_from); ?>
      <?php endif; ?>

      <br/>
      <input type="submit" name="previewButton" value="Testează"/>
      <!-- We want to disable the button on click, but still submit a value -->
      <input type="hidden" name="confirmButton" value=""/>
      <?php if ($this->_tpl_vars['wasPreviewed'] && count ( $this->_tpl_vars['errorMessage'] ) == 0): ?>
        <input type="submit" name="bogusButton" value="Salvează"
               onclick="this.disabled=true; this.parentNode.confirmButton.value = '1';"/>
      <?php endif; ?>
    </form>
  </body>
</html>