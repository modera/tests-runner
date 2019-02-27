<?php

namespace Modera\TestsRunner;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\AssertionFailedError;

if (trait_exists('PHPUnit\Framework\TestListenerDefaultImplementation')) {
    trait TestListenerDefaultImplementation
    {
        use \PHPUnit\Framework\TestListenerDefaultImplementation;

        /**
         * {@inheritdoc}
         */
        public function startTestSuite(TestSuite $suite)
        {
            $this->interceptorsExecutor->handleSuite($suite);
        }
    }
} else {
    trait TestListenerDefaultImplementation
    {
        /**
         * {@inheritdoc}
         */
        public function addError(Test $test, \Exception $e, $time)
        {
        }

        /**
         * {@inheritdoc}
         */
        public function addWarning(Test $test, Warning $e, $time)
        {
        }

        /**
         * {@inheritdoc}
         */
        public function addFailure(Test $test, AssertionFailedError $e, $time)
        {
        }

        /**
         * {@inheritdoc}
         */
        public function addIncompleteTest(Test $test, \Exception $e, $time)
        {
        }

        /**
         * {@inheritdoc}
         */
        public function addRiskyTest(Test $test, \Exception $e, $time)
        {
        }

        /**
         * {@inheritdoc}
         */
        public function addSkippedTest(Test $test, \Exception $e, $time)
        {
        }

        /**
         * {@inheritdoc}
         */
        public function startTestSuite(TestSuite $suite)
        {
            $this->interceptorsExecutor->handleSuite($suite);
        }

        /**
         * {@inheritdoc}
         */
        public function endTestSuite(TestSuite $suite)
        {
        }

        /**
         * {@inheritdoc}
         */
        public function startTest(Test $test)
        {
        }

        /**
         * {@inheritdoc}
         */
        public function endTest(Test $test, $time)
        {
        }
    }
}
