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

namespace PhpCsFixer\Tests\Fixer\NamespaceNotation;

use PhpCsFixer\Test\AbstractFixerTestCase;

/**
 * @author Bram Gotink <bram@gotink.me>
 *
 * @internal
 */
final class NoLeadingNamespaceWhitespaceFixerTest extends AbstractFixerTestCase
{
    /**
     * @dataProvider provideExamples
     */
    public function testFix($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideExamples()
    {
        $manySpaces = array();
        for ($i = 1; $i <= 100; ++$i) {
            $manySpaces[] = 'namespace Test'.$i.';';
        }

        return array(
            // with newline
            array("<?php\nnamespace Test;"),
            array("<?php\n\nnamespace Test;"),
            array("<?php\nnamespace Test;", "<?php\n namespace Test;"),
            // without newline
            array('<?php namespace Test;'),
            array('<?php namespace Test;', '<?php  namespace Test;'),
            // multiple namespaces with newline
            array(
                '<?php
namespace Test1;
namespace Test2;',
            ),
            array(
                '<?php
namespace Test1;
/* abc */
namespace Test2;',
                '<?php
namespace Test1;
/* abc */namespace Test2;',
            ),
            array(
                '<?php
namespace Test1;
namespace Test2;',
                '<?php
 namespace Test1;
    namespace Test2;',
            ),
            array(
                '<?php
namespace Test1;
class Test {}
namespace Test2;',
                '<?php
 namespace Test1;
class Test {}
   namespace Test2;',
            ),
            array(
                '<?php
namespace Test1;
use Exception;
namespace Test2;',
                '<?php
 namespace Test1;
use Exception;
   namespace Test2;',
            ),
            // multiple namespaces without newline
            array('<?php namespace Test1; namespace Test2;'),
            array('<?php namespace Test1; namespace Test2;', '<?php    namespace Test1;  namespace Test2;'),
            array('<?php namespace Test1; namespace Test2;', '<?php namespace Test1;  namespace Test2;'),
            // namespaces without spaces in between
            array(
                '<?php
namespace Test1{}
namespace Test2{}',
                '<?php
     namespace Test1{}namespace Test2{}',
            ),
            array(
                '<?php
namespace Test1;
namespace Test2;',
                '<?php
namespace Test1;namespace Test2;',
            ),
            array(
                '<?php
'.implode("\n", $manySpaces),
                '<?php
'.implode('', $manySpaces),
            ),
        );
    }
}
