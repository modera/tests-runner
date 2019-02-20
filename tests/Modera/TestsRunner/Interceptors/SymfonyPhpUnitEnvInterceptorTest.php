<?php

namespace Modera\TestsRunner\Interceptors;

use org\bovigo\vfs\vfsStream;

if (PHP_MAJOR_VERSION >= 7 && class_exists('PHPUnit\Framework\TestCase')) {
    class SymfonyPhpUnitEnvInterceptorTestCase extends \PHPUnit\Framework\TestCase
    {
    }
} else {
    class SymfonyPhpUnitEnvInterceptorTestCase extends \PHPUnit_Framework_TestCase
    {
    }
}

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2016 Modera Foundation
 */
class SymfonyPhpUnitEnvInterceptorTest extends SymfonyPhpUnitEnvInterceptorTestCase
{
    private function formatPhpUnitXml($varsXml)
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit backupGlobals="false"
         backupStaticAttributes="false"
         colors="true"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true"
         processIsolation="false"
         stopOnFailure="false"
         syntaxCheck="false"
         bootstrap="Tests/bootstrap.php"
        >
    <testsuites>
        <testsuite name="Foo Test Suite">
            <directory>./Tests/</directory>
        </testsuite>
    </testsuites>
    
    %s
</phpunit>
XML;

        return sprintf($xml, $varsXml);
    }

    public function testOnEnterOnLeave()
    {
        $varsXml1 = <<<'XML'
    <php>
        <server name="FOO" value="FOO_VALUE" />
        <server name="KERNEL_DIR" value="./Tests/Fixtures/App/app" />
        <server name="DAMAGED_FOO" />
    </php>
XML;

        $varsXml2 = <<<'XML'
    <php>
        <server name="XXX" value="XXX_VALUE" />
    </php>
XML;

        $root = vfsStream::setup('root', null, array(
            'src' => array(
                'Acme' => array(
                    'Component' => array(
                        'Foo' => array(
                            'phpunit.xml' => $this->formatPhpUnitXml($varsXml1), // this one must have precedence
                            'phpunit.xml.dist' => $this->formatPhpUnitXml($varsXml2),
                        ),
                    ),
                ),
            ),
        ));

        $dir = $root->url().'/src/Acme/Component/Foo';

        $intr = new SymfonyPhpUnitEnvInterceptor();
        $intr->onEnter($dir, array());

        $this->assertArrayHasKey('FOO', $_SERVER);
        $this->assertEquals('FOO_VALUE', $_SERVER['FOO']);
        $this->assertArrayHasKey('KERNEL_DIR', $_SERVER);
        $this->assertEquals("{$dir}/Tests/Fixtures/App/app", $_SERVER['KERNEL_DIR']);
        $this->assertFalse(isset($_SERVER['DAMAGED_FOO']));

        $intr->onLeave($dir, array());

        $this->assertFalse(isset($_SERVER['FOO']));
        $this->assertFalse(isset($_SERVER['KERNEL_DIR']));
    }
}
