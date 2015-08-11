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
namespace HANATAP;

use HANATAP\Request;
use \DOMDocument;

/**
 * HANATAP\Proxy
 * HANATrial Auth Proxy in PHP
 * 
 * @author Rafael Zanetti <rafatz@gmail.com>
 * @uses HANATAP\Request Simple HTTP Request written in PHP
 */
class Proxy {
    /**
     *
     * @var string URL destination of the request
     */
    private $url;
    
    /**
     *
     * @var array HANATAP options
     */
    private $auth_options;
    
    /**
     *
     * @var mixed Auth Cookie
     */
    private $cookie;

    /**
     * 
     *
     * @param array $options HANATAP options
     */
    function __construct($options) {
        $this->url = 'https://'.$options['host'].':'.$options['path'];
        $this->auth_options = $options;

        $this->generateAuthCookie();
    }

    /**
     * Return the auth cookie
     *
     * @return mixed
     */
    function getCookie() {
        return $this->cookie;
    }

    /**
     * Add proxy option when necessary
     * 
     * @param String $url
     * @return array
     */
    private function getOptions($url) {
        $options = array(
            'url' => $url,
            'follow_location' => 0,
            'ssl_check' => 0
        );

        if (isset($this->auth_options['proxy'])) {
            $options['proxy'] = $this->auth_options['proxy'];
        }

        return $options;
    }

    /**
     * Generate the auth cookie 
     * 
     * @return mixed
     */
    private function generateAuthCookie() {
        $req = new Request($this->getOptions($this->url));

        $cookieHana = $req->getHeader('set-cookie');

        $location = $req->getHeader('location');

        $req2 = new Request($this->getOptions($location));

        $cookieAccounts = $req2->getHeader('set-cookie');

        $contents = $req2->getContents();

        $dom = new DOMDocument();
        @$dom->loadHTML($contents);

        $action = $dom->getElementsByTagName('form')->item(0)->getAttribute('action');

        $inputs = $dom->getElementsByTagName('input');
        $form = array();
        foreach ($inputs as $input) {
            switch($input->getAttribute('name')) {
                case 'j_username':
                    $value = $this->auth_options['username'];
                    break;
                case 'j_password':
                    $value = $this->auth_options['password'];
                    break;
                default:
                    $value = $input->getAttribute('value');
                    break;
            }
            $form[$input->getAttribute('name')] = $value;
        }

        $loginurl = $form['targetUrl'].$action;
        $options = $this->getOptions($loginurl);
        $options['form'] = $form;

        foreach($cookieAccounts as $cookie) {
            $options['headers'][] = 'Cookie: '.$cookie;
        }

        $post_req = new Request($options, 'POST');

        $cookieAccounts = $post_req->getHeader('set-cookie');

        // Now we call the initial site
        $dom = new DOMDocument();
        @$dom->loadHTML($post_req->getContents());

        $messages = $dom->getElementById('globalMessages');
        if ($messages == '') {
            // Read the Form Action + Method
            $action = $dom->getElementsByTagName('form')->item(0)->getAttribute('action');
            $inputs = $dom->getElementsByTagName('input');
            $form = array();
            foreach ($inputs as $input) {
                $form[$input->getAttribute('name')] = $input->getAttribute('value');
            }
            $options = $this->getOptions($action);
            $options['form'] = $form;
            foreach($cookieHana as $cookie) {
                $options['headers'][] = 'Cookie: '.$cookie;
            }

            $post_req2 = new Request($options, 'POST');
            $this->cookie = $post_req2->getHeader('set-cookie');
        } else {
            return;
        }
    }
}
