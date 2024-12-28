<?php

namespace Drupal\innowise_events\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;

/**
 * @SchemaExtension(
 *   id = "innowise_events_schema",
 *   name = "Innowise Events Schema",
 *   description = "Schema extension for the Innowise Test module",
 *   schema = "default"
 * )
 */
class InnowiseEventsSchemaExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('Query', 'allActiveEvents',
      $builder->produce('get_active_events')
    );

    $registry->addFieldResolver('Query', 'eventById',
      $builder->produce('get_event_by_id')
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Mutation', 'registerForEvent',
      $builder->produce('register_for_event')
        ->map('eventId', $builder->fromArgument('eventId'))
        ->map('userId', $builder->fromArgument('userId'))
    );
  }
}
