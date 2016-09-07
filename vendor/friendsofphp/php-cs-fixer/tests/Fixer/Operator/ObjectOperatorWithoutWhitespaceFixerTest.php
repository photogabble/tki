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

namespace PhpCsFixer\Tests\Fixer\Operator;

use PhpCsFixer\Test\AbstractFixerTestCase;

/**
 * @author Farhad Safarov <farhad.safarov@gmail.com>
 *
 * @internal
 */
final class ObjectOperatorWithoutWhitespaceFixerTest extends AbstractFixerTestCase
{
    /**
     * @dataProvider provideCases
     */
    public function testFix($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideCases()
    {
        return array(
            array(
                '<?php $object->method();',
                '<?php $object   ->method();',
            ),
            array(
                '<?php $object->method();',
                '<?php $object   ->   method();',
            ),
            array(
                '<?php $object->method();',
                '<?php $object->   method();',
            ),
            array(
                '<?php $object->method();',
                '<?php $object	->method();',
            ),
            array(
                '<?php $object->method();',
                '<?php $object->	method();',
            ),
            array(
                '<?php $object->method();',
                '<?php $object	->	method();',
            ),
            array(
                '<?php $object->method();',
            ),
            array(
                '<?php echo "use it as -> you want";',
            ),
            // Ensure that doesn't break chained multi-line statements
            array(
                '<?php $object->method()
                        ->method2()
                        ->method3();',
            ),
            array(
                '<?php $this
             ->add()
             // Some comment
             ->delete();',
            ),
        );
    }
}
