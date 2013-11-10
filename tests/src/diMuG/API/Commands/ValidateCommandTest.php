<?php
/**
 * ValidateCommandTest.php.
 *
 * @author     Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package    diMuG\API\Commands
 */
 

namespace diMuG\API\Commands;


use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;

class ValidateCommandTest extends \PHPUnit_Framework_TestCase
{
    protected $command;

    protected function setUp($dir = '', array $options = array())
    {
        $translation = new Translator('en_EN', new MessageSelector());
        $translation->setFallbackLocales(array('en'));
        $translation->addLoader('yaml', new YamlFileLoader());
        $translation->addResource('yaml', __DIR__ . '/../../../../../locales/en.yml', 'en');
        $translation->addResource('yaml', __DIR__ . '/../../../../../locales/de.yml', 'de');

        $command = new ValidateCommand();
        $command
            ->setConfigDir($dir)
            ->setTranslator($translation);

        $console  =   new Application();
        $console->add($command);

        $command = $console->find('api:validate');
        $this->command = new CommandTester($command);
        $this->command->execute(
            array_merge(
                array('command' => $command->getName()),
                $options
            )
        );
    }

    public function testValidConfig()
    {
        $this->setUp(__DIR__ . '/valid/');

        $this->assertRegExp('/Your "security.yml" is valid!/', $this->command->getDisplay());
        $this->assertRegExp('/Your "configuration.yml" is valid!/', $this->command->getDisplay());
    }

    public function testValidConfigInGerman()
    {
        $this->setUp(__DIR__ . '/valid/', array('language' => 'de'));

        $this->assertRegExp('/Die Datei "security.yml" korrekt formatiert!/', $this->command->getDisplay());
        $this->assertRegExp('/Die Datei "configuration.yml" korrekt formatiert!/', $this->command->getDisplay());
    }

    /**
     * @param       $dir
     * @param array $messages
     * @dataProvider dataProviderForInValidConfiguration
     */
    public function testInValidConfiguration($dir, array $messages)
    {
        $this->setUp(__DIR__ . $dir);

        foreach ($messages as $message) {
            $this->assertRegExp($message, $this->command->getDisplay());
        }

    }

