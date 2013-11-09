<?php
/**
 * Main Silex based API to access web resources for the digital Museums Guide API v1.
 *
 * @author  Benjamin Geißler <benjamin.geissler@gmail.com>
 * @package diMuG
 * @licence GPL v3
 */
namespace diMuG;

use diMuG\API\Factory;
use diMuG\API\Loader;
use diMuG\API\Security;
use Monolog\Logger;
use Silex\Application;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

// configuration files
$app['parameters.file.security']      = __DIR__ . '/../config/security.yml';
$app['parameters.file.configuration'] = __DIR__ . '/../config/configuration.yml';

// enable security configuration
$app['security.active'] = Security::addFirewall($app, $app['parameters.file.security']);

/**
 * Check if firewall correct started, otherwise return 500 error. Disabled during tests.
 */
$app->before(
    function (Request $request) use ($app) {
        if ($app['security.active'] == false) {
            header('HTTP/1.0 500 Security error!');
            exit;
        }

    },
    Application::EARLY_EVENT
);

// logger configuration
$app->register(
    new MonologServiceProvider(),
    array(
        'monolog.logfile' => __DIR__ . '/../log/dimug.log',
        'monolog.name'    => 'diMuG',
        'monolog.level'   => Logger::WARNING
    )
);

// log 404 and 500 HTTP response codes
$app->finish(
    function (Request $request, Response $response) use ($app) {
        if ($response->getStatusCode() == 404) {
            $app['monolog']->addWarning(
                'Access error!',
                array(
                    'request'       => $request->getRequestUri(),
                    'status code: ' => $response->getStatusCode(),
                    'message:'      => $response->getContent()
                )
            );
        } elseif ($response->getStatusCode() == 500) {
            $app['monolog']->addCritical(
                'There is an critical error in one of the yaml configuration files!',
                array(
                    'request'       => $request->getRequestUri(),
                    'status code: ' => $response->getStatusCode(),
                    'message:'      => $response->getContent()
                )
            );
        }
    }
);

// return values of the configuration.yml file as array
$app['service.config'] = function ($app) {
    if (file_exists($app['parameters.file.configuration']) == true) {
        try {
            return Yaml::parse(file_get_contents($app['parameters.file.configuration']));
        } catch (ParseException $error) {
            throw new \ErrorException('errors.configuration.invalid');
        }
    } else {
        throw new \ErrorException('errors.configuration.missing');
    }
};

// translations
$app->register(new TranslationServiceProvider(), array('locale_fallbacks' => array('en')));
$app['translator'] = $app->share(
    $app->extend(
        'translator',
        function ($translator) {
            $translator->addLoader('yaml', new YamlFileLoader());

            $translator->addResource('yaml', __DIR__ . '/../locales/en.yml', 'en');
            $translator->addResource('yaml', __DIR__ . '/../locales/de.yml', 'de');

            return $translator;
        }
    )
);

// loader to retrieve the necessary information's
$app['service.loader'] = function ($app) {
    return new Loader(new Factory(), $app['service.config']);
};

// return the version of this api
$app->get(
    '/api/version',
    function () use ($app) {
        return $app->json(array('version' => 1));
    }
);

/**
 * Retrieve all supported types of artefact's by this API.
 * Returns arrays with the fields name and label for each artefact type.
 */
$app->get(
    '/api/types',
    function () use ($app) {
        try {
            $config = $app['service.config'];

            $types = array();
            foreach ($config['types'] as $name => $type) {
                $types[] = array(
                    'name'  => $name,
                    'label' => $type['label']
                );
            }

            return $app->json($types);
        } catch (\ErrorException $error) {
            return new Response($app['translator']->trans('errors.configuration.invalid'), 500);
        }
    }
);

/**
 * Receive all field information's (field-type [string, boolean..], label, nullable) for a given artefact type.
 */
$app->get(
    '/api/fields/{type}',
    function ($type) use ($app) {
        try {
            return $app->json($app['service.loader']->loadFields($type));
        } catch (\ErrorException $error) {
            return new Response($app['translator']->trans($error->getMessage(), array('%type%' => $type)), 404);
        }
    }
);

/**
 * Retrieve the information to access the pictures corresponding to the given type.
 */
$app->get(
    '/api/pictures/{type}',
    function ($type) use ($app) {
        try {
            return $app->json($app['service.loader']->loadPictureConfiguration($type));
        } catch (\ErrorException $error) {
            return new Response($app['translator']->trans($error->getMessage(), array('%type%' => $type)), 404);
        }
    }
);

/**
 * Retrieve the information to access the optional sound files corresponding to the given type.
 */
$app->get(
    '/api/sounds/{type}',
    function ($type) use ($app) {
        try {
            return $app->json($app['service.loader']->loadSoundConfiguration($type));
        } catch (\ErrorException $error) {
            return new Response($app['translator']->trans($error->getMessage(), array('%type%' => $type)), 404);
        }
    }
);

/**
 * Retrieve all glossary entries of a given type.
 */
$app->match(
    '/api/glossary/{type}',
    function ($type) use ($app) {
        try {
            return $app->json($app['service.loader']->loadGlossary($type));
        } catch (\ErrorException $error) {
            return new Response($app['translator']->trans($error->getMessage(), array('%type%' => $type)), 404);
        }
    }
)->method('GET|POST');

/**
 * Retrieve an entry by its inventory number from a given type. If no entry is found the result is empty.
 */
$app->get(
    '/api/find/{type}/{inventory}',
    function ($type, $inventory) use ($app) {
        try {
            return $app->json($app['service.loader']->loadFinder($type)->findOne($inventory));
        } catch (\ErrorException $error) {
            return new Response($app['translator']->trans($error->getMessage(), array('%type%' => $type)), 404);
        }
    }
);

/**
 * Retrieve all entries of the given type.
 */
$app->match(
    '/api/findAll/{type}',
    function ($type) use ($app) {
        try {
            return $app->json($app['service.loader']->loadFinder($type)->findAll());
        } catch (\ErrorException $error) {
            return new Response($app['translator']->trans($error->getMessage(), array('%type%' => $type)), 404);
        }
    }
)->method('GET|POST');

$app->run();
  