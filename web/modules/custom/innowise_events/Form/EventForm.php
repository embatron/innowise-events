<?php

namespace Drupal\innowise_events\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Form controller for Event edit forms.
 */
class EventForm extends ContentEntityForm {

  /**
   * Checks and updates the status of the event.
   */
  public function checkAndUpdateStatus() {
    $end_date = $this->get('event_end_date')->value;

    if ($end_date) {
      $current_date = new DrupalDateTime('now');
      $event_end_date = new DrupalDateTime($end_date);

      // Если текущая дата позже даты окончания события, статус становится неактивным.
      if ($current_date > $event_end_date) {
        if ($this->get('status')->value != 0) {
          $this->set('status', 0);
          $this->save();
        }
      } else {
        if ($this->get('status')->value != 1) {
          $this->set('status', 1);
          $this->save();
        }
      }
    }
  }

  /**
   * Triggers the status update when the entity is loaded.
   */
  public static function postLoad(EntityTypeInterface $entity_type, array $entities) {
    foreach ($entities as $entity) {
      if ($entity instanceof self) {
        $entity->checkAndUpdateStatus();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    // Сохраняем сущность.
    $status = parent::save($form, $form_state);

    // Добавляем сообщение о результате сохранения.
    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('The event %title has been created.', ['%title' => $entity->label()]));
        break;

      case SAVED_UPDATED:
        drupal_set_message($this->t('The event %title has been updated.', ['%title' => $entity->label()]));
        break;
    }

    // Перенаправляем пользователя на список сущностей после сохранения.
    $form_state->setRedirect('entity.event.collection');
  }

}
