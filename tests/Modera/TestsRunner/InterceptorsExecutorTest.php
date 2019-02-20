<?php

namespace Modera\TestsRunner;

use org\bovigo\vfs\vfsStream;

require_once __DIR__.'/fixtures.php';

final class InterceptorsExecutorUT extends InterceptorsExecutor
{
    public $mappings = array();

    // override
    protected function resolvePath($className)
    {
        return isset($this->mappings[$className]) ? $this->mappings[$className] : parent::resolvePath($className);
    }
}

if (PHP_MAJOR_VERSION >= 7 && class_exists('PHPUnit\Framework\TestCase')) {
    class InterceptorsExecutorTestCase extends \PHPUnit\Framework\TestCase
    {
    }
} else {
    class InterceptorsExecutorTestCase extends \PHPUnit_Framework_TestCase
    {
    }
}

if (PHP_MAJOR_VERSION >= 7 && class_exists('PHPUnit\Framework\TestSuite')) {
    class InterceptorsExecutorTestSuite extends \PHPUnit\Framework\TestSuite
    {
    }
} else {
    class InterceptorsExecutorTestSuite extends \PHPUnit_Framework_TestSuite
    {
    }
}

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class InterceptorsExecutorTest extends InterceptorsExecutorTestCase
{
    private function createInterceptor()
    {
        $intr = \Phake::mock(InterceptorInterface::class);

        return $intr;
    }

    private function createSuite($className = null)
    {
        $suite = \Phake::mock(InterceptorsExecutorTestSuite::class);

        if ($className) {
            \Phake::when($suite)
                ->getName()
                ->thenReturn($className)
            ;
        }

        return $suite;
    }

    public function testHandleSuite_notClass()
    {
        $suite = $this->createSuite(__NAMESPACE__.'\\NonExistingClass');

        $interceptors = [$this->createInterceptor()];

        $executor = new InterceptorsExecutor($interceptors);

        $this->assertNull($executor->handleSuite($suite));

        \Phake::verify($suite)->getName();
        \Phake::verifyNoOtherInteractions($interceptors[0]);
    }

    public function testHandleSuite()
    {
        $root = vfsStream::setup('root', null, array(
            'src' => array(
                'Acme' => array(
                    'Component' => array(
                        'FooUtil' => array(
                            'Util.php' => '',
                            'Tests' => array(
                                'UtilTest.php' => '', // test case for ../Util class
                            ),
                            'composer.json' => json_encode(array('FooUtil-composer.json')),
                        ),
                        'Bar' => array(
                            'Definition.php' => '',
                            'Test' => array(
                                'DefinitionTest.php' => '',
                            ),
                            'composer.json' => json_encode(array('Bar-composer.json')),
                        ),
                    ),
                ),
            ),
            'composer.json' => json_encode(array('root-composer.json')),
        ));

        $interceptors = [
            $this->createInterceptor(),
            $this->createInterceptor(),
        ];

        $suite1 = $this->createSuite('Acme\Component\FooUtil\Tests\UtilTest');
        $suite2 = $this->createSuite('Acme\Component\Bar\Tests\DefinitionTest');

        $executor = new InterceptorsExecutorUT($interceptors);
        $executor->mappings['Acme\Component\FooUtil\Tests\UtilTest'] = $root->url().'/src/Acme/Component/FooUtil/Tests/UtilTest.php';
        $executor->mappings['Acme\Component\Bar\Tests\DefinitionTest'] = $root->url().'/src/Acme/Component/Bar/Tests/DefinitionTest.php';
        $executor->handleSuite($suite1);

        \Phake::verify($interceptors[0])
            ->onEnter($root->url().'/src/Acme/Component/FooUtil', array('FooUtil-composer.json'))
        ;
        \Phake::verify($interceptors[1])
            ->onEnter($root->url().'/src/Acme/Component/FooUtil', array('FooUtil-composer.json'))
        ;

        $executor->handleSuite($suite2);

        \Phake::inOrder(
            \Phake::verify($interceptors[0])->onLeave($root->url().'/src/Acme/Component/FooUtil', array('FooUtil-composer.json')),
            \Phake::verify($interceptors[1])->onLeave($root->url().'/src/Acme/Component/FooUtil', array('FooUtil-composer.json')),
            \Phake::verify($interceptors[0])->onEnter($root->url().'/src/Acme/Component/Bar', array('Bar-composer.json')),
            \Phake::verify($interceptors[1])->onEnter($root->url().'/src/Acme/Component/Bar', array('Bar-composer.json'))
        );

        // no interactions expected because $suite2 is already active
        \Phake::verifyNoFurtherInteraction($interceptors[0]);
        \Phake::verifyNoFurtherInteraction($interceptors[1]);

        $executor->handleSuite($suite2);
    }
}
