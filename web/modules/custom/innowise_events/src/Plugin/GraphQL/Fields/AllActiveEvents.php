<?php

namespace Drupal\innowise_events\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Fetch all active events.
 *
 * @GraphQLField(
 *   id = "all_active_events",
 *   type = "[Event]",
 *   name = "allActiveEvents",
 *   multi = true,
 *   description = "Fetch all active events."
 * )
 */
class AllActiveEvents extends FieldPluginBase {

  /**
   * Resolver for fetching all active events.
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    $storage = \Drupal::entityTypeManager()->getStorage('event');
    $query = $storage->getQuery();
    $query->condition('status', TRUE);
    $ids = $query->execute();

    return $storage->loadMultiple($ids);
  }

}
