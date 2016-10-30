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

namespace PhpCsFixer\Tests\Fixer\PhpUnit;

use PhpCsFixer\Test\AbstractFixerTestCase;
use PhpCsFixer\Test\AccessibleObject;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class PhpUnitStrictFixerTest extends AbstractFixerTestCase
{
    /**
     * @dataProvider provideTestFixCases
     */
    public function testFix($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideTestFixCases()
    {
        $methodsMap = AccessibleObject::create($this->getFixer())->assertionMap;

        $cases = array(
            array('<?php $self->foo();'),
        );

        foreach ($methodsMap as $methodBefore => $methodAfter) {
            $cases[] = array("<?php \$sth->$methodBefore(1, 1);");
            $cases[] = array("<?php \$sth->$methodAfter(1, 1);");
            $cases[] = array(
                "<?php \$this->$methodAfter(1, 2);",
                "<?php \$this->$methodBefore(1, 2);",
            );
            $cases[] = array(
                "<?php \$this->$methodAfter(1, 2); \$this->$methodAfter(1, 2);",
                "<?php \$this->$methodBefore(1, 2); \$this->$methodBefore(1, 2);",
            );
            $cases[] = array(
                "<?php \$this->$methodAfter(1, 2, 'descr');",
                "<?php \$this->$methodBefore(1, 2, 'descr');",
            );
            $cases[] = array(
                "<?php \$this->/*aaa*/$methodAfter \t /**bbb*/  ( /*ccc*/1  , 2);",
                "<?php \$this->/*aaa*/$methodBefore \t /**bbb*/  ( /*ccc*/1  , 2);",
            );
            $cases[] = array(
                "<?php \$this->$methodAfter(\$expectedTokens->count() + 10, \$tokens->count() ? 10 : 20 , 'Test');",
                "<?php \$this->$methodBefore(\$expectedTokens->count() + 10, \$tokens->count() ? 10 : 20 , 'Test');",
            );
        }

        return $cases;
    }
}
