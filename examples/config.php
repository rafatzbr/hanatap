<?php
/**
 * Basic options to initialize the HANATrialProxy Object
*/
$options = array(
	'username' 	=> '', //Your HCP user. Ex, p000000000
	'accountname' 	=> '', // You HCP account name. Ex. p000000000trial
	'password' 	=> '', // Your HCP password
	'host' 		=> '', // The HCP host where your application is installed. Ex. s9hanaxs.hanatrial.ondemand.com
	'namespace' 	=> '', // Namespace of the application. Ex. app.package
	'proxy' 	=> '' // OPTIONAL: Proxy of your network. Ex. proxyhost:port
	// Other options
	// 'file' 	=> '', // File you want to access in your application that is inside the namespace. Ex. file.xodata
	// 'params' 	=> '', // OPTIONAL: Any extra URL parameter. Ex. $metadata
	// 'format' 	=> '', // OPTIONAL: Format of the data to be returned by the object. Ex. json
	// 'path' 	=> '', // OPTIONAL: Full path of the application. Ex. /<Account Name>/<Application>/<Package>/file.xsodata/$metadata
);

