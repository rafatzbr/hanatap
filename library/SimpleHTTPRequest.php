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
 * @author Rafael Zanetti <rafatz@gmail.com>
 */
class SimpleHTTPRequest {
    /**
     * Maps the string option to the cURL parameter
     *  
     * @var array
     */
    private $options_map = array(
        'url'               => CURLOPT_URL,
        'follow_location'   => CURLOPT_FOLLOWLOCATION,
        'ssl_check'         => CURLOPT_SSL_VERIFYPEER,
        'proxy'             => CURLOPT_PROXY
    );

    /**
     *
     * @var array Request Options
     */
    private $options;
    
    /**
     * 
     * @var String Request type - GET or POST
     */
    private $type;
    
    /**
     * 
     * @var type Request's HTTP Headers
     */
    private $headers = array();
    
    /**
     * 
     * @var String Contents of the request
     */
    private $contents;

    
    /**
     *
     * @var boolean Debug flag 
     */
    private $debug = false;
    
    /**
     *
     * @param type $options
     * @param type $type
     * @param type $debug
     */
    function __construct($options, $type = 'GET', $debug = false) {
        $this->options = $options;
        $this->type = strtoupper($type);
        $this->debug = $debug;

        $this->request();
    }

    /**
     * Create cURL parameters
     * 
     * @return array
     */
    private function createOptions() {
        $curl_options[CURLOPT_HEADERFUNCTION] = array($this, 'handleHeaderLine');
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_BINARYTRANSFER] = true;

        $is_form = false;

        if (isset($this->options['form'])) {
            $curl_options[CURLOPT_POSTFIELDS] = http_build_query($this->options['form']);
            //print_r($this->options['form']);
            $is_form = true;
            unset($this->options['form']);
        }

        if (isset($this->options['json_data'])) {
            $curl_options[CURLOPT_POSTFIELDS] = json_encode($this->options['json_data']);
            unset($this->options['json_data']);
        }

        if (isset($this->options['headers'])) {
            if (!is_array($this->options['headers'])) {
                $this->options['headers'] = array($this->options['headers']);
            }

            if ($this->type == "POST" && $is_form) {
                $this->options['headers'][] = 'content-type: application/x-www-form-urlencoded';
            }

            $curl_options[CURLOPT_HTTPHEADER] = $this->options['headers'];

            unset($this->options['headers']);
        }

        foreach ($this->options as $key => $value) {
            if (isset($this->options_map[$key])) {
                $curl_options[$this->options_map[$key]] = $value;
            }
        }

        if ($this->type == 'POST' || $this->type == 'post') {
            $curl_options[CURLOPT_POST] = 1;
        }

        return $curl_options;
    }

    /**
     * Performs the request
     * 
     * @return boolean
     */
    private function request() {
        $req = curl_init();
        curl_setopt_array($req, $this->createOptions());

        $this->contents = $this->curlExec($req);

        $httpcode = curl_getinfo($req, CURLINFO_HTTP_CODE);

        curl_close($req);
        
        //TODO Handle HTTP error
        if ($httpcode > 400) {
            //$this->handleError('HTTP Request Error in '.$this->url.': '.$httpcode);
            return false;
        }
    }

    /**
     * Get the HTTP headers
     * 
     * @return array
     */
    function getHeaders() {
        return $this->headers;
    }

    /**
     * Get the value of one header parameter
     * 
     * @param string $value
     * @return mixed
     */
    function getHeader($value) {
        if (isset($this->headers[$value])) {
            return $this->headers[$value];
        } else {
            return null;
        }
    }

    /**
     * Get the request contents
     *
     * @return string
     */
    function getContents() {
        return $this->contents;
    }

    /**
     * Execute the cURL request
     * 
     * @param Object $req cURL Request
     * @return Object
     */
    private function curlExec($req) {
        if ($this->debug) {
            $verbose = fopen('php://temp', 'rw+');
            curl_setopt($req, CURLOPT_VERBOSE, true);
            curl_setopt($req, CURLOPT_STDERR, $verbose);
        }
        
        $ret = curl_exec($req);

        if ($this->debug) {
            if ($ret === FALSE) {
                printf("cUrl error (#%d): %s<br>\n", curl_errno($req),
                        htmlspecialchars(curl_error($req)));
            }

            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);

            echo "<hr />Verbose information:<br />URI: {$this->options['url']}<pre>", htmlspecialchars($verboseLog), "</pre>\n";
        }

        return $ret;
    }

    /**
     * Add a header line to the headers property
     * 
     * @param Object $curl
     * @param String $header_line
     * @return int
     */
    private function handleHeaderLine( $curl, $header_line) {
        $index = $contents = '';
        if (strstr($header_line, ':')) {
            $pos = strpos($header_line, ': ');
            $index = strtolower(substr($header_line, 0, $pos));
            $contents = trim(substr($header_line, $pos+2));
        } elseif (trim($header_line) != '') {
            $index = count($this->headers);
            $contents = trim($header_line);
        }

        if ($contents) {
            if (!isset($this->headers[$index])) {
                $this->headers[$index] = $contents;
            } else {
                if (!is_array($this->headers[$index])) {
                    $this->headers[$index] = array($this->headers[$index]);
                }

                $this->headers[$index][] = $contents;
            }
        }

        return strlen($header_line);
    }
}
