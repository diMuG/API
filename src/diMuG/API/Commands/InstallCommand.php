<?php
namespace diMuG\API\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Translator;

/**
 * InstallCommand.php.
 *
 * @author  Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package diMuG\API\Commands
 * @licence GPL v3
 */
class InstallCommand extends Command
{
    /** @var  \Symfony\Component\Translation\Translator */
    private $translator;
    /** @var  array */
    private $installs;

    /**
     * Inject the translator object.
     *
     * @param Translator $translator
     * @return $this
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * Define the dir's to create and files to copy. Array of array, where each must have at least a target key for the
     * dir to be created. The key source is optional and referencing the target dir, files must be an array of the file
     * to be copied.
     *
     * @param array $installs
     * @return $this
     */
    public function setInstalls(array $installs)
    {
        $this->installs = $installs;
        return $this;
    }

    /**
     * Configure the console command
     */
    protected function configure()
    {
        $this
            ->setName('api:install')
            ->setDescription('Copy the skeletons files to get started')
            ->addArgument(
                'language',
                InputArgument::OPTIONAL,
                'The languages in which the messages should be displayed (de, en)'
            );
    }

    /**
     * Validate the security.yml and the configuration.yml.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $language = $input->getArgument('language');
        if (in_array($language, array('de', 'en')) == true) {
            $this->translator->setLocale($language);
        }

        foreach ($this->installs as $install) {
            if ($this->createDir($install['target']) == true) {
                if (isset($install['source']) == true) {
                    if ($this->copyFiles($output, $install['source'], $install['target'], $install['files']) == false) {
                        return;
                    }
                }
            } else {
                $output->writeln($this->translator->trans('install.dir.create', array('%dir%' => $install['target'])));
                return;
            }
        }

        $output->writeln($this->translator->trans('install.success'));
    }

    /**
     * Create a dir, if not already existing.
     *
     * @param $dir
     * @return bool
     */
    private function createDir($dir)
    {
        if (file_exists($dir) == false
            || is_dir($dir) == false) {
            mkdir($dir);
        }

        return is_dir($dir);
    }

    /**
     * Copy the basic files to implement the diMuG API from the vendor dir to the main project dir.
     *
     * @param OutputInterface $output
     * @param                 $source
     * @param                 $target
     * @param array           $files
     * @return bool
     */
    private function copyFiles(OutputInterface $output, $source, $target, array $files)
    {
        if (file_exists($source) == false
            || is_dir($source) == false) {
            $output->writeln($this->translator->trans('install.dir.exists', array('%dir%' => $source)));
            return false;
        }

        foreach ($files as $file) {
            if (file_exists($source . DIRECTORY_SEPARATOR . $file) == false
                || is_readable($source . DIRECTORY_SEPARATOR . $file) == false) {
                $output->writeln(
                    $this->translator->trans(
                        'install.file.missing',
                        array(
                            '%source%' => $source,
                            '%file%'   => $file
                        )
                    )
                );
                return false;
            }

            if (file_exists($target . DIRECTORY_SEPARATOR  . $file) == true) {
                $output->writeln(
                    $this->translator->trans(
                        'install.file.exists',
                        array(
                            '%target%' => $target,
                            '%file%'   => $file
                        )
                    )
                );
            } elseif (is_writable($target) == true) {
                copy($source . DIRECTORY_SEPARATOR . $file, $target . DIRECTORY_SEPARATOR . $file);
            } else {
                $output->writeln(
                    $this->translator->trans(
                        'install.file.error',
                        array(
                            '%source%' => $source,
                            '%target%' => $target,
                            '%file%'   => $file
                        )
                    )
                );
                return false;
            }
        }

        return true;
    }
}
