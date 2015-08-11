<?php
/*
[MIT LICENSE]

Copyright (c) 2015 Rafael Zanetti, http://rafaelzanetti.com.br

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
Software), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, andor sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED AS IS, WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/**
 * HANATC
 * A simple HANATrial client
 * 
 * @author Rafael Zanetti <rafatz@gmail.com>
 * @uses HANATAP HanaTrial Authentication Proxy
 * @uses SimpleHTTPRequest Simple HTTP Request PHP library
 */
class HANATC {
	
    private $options;
    private $url;
    private $content_type;
    private $contents;
    private $timeout = 1800;

    /**
     *
     * @param array $options
     * - proxy: HTTP proxy for your network (Optional)
     * - username: Your HCP Username (starting with p)
     * - password: Your HCP Password
     * - host: Hostname where your application is installed
     * - path: Path you want to query (Ex. /p00000000trial/<PACKAGE>/<FILE>/$metadata)
     * OR
     * - namespace: Location of the file you want to access in your HCP (Ex. appname.something)
     * - file: Name of the file in the HCP (Ex. filename.xsodata)
     * - params: aditional URL params (Ex. Name of the xsodata service set in the file, like DataListing)
     * - format: Format you want the returned data (Ex. json)
	 *
     */
    function __construct($options) {
        $this->url = 'https://'.$options['host'].':';
        if (!isset($options['path']) && isset($options['accountname']) && isset($options['namespace']) && isset($options['file'])) {
            $options['path'] = '/'.$options['accountname'].'/'.str_replace('.', '/', $options['namespace']).'/'.$options['file'];

            if (isset($options['params'])) {
                $options['path'] .= '/'.$options['params'];
            }

            if (isset($options['format'])) {
                $options['path'] .= '?$format='.$options['format'];
            }

        } else {
            throw new Exception("Invalid Options Set: Incomplete URL", 1);
        }
        $this->url .= $options['path'];

        $options['ssl_check'] = 0;
        $options['follow_location'] = 0;

        $this->options = $options;

        $this->connect();
    }

    /**
     * Get the content-type header
	 * 
     * @return String
     */
    public function getContentType() {
        return $this->content_type;
    }

    /**
     * Get the contents of the request
	 * 
     * @return String
     */
    public function getContents() {
        return $this->contents;
    }

    /**
	 * Connects to the HCP instance
     * 
     */
    private function connect()  {
        $data = $this->checkAuthFile();

        $options = $this->options;
        $options['url'] = $this->url;

        foreach ($data->cookie as $cookie) {
            $options['headers'][] = 'Cookie: '.$cookie;
        }

        $req = new SimpleHTTPRequest($options);
        $this->content_type = $req->getHeader('content-type');
        $this->contents = $req->getContents();
    }

    private function checkAuthFile() {
        $hash = hash('sha256', $this->options['username'].$this->options['password']);
        $filename =  '.'.$hash;

        if (!file_exists($filename)) {
            return $this->saveAuthCookie($filename);
        } else {
            $data = json_decode(file_get_contents($filename));

            if (time()-($data->timestamp) > $this->timeout) {
                return $this->saveAuthCookie($filename);
            }

            return $data;
        }
    }

    /**
     *
     * @param string $filename
	 * @return mixed
     */
    private function saveAuthCookie($filename) {
        $proxy = new HANATAP($this->options);
        $contents = array(
            'cookie' => $proxy->getCookie(),
            'timestamp' => time()
        );

        file_put_contents($filename, json_encode($contents));
        return $contents;
    }
}
