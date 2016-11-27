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

namespace PhpCsFixer\Console\Command;

use PhpCsFixer\Config;
use PhpCsFixer\ConfigInterface;
use PhpCsFixer\ConfigurationException\UnallowedFixerConfigurationException;
use PhpCsFixer\Console\ConfigurationResolver;
use PhpCsFixer\Console\Output\NullOutput;
use PhpCsFixer\Console\Output\ProcessOutput;
use PhpCsFixer\Differ\NullDiffer;
use PhpCsFixer\Differ\SebastianBergmannDiffer;
use PhpCsFixer\Error\Error;
use PhpCsFixer\Error\ErrorsManager;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\FixerInterface;
use PhpCsFixer\Report\ReportSummary;
use PhpCsFixer\RuleSet;
use PhpCsFixer\Runner\Runner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class FixCommand extends Command
{
    const EXIT_STATUS_FLAG_HAS_INVALID_FILES = 4;
    const EXIT_STATUS_FLAG_HAS_CHANGED_FILES = 8;
    const EXIT_STATUS_FLAG_HAS_INVALID_CONFIG = 16;
    const EXIT_STATUS_FLAG_HAS_INVALID_FIXER_CONFIG = 32;
    const EXIT_STATUS_FLAG_EXCEPTION_IN_APP = 64;

    /**
     * EventDispatcher instance.
     *
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * ErrorsManager instance.
     *
     * @var ErrorsManager
     */
    protected $errorsManager;

    /**
     * Stopwatch instance.
     *
     * @var Stopwatch
     */
    protected $stopwatch;

    /**
     * Config instance.
     *
     * @var ConfigInterface
     */
    protected $defaultConfig;

    public function __construct()
    {
        parent::__construct();

        $this->defaultConfig = new Config();
        $this->errorsManager = new ErrorsManager();
        $this->eventDispatcher = new EventDispatcher();
        $this->stopwatch = new Stopwatch();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fix')
            ->setDefinition(
                array(
                    new InputArgument('path', InputArgument::IS_ARRAY, 'The path', null),
                    new InputOption('path-mode', '', InputOption::VALUE_REQUIRED, 'Specify path mode (can be override or intersection)', 'override'),
                    new InputOption('allow-risky', '', InputOption::VALUE_REQUIRED, 'Are risky fixers allowed (can be yes or no)', null),
                    new InputOption('config', '', InputOption::VALUE_REQUIRED, 'The path to a .php_cs file ', null),
                    new InputOption('dry-run', '', InputOption::VALUE_NONE, 'Only shows which files would have been modified'),
                    new InputOption('rules', '', InputOption::VALUE_REQUIRED, 'The rules', null),
                    new InputOption('using-cache', '', InputOption::VALUE_REQUIRED, 'Does cache should be used (can be yes or no)', null),
                    new InputOption('cache-file', '', InputOption::VALUE_REQUIRED, 'The path to the cache file'),
                    new InputOption('diff', '', InputOption::VALUE_NONE, 'Also produce diff for each file'),
                    new InputOption('format', '', InputOption::VALUE_REQUIRED, 'To output results in other formats', null),
                )
            )
            ->setDescription('Fixes a directory or a file')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command tries to fix as much coding standards
problems as possible on a given file or files in a given directory and its subdirectories:

    <info>$ php %command.full_name% /path/to/dir</info>
    <info>$ php %command.full_name% /path/to/file</info>

The <comment>--format</comment> option for the output format. Supported formats are ``txt`` (default one), ``json``, ``xml`` and ``junit``.

NOTE: When using ``junit`` format report generates in accordance with JUnit xml schema from Jenkins (see docs/junit-10.xsd).

The <comment>--verbose</comment> option will show the applied rules. When using the ``txt`` format it will also displays progress notifications.

The <comment>--rules</comment> option limits the rules to apply on the
project:

    <info>$ php %command.full_name% /path/to/project --rules=@PSR2</info>

By default, all PSR rules are run.

The <comment>--rules</comment> option lets you choose the exact rules to
apply (the rule names must be separated by a comma):

    <info>$ php %command.full_name% /path/to/dir --rules=line_ending,full_opening_tag,indentation_type</info>

You can also blacklist the rules you don't want by placing a dash in front of the rule name, if this is more convenient,
using <comment>-name_of_fixer</comment>:

    <info>$ php %command.full_name% /path/to/dir --rules=-full_opening_tag,-indentation_type</info>

When using combinations of exact and blacklist rules, applying exact ruless along with above blacklisted results:

    <info>$ php %command.full_name% /path/to/project --rules=@Symfony,-@PSR1,-blank_line_before_return,strict_comparison</info>

A combination of <comment>--dry-run</comment> and <comment>--diff</comment> will
display a summary of proposed fixes, leaving your files unchanged.

The <comment>--allow-risky</comment> option allows you to set whether riskys rule may run. Default value is taken from config file.
Risky rule is a rule, which could change code behaviour. By default no risky rules are run.

The command can also read from standard input, in which case it won't
automatically fix anything:

    <info>$ cat foo.php | php %command.full_name% --diff -</info>

Choose from the list of available rules:

{$this->getFixersHelp()}

The <comment>--dry-run</comment> option displays the files that need to be
fixed but without actually modifying them:

    <info>$ php %command.full_name% /path/to/code --dry-run</info>

Instead of using command line options to customize the rule, you can save the
project configuration in a <comment>.php_cs.dist</comment> file in the root directory
of your project. The file must return an instance of ``PhpCsFixer\ConfigInterface``,
which lets you configure the rules, the files and directories that
need to be analyzed. You may also create <comment>.php_cs</comment> file, which is
the local configuration that will be used instead of the project configuration. It
is a good practice to add that file into your <comment>.gitignore</comment> file.
With the <comment>--config</comment> option you can specify the path to the
<comment>.php_cs</comment> file.

The example below will add two rules to the default list of PSR2 set rules:

    <?php

    \$finder = PhpCsFixer\Finder::create()
        ->exclude('somedir')
        ->notPath('src/Symfony/Component/Translation/Tests/fixtures/resources.php')
        ->in(__DIR__)
    ;

    return PhpCsFixer\Config::create()
        ->setRules(array(
            '@PSR2' => true,
            'strict_param' => true,
            'array_syntax' => array('syntax' => 'short'),
        ))
        ->setFinder(\$finder)
    ;

    ?>

**NOTE**: ``exclude`` will work only for directories, so if you need to exclude file, try ``notPath``.

See `Symfony\\\\Finder <http://symfony.com/doc/current/components/finder.html>`_
online documentation for other `Finder` methods.

You may also use a blacklist for the rules instead of the above shown whitelist approach.
The following example shows how to use all ``Symfony`` rules but the ``full_opening_tag`` rule.

    <?php

    \$finder = PhpCsFixer\Finder::create()
        ->exclude('somedir')
        ->in(__DIR__)
    ;

    return PhpCsFixer\Config::create()
        ->setRules(array(
            '@Symfony' => true,
            'full_opening_tag' => false,
        ))
        ->setFinder(\$finder)
    ;

    ?>

You may want to use non-linux whitespaces in your project. Then you need to
configure them in your config file. Please be aware that this feature is
experimental.

    <?php

    return PhpCsFixer\Config::create()
        ->setIndent("\\t")
        ->setLineEnding("\\r\\n")
    ;

    ?>

By using ``--using-cache`` option with yes or no you can set if the caching
mechanism should be used.

Caching
-------

The caching mechanism is enabled by default. This will speed up further runs by
fixing only files that were modified since the last run. The tool will fix all
files if the tool version has changed or the list of rules has changed.
Cache is supported only for tool downloaded as phar file or installed via
composer.

Cache can be disabled via ``--using-cache`` option or config file:

    <?php

    return PhpCsFixer\Config::create()
        ->setUsingCache(false)
    ;

    ?>

Cache file can be specified via ``--cache-file`` option or config file:

    <?php

    return PhpCsFixer\Config::create()
        ->setCacheFile(__DIR__.'/.php_cs.cache')
    ;

    ?>

Using PHP CS Fixer on CI
------------------------

Require ``friendsofphp/php-cs-fixer`` as a `dev`` dependency:

    $ ./composer.phar require --dev friendsofphp/php-cs-fixer

Then, add the following command to your CI:

    $ vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --dry-run --using-cache=no --path-mode=intersection `git diff --name-only --diff-filter=ACMRTUXB \$COMMIT_RANGE`

Where ``\$COMMIT_RANGE`` is your range of commits, eg ``\$TRAVIS_COMMIT_RANGE`` or ``HEAD~..HEAD``.

Exit codes
----------

Exit code is build using following bit flags:

*  0 OK
*  4 Some files have invalid syntax (only in dry-run mode)
*  8 Some files need fixing (only in dry-run mode)
* 16 Configuration error of the application
* 32 Configuration error of a Fixer
* 64 Exception raised within the application
EOF
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $verbosity = $output->getVerbosity();

        $resolver = new ConfigurationResolver(
            $this->defaultConfig,
            array(
                'allow-risky' => $input->getOption('allow-risky'),
                'config' => $input->getOption('config'),
                'dry-run' => $input->getOption('dry-run'),
                'rules' => $input->getOption('rules'),
                'path' => $input->getArgument('path'),
                'path-mode' => $input->getOption('path-mode'),
                'using-cache' => $input->getOption('using-cache'),
                'cache-file' => $input->getOption('cache-file'),
                'format' => $input->getOption('format'),
                'diff' => $input->getOption('diff'),
                'verbosity' => $verbosity,
            ),
            getcwd()
        );

        $reporter = $resolver->getReporter();

        $stdErr = $output instanceof ConsoleOutputInterface
            ? $output->getErrorOutput()
            : ('txt' === $reporter->getFormat() ? $output : null)
        ;

        if (null !== $stdErr && extension_loaded('xdebug')) {
            $stdErr->writeln(sprintf($stdErr->isDecorated() ? '<bg=yellow;fg=black;>%s</>' : '%s', 'You are running php-cs-fixer with xdebug enabled. This has a major impact on runtime performance.'));
        }

        $configFile = $resolver->getConfigFile();

        if (null !== $stdErr) {
            $stdErr->writeln(sprintf('Loaded config <comment>%s</comment>%s.', $resolver->getConfig()->getName(), null === $configFile ? '' : ' from "'.$configFile.'"'));
        }

        if (null !== $stdErr && $resolver->getUsingCache()) {
            $cacheFile = $resolver->getCacheFile();
            if (is_file($cacheFile)) {
                $stdErr->writeln(sprintf('Using cache file "%s".', $cacheFile));
            }
        }

        $showProgress = $resolver->getProgress();
        $runner = new Runner(
            $resolver->getFinder(),
            $resolver->getFixers(),
            $input->getOption('diff') ? new SebastianBergmannDiffer() : new NullDiffer(),
            $showProgress ? $this->eventDispatcher : null,
            $this->errorsManager,
            $resolver->getLinter(),
            $resolver->isDryRun(),
            $resolver->getCacheManager()
        );

        $progressOutput = $showProgress && $stdErr
            ? new ProcessOutput($stdErr, $this->eventDispatcher)
            : new NullOutput()
        ;

        $this->stopwatch->start('fixFiles');
        $changed = $runner->fix();
        $this->stopwatch->stop('fixFiles');

        $progressOutput->printLegend();

        $fixEvent = $this->stopwatch->getEvent('fixFiles');

        $reportSummary = new ReportSummary(
            $changed,
            $fixEvent->getDuration(),
            $fixEvent->getMemory(),
            OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity(),
            $resolver->isDryRun(),
            $output->isDecorated()
        );

        if ($output->isDecorated()) {
            $output->write($reporter->generate($reportSummary));
        } else {
            $output->write($reporter->generate($reportSummary), false, OutputInterface::OUTPUT_RAW);
        }

        $invalidErrors = $this->errorsManager->getInvalidErrors();
        $exceptionErrors = $this->errorsManager->getExceptionErrors();
        $lintErrors = $this->errorsManager->getLintErrors();

        if (null !== $stdErr) {
            if (count($invalidErrors) > 0) {
                $this->listErrors($stdErr, 'linting before fixing', $invalidErrors);
            }

            if (count($exceptionErrors) > 0) {
                $this->listErrors($stdErr, 'fixing', $exceptionErrors);
            }

            if (count($lintErrors) > 0) {
                $this->listErrors($stdErr, 'linting after fixing', $lintErrors);
            }
        }

        return $this->calculateExitStatus(
            $resolver->isDryRun(),
            count($changed) > 0,
            count($invalidErrors) > 0,
            count($exceptionErrors) > 0
        );
    }

    protected function getFixersHelp()
    {
        $help = '';
        $fixerFactory = new FixerFactory();
        $fixers = $fixerFactory->registerBuiltInFixers()->getFixers();

        // sort fixers by name
        usort(
            $fixers,
            function (FixerInterface $a, FixerInterface $b) {
                return strcmp($a->getName(), $b->getName());
            }
        );

        $ruleSets = array();
        foreach (RuleSet::create()->getSetDefinitionNames() as $setName) {
            $ruleSets[$setName] = new RuleSet(array($setName => true));
        }

        $getSetsWithRule = function ($rule) use ($ruleSets) {
            $sets = array();

            foreach ($ruleSets as $setName => $ruleSet) {
                if ($ruleSet->hasRule($rule)) {
                    $sets[] = $setName;
                }
            }

            return $sets;
        };

        $count = count($fixers) - 1;
        foreach ($fixers as $i => $fixer) {
            $sets = $getSetsWithRule($fixer->getName());

            $description = $fixer->getDescription();

            $attributes = array();

            if ($fixer->isRisky()) {
                $attributes[] = 'risky';
            }

            if ($this->isFixerConfigurable($fixer)) {
                $attributes[] = 'configurable';
            }

            $description = wordwrap($description, 72, "\n   | ");

            if (!empty($sets)) {
                $help .= sprintf(" * <comment>%s</comment> [%s]\n   | %s\n", $fixer->getName(), implode(', ', $sets), $description);
            } else {
                $help .= sprintf(" * <comment>%s</comment>\n   | %s\n", $fixer->getName(), $description);
            }

            if (count($attributes)) {
                sort($attributes);
                $help .= sprintf("   | *Rule is: %s.*\n", implode(', ', $attributes));
            }

            if ($count !== $i) {
                $help .= "\n";
            }
        }

        return $help;
    }

    /**
     * @param bool $isDryRun
     * @param bool $hasChangedFiles
     * @param bool $hasInvalidErrors
     * @param bool $hasExceptionErrors
     *
     * @return int
     */
    private function calculateExitStatus($isDryRun, $hasChangedFiles, $hasInvalidErrors, $hasExceptionErrors)
    {
        $exitStatus = 0;

        if ($isDryRun) {
            if ($hasChangedFiles) {
                $exitStatus |= self::EXIT_STATUS_FLAG_HAS_CHANGED_FILES;
            }

            if ($hasInvalidErrors) {
                $exitStatus |= self::EXIT_STATUS_FLAG_HAS_INVALID_FILES;
            }
        }

        if ($hasExceptionErrors) {
            $exitStatus |= self::EXIT_STATUS_FLAG_EXCEPTION_IN_APP;
        }

        return $exitStatus;
    }

    /**
     * @param OutputInterface $output
     * @param string          $process
     * @param Error[]         $errors
     */
    private function listErrors(OutputInterface $output, $process, array $errors)
    {
        $output->writeln('');
        $output->writeln(sprintf(
            'Files that were not fixed due to errors reported during %s:',
            $process
        ));

        foreach ($errors as $i => $error) {
            $output->writeln(sprintf('%4d) %s', $i + 1, $error->getFilePath()));
        }
    }

    /**
     * @param FixerInterface $fixer
     *
     * @return bool
     */
    private function isFixerConfigurable(FixerInterface $fixer)
    {
        try {
            $fixer->configure(array());

            return true;
        } catch (UnallowedFixerConfigurationException $e) {
            return false;
        } catch (\Exception $e) {
            return true;
        }
    }
}
