<?php

require_once('../../phplib/util.php');

$revised = util_getRequestParameter('revised');
$page = util_getRequestParameter('page');
$limit = util_getRequestParameter('rows');
$resp = array();
$images = array();

$total = Model::factory('Visual')->where('revised', $revised)->count();
$lines = Model::factory('Visual')->where('revised', $revised)
                                 ->limit($limit)->offset(($page - 1) * $limit)->find_many();

foreach ($lines as $line) {
  $imgLexeme = Lexem::get_by_id($line->lexemeId);

  if(!empty($imgLexeme)) {
  	$lexemes = '<div class="allLexemes"><span class="mainLexeme">' . $imgLexeme->formUtf8General . '</span><br/>';

  } else {
  	$lexemes = '';
  }

  $tagsLexemes = VisualTag::get_all_by_imageId($line->id);

  foreach ($tagsLexemes as $tagLexeme) {
  	$row = Lexem::get_by_id($tagLexeme->lexemeId);
  	$lexemes .= $row->formUtf8General . ' ';
  }

  $lexemes .= '</div>';

  $user = User::get_by_id($line->userId);
  $link = '<a title="Click pentru a vedea imaginea" href="' . $line->getImageUrl() . '">' . basename($line->path) . '</a>';
  $images[] = array('id' => $line->id, 'lexeme' => $lexemes, 'user' => $user->nick,
                    'width' => $line->width, 'height' => $line->height, 'userId' => $line->userId,
                    'latestMod' => date('d.m.Y', $line->modDate), 'link' => $link);
}

$resp = array('total' => ceil($total / $limit), 'page' => $page, 'records' => $total, 'rows' => $images);

echo json_encode($resp);
?>