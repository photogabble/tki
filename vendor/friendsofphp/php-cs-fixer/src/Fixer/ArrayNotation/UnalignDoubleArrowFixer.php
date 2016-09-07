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

namespace PhpCsFixer\Fixer\ArrayNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class UnalignDoubleArrowFixer extends AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isTokenKindFound(T_DOUBLE_ARROW);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Unalign double arrow symbols.';
    }

    /**
     * {@inheritdoc}
     */
    public function fix(\SplFileInfo $file, Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(T_DOUBLE_ARROW)) {
                continue;
            }

            $this->fixWhitespace($tokens[$index - 1]);
            $this->fixWhitespace($tokens[$index + 1]);
        }
    }

    /**
     * If given token is a single line whitespace then fix it to be a single space.
     *
     * @param Token $token
     */
    private function fixWhitespace(Token $token)
    {
        if ($token->isWhitespace(" \t")) {
            $token->setContent(' ');
        }
    }
}
