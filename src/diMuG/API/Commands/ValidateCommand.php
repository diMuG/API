<?php
namespace diMuG\API\Commands;

use diMuG\API\Interfaces\FinderInterface;
use diMuG\API\Interfaces\GlossaryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Symfony console command to validate your API configuration files.
 *
 * @author  Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package diMuG\API\Commands
 * @licence GPL v3
 */
class ValidateCommand extends Command
{
    /** @var  string */
    private $configDir;
    /** @var  \Symfony\Component\Translation\Translator */
    private $translator;

    /**
     * Set the path the dir containing the files configuration.yml and security.yml
     * @param $configDir
     * @return $this
     */
    public function setConfigDir($configDir)
    {
        $this->configDir = $configDir;
        return $this;
    }

    /**
     * Inject the translator object.
     * @param Translator $translator
     * @return $this
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * Configure the console command
     */
    protected function configure()
    {
        $this
            ->setName('api:validate')
            ->setDescription('Validate your configuration and security files')
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

        if (is_dir($this->configDir) == true) {
            if ($this->validateSecurity($output) == true) {
                $output->writeln($this->translator->trans('validation.success', array('%file%' => 'security')));
            }

            if ($this->validateConfiguration($output) == true) {
                $output->writeln($this->translator->trans('validation.success', array('%file%' => 'configuration')));
            }

        } else {
            $output->writeln($this->translator->trans('validation.error.dir'));
        }
    }

    /**
     * The "security.yml" must contain an key api with the fields user and password, which are not allowed to be empty.
     * @param OutputInterface $output
     * @return bool
     */
    private function validateSecurity(OutputInterface $output)
    {
        if (is_file($this->configDir . '/security.yml') == true) {
            try {
                $yaml = Yaml::parse($this->configDir . '/security.yml');

                if (array_key_exists('api', $yaml) == true) {
                    if ($this->existsAndIsNotEmpty($output, $yaml['api'], 'user', 'api', 'security') === false
                        || $this->existsAndIsNotEmpty($output, $yaml['api'], 'password', 'api', 'security') === false) {
                        return false;
                    }
                } else {
                    $output->writeln(
                        $this->translator->trans(
                            'validation.error.root',
                            array(
                                '%key%'  => 'api',
                                '%file%' => 'security'
                            )
                        )
                    );
                    return false;
                }
            } catch (ParseException $error) {
                $output->writeln($this->translator->trans('validation.error.yaml', array('%file%' => 'security')));
                return false;
            }

            return true;
        } else {
            $output->writeln($this->translator->trans('validation.error.file', array('%file%' => 'security')));
            return false;
        }
    }

    private function validateConfiguration(OutputInterface $output)
    {
        if (is_file($this->configDir . '/configuration.yml') == true) {
            try {
                $yaml = Yaml::parse($this->configDir . '/configuration.yml');

                if (array_key_exists('types', $yaml) == true) {
                    if (is_array($yaml['types']) == false) {
                        $output->writeln(
                            $this->translator->trans(
                                'validation.error.array',
                                array(
                                    '%key%'  => 'types',
                                    '%file%' => 'configuration'
                                )
                            )
                        );
                        return false;
                    }

                    foreach ($yaml['types'] as $name => $type) {
                        $display = 'types -> ' . $name;
                        if (is_array($type) == false) {
                            $output->writeln(
                                $this->translator->trans(
                                    'validation.error.array',
                                    array(
                                        '%key%'  => $display,
                                        '%file%' => 'configuration'
                                    )
                                )
                            );
                            return false;
                        }

                        if ($this->existsAndIsNotEmpty($output, $type, 'label', $display, 'configuration') == false) {
                            return false;
                        }

                        if ($this->existsAndIsArray($output, 'finder', $type, $display) == false
                            || $this->validateClassCreation($output, $type['finder'], $name, 'Finder') == false) {
                            return false;
                        }

                        if ($this->existsAndIsArray($output, 'glossary', $type, $display) == false
                            || $this->validateClassCreation($output, $type['glossary'], $name, 'Glossary') == false) {
                            return false;
                        }

                        if ($this->existsAndIsArray($output, 'fields', $type, $display) == false
                            || $this->validateFields($output, $type['fields'], $name) == false) {
                            return false;
                        }

                        if ($this->existsAndIsArray($output, 'pictures', $type, $display) == false
                            || $this->validateAccess($output, $type['pictures'], $display . ' -> pictures') == false) {
                            return false;
                        }

                        if (array_key_exists('sounds', $type) == true) {
                            if ($this->existsAndIsArray($output, 'sounds', $type, $display) == false
                                || $this->validateAccess($output, $type['sounds'], $display . ' -> sounds') == false) {
                                return false;
                            }
                        }
                    }
                } else {
                    $output->writeln(
                        $this->translator->trans(
                            'validation.error.root',
                            array(
                                '%key%'  => 'types',
                                '%file%' => 'configuration'
                            )
                        )
                    );
                    return false;
                }

            } catch (ParseException $error) {
                $output->writeln($this->translator->trans('validation.error.yaml', array('%file%' => 'configuration')));
                return false;
            }

            return true;
        } else {
            $output->writeln($this->translator->trans('validation.error.file', array('%file%' => 'configuration')));
            return false;
        }
    }

