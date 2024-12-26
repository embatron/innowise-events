<?php

namespace Drupal\innowise_events\GraphQL;

use Drupal\event\Entity\Event;
use Drupal\user\Entity\User;

class RegisterEventResolver {
  public function resolve($event_id, $user) {
    $event = Event::load($event_id);

    if (!$event) {
      return FALSE;
    }

    $participants = $event->get('participants');
    $participants->appendItem($user->id());
    $event->save();

    return TRUE;
  }
}
