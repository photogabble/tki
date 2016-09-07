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
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class NoAliasFunctionsFixerTest extends AbstractFixerTestCase
{
    /**
     * {@inheritdoc}
     */
    public function isLintException($source)
    {
        return false !== strpos($source, '/*lintException*/');
    }

    /**
     * @dataProvider provideCases
     */
    public function testFix($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideCases()
    {
        /** @var $aliases string[] */
        $aliases = static::getStaticAttribute('\PhpCsFixer\Fixer\Alias\NoAliasFunctionsFixer', 'aliases');

        $cases = array();
        foreach ($aliases as $alias => $master) {
            // valid cases
            $cases[] = array("<?php \$smth->$alias(\$a);");
            $cases[] = array("<?php {$alias}Smth(\$a);");
            $cases[] = array("<?php smth_$alias(\$a);");
            $cases[] = array("<?php new $alias(\$a);");
            $cases[] = array("<?php new Smth\\$alias(\$a);");
            $cases[] = array("<?php Smth\\$alias(\$a);");
            $cases[] = array("<?php namespace\\$alias(\$a);");
            $cases[] = array("<?php Smth::$alias(\$a);");
            $cases[] = array("<?php new $alias\\smth(\$a);");
            $cases[] = array("<?php $alias::smth(\$a);");
            $cases[] = array("<?php $alias\\smth(\$a);");
            $cases[] = array('<?php "SELECT ... '.$alias.'(\$a) ...";');
            $cases[] = array('<?php "SELECT ... '.strtoupper($alias).'($a) ...";');
            $cases[] = array("<?php 'test'.'$alias' . 'in concatenation';");
            $cases[] = array('<?php "test" . "'.$alias.'"."in concatenation";');
            $cases[] = array(
                '<?php
class '.ucfirst($alias).'ing
{
    const '.$alias.' = 1;

    public function '.$alias.'($'.$alias.')
    {
        if (defined("'.$alias.'") || $'.$alias.' instanceof '.$alias.') {
            echo '.$alias.';
        }
    }
}

class '.$alias.' extends '.ucfirst($alias).'ing{
    const '.$alias.' = "'.$alias.'";
}
',
            );

            // cases to be fixed
            $cases[] = array(
                "<?php $master(\$a);",
                "<?php $alias(\$a);",
            );
            $cases[] = array(
                "<?php \\$master(\$a);",
                "<?php \\$alias(\$a);",
            );
            $cases[] = array(
                "<?php /*lintException*/ \$ref = &$master(\$a);",
                "<?php /*lintException*/ \$ref = &$alias(\$a);",
            );
            $cases[] = array(
                "<?php /*lintException*/ \$ref = &\\$master(\$a);",
                "<?php /*lintException*/ \$ref = &\\$alias(\$a);",
            );
            $cases[] = array(
                "<?php $master
                            (\$a);",
                "<?php $alias
                            (\$a);",
            );
            $cases[] = array(
                "<?php /* foo */ $master /** bar */ (\$a);",
                "<?php /* foo */ $alias /** bar */ (\$a);",
            );
            $cases[] = array(
                "<?php a($master());",
                "<?php a($alias());",
            );
            $cases[] = array(
                "<?php a(\\$master());",
                "<?php a(\\$alias());",
            );
        }

        // static case to fix - in case previous generation is broken
        $cases[] = array(
            '<?php is_int($a);',
            '<?php is_integer($a);',
        );

        return $cases;
    }
}
