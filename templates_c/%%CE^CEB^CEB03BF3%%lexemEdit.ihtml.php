<?php /* Smarty version 2.6.19, created on 2008-06-11 10:45:51
         compiled from admin/lexemEdit.ihtml */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'admin/lexemEdit.ihtml', 45, false),array('modifier', 'count', 'admin/lexemEdit.ihtml', 220, false),array('modifier', 'date_format', 'admin/lexemEdit.ihtml', 230, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link href="../styles/flex.css" rel="stylesheet" type="text/css"/>
    <link href="../styles/paradigm.css" rel="stylesheet" type="text/css"/>
    <link href="../styles/lexemPicker.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="../js/dex.js"></script>
    <script type="text/javascript" src="../js/flex.js"></script>
    <script type="text/javascript" src="../js/lexemPicker.js"></script>
    <title>DEX | Editare lexem: <?php echo $this->_tpl_vars['lexem']->form; ?>
</title>
  </head>

  <body>
    <?php $this->assign('title', "Editare lexem: ".($this->_tpl_vars['lexem']->id)); ?>
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

    <div class="lexemSectionHeader">Proprietăţi</div>

    <form action="lexemEdit.php" method="post">
    <div class="lexemSection">
      <input type="hidden" name="lexemId" value="<?php echo $this->_tpl_vars['lexem']->id; ?>
"/>
      <table class="editableFields">
        <tr>
          <td>
            Nume:
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'lexemName')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
          <td>
            <input type="text" name="lexemForm" value="<?php echo $this->_tpl_vars['lexem']->form; ?>
"
                   size="40"/>
          </td>
        </tr>
        <tr>
          <td>
            Descriere:
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'lexemDescription')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
          <td>
            <input type="text" name="lexemDescription"
                   value="<?php echo ((is_array($_tmp=$this->_tpl_vars['lexem']->description)) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="40"/>
          </td>
        </tr>
        <?php if ($this->_tpl_vars['is_flex_moderator']): ?>
          <tr>
            <td>
              Silabisire/pronunţie:
              <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'lexemExtra')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            </td>
            <td>
              <input type="text" name="lexemExtra"
                     value="<?php echo ((is_array($_tmp=$this->_tpl_vars['lexem']->extra)) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
" size="40"/>
            </td>
          </tr>
        <?php endif; ?>
        <tr>
          <td>
            Inclus în LOC
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'lexemIsLoc')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
          <td>
            <?php if ($this->_tpl_vars['is_flex_moderator']): ?>
              <input type="radio" name="lexemIsLoc" value="1"
                     id="lexemIsLocYes"
                     <?php if ($this->_tpl_vars['lexem']->isLoc): ?>checked="checked"<?php endif; ?>/>
              <label for="lexemIsLocYes">Da</label>

              <input type="radio" name="lexemIsLoc" value=""
                     id="lexemIsLocNo"
                     <?php if (! $this->_tpl_vars['lexem']->isLoc): ?>checked="checked"<?php endif; ?>/>
              <label for="lexemIsLocNo">Nu</label>
            <?php else: ?>
              <?php if ($this->_tpl_vars['lexem']->isLoc): ?>Da<?php else: ?>Nu<?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <td>
            Necesită accent
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'lexemNoAccent')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
          </td>
          <td>
            <input type="radio" name="lexemNoAccent" value=""
                   id="lexemYesAccent"
                   <?php if (! $this->_tpl_vars['lexem']->noAccent): ?>checked="checked"<?php endif; ?>/>
            <label for="lexemYesAccent">Da</label>

            <input type="radio" name="lexemNoAccent" value="1"
                   id="lexemNoAccent"
                   <?php if ($this->_tpl_vars['lexem']->noAccent): ?>checked="checked"<?php endif; ?>/>
            <label for="lexemNoAccent">Nu</label>
          </td>
        </tr>
        <?php if ($this->_tpl_vars['homonyms']): ?>
          <tr>
            <td>Lexeme omonime:</td>
            <td>
              <?php $_from = $this->_tpl_vars['homonyms']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['h']):
?>
                <?php if ($this->_tpl_vars['i']): ?>|<?php endif; ?>
                <a href="lexemEdit.php?lexemId=<?php echo $this->_tpl_vars['h']->id; ?>
