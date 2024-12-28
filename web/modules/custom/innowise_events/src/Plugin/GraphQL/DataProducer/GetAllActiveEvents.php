<?php

namespace Drupal\innowise_events\Plugin\GraphQL\DataProducer;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Error\UserError;

/**
 *
 * @DataProducer(
 *   id = "get_active_events",
 *   name = @Translation("Get Active Events"),
 *   description = @Translation("Fetches all active events."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Active Events")
 *   )
 * )
 */
class GetAllActiveEvents extends DataProducerPluginBase {

  /**
   *
   * @return array
   *   A list of active events.
   *
   * @throws \GraphQL\Error\UserError
   *   If no events are found.
   */
  public function resolve() {
    $entityTypeManager = \Drupal::service('entity_type.manager');
    $storage = $entityTypeManager->getStorage('mock_event');

    $query = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('field_event_s_status', 1)
      ->sort('created', 'DESC');
    $ids = $query->execute();

    if (empty($ids)) {
      throw new UserError("No active events found.");
    }

    $events = $storage->loadMultiple($ids);
    $result = [];
    foreach ($events as $event) {
      $result[] = [
        'id' => $event->id(),
        'title' => $event->label(),
        'status' => $event->get('field_event_s_status')->value,
        'location' => $event->get('field_event_s_location')->value,
        'startDate' => $event->get('field_start_date')->value,
        'endDate' => $event->get('field_end_date')->value,
        'maxParticipants' => $event->get('field_max_participants')->value,
      ];
    }

    return $result;
  }
}
