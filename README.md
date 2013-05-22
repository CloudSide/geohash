[![](http://vdisk.me/static/images/vi/logo/32x32.png)](#) VdiskSDK-PHP
============

请先前往 [微盘开发者中心](http://vdisk.weibo.com/developers/) 注册为微盘开发者, 并创建应用.

RESTful API文档:
[![](http://vdisk.me/static/images/vi/icon/16x16.png)](http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc)
http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc


Demo演示地址: http://vauth.appsina.com/


SDK For PHP文档地址: http://vauth.appsina.com/Doc/namespaces/Vdisk.html


关于微盘OPENAPI、SDK使用以及技术问题请联系: [@一个开发者](http://weibo.com/smcz)

QQ群: 134719337、162285095

邮箱: [cloudside@sina.cn](mailto:cloudside@sina.cn)

[![](http://service.t.sina.com.cn/widget/qmd/1656360925/02781ba4/4.png)](http://weibo.com/smcz)


### Requirements

* PHP >= 5.3.1
* [PHP cURL]

### Usage


- 用户授权

```php

//实例化 \Vdisk\OAuth2
$oauth2 = new \Vdisk\OAuth2('您应用的appkey', '您应用的appsecret');
$auth_url = $oauth2->getAuthorizeURL('您在开发者中心设置的跳转地址');
/*
引导用户访问授权页面: $auth_url
*/

```

- 获得access token

```php

//用户授权成功后, 会跳转到你的callback地址, 您需要用code参数换取access token

if (isset($_REQUEST['code'])) {
  
  $keys = array();
	$keys['code'] = $_REQUEST['code'];
	$keys['redirect_uri'] = '您在开发者中心设置的跳转地址';
	
	try {
		
		$token = $oauth2->getAccessToken('code', $keys);
		print_r(token); //得到access token
		
	} catch (Exception $e) {
		
		echo "<pre>";
		print_r($e->getMessage());
		echo "</pre>";
		echo "<a href='index.php'>返回</a>";
	}
}

```

- 获得用户信息

```php

$client = new \Vdisk\Client($oauth2);
$client->setDebug(true); //开启调试模式
		
try {
			
	// Attempt to retrieve the account information
	$response = $client->accountInfo(); //调用accountInfo方法
	$accountInfo = $response['body'];
	// Dump the output
	echo "<pre>";
	print_r($accountInfo); //打印用户信息
	echo "</pre>";

} catch (\Vdisk\Exception $e) { //捕获异常
			
	echo "<pre>";
	echo get_class($e) . ' ' . '#' . $e->getCode() . ': ' . $e->getMessage();
	echo "</pre>";
}

```

- 获得文件列表、目录/文件信息

```php

$client = new \Vdisk\Client($oauth2, 'basic');
//$client->setDebug(true);  //开启调试模式

try {
	
	if (isset($_GET['path'])) {
		
		$path = $_GET['path'];
	
	} else {
		
		$path = '/';	
	}
	
	// Attempt to retrieve the account information
	$response = $client->metaData($path); //调用metaData方法
	$metaData = $response['body'];

} catch (\Vdisk\Exception $e) { //捕获异常
	
	echo "<pre>";
	echo get_class($e) . ' ' . '#' . $e->getCode() . ': ' . $e->getMessage();
	echo "</pre>";
}

```

- 下载文件

```php

$client = new \Vdisk\Client($oauth2, 'basic');

$client->setDebug(true); //开启调试模式

try {

    $response = $client->getFile('云端文件的全路径', '下载到本地目标文件的全路径');
   
    // Dump the output
    echo "<pre>";
    print_r($response);
    echo "</pre>";

} catch (\Vdisk\Exception $e) { //捕获异常

    echo "<pre>";
    echo get_class($e) . ' ' . '#' . $e->getCode() . ': ' . $e->getMessage();
    echo "</pre>";
}

```

- 上传文件(POST)

```php

$client = new \Vdisk\Client($oauth2, 'basic');

$client->setDebug(true); //开启调试模式

try {

    $response = $client->putFile('本地文件真实路径', '要上传的目标全路径');
   
    // Dump the output
    echo "<pre>";
    print_r($response);
    echo "</pre>";

} catch (\Vdisk\Exception $e) { //捕获异常

    echo "<pre>";
    echo get_class($e) . ' ' . '#' . $e->getCode() . ': ' . $e->getMessage();
    echo "</pre>";
}

```

- 上传文件(PUT)

```php
$client = new \Vdisk\Client($oauth2, 'basic');

$client->setDebug(true); //开启调试模式

try {

    $response = $client->putStream(fopen('本地文件真实路径', 'r'), '云端目标文件全路径');
   
    // Dump the output
    echo "<pre>";
    print_r($response);
    echo "</pre>";

} catch (\Vdisk\Exception $e) { //捕获异常

    echo "<pre>";
    echo get_class($e) . ' ' . '#' . $e->getCode() . ': ' . $e->getMessage();
    echo "</pre>";
}

```