"
                  ><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/lexemName.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['h'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></a>
              <?php endforeach; endif; unset($_from); ?>
            </td>
          </tr>
        <?php endif; ?>
      </table>
    </div>

    <div class="lexemSectionHeader">Paradigmă</div>

    <?php if ($this->_tpl_vars['is_flex_moderator']): ?>
      <div class="lexemSection">
        Puteţi face etichetarea în trei moduri: (1) alegeţi una dintre
        sugestii; (2) indicaţi un cuvânt care se flexionează la fel; sau
        (3) indicaţi tipul şi numărul modelului.
        <br/><br/>
  
        <b>1. <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/lexemName.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></b>
        se flexionează ca...
        <?php $_from = $this->_tpl_vars['suggestedLexems']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['l']):
?>
          <?php if ($this->_tpl_vars['i']): ?>|<?php endif; ?>
          <a href="lexemEdit.php?lexemId=<?php echo $this->_tpl_vars['lexem']->id; ?>
&amp;similarLexemId=<?php echo $this->_tpl_vars['l']->id; ?>
"
            ><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/lexemName.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['l'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></a>
          (<?php echo $this->_tpl_vars['l']->modelType; ?>
<?php echo $this->_tpl_vars['l']->modelNumber; ?>
<?php echo $this->_tpl_vars['l']->restriction; ?>
)
        <?php endforeach; endif; unset($_from); ?>
        <br/><br/>
  
        <b>2. <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/lexemName.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></b>
        se flexionează ca...<br/>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/lexemPicker.ihtml", 'smarty_include_vars' => array('fieldName' => 'similarLexemName')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <br/><br/>
  
        <b>3. <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/lexemName.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></b>
        se flexionează conform modelului
        <select name="modelType"
                onchange="return updateModelList(this, 'modelListId')">
          <?php $_from = $this->_tpl_vars['modelTypes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['mt']):
?>
            <option value="<?php echo $this->_tpl_vars['mt']->value; ?>
"
              <?php if ($this->_tpl_vars['mt']->value == $this->_tpl_vars['lexem']->modelType): ?>selected="selected"<?php endif; ?>
              ><?php echo $this->_tpl_vars['mt']->value; ?>

            </option>
          <?php endforeach; endif; unset($_from); ?>
        </select>
        <select name="modelNumber" id="modelListId">
          <?php $_from = $this->_tpl_vars['models']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['m']):
?>
            <option value="<?php echo $this->_tpl_vars['m']->number; ?>
"
              <?php if ($this->_tpl_vars['m']->number == $this->_tpl_vars['lexem']->modelNumber): ?>selected="selected"<?php endif; ?>
              ><?php echo $this->_tpl_vars['m']->number; ?>
<?php if (! $this->_tpl_vars['m']->id): ?>*<?php endif; ?> (<?php echo $this->_tpl_vars['m']->exponent; ?>
)
            </option>
          <?php endforeach; endif; unset($_from); ?>
        </select>
        <br/>
  
        cu restricţiile
        <input type="checkbox" id="restrS" name="restr[]" value="S"
          <?php if ($this->_tpl_vars['restrS']): ?>checked="checked"<?php endif; ?>/>
        <label for="restrS">Singular</label>
        &nbsp;&nbsp;
        <input type="checkbox" id="restrP" name="restr[]" value="P"
          <?php if ($this->_tpl_vars['restrP']): ?>checked="checked"<?php endif; ?>/>
        <label for="restrP">Plural</label>
        &nbsp;&nbsp;
        <input type="checkbox" id="restrU" name="restr[]" value="U"
          <?php if ($this->_tpl_vars['restrU']): ?>checked="checked"<?php endif; ?>/>
        <label for="restrU">Unipersonal</label>
        &nbsp;&nbsp;
        <input type="checkbox" id="restrI" name="restr[]" value="I"
          <?php if ($this->_tpl_vars['restrI']): ?>checked="checked"<?php endif; ?>/>
        <label for="restrI">Impersonal</label>
        &nbsp;&nbsp;
        <input type="checkbox" id="restrT" name="restr[]" value="T"
          <?php if ($this->_tpl_vars['restrT']): ?>checked="checked"<?php endif; ?>/>
        <label for="restrT">Trecut</label>
      </div>
    <?php endif; ?>

    <?php if ($this->_tpl_vars['wlMap'] && ! $this->_tpl_vars['errorMessage']): ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/paradigm.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'],'wlMap' => $this->_tpl_vars['wlMap'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endif; ?>

    <div class="lexemSection">
      Comentarii despre paradigmă:
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'lexemComment')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      <br/>
      <textarea name="lexemComment" rows="8" cols="60" class="commentTextArea"
        ><?php echo $this->_tpl_vars['lexem']->comment; ?>
