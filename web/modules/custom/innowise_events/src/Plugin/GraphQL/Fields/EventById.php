<?php

namespace Drupal\innowise_events\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Fetch event by ID.
 *
 * @GraphQLField(
 *   id = "event_by_id",
 *   type = "Event",
 *   name = "eventById",
 *   arguments = {
 *     "id" = "Int"
 *   },
 *   description = "Fetch event by its ID."
 * )
 */
class EventById extends FieldPluginBase {

  /**
   * Resolver for fetching event by ID.
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    $storage = \Drupal::entityTypeManager()->getStorage('event');
    return $storage->load($args['id']);
  }

}
