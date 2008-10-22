<?php /* Smarty version 2.6.18, created on 2008-01-23 13:06:39
         compiled from common/viewModels.ihtml */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'common/viewModels.ihtml', 12, false),)), $this); ?>
<br/>

<script type="text/javascript" src="js/flex.js"></script>

<form action="viewModels.php" method="get">
  Versiunea LOC:
  <select name="locVersion" id="locVersionListId"
          onchange="return updateModelTypeList(this, 'modelTypeListId');">
    <?php $_from = $this->_tpl_vars['locVersions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['lv']):
?>
      <option value="<?php echo $this->_tpl_vars['lv']->name; ?>
"
              <?php if ($this->_tpl_vars['lv']->name == $this->_tpl_vars['selectedLocVersion']): ?>selected="selected"<?php endif; ?>>
        <?php echo $this->_tpl_vars['lv']->name; ?>
 (<?php echo ((is_array($_tmp=$this->_tpl_vars['lv']->freezeTimestamp)) ? $this->_run_mod_handler('date_format', true, $_tmp, "%d %B %Y") : smarty_modifier_date_format($_tmp, "%d %B %Y")); ?>
)
      </option>
    <?php endforeach; endif; unset($_from); ?>
  </select>

  &nbsp;&nbsp; Model:
  <select name="modelType" id="modelTypeListId"
          onchange="return updateModelListWithLocVersion(this, 'modelListId')">
    <?php $_from = $this->_tpl_vars['modelTypes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['mt']):
?>
      <option value="<?php echo $this->_tpl_vars['mt']->value; ?>
"
              <?php if ($this->_tpl_vars['mt']->value == $this->_tpl_vars['selectedModelType']): ?>selected="selected"<?php endif; ?>>
        <?php echo $this->_tpl_vars['mt']->value; ?>
 (<?php echo $this->_tpl_vars['mt']->description; ?>
)
      </option>
    <?php endforeach; endif; unset($_from); ?>
  </select>

  &nbsp;&nbsp; Număr:
  <select name="modelNumber" id="modelListId">
    <option value="-1">Toate</option>
    <?php $_from = $this->_tpl_vars['models']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['m']):
?>
      <option value="<?php echo $this->_tpl_vars['m']->number; ?>
"
        <?php if ($this->_tpl_vars['m']->number == $this->_tpl_vars['selectedModelNumber']): ?>selected="selected"<?php endif; ?>
        ><?php echo $this->_tpl_vars['m']->number; ?>
<?php if (! $this->_tpl_vars['m']->id): ?>*<?php endif; ?> (<?php echo $this->_tpl_vars['m']->exponent; ?>
)
      </option>
    <?php endforeach; endif; unset($_from); ?>
  </select>
  <input type="submit" name="submitButton" value="Arată"
         onclick="return hideSubmitButton(this)"/>
</form>

<?php if ($this->_tpl_vars['modelsToDisplay']): ?>
  <?php $_from = $this->_tpl_vars['modelsToDisplay']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['m']):
?>
    <div class="scrabbleModelName">
      <?php echo $this->_tpl_vars['m']->number; ?>
. <?php echo $this->_tpl_vars['m']->exponent; ?>

    </div>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/paradigm.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexems'][$this->_tpl_vars['i']],'wlMap' => $this->_tpl_vars['paradigms'][$this->_tpl_vars['i']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
  <?php endforeach; endif; unset($_from); ?>
<?php endif; ?>