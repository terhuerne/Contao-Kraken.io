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

namespace krakenIo;

class krakenIoInterface
{

	public function compressThumbnail($image, $width, $height, $mode, $strCacheName, $objFile)
	{
		global $GLOBALS;

		if(isset($GLOBALS['TL_CONFIG']['krakenIo_enable']) && $GLOBALS['TL_CONFIG']['krakenIo_enable'] == true)
		{
			if(isset($GLOBALS['TL_CONFIG']['krakenIo_apiKey']) && isset($GLOBALS['TL_CONFIG']['krakenIo_apiSecret']))
			{
				$krakenIoApi = new KrakenIoApi($GLOBALS['TL_CONFIG']['krakenIo_apiKey'], $GLOBALS['TL_CONFIG']['krakenIo_apiSecret']);

				list($originalWidth, $originalHeight) = getimagesize(TL_ROOT.'/'.$image);
				$ratio = $originalWidth / $originalHeight;

				if($width == 0)
				{
					$width = round($height*$ratio);
					$strategy = 'auto';
				} else if($height == 0)
				{
					$height = round($width/$ratio);
					$strategy = 'auto';
				} else {
					$strategy = 'crop';
				}

				$params = array(
					'file'		=>	TL_ROOT.'/'.$image,
					'resize'	=>	array(
        				'strategy'	=>	$strategy,
        				'width'		=>	intval($width),
        				'height'	=>	intval($height)
					),
					'wait'		=>	true,
				);

				if(isset($GLOBALS['TL_CONFIG']['krakenIo_enable']) && $GLOBALS['TL_CONFIG']['krakenIo_enable'] == true)
				{
					$params['lossy'] = true;
				}

				$krakenIoApiResponse = $krakenIoApi->upload($params);
				$this->parseKrakenIoResponse($krakenIoApiResponse, $strCacheName);	
			}
		}

		return $strCacheName;
	}

	public function checkForImages($arrFiles)
	{
		global $GLOBALS;

		if(isset($GLOBALS['TL_CONFIG']['krakenIo_enable']) && $GLOBALS['TL_CONFIG']['krakenIo_enable'] == true)
		{
			if(isset($GLOBALS['TL_CONFIG']['krakenIo_apiKey']) && isset($GLOBALS['TL_CONFIG']['krakenIo_apiSecret']))
			{
				$getMimeType = new \finfo(FILEINFO_MIME_TYPE);
				$allowedTypes = array('image/jpeg', 'image/png');
				$krakenIoApi = new KrakenIoApi($GLOBALS['TL_CONFIG']['krakenIo_apiKey'], $GLOBALS['TL_CONFIG']['krakenIo_apiSecret']);
				
				foreach($arrFiles as $file)
				{
					if(in_array($getMimeType->file(TL_ROOT.'/'.$file), $allowedTypes))
					{
						if(!strpos('assets', $file))
						{
							$params = array(
								'file'	=>	TL_ROOT.'/'.$file,
								'wait'	=>	true,
							);

							if(isset($GLOBALS['TL_CONFIG']['krakenIo_enable']) && $GLOBALS['TL_CONFIG']['krakenIo_enable'] == true)
							{
								$params['lossy'] = true;
							}

							$krakenIoApiResponse = $krakenIoApi->upload($params);
							$this->parseKrakenIoResponse($krakenIoApiResponse, $file);		
						}
					}
				}
			}
		}
	}

	private function parseKrakenIoResponse($response, $file)
	{
		if($response['code'] == 200)
		{
			if ($response['success'] == true)
			{
				file_put_contents(TL_ROOT.'/'.$file, file_get_contents($response['kraked_url']));
			}

		} else if ($response['code'] == 400)
		{
			\System::log($GLOBALS['TL_LANG']['ERR']['krakenIo_400'], 'krakenIoInterface parseKrakenIoResponse()',TL_ERROR);
		} else if ($response['code'] == 401)
		{
			\System::log($GLOBALS['TL_LANG']['ERR']['krakenIo_401'], 'krakenIoInterface parseKrakenIoResponse()',TL_ERROR);
		} else if ($response['code'] == 403)
		{
			\System::log($GLOBALS['TL_LANG']['ERR']['krakenIo_403'], 'krakenIoInterface parseKrakenIoResponse()',TL_ERROR);
		} else if ($response['code'] == 413)
		{
			\System::log($GLOBALS['TL_LANG']['ERR']['krakenIo_413'], 'krakenIoInterface parseKrakenIoResponse()',TL_ERROR);
		} else if ($response['code'] == 415)
		{
			\System::log($GLOBALS['TL_LANG']['ERR']['krakenIo_415'], 'krakenIoInterface parseKrakenIoResponse()',TL_ERROR);
		} else if ($response['code'] == 422)
		{
			\System::log($GLOBALS['TL_LANG']['ERR']['krakenIo_422'], 'krakenIoInterface parseKrakenIoResponse()',TL_ERROR);
		} else if ($response['code'] == 429)
		{
			\System::log($GLOBALS['TL_LANG']['ERR']['krakenIo_429'], 'krakenIoInterface parseKrakenIoResponse()',TL_ERROR);
		} else if ($response['code'] == 500)
		{
			\System::log($GLOBALS['TL_LANG']['ERR']['krakenIo_500'], 'krakenIoInterface parseKrakenIoResponse()',TL_ERROR);
		}
	}
}