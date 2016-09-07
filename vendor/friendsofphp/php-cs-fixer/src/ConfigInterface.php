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

namespace PhpCsFixer;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface ConfigInterface
{
    /**
     * Returns the name of the configuration.
     *
     * The name must be all lowercase and without any spaces.
     *
     * @return string The name of the configuration
     */
    public function getName();

    /**
     * Returns the description of the configuration.
     *
     * A short one-line description for the configuration.
     *
     * @return string The description of the configuration
     */
    public function getDescription();

    /**
     * Returns an iterator of files to scan.
     *
     * @return \Traversable A \Traversable instance that returns \SplFileInfo instances
     */
    public function getFinder();

    /**
     * Returns the fixers to run.
     *
     * @return FixerInterface[]
     */
    public function getFixers();

    /**
     * Returns true if progress should be hidden.
     *
     * @return bool
     */
    public function getHideProgress();

    /**
     * Adds an instance of a custom fixer.
     *
     * @param FixerInterface $fixer
     */
    public function addCustomFixer(FixerInterface $fixer);

    /**
     * Adds a suite of custom fixers.
     *
     * @param FixerInterface[]|\Traversable $fixers
     */
    public function addCustomFixers($fixers);

    /**
     * Returns the custom fixers to use.
     *
     * @return FixerInterface[]
     */
    public function getCustomFixers();

    /**
     * Returns true if caching should be enabled.
     *
     * @return bool
     */
    public function usingCache();

    /**
     * Returns true if linter should be enabled.
     *
     * @return bool
     */
    public function usingLinter();

    /**
     * Sets the path to the cache file.
     *
     * @param string $cacheFile
     *
     * @return ConfigInterface
     */
    public function setCacheFile($cacheFile);

    /**
     * Returns the path to the cache file.
     *
     * @return string
     */
    public function getCacheFile();

    /**
     * Get configured PHP executable, if any.
     *
     * @return string|null
     */
    public function getPhpExecutable();

    /**
     * Check if it is allowed to run risky fixers.
     *
     * @return bool
     */
    public function getRiskyAllowed();

    /**
     * Set if it is allowed to run risky fixers.
     *
     * @param bool $isRiskyAllowed
     *
     * @return $this
     */
    public function setRiskyAllowed($isRiskyAllowed);

    /**
     * Get rules.
     *
     * Keys of array are names of fixers/sets, values are true/false.
     *
     * @return array
     */
    public function getRules();

    /**
     * Set rules.
     *
     * Keys of array are names of fixers or sets.
     * Value for set must be bool (turn it on or off).
     * Value for fixer may be bool (turn it on or off) or array of configuration
     * (turn it on and contains configuration for FixerInterface::configure method).
     *
     * @param array $rules
     *
     * @return $this
     */
    public function setRules(array $rules);
}
