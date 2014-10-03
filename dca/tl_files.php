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


$GLOBALS['TL_DCA']['tl_files']['list']['operations']['compress'] = array(
	'label'               => &$GLOBALS['TL_LANG']['tl_files']['compress'],
	'icon'                => 'system/modules/krakenio/assets/compress.gif',
	'href'                => 'key=compress',
	'button_callback'     => array('compression_tl_files', 'compressImage')
);

class compression_tl_files extends tl_files
{

	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

	public function compressImage($row, $href, $label, $title, $icon, $attributes)
	{
		if (!$this->User->hasAccess('f5', 'fop'))
		{
			return '';
		}

		$strDecoded = rawurldecode($row['id']);

		if (is_dir(TL_ROOT . '/' . $strDecoded))
		{
			return '';
		}

		$objFile = new File($strDecoded, true);

		$compressableFiles = array('jpg', 'jpeg', 'png');

		if (!in_array($objFile->extension, $compressableFiles))
		{
			return '';
		}
		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
	}
}