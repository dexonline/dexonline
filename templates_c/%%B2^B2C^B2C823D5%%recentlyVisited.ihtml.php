<?php /* Smarty version 2.6.18, created on 2007-10-05 12:47:53
         compiled from admin/recentlyVisited.ihtml */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'admin/recentlyVisited.ihtml', 6, false),)), $this); ?>
<?php if (count ( $this->_tpl_vars['recentLinks'] )): ?>
  <div class="adminRecentlyVisited">
    <div class="title">Pagini recent vizitate</div>
  
    <?php $_from = $this->_tpl_vars['recentLinks']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['rl']):
?>
      <a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['rl']->url)) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
"><?php echo $this->_tpl_vars['rl']->text; ?>
</a><br/>
    <?php endforeach; endif; unset($_from); ?>
  </div>
<?php endif; ?>