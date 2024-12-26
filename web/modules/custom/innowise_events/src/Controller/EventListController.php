<?php

namespace Drupal\innowise_events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Controller for listing all events.
 */
class EventListController extends ControllerBase {

  /**
   * Returns a renderable array for the list of events.
   */
  public function listEvents() {
    // Загружаем все события из базы данных с проверкой доступа.
    $query = \Drupal::entityTypeManager()->getStorage('event')->getQuery();
    $query->accessCheck(TRUE); // Устанавливаем проверку доступа.
    $entity_ids = $query->execute();
    $events = \Drupal::entityTypeManager()->getStorage('event')->loadMultiple($entity_ids);

    // Создаём таблицу с данными.
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
      $status = $event->get('status')->value ? $this->t('Active') : $this->t('Inactive');
      $rows[] = [
        Link::fromTextAndUrl($event->label(), Url::fromRoute('entity.event.canonical', ['event' => $event->id()])),
        $event->get('city')->value,
        $event->get('event_start_date')->value,
        $event->get('event_end_date')->value,
        count($event->get('participants')) . ' / ' . $event->get('max_participants')->value,
        $status,
      ];
    }

    $build['content'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No events available.'),
    ];

    return $build;
  }

}
