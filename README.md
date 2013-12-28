Composer as a service
---------------------

```cli
$ composer install
$ php -S localhost:9090 -t web &
$ rabbitmq-server &
$ php app/console sonata:notification:start
```
