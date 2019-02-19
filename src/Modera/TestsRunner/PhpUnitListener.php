<?php

namespace Modera\TestsRunner;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\AssertionFailedError;

if (class_exists('PHPUnit\Framework\BaseTestListener')) {
    class PhpUnitTestListener extends \PHPUnit\Framework\TestListener
    {
        /**
         * {@inheritdoc}
         */
        public function startTestSuite(\PHPUnit\Framework\TestSuite $suite)
        {
            $this->interceptorsExecutor->handleSuite($suite);
        }

        /**
         * An error occurred.
         */
        public function addError(Test $test, \Throwable $t, float $time): void
        {
        }

        /**
         * A warning occurred.
         */
        public function addWarning(Test $test, Warning $e, float $time): void
        {
        }

        /**
         * A failure occurred.
         */
        public function addFailure(Test $test, AssertionFailedError $e, float $time): void
        {
        }

        /**
         * Incomplete test.
         */
        public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
        {
        }

        /**
         * Risky test.
         */
        public function addRiskyTest(Test $test, \Throwable $t, float $time): void
        {
        }

        /**
         * Skipped test.
         */
        public function addSkippedTest(Test $test, \Throwable $t, float $time): void
        {
        }

        /**
         * A test suite ended.
         */
        public function endTestSuite(TestSuite $suite): void
        {
        }

        /**
         * A test started.
         */
        public function startTest(Test $test): void
        {
        }

        /**
         * A test ended.
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
