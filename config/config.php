<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   kraken.io
 * @author    Johannes Terhürne <johannes@terhuerne.org>
 * @license   MIT
 * @copyright Johannes Terhürne 2014
 */


$GLOBALS['TL_HOOKS']['getImage'][] = array('krakenIoInterface', 'compressThumbnail');
$GLOBALS['TL_HOOKS']['postUpload'][] = array('krakenIoInterface', 'checkForImages');

$GLOBALS['BE_MOD']['system']['files']['compress'] = array('krakenIoInterface', 'compressSingleImage');