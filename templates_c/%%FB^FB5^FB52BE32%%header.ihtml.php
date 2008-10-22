<?php /* Smarty version 2.6.18, created on 2007-10-05 12:47:53
         compiled from admin/header.ihtml */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', 'admin/header.ihtml', 4, false),)), $this); ?>
<div class="header">
  <div class="title">
    <?php echo $this->_tpl_vars['title']; ?>

    <?php if ($this->_tpl_vars['items']): ?>(<?php echo count($this->_tpl_vars['items']); ?>
)<?php endif; ?>
  </div>
  <?php if (! $this->_tpl_vars['noLinks']): ?>
    <div class="links">
      <a href="../">DEX online</a> |
      <a href="../admin/">Pagina moderatorului</a> |
      <a href="#" onclick="return adminHelpWindow();">Ajutor</a>
    </div>
  <?php endif; ?>
  <div style="clear: both;"></div>
</div>