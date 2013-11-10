<?php
/**
 * InstallCommandTest.php.
 *
 * @author     Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package    diMuG\API\Commands
 */
namespace diMuG\API\Commands;


use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;

class InstallCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Symfony\Component\Console\Tester\CommandTester */
    protected $command;

    protected function setUp(array $install = array(), $language = 'en')
    {
        $translation = new Translator('en_EN', new MessageSelector());
        $translation->setFallbackLocales(array('en'));
        $translation->addLoader('yaml', new YamlFileLoader());
        $translation->addResource('yaml', __DIR__ . '/../../../../../locales/en.yml', 'en');
        $translation->addResource('yaml', __DIR__ . '/../../../../../locales/de.yml', 'de');

        $command = new InstallCommand();
        $command
            ->setInstalls($install)
            ->setTranslator($translation);

        $console  =   new Application();
        $console->add($command);

        $command = $console->find('api:install');
        $this->command = new CommandTester($command);
        $this->command->execute(array('command' => $command->getName(), 'language' => $language));
    }

    public function testMakeDir()
    {
        $root = vfsStream::setup(
            'project',
            null,
            array(
                'project' => array(
                    'vendor/dimug/app'
                )
            )
        );

        $this->assertFalse($root->hasChild('app'));
        $this->setUp(
            array(
                array(
                    'target' => vfsStream::url('project/app')
                )
            )
        );
        $this->assertRegExp('/All files have been copied!/', $this->command->getDisplay());
        $this->assertTrue($root->hasChild('app'));
    }

    public function testNoMakeDir()
    {
        $root = vfsStream::setup(
            'project',
            null,
            array(
                'project' => array(
                    'vendor/dimug/app'
                )
            )
        );

        $this->assertFalse($root->hasChild('app'));
        $this->setUp(
            array(
                array(
                    'target' => vfsStream::url('nothere/app')
                )
            )
        );

        $this->assertRegExp('/Could not create dir "vfs:\/\/nothere\/app" in project root!/', $this->command->getDisplay());
        $this->assertFalse($root->hasChild('app'));
    }

    public function testCopyFiles()
    {
        $root = vfsStream::setup(
            'project',
            null,
            array(
                'vendor' => array(
                    'dimug' => array(
                        'locales' => array(
                            'de.yml' => 'Hallo Welt!',
                            'en.yml' => 'Hello world!'
                        )
                    )
                )
            )
        );

        $this->assertFalse($root->hasChild('app'));
        $this->setUp(
            array(
                array(
                    'target' => vfsStream::url('project/locales'),
                    'source' => vfsStream::url('project/vendor/dimug/locales'),
                    'files' => array('de.yml', 'en.yml')
                )
            )
        );
        $this->assertRegExp('/All files have been copied!/', $this->command->getDisplay());
        $this->assertEquals(
            array(
                'project' => array(
                    'vendor' => array(
                        'dimug' => array(
                            'locales' => array(
                                'de.yml' => 'Hallo Welt!',
                                'en.yml' => 'Hello world!'
                            )
                        )
                    ),
                    'locales' => array(
                        'de.yml' => 'Hallo Welt!',
                        'en.yml' => 'Hello world!'
                    )
                )
            ),
            vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure()
        );
    }

    public function testDoNotCopyFiles()
    {
        vfsStream::setup(
            'project',
            null,
            array(
                'vendor' => array(
                    'dimug' => array(
                        'locales' => array(
                            'de.yml' => 'Hallo Welt!',
                            'en.yml' => 'Hello world!'
                        )
                    )
                )
            )
        );

        $this->setUp(
            array(
                array(
                    'target' => vfsStream::url('project/locales'),
                    'source' => vfsStream::url('project/vendor/dimug/noThere'),
                    'files' => array('de.yml', 'en.yml')
                )
            )
        );
        $this->assertRegExp('/The dir "vfs:\/\/project\/vendor\/dimug\/noThere" does not exist!/', $this->command->getDisplay());
    }

    public function testDoNotCopyFiles1()
    {
        vfsStream::setup(
            'project',
            null,
            array(
                'vendor' => array(
                    'dimug' => array(
                        'locales' => array(
                            'de.yml' => 'Hallo Welt!',
                            'en.yml' => 'Hello world!'
                        )
                    )
                )
            )
        );

        $this->setUp(
            array(
                array(
                    'target' => vfsStream::url('project/locales'),
                    'source' => vfsStream::url('project/vendor/dimug/locales'),
                    'files' => array('de.yml', 'en.yml', 'fr.yml')
                )
            )
        );
        $this->assertRegExp('/The source file "vfs:\/\/project\/vendor\/dimug\/locales\/fr.yml" is missing!/', $this->command->getDisplay());
    }

    public function testDoNotCopyFiles2()
    {
        $root = vfsStream::setup(
            'project',
            null,
            array(
                'vendor' => array(
                    'dimug' => array(
                        'locales' => array(
                            'de.yml' => 'Hallo Welt!',
                            'en.yml' => 'Hello world!'
                        )
                    )
                ),
                'locales' => array()
            )
        );

        $root->getChild('locales')->chmod(0);
        $this->setUp(
            array(
                array(
                    'target' => vfsStream::url('project/locales'),
                    'source' => vfsStream::url('project/vendor/dimug/locales'),
                    'files' => array('de.yml', 'en.yml')
                )
            )
        );
        $this->assertRegExp('/The source file "vfs:\/\/project\/vendor\/dimug\/locales\/de.yml" could not be copied to "vfs:\/\/project\/locales\/de.yml"/', $this->command->getDisplay());
    }

    public function testDoNotReplaceFiles()
    {
        $root = vfsStream::setup(
            'project',
            null,
            array(
                'vendor' => array(
                    'dimug' => array(
                        'locales' => array(
                            'de.yml' => 'Hallo Welt!',
                            'en.yml' => 'Hello world!'
                        )
                    )
                ),
                'locales' => array(
                    'de.yml' => 'Hallo Welt!'
                )
            )
        );

        $this->assertTrue($root->hasChild('locales'));
        $this->setUp(
            array(
                array(
                    'target' => vfsStream::url('project/locales'),
                    'source' => vfsStream::url('project/vendor/dimug/locales'),
                    'files' => array('de.yml', 'en.yml')
                )
            )
        );
        $this->assertRegExp('/The target file "vfs:\/\/project\/locales\/de.yml" already exists! File skipped!/', $this->command->getDisplay());
        $this->assertRegExp('/All files have been copied!/', $this->command->getDisplay());
        $this->assertEquals(
            array(
                'project' => array(
                    'vendor' => array(
                        'dimug' => array(
                            'locales' => array(
                                'de.yml' => 'Hallo Welt!',
                                'en.yml' => 'Hello world!'
                            )
                        )
                    ),
                    'locales' => array(
                        'de.yml' => 'Hallo Welt!',
                        'en.yml' => 'Hello world!'
                    )
                )
            ),
            vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure()
        );
    }
}
 