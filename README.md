# Barebone
A really simple framework to build web applications using the classic toolchain Apache >= 2.2, Mysql >= 5.5 and PHP7.
(Work in progress).

# Features
- MVC Architecture
- Templates
- Precise Routing
- Basic ORM (in progress)
- Only native PHP, no external library required

# Installation
Make sure mod_rewrite is activated. ```git clone``` the repository in a folder at your website root directory. Voilà !

# Quick And Simple Infos As A Quick Start Guide
After cloning, create your directory structure (see Configuration - Application). Add your mysql dsn specifying only host/port/socket. Run ```php barebone/scripts/db.php create``` to configure your dbname and character set.
Then, setup your routes, controlers, model classes, and models metadata, run ```php barebone/scripts/db.php prepareModels``` to generate getters and setters, ```php barebone/scripts/db.php update``` to create tables according to your models, and loop on that sentence.

# Some More Infos
## Application Configuration

By default, Barebone awaits this directory structure :
- project_directory/
   - barebone/
   - models/
   - views/
   - controlers/
   - config/
        - database.json
        - application.json
   - index.php

Use this and you'll have nothing to configure except ```database.json``` !
Nonetheless if this structure doesn't suits you, Barebone is okay with that. The only thing it is requiring is a specific directory for all those things. Inside your ```application.json```, simply tell where everything is :
```json
{
	"viewsDir": "/super/path/to/views",
	"modelsDir": "/super/path/to/models",
	"rootDir": "/super/path/to/the/website/root/directory"
}
```

## Routing Example
Inside your entry point :
```php
<?php
require_once 'barebone/Application.php';

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

## Controler Example
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

## View Example
A Barebone controler can return a ```string```, a ```Response``` or a ```Template```. The first two are really trivial and will not be discussed here. The third one is also trivial, but I want to say something so that this paragraph is not too short. ```Template``` will search inside your ```viewsDir``` to find a file corresponding to the first parameter of it's constructor and will replace all strings of form ```{foo}``` by the value you associated with ```foo``` into the second parameter. The above example :
```php
	return new Template(
		'register',
		['registration_form' => $registrationForm->getHTML()]
	);
```
implies that there is a ```register.stpl``` inside your views directory (the extension ```stpl``` stands for "Simple TemPLate") :
```html
... <head> and everything ...

<h1>Register Page</h1>
{registration_form}
```

## Last But Not Least : Model Example
A model for Barebone consists of two things. A PHP class, written under the models directory. And a JSON metadata file under the models metadata directory (by default, they will be the same).
```php
<?php
class User
{
	private $name;
	private $pass;
	/* ... */
}
```
```json
{
	"table": "User",  <-- Optional
	"name": {
		"type": "string",
		"length": 30,
		"unique": true
	},
	"pass": {
		"type": "string",
		"length": 128
	}
}
```
Please note (again ?) the two more-than-useful scripts Barebone provides :
- ```php barebone/scripts/db.php prepareModels``` generates automatically getters and setters based on your classes attributes ;
- ```php barebone/scripts/db.php update```generates automatically all your models' tables.

# License
Barebone is distributed under the MIT License, which means that you can do whatever you want with this code, but still I'm a megalomaniac cause you'll have to say that originally it was my work.

# Lacks a Feature you Love ?
Please contribute !
