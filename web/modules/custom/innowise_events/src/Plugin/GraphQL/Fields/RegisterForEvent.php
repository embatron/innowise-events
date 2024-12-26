<?php

namespace Drupal\innowise_events\Plugin\GraphQL\Fields;

use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Register a user for an event.
 *
 * @GraphQLField(
 *   id = "register_for_event",
 *   type = "String",
 *   name = "registerForEvent",
 *   arguments = {
 *     "eventId" = "Int"
 *   },
 *   description = "Register a user for an event."
 * )
 */
class RegisterForEvent extends FieldPluginBase {

  /**
   * Resolver for registering a user for an event.
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    $event = \Drupal::entityTypeManager()->getStorage('event')->load($args['eventId']);
    $user = \Drupal::currentUser();

    if (!$event || !$user->isAuthenticated()) {
      return 'Event not found or user not authenticated.';
    }

    $participants = $event->get('participants')->referencedEntities();
    foreach ($participants as $participant) {
      if ($participant->id() == $user->id()) {
        return 'You are already registered for this event.';
      }
    }

    $event->get('participants')->appendItem($user->id());
    $event->save();

    return 'Successfully registered for the event.';
  }

}
