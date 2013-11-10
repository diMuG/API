## diMuG API ##
[![Build Status](https://travis-ci.org/diMuG/API.png?branch=master)](https://travis-ci.org/diMuG/API)
[![Coverage Status](https://coveralls.io/repos/diMuG/API/badge.png)](https://coveralls.io/r/diMuG/API)

This is an sample implementation of an API to provide a data source for diMuG - the digital Museums Guide. It's based on
[silex](https://github.com/silexphp/Silex "Silex").

## Installation ##
1. Install [composer](http://getcomposer.org/download/ "composer") or just run the following command:
```
    curl -s http://getcomposer.org/installer | php
```
2. Add following to your **composer.json** and run composer install.
```
    "require" : {
        "dimug/api": "dev-master"
    }
```

3. Run the additional install script via the console by running the following command:
```
    php app/console api:install
```
or if you prefer Deutsch:
```
    php app/console api:install de
```

## How to connect your data source ##
2. Create a class which implements the **diMuG\APIv1\Interfaces\FinderInterface**.
3. Create a class which implements the **diMuG\APIv1\Interfaces\GlossaryInterface**.
4. Use the PHPUnit test skeleton files in the dir **tests** to test your classes.
5. Edit the configuration files **config/configuration.yml** and **config/security.yml** so that they represent your
data. Use the following console command to test your configuration files:
```
    php app/console api:validate
```
or if you prefer Deutsch:
```
    php app/console api:validate de
```