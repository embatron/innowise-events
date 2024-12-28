<?php

namespace Drupal\innowise_events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Session\AccountProxyInterface;

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
   * Constructs a new MockEventController object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user service.
   */
  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  /**
   * Handles AJAX registration for mock events.
   */
  public function registerAjax($mock_event) {
    $response = new AjaxResponse();

    $mock_event_entity = $this->entityTypeManager()->getStorage('mock_event')->load($mock_event);
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

    $status = $mock_event_entity->get('field_event_s_status')->value;
    if (!$status) {
      $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--error">This event is no longer active and registrations are not allowed.</div>'));
      return $response;
    }

    $is_registered = false;
    $participants = $mock_event_entity->get('field_participants')->referencedEntities();
    foreach ($participants as $participant) {
      if ($participant->id() == $user->id()) {
        $is_registered = true;
        break;
      }
    }

    if ($is_registered) {
      $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--warning">You are already registered for this event.</div>'));
      return $response;
    }

    $max_participants = $mock_event_entity->get('field_max_participants')->value;
    if (count($participants) >= $max_participants) {
      $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--error">Registration is closed as the event is full.</div>'));
      return $response;
    }

    $mock_event_entity->get('field_participants')->appendItem($user->id());
    $mock_event_entity->save();

    $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--status">You have successfully registered for the event.</div>'));
    return $response;
  }

}
