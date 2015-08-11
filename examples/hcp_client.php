<?php
require_once '../library/HANATAP.php';
require_once '../library/HANATC.php';

$proxy = new HANATC(array(
    'username'      => '', //Your HCP user. Ex, p000000000
    'accountname'   => '', // You HCP account name. Ex. p000000000trial
    'password'      => '', // Your HCP password
    'host'          => '', // The HCP host where your application is installed. Ex. s9hanaxs.hanatrial.ondemand.com
    'namespace'     => '', // Namespace of the application. Ex. app.package
    'proxy'         => '', // OPTIONAL: Proxy of your network. Ex. proxyhost:port
    // Extra options
    'params'        => '$metadata',
    'format'        => 'json'
));

// Set the header to dislay the content correctly
header('Content-Type: '.$proxy->getContentType());

echo '<pre>';
print_r(json_decode($proxy->getContents()));