<?php

namespace Modera\TestsRunner;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\AssertionFailedError;

trait TestListenerDefaultImplementation
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
    public function startTestSuite(TestSuite $suite): void
    {
        $this->interceptorsExecutor->handleSuite($suite);
    }

    /**
     * {@inheritdoc}
     */
    public function endTestSuite(TestSuite $suite): void
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
