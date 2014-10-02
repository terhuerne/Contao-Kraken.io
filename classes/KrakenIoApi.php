<?php

/**
 * Copyright (c) 2013 Nekkra UG
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO
 * THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace krakenIo;

class KrakenIoApi
{

    protected $auth = array();

    public function __construct($key = '', $secret = '')
    {
        $this->auth = array(
            "auth" => array(
                "api_key" => $key,
                "api_secret" => $secret
            )
        );
    }

    public function url($opts = array())
    {
        $data = json_encode(array_merge($this->auth, $opts));
        $response = self::request($data, "https://api.kraken.io/v1/url");

        return $response;
    }

    public function upload($opts = array())
    {
        if (!isset($opts['file']))
        {
            return array(
                "success" => false,
                "error" => "File parameter was not provided"
            );
        }

        if (preg_match("/\/\//i", $opts['file']))
        {
            $opts['url'] = $opts['file'];
            unset($opts['file']);
            return $this->url($opts);
        }

        if (!file_exists($opts['file']))
        {
            return array(
                "success" => false,
                "error" => "File `" . $opts['file'] . "` does not exist"
            );
        }

        if( class_exists( 'CURLFile') ) {
			$file = new \CURLFile( $opts['file'] );
		} else {
			$file = '@' . $opts['file'];
		}

        unset($opts['file']);

        $data = array_merge(array(
            "file" => $file,
            "data" => json_encode(array_merge(
                            $this->auth, $opts
            ))
        ));

        $response = self::request($data, "https://api.kraken.io/v1/upload");

        return $response;
    }

    public function status()
    {
        $data = array('auth' => array(
            'api_key' => $this->auth['auth']['api_key'],
            'api_secret' => $this->auth['auth']['api_secret']
        ));
        $response = self::request(json_encode($data), "https://api.kraken.io/user_status");

        return $response;
    }

    private function request($data, $url)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);

        $response = json_decode(curl_exec($curl), true);

        /* Added by Johannes Terhuerne [johannes@terhuerne.org]: Return HTTP Code */
        $response['code'] = curl_getinfo ($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return $response;
    }

}