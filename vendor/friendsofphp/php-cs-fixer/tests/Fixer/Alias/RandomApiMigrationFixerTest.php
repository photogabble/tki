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

namespace PhpCsFixer\Tests\Fixer\Alias;

use PhpCsFixer\Test\AbstractFixerTestCase;

/**
 * @author Vladimir Reznichenko <kalessil@gmail.com>
 *
 * @internal
 */
final class RandomApiMigrationFixerTest extends AbstractFixerTestCase
{
    /**
     * @expectedException \PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException
     * @expectedExceptionMessage [random_api_migration] "is_null" is not handled by the fixer.
     */
    public function testConfigureCheckSearchFunction()
    {
        $this->getFixer()->configure(array('is_null' => 'random_int'));
    }

    /**
     * @expectedException \PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException
     * @expectedExceptionMessage [random_api_migration] Expected string got "NULL".
     */
    public function testConfigureCheckReplacementType()
    {
        $this->getFixer()->configure(array('rand' => null));
    }

    public function testConfigure()
    {
        $config = array('rand' => 'random_int');
        $this->getFixer()->configure($config);

        /** @var $replacements string[] */
        $replacements = static::getObjectAttribute($this->getFixer(), 'configuration');
        static::assertSame($config, $replacements);
    }

    /**
     * @dataProvider provideCases
     */
    public function testFix($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    /**
     * @return array[]
     */
    public function provideCases()
    {
        return array(
            array('<?php $smth->srand($a);'),
            array('<?php srandSmth($a);'),
            array('<?php smth_srand($a);'),
            array('<?php new srand($a);'),
            array('<?php new Smth\\srand($a);'),
            array('<?php Smth\\srand($a);'),
            array('<?php namespace\\srand($a);'),
            array('<?php Smth::srand($a);'),
            array('<?php new srand\\smth($a);'),
            array('<?php srand::smth($a);'),
            array('<?php srand\\smth($a);'),
            array('<?php "SELECT ... srand(\$a) ...";'),
            array('<?php "SELECT ... SRAND($a) ...";'),
            array("<?php 'test'.'srand' . 'in concatenation';"),
            array('<?php "test" . "srand"."in concatenation";'),
            array(
            '<?php
class SrandClass
{
    const srand = 1;
    public function srand($srand)
    {
        if (!defined("srand") || $srand instanceof srand) {
            echo srand;
        }
    }
}

class srand extends SrandClass{
    const srand = "srand";
}
', ),
            array('<?php mt_srand($a);', '<?php srand($a);'),
            array('<?php \\mt_srand($a);', '<?php \\srand($a);'),
            array('<?php $a = &mt_srand($a);', '<?php $a = &srand($a);'),
            array('<?php $a = &\\mt_srand($a);', '<?php $a = &\\srand($a);'),
            array('<?php /* foo */ mt_srand /** bar */ ($a);', '<?php /* foo */ srand /** bar */ ($a);'),
            array('<?php a(mt_getrandmax ());', '<?php a(getrandmax ());'),
            array('<?php a(mt_rand());', '<?php a(rand());'),
            array('<?php a(mt_srand());', '<?php a(srand());'),
            array('<?php a(\\mt_srand());', '<?php a(\\srand());'),
        );
    }

    /**
     * @dataProvider provideCasesForCustomConfiguration
     */
    public function testFixForCustomConfiguration($expected, $input = null)
    {
        $this->getFixer()->configure(array('rand' => 'random_int'));

        $this->doTest($expected, $input);
    }

    /**
     * @return array[]
     */
    public function provideCasesForCustomConfiguration()
    {
        return array(
            array('<?php random_int(random_int($a));', '<?php rand(rand($a));'),
            array('<?php random_int(\Other\Scope\mt_rand($a));', '<?php rand(\Other\Scope\mt_rand($a));'),
        );
    }
}
