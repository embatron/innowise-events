<?php

namespace Drupal\innowise_events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for viewing participants of an event.
 */
class EventParticipantsController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new EventParticipantsController.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Renders a list of participants for a given event.
   *
   * @param int $event
   *   The event ID.
   *
   * @return array
   *   A render array.
   */
  public function viewParticipants($event) {
    // Load the event entity.
    $event_entity = $this->entityTypeManager->getStorage('event')->load($event);

    if (!$event_entity) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    // Get participant IDs.
    $participant_ids = array_column($event_entity->get('participants')->getValue(), 'target_id');

    // Load participant entities.
    $participants = $this->entityTypeManager->getStorage('user')->loadMultiple($participant_ids);

    // Define table headers.
    $header = [
      'User ID',
      'Username',
    ];

    // Build table rows.
    $rows = [];
    foreach ($participant_ids as $id) {
      if (isset($participants[$id])) {
        $participant = $participants[$id];
        $rows[] = [
          $participant->id(),
          $participant->getDisplayName(),
        ];
      }
      else {
        $rows[] = [
          $id,
          'User not found (deleted or missing)',
        ];
      }
    }

    // Render the table.
    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => 'No participants for this event.',
    ];
  }

}