</textarea>
    </div>

    <div class="lexemSectionHeader">Acţiuni</div>

    <div class="lexemSection">
      <input type="submit" name="refreshLexem" value="Reafişează"/>
      &nbsp;&nbsp;
      <input type="submit" name="updateLexem" value="Salvează"/>
      &nbsp;&nbsp;
      <input type="submit" name="cloneLexem" value="Clonează"/>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'lexemClone')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      &nbsp;&nbsp;
      <input type="submit" name="deleteLexem" value="Şterge"
           <?php if ($this->_tpl_vars['lexem']->isLoc): ?>disabled="disabled"<?php endif; ?>
           onclick="return confirm('Confirmaţi ştergerea acestui lexem?');"/>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'lexemDelete')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      <br/>

      Asociază definiţia cu ID-ul:
      <input type="text" name="associateDefinitionId"/>
      <input type="submit" name="associateDefinition" value="Asociază"/>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "admin/helpLink.ihtml", 'smarty_include_vars' => array('anchor' => 'lexemAssociateNew')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </div>
    </form>

    <div class="lexemSectionHeader">
      Definiţii asociate (<?php echo count($this->_tpl_vars['searchResults']); ?>
):
    </div>

    <div class="lexemSection">
      <?php $_from = $this->_tpl_vars['searchResults']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?>
        <?php echo $this->_tpl_vars['row']->definition->htmlRep; ?>
<br/>
        <span class="defDetails">
          Id: <?php echo $this->_tpl_vars['row']->definition->id; ?>
 |
          Sursa: <?php echo $this->_tpl_vars['row']->source->shortName; ?>
 |
          Trimisă de <?php echo $this->_tpl_vars['row']->user->nick; ?>
,
          <?php echo ((is_array($_tmp=$this->_tpl_vars['row']->definition->createDate)) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %b %Y") : smarty_modifier_date_format($_tmp, "%e %b %Y")); ?>
 |
          <?php $this->assign('status', ($this->_tpl_vars['row']->definition->status)); ?>
          <?php $this->assign('statusName', ($this->_tpl_vars['allStatuses'][$this->_tpl_vars['status']])); ?>
          Starea: <?php echo $this->_tpl_vars['statusName']; ?>
 |
  
          <a href="definitionEdit.php?definitionId=<?php echo $this->_tpl_vars['row']->definition->id; ?>
"
            >Editează</a> |
  
          <a href="lexemEdit.php?lexemId=<?php echo $this->_tpl_vars['lexem']->id; ?>
&amp;dissociateDefinitionId=<?php echo $this->_tpl_vars['row']->definition->id; ?>
"
             onclick="return confirmDissociateDefinition(<?php echo $this->_tpl_vars['row']->definition->id; ?>
)"
            >Disociază</a>
        </span>
        <br/><br/>
      <?php endforeach; endif; unset($_from); ?>

      <?php if (! count ( $this->_tpl_vars['searchResults'] )): ?>
        Nu există nici o definiţie. Puteţi crea o
        mini-definiţie. Introduceţi termenul-destinaţie, fără alte
        formatări (bold, italic etc.):<br/>

        <form action="lexemEdit.php" method="post">
          <input type="hidden" name="lexemId" value="<?php echo $this->_tpl_vars['lexem']->id; ?>
"/>
          <b><?php echo $this->_tpl_vars['definitionLexem']; ?>
</b> v.
          <input type="text" name="miniDefTarget" size="20"
                 class="miniDefTarget"/>.
          &nbsp;&nbsp;
          <input type="submit" name="createDefinition" value="Creează"/>
        </form>
      <?php endif; ?>
    </div>

    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/debugInfo.ihtml", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?> 
 </body>
</html>