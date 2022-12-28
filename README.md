# TUTORIAL MVC

MVC is a famous structure paradigm for developing web applications. It is used by many web frameworks in many different programming languages. Some of the frameworks that use this architecture are Laravel and Django. In this tutorial we will structure app to have our own MVC framework.

## FOLDER STRUCTURE

We will create these folders:
* app
* bootstrap
* config
* public
* routes
* views

and these files:
* .htaccess

In our public file we will have index.php and that is our starting point. 

## .htaccess

The .htaccess file is an important file that controls high-level configuration of a website. On Apache web servers, .htaccess allows us to make changes in our website configuration without having to edit server configuration files.

Apache is configured that all files named .htaccess are hidden. Commonly is placed in website's public_html folder.

Use cases of .htaccess are these:

* Redirection for certain URLs
* Load custom error pages
* Forcing HTTPS
* Password protect certain directories
* Prevent hotlinking

Create .htaccess file in root. 

```
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Stop processing if already in the /public directory
    RewriteRule ^public/ - [L]

    # Static resources if they exist
    RewriteCond %{DOCUMENT_ROOT}/public/$1 -f
    RewriteRule (.+) public/$1 [L]

    # Route all other requests
    RewriteRule (.*) public/index.php?route=$1 [L,QSA]
</IfModule>
```

With this setting we can filter all our traffic to public/index.php.

### Bootstraping

Inside config folder create ```app.php```.

```
<?php
define('APP_NAME', "PHP MVC NTIP");
define('APP_ROOT', dirname(dirname(__FILE__)));
```

Inside our public folder create ```index.php``` and include config\app.php.

```
<?php 
require_once '../config/app.php';
```

If everything is ok we can var_dump our APP_NAME eg. ```var_dump(APP_NAME);

For the purpose of autoloading classes, we will be using composer. Composer is a tool for dependency management in PHP. It allows you to declare the libraries your project depends on and it will manage (install/update) them for you.

### composer.json

Inside root create composer.json file and paste this code:
```
{
    "name": "zinaid/mvc-php",
    "description": "Simple MVC framework with PHP",
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}
```
psr-4 auto-loading standard is a standard that maps a path to particular namespace. It also guesses this subfolders according to the namespace defined in it. In our case autoloader checks for any file that has a namespace starting with App in the app directory.

Next create Controllers and Models folders in app.

To install composer dependencies run ```composer dump-autoload```. Include the autoload file in public/index.php.

```
require_once '../vendor/autoload.php';
```


### TESTING BOOTSTRAP WORK

Inside Controllers create php file HomeController.php and create a class that just echoes some message.

```
<?php
namespace App\Controllers;

class HomeController
{
    public function home(Request $request)
    {
        return "Test";
    }
}
```

Now we can call this function in index.php.

```
echo App\Controllers\HomeController::home();
```

### ROUTING

We will try to create routing like in some common frameworks. The aim is to have something like this:
```
<?php

use Core\Framework;

$app = new Framework();

$app::get('/', 'Controller', 'Method');


$app->run();

```

First of all update composer.json:

```
{
    "name": "zinaid/mvc-php",
    "description": "Simple MVC framework with PHP",
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Core\\": "bootstrap/"
        }
    }
}
```

We added the psr-4 route to load Core namespace files from the bootstrap folder. After changing composer.json we need to run ```composer dump-autoload```.

Next step is to create Router.php inside bootstrap folder.

```
<?php
namespace Core;

trait Router
{
    private static $map;
    public static function get($url, $class, $method)
    {
        self::cleanUrl($url);
        self::cleanClass($class);
        self::cleanMethod($method);

        self::$map['get'][$url] = [
            'class'=>$class,
            'method'=>$method
        ];
    }

    public static function post($url, $class, $method)
    {
        self::cleanUrl($url);
        self::cleanClass($class);
        self::cleanMethod($method);

        self::$map['post'][$url] = [
            'class'=>$class,
            'method'=>$method
        ];
    }

    private static function cleanUrl($url)
    {

    }

    private static function cleanClass($class)
    {

    }

    private static function cleanMethod($method)
    {

    }

