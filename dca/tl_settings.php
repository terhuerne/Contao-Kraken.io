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

$GLOBALS['TL_DCA']['tl_settings']['palettes']['__selector__'][] = 'krakenIo_enable';
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{krakenIo_legend:hide},krakenIo_enable';
$GLOBALS['TL_DCA']['tl_settings']['subpalettes']['krakenIo_enable'] = 'krakenIo_apiKey,krakenIo_apiSecret,krakenIo_lossyCompression';

$GLOBALS['TL_DCA']['tl_settings']['fields']['krakenIo_enable'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_settings']['krakenIo_enable'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> array('submitOnChange'=>true)
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['krakenIo_apiKey'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_settings']['krakenIo_apiKey'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array('nospace'=>true, 'tl_class'=>'w50 m12')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['krakenIo_apiSecret'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_settings']['krakenIo_apiSecret'],
	'exclude'	=> true,
	'inputType'	=> 'text',
	'eval'		=> array('nospace'=>true, 'tl_class'=>'w50 m12')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['krakenIo_lossyCompression'] = array(
	'label'		=> &$GLOBALS['TL_LANG']['tl_settings']['krakenIo_lossyCompression'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox'
);