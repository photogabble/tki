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

namespace PhpCsFixer\Tests\Fixer\StringNotation;

use PhpCsFixer\Test\AbstractFixerTestCase;

/**
 * @author Gregor Harlan <gharlan@web.de>
 *
 * @internal
 */
final class SingleQuoteFixerTest extends AbstractFixerTestCase
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
        return array(
            array(
                '<?php $a = \'\';',
                '<?php $a = "";',
            ),
            array(
                '<?php $a = \'foo bar\';',
                '<?php $a = "foo bar";',
            ),
            array(
                '<?php $a = \'foo
                    bar\';',
                '<?php $a = "foo
                    bar";',
            ),
            array(
                '<?php $a = \'foo\'.\'bar\'."$baz";',
                '<?php $a = \'foo\'."bar"."$baz";',
            ),
            array(
                '<?php $a = \'foo "bar"\';',
                '<?php $a = "foo \"bar\"";',
            ),
            array(<<<'EOF'
<?php $a = '\\foo\\bar\\\\';
EOF
                , <<<'EOF'
<?php $a = "\\foo\\bar\\\\";
EOF
            ),
            array(
                '<?php $a = \'foo $bar7\';',
                '<?php $a = "foo \$bar7";',
            ),
            array(
                '<?php $a = \'foo $(bar7)\';',
                '<?php $a = "foo \$(bar7)";',
            ),
            array(
                '<?php $a = \'foo \\\\($bar8)\';',
                '<?php $a = "foo \\\\(\$bar8)";',
            ),
            array('<?php $a = "foo \\" \\$$bar";'),
            array('<?php $a = \'foo bar\';'),
            array('<?php $a = \'foo "bar"\';'),
            array('<?php $a = "foo \'bar\'";'),
            array('<?php $a = "foo $bar";'),
            array('<?php $a = "foo ${bar}";'),
            array('<?php $a = "foo\n bar";'),
            array(<<<'EOF'
<?php $a = "\\\n";
EOF
            ),
        );
    }
}
