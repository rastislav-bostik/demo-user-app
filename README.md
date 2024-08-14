Prerequesities
--------------

* [PHP 8.1+][1]
* [Composer][2]
* [Symfony CLI][3] (optional)

Installation
------------

* clone the repository
```console
[foo@bar ~]$ git clone https://github.com/rastislav-bostik/demo-user-app.git
```
* move to cloned folder
```console
[foo@bar ~]$ cd demo-user-app
```
* install dependencies
```console
[foo@bar ~/demo-user-app]$ composer install
```
* initialize in SQLite database
```console
[foo@bar ~/demo-user-app]$ bin/console doctrine:migrations:migrate
 WARNING! You are about to execute a migration in database "main" that could result in schema changes and data loss. Are you sure you wish to continue? (yes/no) [yes]:
 > yes
```
* start the local server at localhost:8000
```console
[foo@bar ~/demo-user-app]$ symfony server:start
```
* open your browser and go to http://localhost:8000/api to see the swagger UI

Find out more
-------------
For API JSON schemas open:
- http://localhost:8000/api/docs.jsonopenapi
- http://localhost:8000/api/docs.jsonld

To see naked JSON data open:
- http://localhost:8000/api/users

Enjoy!

[1]: https://www.php.net/manual/en/install.php
[2]: https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos
[3]: https://symfony.com/download
