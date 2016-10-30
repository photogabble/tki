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
 * @author Javier Spagnoletti <phansys@gmail.com>
 *
 * @internal
 */
final class NotOperatorWithSuccessorSpaceFixerTest extends AbstractFixerTestCase
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
                '<?php $i = 0; $i++; $foo = ! false || (! true || ! ! false && (2 === (7 -5)));',
                '<?php $i = 0; $i++; $foo = !false || (!true || !!false && (2 === (7 -5)));',
            ),
            array(
                '<?php $i = 0; $i--; $foo = ! false || ($i && ! true);',
                '<?php $i = 0; $i--; $foo = !false || ($i && !true);',
            ),
            array(
                '<?php $i = 0; $i--; $foo = ! false || ($i && ! /* some comment */true);',
                '<?php $i = 0; $i--; $foo = !false || ($i && !/* some comment */true);',
            ),
            array(
                '<?php $i = 0; $i--; $foo = ! false || ($i && ! true);',
                '<?php $i = 0; $i--; $foo = !false || ($i && !    true);',
            ),
            array(
                '<?php $i = 0; $i--; $foo = ! false || ($i && ! /* some comment */ true);',
                '<?php $i = 0; $i--; $foo = !false || ($i && !  /* some comment */ true);',
            ),
        );
    }
}
