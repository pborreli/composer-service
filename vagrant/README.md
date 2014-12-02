# Vagrant usage

Requirements:
* [Vagrant](http://www.vagrantup.com/downloads.html) >= 1.4.0
* NFS Server

First you should add composer-service.dev address to ``/etc/hosts`` on the host machine.

```
# /etc/hosts

10.0.0.215  composer-service.dev
```

Now you should setup the virtual machine

```

$ cd vagrant
$ vagrant up
$ vagrant ssh
$ cd /var/www/composer-service
$ composer install
$ php app/console assetic:dump
$ php app/console sonata:notification:start
```

From now you should be able to open http://composer-service.dev/app_dev.php in your browser.
