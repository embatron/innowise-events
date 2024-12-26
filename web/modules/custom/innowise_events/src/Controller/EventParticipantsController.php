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
    $event_entity = $this->entityTypeManager->getStorage('event')->load($event);

    if (!$event_entity) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $participants = $event_entity->get('participants')->referencedEntities();

    $header = [
      $this->t('User ID'),
      $this->t('Username'),
    ];

    $rows = [];
    foreach ($participants as $participant) {
      $rows[] = [
        $participant->id(),
        $participant->getDisplayName(),
      ];
    }

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No participants for this event.'),
    ];
  }

}
