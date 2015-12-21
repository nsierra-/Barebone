# Barebone
A really simple PHP7 Framework to build web applications.
(Work in progress).

# Features
- MVC Architecture
- Templates
- Precise Routing
- Basic ORM (in progress)

# Installation
Make sure mod_rewrite is activated. ```git clone``` the repository in a folder at your website root directory. Include ```barebone_dir/Application.php``` to your website's entry point. Voilà !

# Simple Use Case
This framework is highly configurable, and it uses many things by default. For instance, here is the directory structure that Barebone recognizes.
- project_directory/
   - framework/ (the Barebone directory)
   - models/
   - views/
   - controlers/
   - config/
   - index.php
 
Here is a simple routing example, showing you the basic usage. This happens inside ```index.php```.
```php
<?php
require_once 'framework/Application.php';

$app = new Application();
$app->router->addRoute(
	'registering',
	'/register',
	[
		'handler' => [
			'GET'  => 'RegisterControler.registration',
			'POST' => 'RegisterControler.validation'
		]
	]
);
$app->router->addRoute('home', '/', ['handler' => 'DefaultControler.home']);
$app->run();
```

Handler strings are in the form ```ControlerClassName.method```, so this example would imply that inside your ```controlers```directory, you would find...
- controlers/
  - DefaultControler.php
  - RegisterControler.php

... and that you could find this for instance inside ```RegisterControler.php```
```php
<?php
class RegisterControler
{
	public function registration($request, $params)
	{
		$registrationForm = new Form('register');

		$registrationForm
			->input(['name' => 'username', 'value' => 'username'])
			->input(['name' => 'password', 'value' => 'password'])
			->submit('Duh yaself a favurr')
		;

		return new Template(
			'register',
			['registration_form' => $registrationForm->getHTML()]
		);
	}

	public function validation($request, $params)
	{
		return new Template('registered');
	}
}
```

As you can see, you did not have to include anything to use ```Form```and ```Template``` classes, thanks to autoloading. This convenience will be the same thorough all your files and applies to every single class of your project that are inside the aformentionned directories.

You also could take a look at ```Form```'s API and the way to use a template for your view. Lets see this return instruction once again :
```php
		return new Template(
			'register',
			['registration_form' => $registrationForm->getHTML()]
		);
```

This implies that you have a file called ```register.stpl``` inside your ```views```directory. Here we linked template variable ```registration_form``` to the result of ```$registrationForm->getHTML()```. Using this in the template file is quite easy :
```html
... <head> and everything ...

<h1>Register Page</h1>
{registration_form}
```

(Work in progress...)
