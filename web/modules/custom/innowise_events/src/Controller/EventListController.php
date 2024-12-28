<?php

namespace Drupal\innowise_events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Controller for listing all events.
 */
class EventListController extends ControllerBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new EventListController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
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
   * Returns a renderable array for the list of events.
   *
   * @return array
   *   A renderable array containing the table of events.
   */
  public function listEvents() {
    $query = $this->entityTypeManager->getStorage('event')->getQuery();
    $query->accessCheck(TRUE);
    $entity_ids = $query->execute();
    $events = $this->entityTypeManager->getStorage('event')->loadMultiple($entity_ids);

    $header = [
      $this->t('Title'),
      $this->t('City'),
      $this->t('Start Date'),
      $this->t('End Date'),
      $this->t('Participants'),
      $this->t('Status'),
    ];

    $rows = [];
    foreach ($events as $event) {
      $participant_count = count($event->get('participants')->getValue());
      $rows[] = [
        Link::fromTextAndUrl($event->label(), Url::fromRoute('entity.event.canonical', ['event' => $event->id()])),
        $event->get('city')->value,
        $event->get('event_start_date')->value,
        $event->get('event_end_date')->value,
        $this->t('@count / @max', [
          '@count' => $participant_count,
          '@max' => $event->get('max_participants')->value,
        ]),
        $this->getEventStatus($event),
      ];
    }

    return [
      'content' => [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No events available.'),
      ],
    ];
  }

  /**
   * Gets the event status.
   *
   * @param object $event
   *   The event entity.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The translated status.
   */
  private function getEventStatus($event) {
    return $event->get('status')->value ? $this->t('Active') : $this->t('Inactive');
  }

}