    public static function getMap(){
        return self::$map;
    }
}
```

Inside this class we are going to map our routes. The first static property map will hold an array of all registered routes.

Static methods get and post will take in the request URI, class and method and match it to their respective request method. Static function getMap will serve as a getter to fetch all values of the private static $map property.

We will use antoher trait named UrlEngine.php.

```
<?php
namespace Core;

trait UrlEngine
{
    public function method()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function path()
    {
        return $_SERVER['REQUEST_URI'];
    }
}
```

This file gets us a lower case value of the request method and current path.

Then we create a file named Framework.php, also inside our bootstrap folder.

```
<?php
namespace Core;

class Framework
{
    use Router, UrlEngine;

    /**
     * @throws \Exception
     */
    public function run()
    {
        //run the match function to get the class and method
        $callable = $this->match($this->method(), $this->path());
        if (!$callable){
            throw new \Exception('Oops! you are lost', 404);
        }
        //call the class, pass the method
        //add the default namespace to the controller
        $class = "App\\Controllers\\".$callable['class'];
        if (!class_exists($class)){
            throw new \Exception('Class does not exist', 500);
        }

        $method = $callable['method'];

        if (!is_callable($class, $method)){
            throw new \Exception("$method is not a valid method in class $callable[class]", 500);
        }
        $class = new $class();

        //run the method
        $class->$method();
        return;
    }

    private function match($method, $url)
    {
        foreach (self::$map[$method] as $uri=>$call){
            //does the $url have a trailing slash? if yes, remove it
            //make sure the only string present is not the slash
            if (substr($url, -1) === '/' && $uri != '/'){
                //remove the slash
                $url = substr($url, 0, -1);
            }
            //if our $uri does not contain a pre-slash, we add it
            if ($url == $uri){
                return $call;
            }
        }
        return false;
    }
}
```

The class below has two methods. Method match and run.

Method match checks the current request URI and mathes it with the $map array we have to return an array of the class and method or false.

Method run gets the class and method from the match method, adds our default namespace to the class, checks if the class and method exists, then calls the method on the class.

### FIRST CONTROLLER

Let us create our first controller in Controllers named HomeController.php.

```
<?php
namespace App\Controllers;

class HomeController
{
    public function home()
    {
        echo "bootstrap working";
    }
}
```

In our routes file we will create web.php file. 

```
<?php

use Core\Framework;

$app = new Framework();

$app::get('/', 'HomeController', 'home');
$app::post('/', 'HomeController', 'home');
$app->run();
```

If everything works it will display our message in HomeController method home().

### VIEW

View is an important part of MVC. We will develop a view engine that will find and render our view files that are placed in view directory. We want to achieve something like this:

```
return view('home.index', compact('first', 'second'))
or
return view ('home.index', ['first', 'second'])
```

Next we create a View.php trait inside boostrap folder.

```
<?php


namespace Core;


trait View
{
    /**
     * @throws \Exception
     */
    public function render($view, $params = [])
    {
        $position = strpos($view, ".");
        if ($position){
            $view = str_replace(".", "/", $view);
        }

        if (is_readable(APP_ROOT."/views/$view.php"))
        {
            return $this->generateView($view, $params);
        }else{
            throw new \Exception("404 PAGE NOT FOUND", 404);
        }
    }

    /**
     * @throws \Exception
     */
    private function generateView($view, $params)
    {
        foreach ($params as $key => $value){
            $$key = $value;
        }

        ob_start();
        require_once APP_ROOT."/views/$view.php";
        echo ob_get_clean();
        return true;
    }
}
```

This trait has methods render and generateView. The render view is called directly and generate function is used for output, after that we call ob_get_clean() to return the content of buffering and delete it.

Inside boostrap we create another folder helpers and inside helpers a new file ```app.php```. This PHP file will shorten long method calls for the framework.

```
<?php
$app = new \Core\Framework();
/**
 * @throws Exception
 */
