<?php
// Autoload the classes
require_once '../vendor/autoload.php';
// You can also add it manually
//require_once '../library/Request.php';
//require_once '../library/Proxy.php';
//require_once '../library/Client.php';

use HANATAP\Client;

$proxy = new Client(array(
    'username'      => '', //Your HCP user. Ex, p000000000
    'accountname'   => '', // You HCP account name. Ex. p000000000trial
    'password'      => '', // Your HCP password
    'host'          => '', // The HCP host where your application is installed. Ex. s9hanaxs.hanatrial.ondemand.com
    'namespace'     => '', // Namespace of the application. Ex. app.package
    'proxy'         => '', // OPTIONAL: Proxy of your network. Ex. proxyhost:port
    // Extra options
    'params'        => '$metadata'
));

// Set the header to dislay the content correctly
header('Content-Type: '.$proxy->getContentType());

echo $proxy->getContents();