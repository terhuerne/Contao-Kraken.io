<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package kraken.io
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'krakenIo',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'krakenIo\KrakenIoApi'       => 'system/modules/krakenio/classes/KrakenIoApi.php',
	'krakenIo\krakenIoInterface' => 'system/modules/krakenio/classes/krakenIoInterface.php',
));
