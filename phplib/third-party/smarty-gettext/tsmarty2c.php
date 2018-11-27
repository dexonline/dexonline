#!/usr/bin/env php
<?php

/*
 * This file is part of the smarty-gettext package.
 *
 * @copyright (c) Elan Ruusamäe
 * @license GNU Lesser General Public License, version 2.1
 * @see https://github.com/smarty-gettext/smarty-gettext/
 *
 * For the full copyright and license information,
 * please see the LICENSE and AUTHORS files
 * that were distributed with this source code.
 */

/**
 * tsmarty2c.php - rips gettext strings from smarty template
 *
 * This commandline script rips gettext strings from smarty file,
 * and prints them to stdout in already gettext encoded format, which you can
 * later manipulate with standard gettext tools.
 *
 * Usage:
 * ./tsmarty2c.php -o template.pot <filename or directory> <file2> <..>
 *
 * Extract gettext strings of default (empty) domain only:
 * ./tsmarty2c.php -d -o default.pot <filename or directory> <file2> <..>
 *
 * Extract gettext strings of domain "custom" only (e.g {t domain="custom"}...):
 * ./tsmarty2c.php -d=custom -o custom.pot <filename or directory> <file2> <..>
 *
 * If a parameter is a directory, the template files within will be parsed.
 */

// smarty open tag
$ldq = preg_quote('{');

// smarty close tag
$rdq = preg_quote('}');

// smarty command
$cmd = preg_quote('t');

// extensions of smarty files, used when going through a directory
$extensions = array('tpl');

