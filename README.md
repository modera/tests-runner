# modera/tests-runner

[![StyleCI](https://styleci.io/repos/66460721/shield)](https://styleci.io/repos/66460721)
[![Build Status](https://travis-ci.org/modera/tests-runner.svg?branch=master)](https://travis-ci.org/modera/tests-runner)

The package provides a PHPUnit listener that can be used to perform certain operations before/after test suite is 
executed. For instance when running functional tests you may want to create a database before test is run and drop 
it afterwards.

## Installation

 * In a project where you want to run tests use this command to install runner bash script:
 
        wget https://raw.githubusercontent.com/modera/tests-runner/master/scripts/phpunit.sh && chmod +x phpunit.sh

 * Create file named **.mtr** (notice a dot in the beginning) next to your phpunit.xml/phpunit.xml.dist, sample:
 
        <?php
        
        return [
            new \Modera\TestsRunner\Interceptors\ServerEnvExportingInterceptor(array(
                'SYMFONY__DB_HOST' => 'mysql',
                'SYMFONY__DB_PORT' => 3306,
                'SYMFONY__DB_USER' => 'root',
                'SYMFONY__DB_PASSWORD' => '123123'
            )),
            new \Modera\TestsRunner\Interceptors\SymfonyPhpUnitEnvInterceptor(),
            new \Modera\TestsRunner\Interceptors\MySqlInterceptor(
                function() { // config provider
                    return array(
                        'host' => $_SERVER['SYMFONY__DB_HOST'],
                        'user' => $_SERVER['SYMFONY__DB_USER'],
                        'password' => $_SERVER['SYMFONY__DB_PASSWORD'],
                        'port' => $_SERVER['SYMFONY__DB_PORT'],
                        'attempts' => isset($_SERVER['DB_ATTEMPTS']) ? $_SERVER['DB_ATTEMPTS'] : 40,
                    );
                }
            ),
        ];
        
 This file is responsible for creating so called interceptors - additional pieces of code that will get executed before
 and after test-cases.
        
 * Update your phpunit.xml file to reference test runner's listener, here we are assuming that test runner is located
 in a directory called *mtr* (by default installing script from the first step installs the tests runner there):
 
        <listeners>
            <listener class="Modera\TestsRunner\PhpUnitListener" file="./mtr/src/Modera/TestsRunner/PhpUnitListener.php"></listener>
        </listeners>
        
 * Now you can use `phpunit.sh` script created in the first step to run your tests

## Interceptors

Before we take a look at specific implementations of interceptors that come with the tests runner it makes sense to say
a couple words about what interceptors really are and what they are used for. Essentially, as you probably have already
guessed, interceptors allow you to perform additional actions before and after PHPUnit switcheds a package that it is 
running tests for. The package itself is designated by existence of composer.json file. Imaging that you have a following 
files structure:
    
    src\
        Acme\
            FooBundle\
                Tests\
                    ...
                composer.json
            BarBundle\
                Tests\
                    ...
                composer.json
                
When tests-runner runs tests for *src/* directory it will run interceptors two times, more specifically:

 * Before entering FooBundle
 * Before leaving a FooBundle and entering BarBundle
 * Before entering BarBundle
 * Before leaving BarBundle and possibly entering a next package
 
In order to implement an interceptor you need to implement Modera\TestRunner\InterceptorInterface, please consult to 
its API docs for more information. Now that you have understanding of what interceptors are let's take a look at those
ones which are provided out of the box:

 * **MySqlInterceptor** - this interceptor allows to create and drop a MySQL database. For example, if a package's name is
   modera/foo (name is take from "name" parameter of composer.json file), then before PHPUnit running tests this interceptor
   will create a table with name "modera_foo" and after the tests execution is complete it will drop the table automatically.
 * **ServerEnvExportingInterceptor** - allows to define environment variables if they do not exist yet.
 * **SymfonyPhpUnitEnvInterceptor** - this one is interesting, you will want to use it if you are running tests for monolithic
   repository which contains Symfony bundles. This interceptor will check if there's a phpunit.xml/phpunit.xml.dist file
   placed next to composer.json and if it is then the interceptor will scan its <php> section to detect if environment
   variable **KERNEL_DIR** is set and if it is defined then it will dynamically update its path so that Symfony's
   WebTestCase would know where to load a kernel class from. Please read docs provided by Symfony describing how
   to prepare your bundles for running functional tests - http://symfony.com/doc/current/testing.html#your-first-functional-test.
   
 
 