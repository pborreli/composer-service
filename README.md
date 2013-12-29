#Composer as a service

[![Build Status](https://travis-ci.org/pborreli/composer-service.png?branch=master)](https://travis-ci.org/pborreli/composer-service)

## What is it ?

Originally the idea came from @pgodel

>![twitter-pgodel](https://f.cloud.github.com/assets/77759/1818659/a6217488-7018-11e3-8891-4e21f75954a0.png)

Having some issue with little instances or slow connection, I decided to make the tool as a side-project.

## Requirements

rabbitmq-server  
[pusher.com](https://app.pusher.com) account (free plan for few connections)  

## Installation

```bash
composer install
php -S localhost:9090 -t web &
rabbitmq-server &
php app/console sonata:notification:start
```

## Future

An API is planned so it could be possible to be called from inside composer itself.

## License

It's MIT, you can do whatever you like without need of thank or anything.
I'd be glad if you use it, happy if you enjoy it and very happy if you decide to contribute to make it better.
## Contributors

[@pborreli](https://github.com/pborreli)  
[@ubermuda](https://github.com/ubermuda)  
[@cordoval](https://github.com/cordoval) 
