<?php

User::mustHave(User::PRIV_ADMIN);

$sourceId = Request::get('id');
$saveButton = Request::has('saveButton');
$src = $sourceId ? Source::get_by_id($sourceId) : Model::factory('Source')->create();

if ($saveButton) {
  $src->name = Request::get('name');
  $src->shortName = Request::get('shortName');
  $src->urlName = Request::get('urlName');
  $src->author = Request::get('author');
  $src->publisher = Request::get('publisher');
  $src->year = Request::get('year');
  $src->sourceTypeId = Request::get('sourceTypeId');
  $src->managerId = Request::get('managerId');
  $src->importType = Request::get('importType');
  $src->reformId = Request::get('reformId');
  $src->remark = Request::get('remark');
  $src->link = Request::get('link');
  $src->courtesyLink = Request::get('courtesyLink');
  $src->courtesyText = Request::get('courtesyText');
  $src->hidden = Request::has('hidden');
  $src->type = Request::get('type');
  $src->canModerate = Request::has('canModerate');
  $src->canDistribute = Request::has('canDistribute');
  $src->structurable = Request::has('structurable');
  $src->hasPageImages = Request::has('hasPageImages');
  $src->defCount = Request::get('defCount');
  $src->commonGlyphs = Request::get('commonGlyphs');
  $tagIds = Request::getArray('tagIds');

  $authors = buildAuthors(
    Request::getArray('authorId'),
    Request::getArray('authorTitle'),
    Request::getArray('authorName'),
    Request::getArray('authorAcademicRank'),
    Request::getArray('authorRoleId'));

  if (validate($src, $authors)) {
    // For new sources, set displayOrder to the highest available + 1
    if (!$sourceId) {
      $src->displayOrder = Model::factory('Source')->count() + 1;
    }
    $src->updatePercentComplete();
    $src->save();
    ObjectTag::wipeAndRecreate($src->id, ObjectTag::TYPE_SOURCE, $tagIds);
    SourceAuthor::update($authors, $src);

    Log::notice("Added/saved source {$src->id} ({$src->shortName})");
    FlashMessage::add('Am salvat modificările.', 'success');
    Util::redirect(Router::link('source/view') . '/' . $src->id);
  }
} else {
  // first time loading this page
  $authors = $src->getAuthors();
}

$ots = ObjectTag::getSourceTags($src->id);
$tagIds = Util::objectProperty($ots, 'tagId');

$managers = Model::factory('User')
  ->where_raw('moderator & 4')
  ->order_by_asc('id')
  ->find_many();

$sourceTypes = Model::factory('SourceType')
  ->order_by_asc('id')
  ->find_many();

$reforms = Model::factory('OrthographicReforms')
  ->order_by_asc('id')
  ->find_many();

Smart::assign([
  'src' => $src,
  'tagIds' => $tagIds,
  'managers' => $managers,
  'sourceTypes' => $sourceTypes,
  'reforms' => $reforms,
  'authors' => $authors,
]);
Smart::addResources('select2Dev', 'sortable');
Smart::display('source/edit.tpl');

/*************************************************************************/

/**
 * Returns true on success, false on errors.
 */
function validate($src, $authors) {
  if (!$src->name) {
    FlashMessage::add('Numele nu poate fi vid.');
  }
  if (!$src->shortName) {
    FlashMessage::add('Numele scurt nu poate fi vid.');
  }
  if (!$src->urlName) {
    FlashMessage::add('Numele URL nu poate fi vid.');
  }
  if (!$src->author) {
    FlashMessage::add('Autorul nu poate fi vid.');
  }
  if ($src->defCount < 0 && $src->defCount != Source::UNKNOWN_DEF_COUNT) {
    FlashMessage::add('Numărul de definiții trebuie să fie pozitiv.');
  }

  // glyph validation
  $base = array_fill_keys(Str::unicodeExplode(Source::BASE_GLYPHS), true);
  $common = array_fill_keys(Str::unicodeExplode($src->commonGlyphs), true);

  $redundantCommon = '';
  foreach ($common as $glyph => $ignored) {
    if (isset($base[$glyph])) {
      $redundantCommon .= ' ' . $glyph;
      $src->commonGlyphs = str_replace($glyph, '', $src->commonGlyphs);
    }
  }
  if ($redundantCommon) {
    FlashMessage::add("Am eliminat glifele comune <b>$redundantCommon</b>, " .
                      'care sînt incluse automat.', 'warning');
  }

  // authors
  foreach ($authors as $a) {
    if (!$a->name) {
      FlashMessage::add('Numele autorilor nu pot fi vide.');
    }
  }

  return !FlashMessage::hasErrors();
}

function buildAuthors($ids, $titles, $names, $academicRanks, $roleIds) {
  $result = [];

  foreach ($ids as $i => $id) {
    // ignore empty records
    if ($titles[$i] || $names[$i] || $academicRanks[$i] || $roleIds[$i]) {
      $author = $id
        ? SourceAuthor::get_by_id($id)
        : Model::factory('SourceAuthor')->create();
      $author->title = $titles[$i];
      $author->name = $names[$i];
      $author->academicRank = $academicRanks[$i];
      $author->sourceRoleId = $roleIds[$i];
      $result[] = $author;
    }
  }

  return $result;
}