function view($view, $params = [])
{
    global $app;
    return $app->render($view, $params);
}
```

We have assigned our core/Framework class a variable, and access it as a global in our functions. This way we have access to the view trait.

We modify composer.json and tell him to autload this file anytime application runs.

In the above code, we assigned our core (Framework) class a variable, and accessed it as a global in our functions, this way we have access to the view trait.

Step 3: add this line of code to your composer.json, this is an instruction to autoload this file anytime the application is run.

```
{
    "name": "emiracle/simple-mvc-framework-with-php",
    "description": "Simple MVC framework with PHP",
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Core\\": "bootstrap/"
        },
        "files": ["bootstrap/helpers/app.php"]
    }
}
```
Now run ```composer dump-autoload```. 

Now we create a home directory inside our views folder and inside of it index.php file.

```
<!DOCTYPE html>
<html>
<body>

<h1>My First Framework</h1>

</body>
</html>
```

Now we will say to our HomeController to return the view.

```
<?php
namespace App\Controllers;

class HomeController
{
    public function test()
    {
        return view('home.index');
    }
}
```

If everyhing works we should see content from index.php in views.

### REQUEST PARSING

We will try to achieve this:

```
<?php
namespace App\Controllers;

use Core\Request;

class HomeController
{
    public function home(Request $request)
    {
        $string = "My First Framework";
        $test = $request->test;
        return view('home.index', compact('string', 'test'));
    }
}
```

To achieve that we need to change line 13 in UrlEngine.php and replace it with:

```
<?php
namespace Core;

trait UrlEngine
{
    public function method()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function path()
    {
        return strtok($_SERVER["REQUEST_URI"], '?');
    }
}
```

We do that because we want to separate query string from the URL.

Next we create class Request.php inside bootstrap folder.

```
<?php
namespace Core;

use stdClass;

class Request
{
    private $data;
    use UrlEngine;
    public function __construct()
    {
        $this->data = new stdClass;
        $this->setData();
    }

    private function setData(){
        foreach ($_REQUEST as $key => $value) {
            if($this->method() === 'get'){
                //this makes is dynamically available
                $this->$key = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                //this collects it
                $this->data->$key = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
            elseif ($this->method() === 'post') {
                foreach ($_POST as $key => $value) {
                    $this->$key = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                    $this->data->$key = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }else{
                $this->$key = $value;
                $this->data->$key = $value;
            }
        }
    }

    public function data($x = null){
        return $x?$this->data->$x:$this->data;
    }
}
```

We use stdClass, because we wish to pass data as objects. Construct initializes data as a new object and calls the set data method.

setData method loops through all post and get request, sanitize it and populates data object.

Now we would have to make modifications to our Framework.php file to accommodate the request class when instantiating a method.

```
<?php
namespace Core;


class Framework
{
    use Router, UrlEngine, View;
    private $request;

    public function __construct(){
        $this->request = new Request();
    }
    /**
     * @throws \Exception
     */
    public function run()
    {
        //run the match function to get the class and method
        $callable = $this->match($this->method(), $this->path());
        if (!$callable){
            throw new \Exception('Oops! you are lost', 404);
        }
        //call the class, pass the method
        //add the default namespace to the controller
        $class = "App\\Controllers\\".$callable['class'];
        if (!class_exists($class)){
            throw new \Exception('Class does not exist', 500);
        }

        $method = $callable['method'];

        if (!is_callable($class, $method)){
            throw new \Exception("$method is not a valid method in class $callable[class]", 500);
        }
        $class = new $class();

        //run the method
        $class->$method($this->request);
        return;
    }

    private function match($method, $url)
    {
        foreach (self::$map[$method] as $uri=>$call){
            //does the $url have a trailing slash? if yes, remove it
            //make sure the only string present is not the slash
            if (substr($url, -1) === '/' && $uri != '/'){
                //remove the slash
                $url = substr($url, 0, -1);
            }
            //if our $uri does not contain a pre-slash, we add it
            if ($url == $uri){
                return $call;
            }
        }
        return false;
    }
}
```


We instantiated the Request class in a construct in our Framework.php file and also passed the instantiated request to the method automatically while calling the method on line 38. The reason is to prevent the too few argument supplied error that PHP will throw.

With this we have created simple MVC structure. We will use this structure to make CRUD system. Next steps are to add model components and all CRUD operations over some model. We will be using PDO connection to MySQL database.