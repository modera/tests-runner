<?php

namespace Modera\TestsRunner;

/**
 * Extend.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class BaseInterceptor implements InterceptorInterface
{
    /**
     * {@inheritdoc}
     */
    public function onEnter($dir, array $composerJson)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onLeave($dir, array $composerJson)
    {
    }
}
