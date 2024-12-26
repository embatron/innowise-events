<?php

namespace Drupal\innowise_events\GraphQL;

use Drupal\event\Entity\Event;

class ActiveEventResolver {
  public function resolve() {
    $query = \Drupal::entityQuery('event')
      ->condition('status', TRUE);
    $entity_ids = $query->execute();

    return Event::loadMultiple($entity_ids);
  }
}