    public function dataProviderForInValidConfiguration()
    {
        return array(
            array(
                '/error1/', array('/The "security.yml" file is not a valid YAML file!/')
            ),
            array(
                '/error2/', array('/The root key "api" is missing in the file "security.yml"!/')
            ),
            array(
                '/error3/', array('/The key "user" is missing for the type "api" in the file "security.yml"!/')
            ),
            array(
                '/error4/', array('/The key "user" for the type "api" in the file "security.yml" is empty!/')
            ),
            array(
                '/error5/', array('/The key "password" is missing for the type "api" in the file "security.yml"!/')
            ),
            array(
                '/error6/', array('/The key "password" for the type "api" in the file "security.yml" is empty!/')
            ),
            array(
                '/error7/', array('/The "security.yml" file is missing!/')
            ),
            array(
                '/error8/', array('/The "configuration.yml" file is missing!/')
            ),
            array(
                '/error9/', array('/The key "finder" is missing for the type "types -> coins" in the file "configuration.yml"!/')
            ),
            array(
                '/error10/', array('/The key "class" is missing for the type "coins \-> finder" in the file "configuration.yml"!/')
            ),
            array(
                '/error11/', array('/The class "diMuG\\\Test\\\NotThereMock", defined for the type "coins -> finder" in the file "configuration.yml" could not be found!/')
            ),
            array(
                '/error12/', array('/The method "getFinder" in the class "diMuG\\\Test\\\FactoryMock" of the type "coins -> finder" is not a static method!/')
            ),
            array(
                '/error13/', array('/There is no method "notThere" in the class "diMuG\\\Test\\\FactoryMock" for the type "coins -> finder" defined!/')
            ),
            array(
                '/error14/', array('/The key "method" is missing for the type "coins -> finder" in the file "configuration.yml"!/')
            ),
            array(
                '/error15/', array('/The class "diMuG\\\Test\\\GlossaryMock" is not an implementing the interface "diMuG\\\API\\\Interfaces\\\FinderInterface"/')
            ),
            array(
                '/error16/', array('/The key "types" in the file "configuration.yml" is no array!/')
            ),
            array(
                '/error17/', array('/The key "label" is missing for the type "types \-> coins" in the file "configuration.yml"!/')
            ),
            array(
                '/error18/', array('/The key "types \-> coins" in the file "configuration.yml" is no array!/')
            ),
            array(
                '/error19/', array('/The key "fields" is missing for the type "types -> coins" in the file "configuration.yml"!/')
            ),
            array(
                '/error20/', array('/The key "types \-> coins \-> fields" in the file "configuration.yml" is no array!/')
            ),
            array(
                '/error21/', array('/The key "type" is missing for the type "coins \-> fields \-> test" in the file "configuration.yml"!/')
            ),
            array(
                '/error22/', array('/The field type "array" for the field "coins \-> fields \-> test" in the file "configuration.yml" is not supported! Supported field types are string, boolean, number, integer and float!/')
            ),
            array(
                '/error23/', array('/The value of the field "coins -> fields -> test -> nullable" in the file "configuration.yml" must be a boolean value!/')
            ),
            array(
                '/error24/', array('/The key "label" for the type "coins -> fields -> test" in the file "configuration.yml" is empty!/')
            ),
            array(
                '/error25/', array('/The key "coins -> fields -> test" in the file "configuration.yml" is no array!/')
            ),
            array(
                '/error26/', array('/The key "label" is missing for the type "coins -> fields -> test" in the file "configuration.yml"!/')
            ),
            array(
                '/error27/', array('/The root key "types" is missing in the file "configuration.yml"!/')
            ),
            array(
                '/error28/', array('/The "configuration.yml" file is not a valid YAML file!/')
            ),
            array(
                '/error29/', array('/The "configuration.yml" file is not a valid YAML file!/')
            ),
            array(
                '/error30/', array('/The key "types \-> coins \-> sounds" in the file "configuration.yml" is no array!/')
            ),
            array(
                '/error31/', array('/The key "url" is missing for the type "types -> coins -> pictures" in the file "configuration.yml"!/')
            ),
            array(
                '/error32/', array('/Your "configuration.yml" is valid!/')
            ),
            array(
                '/error33/', array('/The key "user" is missing for the type "types -> coins -> pictures" in the file "configuration.yml"!/')
            ),
            array(
                '/error33/', array('/The key "user" is missing for the type "types -> coins -> pictures" in the file "configuration.yml"!/')
            ),
            array(
                '/error34/', array('/The key "types -> coins -> pictures" in the file "configuration.yml" is no array!/')
            ),
            array(
                '/error35/', array('/The key "password" is missing for the type "types -> coins -> pictures" in the file "configuration.yml"!/')
            ),
            array(
                '/error36/', array('/The key "online" is missing for the type "types -> coins -> pictures" in the file "configuration.yml"!/')
            ),
            array(
                '/error37/', array('/The key "password" for the type "types -> coins -> pictures" in the file "configuration.yml" is empty!/')
            ),
            array(
                '/error38/', array('/The value of the field "security" in the file "configuration.yml" must be a boolean value!/')
            ),
            array(
                '/error39/', array('/The key "security" is missing for the type "types -> coins -> pictures" in the file "configuration.yml"!/')
            ),
            array(
                '/error40/', array('/The value of the field "online" in the file "configuration.yml" must be a boolean value!/')
            ),
            array(
                '/error41/', array('/The class "diMuG\\\Test\\\FinderMock" is not an implementing the interface "diMuG\\\API\\\Interfaces\\\GlossaryInterface"!/')
            )

        );
    }

}
 