    /**
     * Test if a key exits and contains an array.
     * @param OutputInterface $output
     * @param                 $field
     * @param array           $data
     * @param                 $display
     * @return bool
     */
    private function existsAndIsArray(OutputInterface $output, $field, array $data, $display)
    {
        if (array_key_exists($field, $data) == false) {
            $output->writeln(
                $this->translator->trans(
                    'validation.error.key',
                    array(
                        '%key%'  => $field,
                        '%type%' => $display,
                        '%file%' => 'configuration'
                    )
                )
            );
            return false;
        } elseif (is_array($data[$field]) == false) {
            $output->writeln(
                $this->translator->trans(
                    'validation.error.array',
                    array(
                        '%key%'  => $display . ' -> ' . $field,
                        '%file%' => 'configuration'
                    )
                )
            );
            return false;
        }

        return true;
    }

    /**
     * Validates the creation of a class. A class can be created by creating a new instance (new CLASNAME) or by a
     * factory (FACTORY->METHOD() or FACTORY::METHOD()). The returning class must also implement the FinderInterface
     * or the GlossaryInterface.
     *
     * @param OutputInterface $output
     * @param array           $definition
     * @param                 $type
     * @param                 $classType
     * @return bool
     */
    private function validateClassCreation(OutputInterface $output, array $definition, $type, $classType)
    {
        $typeName = $type . ' -> ' . strtolower($classType);
        if (array_key_exists('class', $definition) == false) {
            $output->writeln(
                $this->translator->trans(
                    'validation.error.key',
                    array(
                        '%key%'  => 'class',
                        '%type%' => $typeName,
                        '%file%' => 'configuration'
                    )
                )
            );
            return false;
        } elseif (class_exists($definition['class']) == false) {
            $output->writeln(
                $this->translator->trans(
                    'validation.error.class',
                    array(
                        '%class%' => $definition['class'],
                        '%type%'  => $typeName,
                        '%file%'  => 'configuration'
                    )
                )
            );
            return false;
        }

        if (array_key_exists('factory', $definition) == true
            && $definition['factory'] == true) {
            if (array_key_exists('method', $definition) == false) {
                $output->writeln(
                    $this->translator->trans(
                        'validation.error.key',
                        array(
                            '%key%'  => 'method',
                            '%type%' => $typeName,
                            '%file%' => 'configuration'
                        )
                    )
                );
                return false;
            } elseif (method_exists($definition['class'], $definition['method']) == false) {
                $output->writeln(
                    $this->translator->trans(
                        'validation.error.method',
                        array(
                            '%method%' => $definition['method'],
                            '%class%'  => $definition['class'],
                            '%type%'   => $typeName
                        )
                    )
                );
                return false;
            }

            $factory = new $definition['class'];
            $method = $definition['method'];
            if (array_key_exists('static', $definition) == true
                && $definition['static'] == true) {
                $reflection = new \ReflectionMethod($factory, $definition['method']);

                if ($reflection->isStatic() == false) {
                    $output->writeln(
                        $this->translator->trans(
                            'validation.error.static',
                            array(
                                '%method%' => $definition['method'],
                                '%class%'  => $definition['class'],
                                '%type%'   => $typeName
                            )
                        )
                    );
                    return false;
                }

                $class = $factory::$method();
            } else {
                $class = $factory->$method();
            }
        } else {
            $class = new $definition['class']();
        }

        if (($classType == 'Finder'
                && ($class instanceof FinderInterface) === false)
            || ($classType == 'Glossary'
                && ($class instanceof GlossaryInterface) === false)
            ) {
            $output->writeln(
                $this->translator->trans(
                    'validation.error.interface',
                    array(
                        '%class%'     => get_class($class),
                        '%interface%' => $classType
                    )
                )
            );
            return false;
        }

        return true;
    }

