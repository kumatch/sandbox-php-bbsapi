(Sandbox) API base BBS sample application
========

Requirement
----

* PHP >= 5.5
* Composer
* PDO sqlite driver


Instllation
----

### 1. Clones this Git repository

この Git リポジトリを Clone します.

```
$ cd /path/to
$ git clone https://github.com/kumatch/sandbox-php-bbsapi.git
```


### 2. Installs PHP Composer modules

PHP Composer モジュールをインストールします.


```
$ cd /path/to/sandbox-php-bbsapi
$ composer install
```


### 3. Creates an application database

アプリケーションデータベースを作成します.

```
$ cd /path/to/sandbox-php-bbsapi/scripts/doctrine
$ ./create-db.sh
```

then created a SQLite database to `db/db.sqlite`.
すると `db/db.sqlite` という SQLite データベースが作成されます.

### 4. Runs application server with PHP built-in web server

PHP ビルトイン Web サーバでアプリケーションを起動します.

```
$ cd /path/to/sandbox-php-bbsapi/public
$ php -S localhost:8080
```

and access to [http://localhost:8080/](http://localhost:8080/).
そして [http://localhost:8080/](http://localhost:8080/) へアクセスします。


Usage
---

* [API](https://github.com/kumatch/sandbox-php-bbsapi/blob/master/API.md)
