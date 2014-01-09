#Composer as a service [![SensioLabsInsight](https://insight.sensiolabs.com/projects/20408423-f871-4d67-a87c-8967bedd6ef7/small.png)](https://insight.sensiolabs.com/projects/20408423-f871-4d67-a87c-8967bedd6ef7)

## What is it ?

Originally the idea came from @pgodel

>![twitter-pgodel](https://f.cloud.github.com/assets/77759/1818659/a6217488-7018-11e3-8891-4e21f75954a0.png)

Having some issue with little instances or slow connection, I decided to make the tool as a side-project.

## Requirements

rabbitmq-server  
[pusher.com](https://app.pusher.com) account (free plan for few connections)  

## Installation

```bash
composer create-project pborreli/composer-service -sdev
cd composer-service
php -S localhost:9090 -t web &
rabbitmq-server &
php app/console sonata:notification:start
```

## Run test suite

You will need phantomjs executable:

```bash
brew update && brew install phantomjs
```

## Future

 - An API is planned so it could be possible to be called from inside composer itself.
 - More tests
 - Better code
 - Insert your needed feature here

## License

It's MIT, you can do whatever you like without need of thank or anything.
I'd be glad if you use it, happy if you enjoy it and very happy if you decide to contribute to make it better.

## Quality

[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/pborreli/composer-service/badges/quality-score.png?s=e24aa692dbefefcc7c9ed77bc1e9c64968a12571)](https://scrutinizer-ci.com/g/pborreli/composer-service/)
[![Code Coverage](https://scrutinizer-ci.com/g/pborreli/composer-service/badges/coverage.png?s=0b0899966b79caa9e06c881b9bc6e9c7ac8dafe7)](https://scrutinizer-ci.com/g/pborreli/composer-service/)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/20408423-f871-4d67-a87c-8967bedd6ef7/mini.png)](https://insight.sensiolabs.com/projects/20408423-f871-4d67-a87c-8967bedd6ef7)
[![Build Status](https://travis-ci.org/pborreli/composer-service.png?branch=master)](https://travis-ci.org/pborreli/composer-service)
[![Dependency Status](https://www.versioneye.com/php/pborreli:composer-service/dev-master/badge.png)](https://www.versioneye.com/php/pborreli:composer-service/dev-master)

## Contributing

If you wish to contribute to this website, please [fork it on GitHub](https://github.com/pborreli/composer-service/fork), push your
change to a named branch, then send me a pull request.

## Contributors

[@pborreli](https://github.com/pborreli)  
[@ubermuda](https://github.com/ubermuda)  
[@cordoval](https://github.com/cordoval)  
[@youbs](https://github.com/youbs) 

