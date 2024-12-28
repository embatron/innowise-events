<?php

namespace Drupal\innowise_events\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form controller for Event edit forms.
 */
class EventForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->updateEventStatus();

    $status = parent::save($form, $form_state);

    $message = $status === SAVED_NEW
      ? $this->t('The event %title has been created.', ['%title' => $this->entity->label()])
      : $this->t('The event %title has been updated.', ['%title' => $this->entity->label()]);
    $this->messenger()->addMessage($message);

    $form_state->setRedirect('innowise_events.admin_events_list');
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    if (!$this->entity->isNew()) {
      $actions['view'] = [
        '#type' => 'link',
        '#title' => $this->t('View'),
        '#url' => $this->entity->toUrl(),
        '#attributes' => ['class' => ['button']],
        '#weight' => 0,
      ];
    }

    $actions['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromRoute('innowise_events.admin_events_list'),
      '#attributes' => ['class' => ['button']],
      '#weight' => 10,
    ];

    return $actions;
  }

  /**
   * Updates the status of the event based on the end date.
   */
  protected function updateEventStatus() {
    $end_date = $this->entity->get('event_end_date')->value;

    if ($end_date) {
      $current_date = new \DateTime('now', new \DateTimeZone('UTC'));
      $event_end_date = \DateTime::createFromFormat('Y-m-d', $end_date, new \DateTimeZone('UTC'));

      if ($event_end_date === false) {
        $this->messenger()->addError($this->t('Invalid end date format for the event.'));
        return;
      }

      $new_status = $current_date > $event_end_date ? 0 : 1;
      if ($this->entity->get('status')->value != $new_status) {
        $this->entity->set('status', $new_status);
      }
    }
  }

}
