<?php

namespace Drupal\innowise_events\Plugin\GraphQL\DataProducer;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Error\UserError;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 *
 * @DataProducer(
 *   id = "register_for_event",
 *   name = @Translation("Register For Event"),
 *   description = @Translation("Handles user registration for an event."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Registration Result")
 *   ),
 *   consumes = {
 *     "eventId" = @ContextDefinition("string",
 *       label = @Translation("Event ID")
 *     ),
 *     "userId" = @ContextDefinition("string",
 *       label = @Translation("User ID")
 *     )
 *   }
 * )
 */
class RegisterForEvent extends DataProducerPluginBase {

  /**
   * Resolves the mutation to register a user for an event.
   *
   * @param string $eventId
   *   The event ID.
   * @param string $userId
   *   The user ID.
   *
   * @return string
   *   The registration result.
   *
   * @throws \GraphQL\Error\UserError
   *   If validation fails or the registration cannot be completed.
   */
public function resolve($eventId, $userId) {
    $entityTypeManager = \Drupal::service('entity_type.manager');
    $storage = $entityTypeManager->getStorage('mock_event');
    $userStorage = $entityTypeManager->getStorage('user');

    $event = $storage->load($eventId);
    if (!$event) {
        throw new UserError("The event with ID {$eventId} does not exist.");
    }

    if ($userId == 0) {
        throw new UserError("Anonymous users cannot register for events. Please log in.");
    }

    $user = $userStorage->load($userId);
    if (!$user) {
        throw new UserError("The user with ID {$userId} does not exist.");
    }

    if (!$event->get('field_event_s_status')->value) {
        throw new UserError("This event is no longer active, and registrations are not allowed.");
    }

    $participants = $event->get('field_participants');
    foreach ($participants as $participant) {
        if ($participant->target_id == $user->id()) {
            throw new UserError("The user is already registered for this event.");
        }
    }

    $maxParticipants = $event->get('field_max_participants')->value;
    if (count($participants) >= $maxParticipants) {
        throw new UserError("Registration is closed as the event is full.");
    }

    $event->get('field_participants')->appendItem($user->id());
    $event->save();

    return "User with ID {$userId} has successfully registered for the event with ID {$eventId}.";
}
}
