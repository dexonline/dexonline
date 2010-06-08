<?
/**
 * An abstract class defining methods common to all ads modules.
**/
abstract class AdsModule {
  // Return an array of (name, value) variables that define an ad.
  // The caller is responsible for passing these on to Smarty, then including
  // the corresponding Smarty template.
  // Returns null if the implementing class cannot serve a relevant ad.
  abstract public function run($lexems, $definitions);
}

?>
