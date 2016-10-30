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

namespace PhpCsFixer\Tests;

use PhpCsFixer\Test\AbstractIntegrationTestCase;

/**
 * Test that parses and runs the fixture '*.test' files found in '/Fixtures/Integration'.
 *
 * @author SpacePossum <possumfromspace@gmail.com>
 *
 * @internal
 */
final class IntegrationTest extends AbstractIntegrationTestCase
{
    /**
     * {@inheritdoc}
     */
    protected static function getFixturesDir()
    {
        return __DIR__.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR.'Integration';
    }

    /**
     * {@inheritdoc}
     */
    protected static function getTempFile()
    {
        return self::getFixturesDir().DIRECTORY_SEPARATOR.'.tmp.php';
    }
}
