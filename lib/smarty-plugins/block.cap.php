<?php

// capitalize is already implemented as a modifier, but capitalizes every
// word, which we don't want.
function smarty_block_cap($params, $text) {
  return Str::capitalize($text);
}
