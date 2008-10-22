<?php /* Smarty version 2.6.18, created on 2007-11-26 09:04:49
         compiled from common/search.ihtml */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', 'common/search.ihtml', 15, false),array('modifier', 'date_format', 'common/search.ihtml', 116, false),)), $this); ?>
<div class="resultBar">
  <div class="abbrev">
    <?php if ($this->_tpl_vars['wordListMaps']): ?>
      <a href="#" onclick="toggleDivVisibility('paradigmDiv'); return false;"
      >Flexiuni</a>
      |
    <?php endif; ?>
    <a href="#" onclick="abbrevwindow(); return false;">Abrevieri</a>
  </div>

  <?php if ($this->_tpl_vars['searchType'] == SEARCH_LEXEM || $this->_tpl_vars['searchType'] == SEARCH_WORDLIST): ?>
    <?php if (count ( $this->_tpl_vars['results'] ) == 1): ?>
      O definiţie pentru
    <?php else: ?>
      <?php echo count($this->_tpl_vars['results']); ?>
 definiţii pentru
    <?php endif; ?>
    <?php $_from = $this->_tpl_vars['lexems']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row_id'] => $this->_tpl_vars['lexem']):
?>
      <a href="search.php?lexemId=<?php echo $this->_tpl_vars['lexem']->id; ?>
"><?php echo $this->_tpl_vars['lexem']->unaccented; ?>
</a
      ><?php if ($this->_tpl_vars['lexem']->description): ?> (<?php echo $this->_tpl_vars['lexem']->description; ?>
)<?php endif; ?><?php if ($this->_tpl_vars['row_id'] < count ( $this->_tpl_vars['lexems'] ) - 1): ?>,<?php endif; ?>
    <?php endforeach; endif; unset($_from); ?>

  <?php elseif ($this->_tpl_vars['searchType'] == SEARCH_APPROXIMATE): ?>
    <?php if (count ( $this->_tpl_vars['lexems'] )): ?>
      Cuvântul "<?php echo $this->_tpl_vars['cuv']; ?>
" nu a fost găsit, dar am găsit următoarele cuvinte
      apropiate:
    <?php else: ?>
      Cuvântul "<?php echo $this->_tpl_vars['cuv']; ?>
" nu a fost găsit. Nu există nici un cuvânt similar.
    <?php endif; ?>

  <?php elseif ($this->_tpl_vars['searchType'] == SEARCH_DEF_ID): ?>
    <?php if (count ( $this->_tpl_vars['results'] )): ?>
      Definiţia cu ID-ul <?php echo $this->_tpl_vars['cuv']; ?>
:
    <?php else: ?>
      Nu există nici o definiţie cu ID-ul <?php echo $this->_tpl_vars['cuv']; ?>
.
    <?php endif; ?>

  <?php elseif ($this->_tpl_vars['searchType'] == SEARCH_REGEXP): ?>
    <?php if ($this->_tpl_vars['numResults']): ?>
      <?php if ($this->_tpl_vars['numResults'] > count ( $this->_tpl_vars['lexems'] )): ?>
        <?php echo $this->_tpl_vars['numResults']; ?>
 rezultate pentru <?php echo $this->_tpl_vars['cuv']; ?>
 (maxim <?php echo count($this->_tpl_vars['lexems']); ?>
 afişate):
      <?php else: ?>
        <?php echo $this->_tpl_vars['numResults']; ?>
 rezultate pentru <?php echo $this->_tpl_vars['cuv']; ?>
:
      <?php endif; ?>
    <?php else: ?>
      Nici un rezultat pentru <?php echo $this->_tpl_vars['cuv']; ?>
