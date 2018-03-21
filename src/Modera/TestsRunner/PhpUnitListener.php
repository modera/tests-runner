<?php

namespace Modera\TestsRunner;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class PhpUnitListener extends \PHPUnit_Framework_BaseTestListener
{
    /**
     * @var InterceptorsExecutor
     */
    private $interceptorsExecutor;

    public function __construct()
    {
        // BC, if installed in old way
        $loaderPath = __DIR__.'/../../../vendor/autoload.php';
        if (file_exists($loaderPath)) {
            require_once $loaderPath;
        } else {
            //throw new \RuntimeException();
        }

        $interceptors = [];

        $configFilePath = getcwd().'/.mtr';
        if (file_exists($configFilePath)) {
            $interceptors = require_once $configFilePath;
        }

        $this->interceptorsExecutor = new InterceptorsExecutor($interceptors);
    }

    /**
     * {@inheritdoc}
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->interceptorsExecutor->handleSuite($suite);
    }
}
