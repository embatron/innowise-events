<?php

namespace Drupal\innowise_events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Handles registration functionality for mock events.
 */
class MockEventController extends ControllerBase {

  /**
   * Current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new MockEventController object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(AccountProxyInterface $current_user, EntityTypeManagerInterface $entityTypeManager) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Handles AJAX registration for mock events.
   *
   * @param int $mock_event
   *   The mock event ID.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response.
   */
  public function registerAjax($mock_event) {
    $response = new AjaxResponse();

    $mock_event_entity = $this->entityTypeManager->getStorage('mock_event')->load($mock_event);

    if (!$mock_event_entity) {
      $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--error">The event does not exist.</div>'));
      return $response;
    }

    $user = $this->currentUser();
    if ($user->isAnonymous()) {
      $login_url = \Drupal\Core\Url::fromRoute('user.login')->toString();
      $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--error">You must <a href="' . $login_url . '">log in</a> to register for the event.</div>'));
      return $response;
    }

    if (!$mock_event_entity->get('field_event_s_status')->value) {
      $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--error">This event is no longer active and registrations are not allowed.</div>'));
      return $response;
    }

    $participant_ids = array_column($mock_event_entity->get('field_participants')->getValue(), 'target_id');
    if (in_array($user->id(), $participant_ids)) {
      $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--warning">You are already registered for this event.</div>'));
      return $response;
    }

    $max_participants = $mock_event_entity->get('field_max_participants')->value;
    if (count($participant_ids) >= $max_participants) {
      $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--error">Registration is closed as the event is full.</div>'));
      return $response;
    }

    $mock_event_entity->get('field_participants')->appendItem($user->id());
    $mock_event_entity->save();

    $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--status">You have successfully registered for the event.</div>'));
    return $response;
  }

}
