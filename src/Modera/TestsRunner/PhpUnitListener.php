<?php

namespace Modera\TestsRunner;

use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class PhpUnitListener implements TestListener
{
    use TestListenerDefaultImplementation;

    /**
     * @var InterceptorsExecutor
     */
    protected $interceptorsExecutor;

    public function __construct()
    {
        $interceptors = [];
        $configFilePath = getcwd() . DIRECTORY_SEPARATOR . '.mtr';

        if (file_exists($configFilePath)) {
            $interceptors = require_once $configFilePath;
        }

        $this->interceptorsExecutor = new InterceptorsExecutor($interceptors);
    }

    /**
     * {@inheritdoc}
     */
    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite): void
    {
        $this->interceptorsExecutor->handleSuite($suite);
    }
}
