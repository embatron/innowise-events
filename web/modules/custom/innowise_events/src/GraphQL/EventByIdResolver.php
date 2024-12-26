<?php

namespace Drupal\innowise_events\GraphQL;

use Drupal\event\Entity\Event;

class EventByIdResolver {
  public function resolve($id) {
    return Event::load($id);
  }
}
