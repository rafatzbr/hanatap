# HANATAP
> A proxy written in PHP to authenticate against HANATrial instances in hanatrial.ondemand.com

Inspired by the excellent https://github.com/gregorwolf/hanatrial-auth-proxy

## Main Features
* **Simple** - Very easy to learn and use
* **Complete** - Contains the Autentication Proxy, an HCP Client and a Simple HTTP Request Library
* **Free** - With the MIT license you can basically do anything you want

## Getting Started

```php
// Include Composer's Autoload
require_once 'vendor/autoload.php';

// Or you can manually add the files
//require_once '../library/Request.php';
//require_once '../library/Proxy.php';
//require_once '../library/Client.php';

// Set the namespace
use HANATAP\Client;

// Initialize the object
$proxy = new Client(array(
    'username'      => '', //Your HCP user. Ex, p000000000
    'accountname'   => '', // Your HCP account name. Ex. p000000000trial
    'password'      => '', // Your HCP password
    'host'          => '', // The HCP host where your application is installed. Ex. s9hanaxs.hanatrial.ondemand.com
    'namespace'     => '', // Namespace of the application. Ex. app.package
    'proxy'         => '', // OPTIONAL: Proxy of your network. Ex. proxyhost:port
    // Extra options
    'params'        => '$metadata'
));

// Set the header to display the content correctly
header('Content-Type: '.$proxy->getContentType());

// Show the content
echo $proxy->getContents();
```

## Configuration Parameters
* **username** - Your HCP user. Ex, p000000000
* **accountname** - Your HCP account name. Ex. p000000000trial
* **password** - Your HCP password
* **host** - The HCP host where your application is installed. Ex. s9hanaxs.hanatrial.ondemand.com
* **namespace** - Namespace of the application. Ex. app.package
* **file** - File you want to access in your application that is inside the namespace. Ex. file.xodata
* **params** - OPTIONAL: Optional URL parameters. Ex. $metadata
* **format** - OPTIONAL: Format of the data to be returned by the object. Ex. json
* **proxy** - OPTIONAL: Proxy of your network. Ex. proxyhost:port
* **path** - OPTIONAL: Full path of the application. You can use instead of namespace, file, params and format Ex. /<Account Name>/<Application>/<Package>/file.xsodata/$metadata

## License
HANATAP is under the MIT License

## Links
* HANA Cloud Platform - Developer License: [http://hanatrial.ondemand.com]