// we msgcat found strings from each file.
// need header for each temporary .pot file to be merged.
// https://help.launchpad.net/Translations/YourProject/PartialPOExport
define('MSGID_HEADER', 'msgid ""
msgstr "Content-Type: text/plain; charset=UTF-8\n"

');

// "fix" string - strip slashes, escape and convert new lines to \n
function fs($str) {
	$str = stripslashes($str);
	$str = str_replace('"', '\"', $str);
	$str = str_replace("\n", '\n', $str);

	return $str;
}

function lineno_from_offset($content, $offset) {
	return substr_count($content, "\n", 0, $offset) + 1;
}

function msgmerge($outfile, $data) {
	// skip empty
	if (empty($data)) {
		return;
	}

	// write new data to tmp file
	$tmp = tempnam(TMPDIR, 'tsmarty2c');
	file_put_contents($tmp, $data);

	// temp file for result cat
	$tmp2 = tempnam(TMPDIR, 'tsmarty2c');
	passthru('msgcat -o ' . escapeshellarg($tmp2) . ' ' . escapeshellarg($outfile) . ' ' . escapeshellarg($tmp), $rc);
	unlink($tmp);

	if ($rc) {
		fwrite(STDERR, "msgcat failed with $rc\n");
		exit($rc);
	}

	// rename if output was produced
	if (file_exists($tmp2)) {
		rename($tmp2, $outfile);
	}
}

// rips gettext strings from $file and prints them in C format
function do_file($outfile, $file) {
	$content = file_get_contents($file);

	if (empty($content)) {
		return;
	}

	global $ldq, $rdq, $cmd;

	preg_match_all(
		"/{$ldq}\s*({$cmd})\s*([^{$rdq}]*){$rdq}+([^{$ldq}]*){$ldq}\/\\1{$rdq}/",
		$content,
		$matches,
		PREG_OFFSET_CAPTURE
	);

	$result_msgctxt = array(); //msgctxt -> msgid based content
	$result_msgid = array(); //only msgid based content
	for ($i = 0; $i < count($matches[0]); $i++) {
		$msg_ctxt = null;
		$plural = null;

		if (defined('DOMAIN')) {
			if (preg_match('/domain\s*=\s*["\']?\s*(.[^\"\']*)\s*["\']?/', $matches[2][$i][0], $match)) {
				if($match[1] != DOMAIN) {
					continue; // Skip strings with domain, if not matching domain to extract
				}
			} elseif (DOMAIN != '') {
				continue; // Skip strings without domain, if domain to extract is not default/empty
			}
		}

		if (preg_match('/context\s*=\s*["\']?\s*(.[^\"\']*)\s*["\']?/', $matches[2][$i][0], $match)) {
			$msg_ctxt = $match[1];
		}

		if (preg_match('/plural\s*=\s*["\']?\s*(.[^\"\']*)\s*["\']?/', $matches[2][$i][0], $match)) {
			$msgid = $matches[3][$i][0];
			$plural = $match[1];
		} else {
			$msgid = $matches[3][$i][0];
		}

		if ($msg_ctxt && empty($result_msgctxt[$msg_ctxt])) {
			$result_msgctxt[$msg_ctxt] = array();
		}

		if ($msg_ctxt && empty($result_msgctxt[$msg_ctxt][$msgid])) {
			$result_msgctxt[$msg_ctxt][$msgid] = array();
		} elseif (empty($result_msgid[$msgid])) {
			$result_msgid[$msgid] = array();
		}

		if ($plural) {
			if ($msg_ctxt) {
				$result_msgctxt[$msg_ctxt][$msgid]['plural'] = $plural;
			} else {
				$result_msgid[$msgid]['plural'] = $plural;
			}
		}

		$lineno = lineno_from_offset($content, $matches[2][$i][1]);
		if ($msg_ctxt) {
			$result_msgctxt[$msg_ctxt][$msgid]['lineno'][] = "$file:$lineno";
		} else {
			$result_msgid[$msgid]['lineno'][] = "$file:$lineno";
		}
	}

	ob_start();
	echo MSGID_HEADER;
	foreach($result_msgctxt as $msgctxt => $data_msgid) {
		foreach($data_msgid as $msgid => $data) {
			echo "#: ", join(' ', $data['lineno']), "\n";
			echo 'msgctxt "' . fs($msgctxt) . '"', "\n";
			echo 'msgid "' . fs($msgid) . '"', "\n";
			if (isset($data['plural'])) {
				echo 'msgid_plural "' . fs($data['plural']) . '"', "\n";
				echo 'msgstr[0] ""', "\n";
				echo 'msgstr[1] ""', "\n";
			} else {
				echo 'msgstr ""', "\n";
			}
			echo "\n";
		}
	}
	//without msgctxt
	foreach($result_msgid as $msgid => $data) {
		echo "#: ", join(' ', $data['lineno']), "\n";
		echo 'msgid "' . fs($msgid) . '"', "\n";
		if (isset($data['plural'])) {
			echo 'msgid_plural "' . fs($data['plural']) . '"', "\n";
			echo 'msgstr[0] ""', "\n";
			echo 'msgstr[1] ""', "\n";
		} else {
			echo 'msgstr ""', "\n";
		}
		echo "\n";
	}

	$out = ob_get_contents();
	ob_end_clean();
	msgmerge($outfile, $out);
}

// go through a directory
function do_dir($outfile, $dir) {
	$d = dir($dir);

	while (false !== ($entry = $d->read())) {
		if ($entry == '.' || $entry == '..') {
			continue;
		}

		$entry = $dir . '/' . $entry;

		if (is_dir($entry)) { // if a directory, go through it
			do_dir($outfile, $entry);
		} else { // if file, parse only if extension is matched
			$pi = pathinfo($entry);

			if (isset($pi['extension']) && in_array($pi['extension'], $GLOBALS['extensions'])) {
				do_file($outfile, $entry);
			}
		}
	}

	$d->close();
}

if ('cli' != php_sapi_name()) {
	error_log("ERROR: This program is for command line mode only.");
	exit(1);
}

define('PROGRAM', basename(array_shift($argv)));
define('TMPDIR', sys_get_temp_dir());
$opt = getopt('o:d::');
$outfile = isset($opt['o']) ? $opt['o'] : tempnam(TMPDIR, 'tsmarty2c');

// remove -o FILENAME from $argv.
if (isset($opt['o'])) {
	foreach ($argv as $i => $v) {
		if ($v != '-o') {
			continue;
		}

		unset($argv[$i]);
		unset($argv[$i + 1]);
		break;
	}
}

// remove -d DOMAIN from $argv.
if (isset($opt['d'])) {
	define('DOMAIN', trim($opt['d']));
	foreach ($argv as $i => $v) {
		if (!preg_match('#^-d=?#',$v)) {
			continue;
		}
		unset($argv[$i]);
		break;
	}
}

// initialize output
file_put_contents($outfile, MSGID_HEADER);

// process dirs/files
foreach ($argv as $arg) {
	if (is_dir($arg)) {
		do_dir($outfile, $arg);
	} else {
		do_file($outfile, $arg);
	}
}

// output and cleanup
if (!isset($opt['o'])) {
	echo file_get_contents($outfile);
	unlink($outfile);
}
