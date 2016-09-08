<?php

namespace Modera\TestsRunner;

/**
 * Implementations of this interface will be able to perform additional action before a test-suite is started
 * and after it.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
interface InterceptorInterface
{
    /**
     * Method is invoked when a new test-case is started.
     *
     * @param string $dir
     * @param array  $composerJson
     */
    public function onEnter($dir, array $composerJson);

    /**
     * Method is invoked before a currently active test-case is finished and a new one is started.
     *
     * @param string $dir
     * @param array  $composerJson
     */
    public function onLeave($dir, array $composerJson);
}
