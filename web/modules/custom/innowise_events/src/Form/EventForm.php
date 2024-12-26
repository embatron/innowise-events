<?php

namespace Drupal\innowise_events\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\DrupalDateTime;

/**
 * Form controller for Event edit forms.
 */
class EventForm extends ContentEntityForm {

  /**
   * Updates the status of the event based on the end date.
   */
  protected function updateEventStatus() {
    $end_date = $this->entity->get('event_end_date')->value;

    if ($end_date) {
      $current_date = new \DateTime('now', new \DateTimeZone('UTC'));
      $event_end_date = new \DateTime($end_date, new \DateTimeZone('UTC'));

      // Update status based on the comparison between current date and event end date.
      $new_status = $current_date > $event_end_date ? 0 : 1;

      // Only update status if it has changed.
      if ($this->entity->get('status')->value != $new_status) {
        $this->entity->set('status', $new_status);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Update the event status before saving.
    $this->updateEventStatus();

    // Save the entity.
    $status = parent::save($form, $form_state);

    // Display messages based on the save status.
    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('The event %title has been created.', ['%title' => $this->entity->label()]));
        break;

      case SAVED_UPDATED:
        $this->messenger()->addMessage($this->t('The event %title has been updated.', ['%title' => $this->entity->label()]));
        break;
    }

    // Redirect to the collection page after saving.
    $form_state->setRedirect('entity.event.collection');
  }

}