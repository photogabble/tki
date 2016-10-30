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

namespace PhpCsFixer\Tests\DocBlock;

use PhpCsFixer\DocBlock\Line;
use PhpCsFixer\DocBlock\Tag;
use PhpCsFixer\DocBlock\TagComparator;

/**
 * @author Graham Campbell <graham@mineuk.com>
 *
 * @internal
 */
final class TagComparatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideComparatorCases
     */
    public function testComparatorTogether($first, $second, $expected)
    {
        $tag1 = new Tag(new Line('* @'.$first));
        $tag2 = new Tag(new Line('* @'.$second));

        $this->assertSame($expected, TagComparator::shouldBeTogether($tag1, $tag2));
    }

    public function provideComparatorCases()
    {
        return array(
            array('return', 'return', true),
            array('param', 'param', true),
            array('return', 'param', false),
            array('var', 'foo', false),
            array('api', 'deprecated', false),
            array('author', 'copyright', true),
            array('author', 'since', false),
            array('link', 'see', true),
            array('category', 'package', true),
        );
    }
}
