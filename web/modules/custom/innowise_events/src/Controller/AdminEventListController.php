<?php

namespace Drupal\innowise_events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for listing events with administrative actions.
 */
class AdminEventListController extends ControllerBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new AdminEventListController.
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
   * Displays a list of events.
   *
   * @return array
   *   A renderable array containing the list of events.
   */
  public function listEvents() {
    $query = $this->entityTypeManager->getStorage('event')->getQuery();
    $query->accessCheck(TRUE);
    $entity_ids = $query->execute();
    $events = $this->entityTypeManager->getStorage('event')->loadMultiple($entity_ids);

    $add_new_button = Link::fromTextAndUrl($this->t('Add New Event'), Url::fromRoute('entity.event.add_form'))
      ->toRenderable();
    $add_new_button['#attributes'] = ['class' => ['button', 'button--primary', 'add-event-button']];

    $header = [
      $this->t('ID'),
      $this->t('Title'),
      $this->t('City'),
      $this->t('Start Date'),
      $this->t('End Date'),
      $this->t('Participants'),
      $this->t('Status'),
      $this->t('Actions'),
    ];

    $rows = [];
    $index = 1;

    foreach ($events as $event) {
      $participants_count = count($event->get('participants')->getValue());
      $max_participants = $event->get('max_participants')->value;
      $status = $event->get('status')->value ? $this->t('Active') : $this->t('Inactive');

      $rows[] = [
        $index++,
        Link::fromTextAndUrl($event->label(), Url::fromRoute('entity.event.canonical', ['event' => $event->id()])),
        $event->get('city')->value,
        $event->get('event_start_date')->value,
        $event->get('event_end_date')->value,
        $this->t('@count / @max', ['@count' => $participants_count, '@max' => $max_participants]),
        $status,
        [
          'data' => [
            '#type' => 'operations',
            '#links' => [
              'edit' => [
                'title' => $this->t('Edit'),
                'url' => Url::fromRoute('entity.event.edit_form', ['event' => $event->id()]),
              ],
              'delete' => [
                'title' => $this->t('Delete'),
                'url' => Url::fromRoute('entity.event.delete_form', ['event' => $event->id()]),
              ],
              'view_participants' => [
                'title' => $this->t('View Participants'),
                'url' => Url::fromRoute('innowise_events.view_participants', ['event' => $event->id()]),
              ],
            ],
          ],
        ],
      ];
    }

    return [
      'add_new_button' => $add_new_button,
      'events_table' => [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No events available.'),
      ],
    ];
  }

}
