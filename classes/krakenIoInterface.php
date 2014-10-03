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

class krakenIoInterface extends \Backend
{

	public function compressThumbnail($image, $width, $height, $mode, $strCacheName, $objFile)
	{
		global $GLOBALS;

		if(isset($GLOBALS['TL_CONFIG']['krakenIo_enable']) && $GLOBALS['TL_CONFIG']['krakenIo_enable'] == true)
		{
			if(isset($GLOBALS['TL_CONFIG']['krakenIo_apiKey']) && isset($GLOBALS['TL_CONFIG']['krakenIo_apiSecret']))
			{
				$krakenIoApi = new KrakenIoApi($GLOBALS['TL_CONFIG']['krakenIo_apiKey'], $GLOBALS['TL_CONFIG']['krakenIo_apiSecret']);

				$strCacheName = $this->generateImageContaoLogic($image, $width, $height, $mode, $strCacheName);

				$params = array(
					'file'		=>	TL_ROOT.'/'.$strCacheName,
					'wait'		=>	true,
				);

				if(isset($GLOBALS['TL_CONFIG']['krakenIo_enable']) && $GLOBALS['TL_CONFIG']['krakenIo_enable'] == true)
				{
					$params['lossy'] = true;
				}

				$krakenIoApiResponse = $krakenIoApi->upload($params);
				$this->parseKrakenIoResponse($krakenIoApiResponse, $strCacheName);	
			} else {
				\System::log($GLOBALS['TL_LANG']['ERR']['krakenIo_404'], 'krakenIoInterface parseKrakenIoResponse()',TL_ERROR);
			}
		} else {
			$strCacheName = $this->generateImageContaoLogic($image, $width, $height, $mode, $strCacheName);
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
			} else {
				\System::log($GLOBALS['TL_LANG']['ERR']['krakenIo_404'], 'krakenIoInterface parseKrakenIoResponse()',TL_ERROR);
			}
		}
	}

	public function compressSingleImage(\DataContainer $dc){
		$objUser = \BackendUser::getInstance();
		if (!$objUser->hasAccess('f5', 'fop'))
		{
			$url = $this->Environment->base.'/contao/main.php?do=files';
			$this->redirect($url);
		}

		$strDecoded = rawurldecode($_GET['id']);

		if (is_dir(TL_ROOT . '/' . $strDecoded))
		{
			$url = $this->Environment->base.'/contao/main.php?do=files';
			$this->redirect($url);
		}

		$objFile = new \File($strDecoded, true);

		$compressableFiles = array('jpg', 'jpeg', 'png');

		if (in_array($objFile->extension, $compressableFiles))
		{
			if(isset($GLOBALS['TL_CONFIG']['krakenIo_enable']) && $GLOBALS['TL_CONFIG']['krakenIo_enable'] == true)
			{
				if(isset($GLOBALS['TL_CONFIG']['krakenIo_apiKey']) && isset($GLOBALS['TL_CONFIG']['krakenIo_apiSecret']))
				{
					$krakenIoApi = new KrakenIoApi($GLOBALS['TL_CONFIG']['krakenIo_apiKey'], $GLOBALS['TL_CONFIG']['krakenIo_apiSecret']);
					$params = array(
						'file'	=>	TL_ROOT.'/'.$_GET['id'],
						'wait'	=>	true,
					);

					if(isset($GLOBALS['TL_CONFIG']['krakenIo_enable']) && $GLOBALS['TL_CONFIG']['krakenIo_enable'] == true)
					{
						$params['lossy'] = true;
					}

					$krakenIoApiResponse = $krakenIoApi->upload($params);
					$this->parseKrakenIoResponse($krakenIoApiResponse, $_GET['id']);

				} else {
					\System::log($GLOBALS['TL_LANG']['ERR']['krakenIo_404'], 'krakenIoInterface parseKrakenIoResponse()',TL_ERROR);
				}
			}
		}
		$url = $this->Environment->base.'/contao/main.php?do=files';
		$this->redirect($url);
	}

	public static function generateImageContaoLogic($image, $width, $height, $mode='', $target=null, $force=false)
	{
		if ($image == '')
		{
			return null;
		}

		$image = rawurldecode($image);

		// Check whether the file exists
		if (!is_file(TL_ROOT . '/' . $image))
		{
			\System::log('Image "' . $image . '" could not be found', __METHOD__, TL_ERROR);
			return null;
		}

		$objFile = new \File($image, true);
		$arrAllowedTypes = trimsplit(',', strtolower(\Config::get('validImageTypes')));

		// Check the file type
		if (!in_array($objFile->extension, $arrAllowedTypes))
		{
			\System::log('Image type "' . $objFile->extension . '" was not allowed to be processed', __METHOD__, TL_ERROR);
			return null;
		}

		// No resizing required
		if (($objFile->width == $width || !$width) && ($objFile->height == $height || !$height))
		{
			// Return the target image (thanks to Tristan Lins) (see #4166)
			if ($target)
			{
				// Copy the source image if the target image does not exist or is older than the source image
				if (!file_exists(TL_ROOT . '/' . $target) || $objFile->mtime > filemtime(TL_ROOT . '/' . $target))
				{
					\Files::getInstance()->copy($image, $target);
				}

				return \System::urlEncode($target);
			}

			return \System::urlEncode($image);
		}

		// No mode given
		if ($mode == '')
		{
			// Backwards compatibility
			if ($width && $height)
			{
				$mode = 'center_top';
			}
			else
			{
				$mode = 'proportional';
			}
		}

		// Backwards compatibility
		if ($mode == 'crop')
		{
			$mode = 'center_center';
		}

		$strCacheKey = substr(md5('-w' . $width . '-h' . $height . '-' . $image . '-' . $mode . '-' . $objFile->mtime), 0, 8);
		$strCacheName = 'assets/images/' . substr($strCacheKey, -1) . '/' . $objFile->filename . '-' . $strCacheKey . '.' . $objFile->extension;

		// Check whether the image exists already
		if (!\Config::get('debugMode'))
		{
			// Custom target (thanks to Tristan Lins) (see #4166)
			if ($target && !$force)
			{
				if (file_exists(TL_ROOT . '/' . $target) && $objFile->mtime <= filemtime(TL_ROOT . '/' . $target))
				{
					return \System::urlEncode($target);
				}
			}

			// Regular cache file
			if (file_exists(TL_ROOT . '/' . $strCacheName))
			{
				// Copy the cached file if it exists
				if ($target)
				{
					\Files::getInstance()->copy($strCacheName, $target);
					return \System::urlEncode($target);
				}

				return \System::urlEncode($strCacheName);
			}
		}

		// Return the path to the original image if the GDlib cannot handle it
		if (!extension_loaded('gd') || !$objFile->isGdImage || $objFile->width > \Config::get('gdMaxImgWidth') || $objFile->height > \Config::get('gdMaxImgHeight') || (!$width && !$height) || $width > \Config::get('gdMaxImgWidth') || $height > \Config::get('gdMaxImgHeight'))
		{
			return \System::urlEncode($image);
		}

		$intPositionX = 0;
		$intPositionY = 0;
		$intWidth = $width;
		$intHeight = $height;

		// Mode-specific changes
		if ($intWidth && $intHeight)
		{
			switch ($mode)
			{
				case 'proportional':
					if ($objFile->width >= $objFile->height)
					{
						unset($height, $intHeight);
					}
					else
					{
						unset($width, $intWidth);
					}
					break;

				case 'box':
					if (round($objFile->height * $width / $objFile->width) <= $intHeight)
					{
						unset($height, $intHeight);
					}
					else
					{
						unset($width, $intWidth);
					}
					break;
			}
		}

		$strNewImage = null;
		$strSourceImage = null;

		// Resize width and height and crop the image if necessary
		if ($intWidth && $intHeight)
		{
			if (($intWidth * $objFile->height) != ($intHeight * $objFile->width))
			{
				$intWidth = max(round($objFile->width * $height / $objFile->height), 1);
				$intPositionX = -intval(($intWidth - $width) / 2);

				if ($intWidth < $width)
				{
					$intWidth = $width;
					$intHeight = max(round($objFile->height * $width / $objFile->width), 1);
					$intPositionX = 0;
					$intPositionY = -intval(($intHeight - $height) / 2);
				}
			}

			// Advanced crop modes
			switch ($mode)
			{
				case 'left_top':
					$intPositionX = 0;
					$intPositionY = 0;
					break;

				case 'center_top':
					$intPositionX = -intval(($intWidth - $width) / 2);
					$intPositionY = 0;
					break;

				case 'right_top':
					$intPositionX = -intval($intWidth - $width);
					$intPositionY = 0;
					break;

				case 'left_center':
					$intPositionX = 0;
					$intPositionY = -intval(($intHeight - $height) / 2);
					break;

				case 'center_center':
					$intPositionX = -intval(($intWidth - $width) / 2);
					$intPositionY = -intval(($intHeight - $height) / 2);
					break;

				case 'right_center':
					$intPositionX = -intval($intWidth - $width);
					$intPositionY = -intval(($intHeight - $height) / 2);
					break;

				case 'left_bottom':
					$intPositionX = 0;
					$intPositionY = -intval($intHeight - $height);
					break;

				case 'center_bottom':
					$intPositionX = -intval(($intWidth - $width) / 2);
					$intPositionY = -intval($intHeight - $height);
					break;

				case 'right_bottom':
					$intPositionX = -intval($intWidth - $width);
					$intPositionY = -intval($intHeight - $height);
					break;
			}

			$strNewImage = imagecreatetruecolor($width, $height);
		}

		// Calculate the height if only the width is given
		elseif ($intWidth)
		{
			$intHeight = max(round($objFile->height * $width / $objFile->width), 1);
			$strNewImage = imagecreatetruecolor($intWidth, $intHeight);
		}

		// Calculate the width if only the height is given
		elseif ($intHeight)
		{
			$intWidth = max(round($objFile->width * $height / $objFile->height), 1);
			$strNewImage = imagecreatetruecolor($intWidth, $intHeight);
		}

		$arrGdinfo = gd_info();
		$strGdVersion = preg_replace('/[^0-9\.]+/', '', $arrGdinfo['GD Version']);

		switch ($objFile->extension)
		{
			case 'gif':
				if ($arrGdinfo['GIF Read Support'])
				{
					$strSourceImage = imagecreatefromgif(TL_ROOT . '/' . $image);
					$intTranspIndex = imagecolortransparent($strSourceImage);

					// Handle transparency
					if ($intTranspIndex >= 0 && $intTranspIndex < imagecolorstotal($strSourceImage))
					{
						$arrColor = imagecolorsforindex($strSourceImage, $intTranspIndex);
						$intTranspIndex = imagecolorallocate($strNewImage, $arrColor['red'], $arrColor['green'], $arrColor['blue']);
						imagefill($strNewImage, 0, 0, $intTranspIndex);
						imagecolortransparent($strNewImage, $intTranspIndex);
					}
				}
				break;

			case 'jpg':
			case 'jpeg':
				if ($arrGdinfo['JPG Support'] || $arrGdinfo['JPEG Support'])
				{
					$strSourceImage = imagecreatefromjpeg(TL_ROOT . '/' . $image);
				}
				break;

			case 'png':
				if ($arrGdinfo['PNG Support'])
				{
					$strSourceImage = imagecreatefrompng(TL_ROOT . '/' . $image);

					// Handle transparency (GDlib >= 2.0 required)
					if (version_compare($strGdVersion, '2.0', '>='))
					{
						imagealphablending($strNewImage, false);
						$intTranspIndex = imagecolorallocatealpha($strNewImage, 0, 0, 0, 127);
						imagefill($strNewImage, 0, 0, $intTranspIndex);
						imagesavealpha($strNewImage, true);
					}
				}
				break;
		}

		// The new image could not be created
		if (!$strSourceImage)
		{
			imagedestroy($strNewImage);
			\System::log('Image "' . $image . '" could not be processed', __METHOD__, TL_ERROR);
			return null;
		}

		imageinterlace($strNewImage, 1); // see #6529
		imagecopyresampled($strNewImage, $strSourceImage, $intPositionX, $intPositionY, 0, 0, $intWidth, $intHeight, $objFile->width, $objFile->height);

		// Fallback to PNG if GIF ist not supported
		if ($objFile->extension == 'gif' && !$arrGdinfo['GIF Create Support'])
		{
			$objFile->extension = 'png';
		}

		// Create the new image
		switch ($objFile->extension)
		{
			case 'gif':
				imagegif($strNewImage, TL_ROOT . '/' . $strCacheName);
				break;

			case 'jpg':
			case 'jpeg':
				imagejpeg($strNewImage, TL_ROOT . '/' . $strCacheName, (\Config::get('jpgQuality') ?: 80));
				break;

			case 'png':
				// Optimize non-truecolor images (see #2426)
				if (version_compare($strGdVersion, '2.0', '>=') && function_exists('imagecolormatch') && !imageistruecolor($strSourceImage))
				{
					// TODO: make it work with transparent images, too
					if (imagecolortransparent($strSourceImage) == -1)
					{
						$intColors = imagecolorstotal($strSourceImage);

						// Convert to a palette image
						// @see http://www.php.net/manual/de/function.imagetruecolortopalette.php#44803
						if ($intColors > 0 && $intColors < 256)
						{
							$wi = imagesx($strNewImage);
							$he = imagesy($strNewImage);
							$ch = imagecreatetruecolor($wi, $he);
							imagecopymerge($ch, $strNewImage, 0, 0, 0, 0, $wi, $he, 100);
							imagetruecolortopalette($strNewImage, false, $intColors);
							imagecolormatch($ch, $strNewImage);
							imagedestroy($ch);
						}
					}
				}

				imagepng($strNewImage, TL_ROOT . '/' . $strCacheName);
				break;
		}

		// Destroy the temporary images
		imagedestroy($strSourceImage);
		imagedestroy($strNewImage);

		// Resize the original image
		if ($target)
		{
			\Files::getInstance()->copy($strCacheName, $target);
			return \System::urlEncode($target);
		}

		// Set the file permissions when the Safe Mode Hack is used
		if (\Config::get('useFTP'))
		{
			\Files::getInstance()->chmod($strCacheName, \Config::get('defaultFileChmod'));
		}

		// Return the path to new image
		return \System::urlEncode($strCacheName);
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