.
    <?php endif; ?>

  <?php elseif ($this->_tpl_vars['searchType'] == SEARCH_LEXEM_ID): ?>
    <?php if (count ( $this->_tpl_vars['lexems'] ) > 0): ?>
      <?php if (count ( $this->_tpl_vars['results'] ) == 1): ?>
        O definiţie pentru
      <?php else: ?>
        <?php echo count($this->_tpl_vars['results']); ?>
 definiţii pentru
      <?php endif; ?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/lexemName.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexems']['0'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php else: ?>
      Nu există nici un lexem cu ID-ul căutat.
    <?php endif; ?>

  <?php elseif ($this->_tpl_vars['searchType'] == SEARCH_FULL_TEXT): ?>
    <?php if ($this->_tpl_vars['lockExists']): ?>
      Momentan nu puteţi căuta prin textul definiţiilor, deoarece indexul
      este în curs de reconstrucţie. Această operaţie durează de obicei circa
      10 minute. Ne cerem scuze pentru neplăcere.
    <?php else: ?>
      <?php if ($this->_tpl_vars['numResults'] == 1): ?>
        O definiţie cuprinde toate cuvintele căutate
      <?php elseif ($this->_tpl_vars['numResults'] > 1): ?>
        <?php echo $this->_tpl_vars['numResults']; ?>
 definiţii cuprind toate cuvintele căutate
      <?php else: ?>
        Nici o definiţie nu conţine toate cuvintele căutate.
      <?php endif; ?>

      <?php if ($this->_tpl_vars['numResults'] > count ( $this->_tpl_vars['results'] )): ?>
        (maxim <?php echo count($this->_tpl_vars['results']); ?>
 afişate)
      <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php if ($this->_tpl_vars['wordListMaps']): ?>
  <div style="display: none" id="paradigmDiv">
    <?php $_from = $this->_tpl_vars['wordListMaps']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i'] => $this->_tpl_vars['wlMap']):
?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/paradigm.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexems'][$this->_tpl_vars['i']],'wlMap' => $this->_tpl_vars['wlMap'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endforeach; endif; unset($_from); ?>
  </div>
<?php endif; ?>

<?php if ($this->_tpl_vars['stopWords']): ?>
  <span class="stopWords">
    Unul sau mai multe cuvinte au fost ignorate deoarece sunt prea comune:
    <b>
      <?php $_from = $this->_tpl_vars['stopWords']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['word']):
?>
        <?php echo $this->_tpl_vars['word']; ?>

      <?php endforeach; endif; unset($_from); ?>
    </b>
  </span>
<?php endif; ?>

<?php $_from = $this->_tpl_vars['results']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row']):
?>
  <p>
    <span class="def" onclick="return searchClickedWord(event);">
      <?php echo $this->_tpl_vars['row']->definition->htmlRep; ?>

    </span>
    <br/>

    <span class="defDetails">
      Sursa: <a class="ref" href="/faq.php#surse"
                title="<?php echo $this->_tpl_vars['row']->source->name; ?>
, <?php echo $this->_tpl_vars['row']->source->year; ?>
"
	     ><?php echo $this->_tpl_vars['row']->source->shortName; ?>
</a> |

      Trimisă de <a href="user.php?n=<?php echo $this->_tpl_vars['row']->user->nick; ?>
"
        ><?php echo $this->_tpl_vars['row']->user->nick; ?>
</a>,
      <?php echo ((is_array($_tmp=$this->_tpl_vars['row']->definition->createDate)) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %b %Y") : smarty_modifier_date_format($_tmp, "%e %b %Y")); ?>


      <?php if ($this->_tpl_vars['is_moderator']): ?>
        | Id: <?php echo $this->_tpl_vars['row']->definition->id; ?>

      <?php endif; ?>

      |
      <a href="javascript:typoWindow(<?php echo $this->_tpl_vars['row']->definition->id; ?>
)"
      >Greşeală de tipar</a>

      <?php if ($this->_tpl_vars['is_moderator'] && ! $this->_tpl_vars['is_mirror']): ?>
        |
        <a target="edit_window"
           href="admin/definitionEdit.php?definitionId=<?php echo $this->_tpl_vars['row']->definition->id; ?>
">
          Editează</a>
      <?php endif; ?>
    </span>

    <?php if ($this->_tpl_vars['row']->comment): ?>
      <div class="defComment">
        Comentariu: <?php echo $this->_tpl_vars['row']->comment->htmlContents; ?>
 -
        <a href="user.php?n=<?php echo $this->_tpl_vars['row']->commentAuthor->nick; ?>
"
        ><?php echo $this->_tpl_vars['row']->commentAuthor->nick; ?>
</a>
      </div>
    <?php endif; ?>
  </p>
<?php endforeach; endif; unset($_from); ?>

<?php if ($this->_tpl_vars['searchType'] == SEARCH_APPROXIMATE || $this->_tpl_vars['searchType'] == SEARCH_REGEXP): ?>
  <?php $_from = $this->_tpl_vars['lexems']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row_id'] => $this->_tpl_vars['lexem']):
?>
    <?php if ($this->_tpl_vars['row_id']): ?>|<?php endif; ?>
    <a href="search.php?lexemId=<?php echo $this->_tpl_vars['lexem']->id; ?>
"
    ><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "common/bits/lexemName.ihtml", 'smarty_include_vars' => array('lexem' => $this->_tpl_vars['lexem'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></a>
  <?php endforeach; endif; unset($_from); ?>
<?php endif; ?>