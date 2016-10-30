<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests\Cache;

use PhpCsFixer\Cache\Signature;

/**
 * @author Andreas Möller <am@localheinz.com>
 *
 * @internal
 */
final class SignatureTest extends \PHPUnit_Framework_TestCase
{
    public function testIsFinal()
    {
        $reflection = new \ReflectionClass('PhpCsFixer\Cache\Signature');

        $this->assertTrue($reflection->isFinal());
    }

    public function testImplementsSignatureInterface()
    {
        $reflection = new \ReflectionClass('PhpCsFixer\Cache\Signature');

        $this->assertTrue($reflection->implementsInterface('PhpCsFixer\Cache\SignatureInterface'));
    }

    public function testConstructorSetsValues()
    {
        $php = PHP_VERSION;
        $version = '2.0';
        $linting = false;
        $rules = array(
            'foo',
            'bar',
        );

        $signature = new Signature(
            $php,
            $version,
            $linting,
            $rules
        );

        $this->assertSame($php, $signature->getPhpVersion());
        $this->assertSame($version, $signature->getFixerVersion());
        $this->assertSame($linting, $signature->isLintingEnabled());
        $this->assertSame($rules, $signature->getRules());
    }

    public function testEqualsReturnsFalseIfValuesAreNotIdentical()
    {
        $php = PHP_VERSION;
        $version = '2.0';
        $linting = false;
        $rules = array(
            'foo',
            'bar',
        );

        $signature = new Signature(
            $php,
            $version,
            $linting,
            $rules
        );

        $anotherSignature = new Signature(
            $php,
            $version,
            !$linting,
            $rules
        );

        $this->assertFalse($signature->equals($anotherSignature));
    }

    public function testEqualsReturnsTrueIfValuesAreIdentical()
    {
        $php = PHP_VERSION;
        $version = '2.0';
        $linting = false;
        $rules = array(
            'foo',
            'bar',
        );

        $signature = new Signature(
            $php,
            $version,
            $linting,
            $rules
        );

        $anotherSignature = new Signature(
            $php,
            $version,
            $linting,
            $rules
        );

        $this->assertTrue($signature->equals($anotherSignature));
    }
}
