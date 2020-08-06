# iapk-api

> **iAPK**: Free download android apk file

Web service source code for [iapk.org](https://iapk.org/) website with Flight micro-framework for PHP. 

## Structure

```php
Flight::route('/(@lang)', function($lang) {}
Flight::route('/@lang/search/@query/', function($lang, $query) {}
Flight::route('/@lang/@categorySlug/', function($lang, $categorySlug) {}
Flight::route('/@lang/download/@applicationSlug', function($lang, $applicationSlug) {}
Flight::route('/@lang/@categorySlug/@applicationSlug', function($lang, $categorySlug, $applicationSlug) {}
```

## Config for Nginx

```
server {
  #port
  listen 80;
  #domain
  server_name webservice-testing.iapk.org;
  root   /home/android-iapk-site/root/subdomain-api/;
  index index.php;
  location / {
    try_files $uri $uri/ /index.php;
  }
  location ~ \.php$ {
    fastcgi_pass   127.0.0.1:9000;
    fastcgi_index  index.php;
    fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
    include        fastcgi_params;
  }
}

```

### What's Flight?

#### An extensible micro-framework for PHPflightphp.com

Flight is a fast, simple, extensible framework for **PHP**. Flight enables you to quickly and easily **build RESTful** web applications.

https://flightphp.com/

```php
<?php
require 'flight/Flight.php';

Flight::route('/', function(){
  echo 'hello world!';
});

Flight::start();
```

---------

# Max Base

My nickname is Max, Programming language developer, Full-stack programmer. I love computer scientists, researchers, and compilers. ([Max Base](https://maxbase.org/))

<a target="_blank" href="https://www.paypal.com/donate/?cmd=_donations&business=maxbasecode@gmail.com&currency_code=USD&source=url&item_name=Donate:+Supporting+my+open+source+activities+GitHub.com/basemax&item_number=GitHub,+Inc">
<img src="https://raw.githubusercontent.com/BaseMax/BaseMax/master/donate.gif">
</a>

## Asrez Team

A team includes some programmer, developer, designer, researcher(s) especially Max Base.

[Asrez Team](https://www.asrez.com/)
