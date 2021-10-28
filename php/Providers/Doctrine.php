<?php

namespace Crisis\Providers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use UMA\DIC\Container;

/**
 * A ServiceProvider for registering services related to
 * Doctrine in a DI container.
 *
 * If the project had custom repositories (e.g. UserRepository)
 * they could be registered here.
 */
class Doctrine implements \UMA\DIC\ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function provide(Container $c): void
    {
        $c->set(EntityManager::class, static function (Container $c): EntityManager {
            /** @var array $settings */
            $settings = $c->get('settings');

            $ormConfiguration = Setup::createAnnotationMetadataConfiguration(
                $settings['doctrine']['metadata_dirs'],
                $settings['doctrine']['dev_mode'],
            );

            return EntityManager::create(
                $settings['doctrine']['connection'],
                $ormConfiguration
            );
        });
    }
}