    /**
     * Validate the configuration of the fields returned by the API. There must be at least one field! Each field must
     * have a not empty label and a content type (= type), which must be string, boolean, number, integer or float. The
     * parameter nullable is optional. But if given it must be a boolean.
     *
     * @param OutputInterface $output
     * @param array           $fields
     * @param                 $type
     * @return bool
     */
    private function validateFields(OutputInterface $output, array $fields, $type)
    {
        $fieldTyp = array('string', 'boolean', 'number', 'integer', 'float');
        foreach ($fields as $name => $values) {
            $display = $type .  ' -> fields -> ' . $name;
            if (is_array($values) == false) {
                $output->writeln(
                    $this->translator->trans(
                        'validation.error.array',
                        array(
                            '%key%'  => $display,
                            '%file%' => 'configuration'
                        )
                    )
                );
                return false;
            }

            if (array_key_exists('type', $values) == false) {
                $output->writeln(
                    $this->translator->trans(
                        'validation.error.key',
                        array(
                            '%key%'  => 'type',
                            '%type%' =>  $display,
                            '%file%' => 'configuration'
                        )
                    )
                );
                return false;
            } elseif (in_array($values['type'], $fieldTyp) == false) {
                $output->writeln(
                    $this->translator->trans(
                        'validation.error.type',
                        array(
                            '%type%'  => $values['type'],
                            '%field%' => $display,
                            '%file%'  => 'configuration'
                        )
                    )
                );
                return false;
            }

            if (array_key_exists('label', $values) == false) {
                $output->writeln(
                    $this->translator->trans(
                        'validation.error.key',
                        array(
                            '%key%'  => 'label',
                            '%type%' => $display,
                            '%file%' => 'configuration'
                        )
                    )
                );
                return false;
            } elseif ($values['label'] === '') {
                $output->writeln(
                    $this->translator->trans(
                        'validation.error.empty',
                        array(
                            '%key%'  => 'label',
                            '%type%' => $display,
                            '%file%' => 'configuration'
                        )
                    )
                );
                return false;
            }

            if (array_key_exists('nullable', $values) == true) {
                if (is_bool($values['nullable']) == false) {
                    $output->writeln(
                        $this->translator->trans(
                            'validation.error.boolean',
                            array(
                                '%field%' => $display . ' -> nullable',
                                '%file%'  => 'configuration'
                            )
                        )
                    );
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Validates the access configuration for pictures and sound files. The field online must be set to a boolean. If
     * online is true, a url must be given and security must be set to a boolean. If security is true, a user and
     * password field must contain an not empty value.
     *
     * @param OutputInterface $output
     * @param array           $fields
     * @param                 $type
     * @return bool
     */
    private function validateAccess(OutputInterface $output, array $fields, $type)
    {
        if (array_key_exists('online', $fields) == false) {
            $output->writeln(
                $this->translator->trans(
                    'validation.error.key',
                    array(
                        '%key%'  => 'online',
                        '%type%' => $type,
                        '%file%' => 'configuration'
                    )
                )
            );
            return false;
        }

        if ($fields['online'] === false) {
            return true;
        } elseif ($fields['online'] === true) {
            if ($this->existsAndIsNotEmpty($output, $fields, 'url', $type, 'configuration') == false) {
                return false;
            }

            if (array_key_exists('security', $fields) == false) {
                $output->writeln(
                    $this->translator->trans(
                        'validation.error.key',
                        array(
                            '%key%'  => 'security',
                            '%type%' => $type,
                            '%file%' => 'configuration'
                        )
                    )
                );
                return false;
            }

            if ($fields['security'] == false) {
                return true;
            } elseif ($fields['security'] === true) {
                if ($this->existsAndIsNotEmpty($output, $fields, 'user', $type, 'configuration') === false
                    || $this->existsAndIsNotEmpty($output, $fields, 'password', $type, 'configuration') == false) {
                    return false;
                }

                return true;
            } else {
                $output->writeln(
                    $this->translator->trans(
                        'validation.error.boolean',
                        array(
                            '%field%'  => 'security',
                            '%type%' => $type,
                            '%file%' => 'configuration'
                        )
                    )
                );
                return false;
            }
        } else {
            $output->writeln(
                $this->translator->trans(
                    'validation.error.boolean',
                    array(
                        '%field%' => 'online',
                        '%file%'  => 'configuration'
                    )
                )
            );
            return false;
        }
    }

    /**
     * Test if a field exits in the given array and is not empty ( !== '').
     * @param OutputInterface $output
     * @param array           $fields
     * @param                 $field
     * @param                 $type
     * @param                 $file
     * @return bool
     */
    private function existsAndIsNotEmpty(OutputInterface $output, array $fields, $field, $type, $file)
    {
        if (array_key_exists($field, $fields) == false) {
            $output->writeln(
                $this->translator->trans(
                    'validation.error.key',
                    array(
                        '%key%'  => $field,
                        '%type%' => $type,
                        '%file%' => $file
                    )
                )
            );
            return false;
        } elseif ($fields[$field] === '') {
            $output->writeln(
                $this->translator->trans(
                    'validation.error.empty',
                    array(
                        '%key%'  => $field,
                        '%type%' => $type,
                        '%file%' => $file
                    )
                )
            );
            return false;
        }

        return true;
    }
}
 