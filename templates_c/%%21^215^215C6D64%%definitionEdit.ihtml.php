<?php /* Smarty version 2.6.18, created on 2007-10-11 08:07:24
         compiled from admin/definitionEdit.ihtml */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'admin/definitionEdit.ihtml', 102, false),array('modifier', 'date_format', 'admin/definitionEdit.ihtml', 135, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link href="../styles/flex.css" rel="stylesheet" type="text/css"/>
    <link href="../styles/lexemPicker.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="../js/dex.js"></script>
    <script type="text/javascript" src="../js/lexemPicker.js"></script>
    <title>DEX | Editare definiţie</title>
  </head>

  <body>
    <?php $this->assign('title', "Editare definiţie: ".($this->_tpl_vars['def']->id)); ?>
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

    <?php if ($this->_tpl_vars['errorMessage']): ?>
      <table class="errorMessage">
        <tr><td><?php echo $this->_tpl_vars['errorMessage']; ?>
</td></tr>
      </table>
    <?php endif; ?>

    <form action="definitionEdit.php" method="post">
      <input type="hidden" name="definitionId" value="<?php echo $this->_tpl_vars['def']->id; ?>
"/>
      <table class="editableFields" id="lexemTable">
        <tr style="display:none">
          <td>Lexem:</td>
          <td>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/lexemPicker.ihtml", 'smarty_include_vars' => array('fieldName' => "lexemName[]",'submitName' => "lexemId[]",'dissociateLink' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        <tr>

        <?php $_from = $this->_tpl_vars['lexems']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['l']):
?>
          <?php if ($this->_tpl_vars['l']->description): ?>
            <?php $this->assign('lexemName', ($this->_tpl_vars['l']->unaccented)." (".($this->_tpl_vars['l']->description).")"); ?>
          <?php else: ?>
            <?php $this->assign('lexemName', ($this->_tpl_vars['l']->unaccented)); ?>
          <?php endif; ?>
          <tr>
            <td>Lexem:</td>
            <td>
              <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/lexemPicker.ihtml", 'smarty_include_vars' => array('displayValue' => ($this->_tpl_vars['lexemName']),'fieldName' => "lexemName[]",'submitName' => "lexemId[]",'submitValue' => ($this->_tpl_vars['l']->id),'editLinkId' => ($this->_tpl_vars['l']->id),'dissociateLink' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            </td>
          </tr>
        <?php endforeach; endif; unset($_from); ?>
      </table>

      <a href="#" onclick="addLexemRow('lexemTable'); return false;">
        Adaugă un lexem
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'definitionAddLexem')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      </a>

      <table class="editableFields">
        <tr>
          <td>Sursa:</td>
          <td>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/sourceDropDown.ihtml", 'smarty_include_vars' => array('sources' => $this->_tpl_vars['allModeratorSources'],'src_selected' => $this->_tpl_vars['def']->sourceId,'skipAnySource' => true)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <tr>
          <td>
            Starea:
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'definitionDelete')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
          <td>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/statusDropDown.ihtml", 'smarty_include_vars' => array('name' => 'status','statuses' => $this->_tpl_vars['allStatuses'],'selectedStatus' => $this->_tpl_vars['def']->status)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
        <?php if (count ( $this->_tpl_vars['typos'] )): ?>
          <tr>
            <td>Greşeli de tipar:</td>
            <td>
              <?php $_from = $this->_tpl_vars['typos']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['typo']):
?>
                <span class="typo">* <?php echo $this->_tpl_vars['typo']->problem; ?>
</span><br/>
              <?php endforeach; endif; unset($_from); ?>
            </td>
          </tr>
        <?php endif; ?>
        <tr>
          <td>Conţinut:</td>
          <td>
            <textarea name="internalRep" rows="15" cols="80"
            ><?php echo ((is_array($_tmp=$this->_tpl_vars['def']->internalRep)) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</textarea>
          </td>
        </tr>
        <tr>
          <td>
            Comentariu<br/>
            (opţional):<br/>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'definitionComment')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
          <td>
            <textarea name="commentContents" rows="5" cols="80"
              ><?php echo ((is_array($_tmp=$this->_tpl_vars['comment']->contents)) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
</textarea>
          </td>
        </tr>
        <tr>
          <td colspan="3" class="buttonRow">
            <input type="submit" name="but_refresh" value="Reafişează"/>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'definitionRefresh')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            &nbsp;&nbsp;
            <input type="submit" name="but_accept" value="Acceptă"/>
            &nbsp;&nbsp;
            <input type="submit" name="but_move" value="Activează"/>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'definitionActivate')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
        </tr>
      </table>
    </form>

    <br/>
    <?php echo $this->_tpl_vars['def']->htmlRep; ?>
<br/>
    <span class="defDetails">
      Id: <?php echo $this->_tpl_vars['def']->id; ?>
 |
      Sursa: <?php echo $this->_tpl_vars['source']->shortName; ?>
 |
      Trimisă de <?php echo $this->_tpl_vars['user']->nick; ?>
, <?php echo ((is_array($_tmp=$this->_tpl_vars['def']->createDate)) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %b %Y") : smarty_modifier_date_format($_tmp, "%e %b %Y")); ?>
 |
      <?php $this->assign('status', ($this->_tpl_vars['def']->status)); ?>
      <?php $this->assign('statusName', ($this->_tpl_vars['allStatuses'][$this->_tpl_vars['status']])); ?>
      Starea: <?php echo $this->_tpl_vars['statusName']; ?>

    </span>

    <br/><br/>
    Comentariu: <?php echo $this->_tpl_vars['comment']->htmlContents; ?>


  </body>
</html>