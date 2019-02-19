<?php

namespace Modera\TestsRunner;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\AssertionFailedError;

if (interface_exists('PHPUnit\Framework\TestListener')) {
    class PhpUnitTestListener implements \PHPUnit\Framework\TestListener
    {
        /**
         * {@inheritdoc}
         */
        public function addError(Test $test, \Throwable $t, float $time): void
        {
        }

        /**
         * {@inheritdoc}
         */
        public function addWarning(Test $test, Warning $e, float $time): void
        {
        }

        /**
         * {@inheritdoc}
         */
        public function addFailure(Test $test, AssertionFailedError $e, float $time): void
        {
        }

        /**
         * {@inheritdoc}
         */
        public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
        {
        }

        /**
         * {@inheritdoc}
         */
        public function addRiskyTest(Test $test, \Throwable $t, float $time): void
        {
        }

        /**
         * {@inheritdoc}
         */
        public function addSkippedTest(Test $test, \Throwable $t, float $time): void
        {
        }

        /**
         * {@inheritdoc}
         */
        public function startTestSuite(\PHPUnit\Framework\TestSuite $suite): void
        {
            $this->interceptorsExecutor->handleSuite($suite);
        }

        /**
         * {@inheritdoc}
         */
        public function endTestSuite(\PHPUnit\Framework\TestSuite $suite): void
        {
        }

        /**
         * {@inheritdoc}
         */
        public function startTest(Test $test): void
        {
        }

        /**
         * {@inheritdoc}
         */
        public function endTest(Test $test, float $time): void
        {
        }
    }
} else {
    class PhpUnitTestListener extends \PHPUnit_Framework_BaseTestListener
    {
        /**
         * {@inheritdoc}
         */
        public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
        {
            $this->interceptorsExecutor->handleSuite($suite);
        }
    }
}

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class PhpUnitListener extends PhpUnitTestListener
{
    /**
     * @var InterceptorsExecutor
     */
    protected $interceptorsExecutor;

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
}
