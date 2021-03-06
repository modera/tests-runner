<?php

namespace Modera\TestsRunner;

if (class_exists('PHPUnit_Framework_TestSuite')) {
    class __InterceptorsExecutor__
    {
        /**
         * @param \PHPUnit_Framework_TestSuite $suite
         */
        public function handleSuite(\PHPUnit_Framework_TestSuite $suite)
        {
            $this->_handleSuite($suite);
        }
    }
} else {
    class __InterceptorsExecutor__
    {
        /**
         * @param \PHPUnit\Framework\TestSuite $suite
         */
        public function handleSuite(\PHPUnit\Framework\TestSuite $suite)
        {
            $this->_handleSuite($suite);
        }
    }
}

/**
 * @internal
 *
 * Orchestrates interceptors
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class InterceptorsExecutor extends __InterceptorsExecutor__
{
    /**
     * @var InterceptorInterface[]
     */
    private $interceptors = [];

    /**
     * @var array
     */
    private $activeSuite = null;

    /**
     * @param array $interceptors
     */
    public function __construct(array $interceptors)
    {
        $this->interceptors = $interceptors;
    }

    /**
     * @param \PHPUnit\Framework\TestSuite $suite
     */
    protected function _handleSuite($suite)
    {
        $suiteClassName = $suite->getName();
        if (!class_exists($suiteClassName)) {
            return;
        }

        $packageDir = null;
        $packageComposerJson = array();

        $classPathname = $this->resolvePath($suiteClassName);
        $path = explode(DIRECTORY_SEPARATOR, $classPathname);

        // finding a nearest composer.json relatively to the given test case suite
        for ($i = count($path); $i >= -1; --$i) {
            $currentRootDir = implode(DIRECTORY_SEPARATOR, array_slice($path, 0, $i));
            if (file_exists($currentRootDir.'/composer.json')) {
                $packageDir = $currentRootDir;
                $packageComposerJson = json_decode(file_get_contents($currentRootDir.'/composer.json'), true);

                break;
            }
        }

        if ($this->activeSuite) {
            if ($this->activeSuite['dir'] != $packageDir) {
                foreach ($this->interceptors as $interceptor) {
                    $interceptor->onLeave($this->activeSuite['dir'], $this->activeSuite['composer_json']);
                }

                foreach ($this->interceptors as $interceptor) {
                    $interceptor->onEnter($packageDir, $packageComposerJson);
                }

                $this->activeSuite = array(
                    'dir' => $packageDir,
                    'composer_json' => $packageComposerJson,
                );
            }
        } else {
            foreach ($this->interceptors as $interceptor) {
                $interceptor->onEnter($packageDir, $packageComposerJson);
            }

            $this->activeSuite = array(
                'dir' => $packageDir,
                'composer_json' => $packageComposerJson,
            );
        }
    }

    protected function resolvePath($className)
    {
        return (new \ReflectionClass($className))->getFileName();
    }
}
