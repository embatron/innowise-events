<?php

namespace Drupal\innowise_events\Plugin\GraphQL\DataProducer;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Error\UserError;

/**
 *
 * @DataProducer(
 *   id = "get_event_by_id",
 *   name = @Translation("Get Event By ID"),
 *   description = @Translation("Fetches an event by its ID."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Event")
 *   ),
 *   consumes = {
 *     "id" = @ContextDefinition("string",
 *       label = @Translation("Event ID")
 *     )
 *   }
 * )
 */
class GetEventById extends DataProducerPluginBase {

  /**
   * Resolves the event by ID.
   *
   * @param string $id
   *   The ID of the event.
   *
   * @return array
   *   The event data.
   *
   * @throws \GraphQL\Error\UserError
   *   If the event is not found.
   */
  public function resolve($id) {
    $entityTypeManager = \Drupal::service('entity_type.manager');
    $storage = $entityTypeManager->getStorage('mock_event');
    $event = $storage->load($id);

    if (!$event) {
      throw new UserError("Event with ID {$id} not found.");
    }

    return [
      'id' => $event->id(),
      'title' => $event->label(),
      'status' => $event->get('field_event_s_status')->value ? 'Active' : 'Completed',
      'location' => $event->get('field_event_s_location')->value,
      'startDate' => $event->get('field_start_date')->value,
      'endDate' => $event->get('field_end_date')->value,
      'maxParticipants' => $event->get('field_max_participants')->value,
      'participantsCount' => count($event->get('field_participants')->referencedEntities()),
    ];
  }
}
