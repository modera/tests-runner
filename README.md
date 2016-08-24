# modera/tests-runner

[![StyleCI](https://styleci.io/repos/66460721/shield)](https://styleci.io/repos/66460721)

The package provides a PHPUnit listener that can be used to perform certain operations before/after test suite is 
executed. For instance when running functional tests you may want to create a database before test is run and drop 
it afterwards.

## Installation

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
                        'attempts' => isset($_SERVER['DB_ATTEMPTS']) ? $_SERVER['DB_ATTEMPTS'] : 5,
                    );
                }
            ),
        ];
        
 * Update your phpunit.xml file to reference test runner's listener, here we are assuming that test runner is located
 in directory called *mtr*:
 
        <listeners>
            <listener class="Modera\TestsRunner\PhpUnitListener" file="./mtr/src/Modera/TestsRunner/PhpUnitListener.php"></listener>
        </listeners>