<?php

namespace Drupal\innowise_events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;

/**
 * Provides registration functionality for events.
 */
class EventController extends ControllerBase {

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
   * Constructs a new EventController object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user.
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
   * Handles event registration via AJAX.
   *
   * @param int $event
   *   The event ID.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response.
   */
  public function registerAjax($event) {
    $response = new AjaxResponse();

    $event_entity = $this->entityTypeManager->getStorage('event')->load($event);
    if (!$event_entity) {
      $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--error">The event does not exist.</div>'));
      return $response;
    }

    $user = $this->currentUser();
    if ($user->isAnonymous()) {
      $login_url = Url::fromRoute('user.login')->toString();
      $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--error">You must <a href="' . $login_url . '">log in</a> to register for the event.</div>'));
      return $response;
    }

    $status = $event_entity->get('status')->value ?? 0;
    if (!$status) {
      $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--error">This event is no longer active and registrations are not allowed.</div>'));
      return $response;
    }

    $participants = $event_entity->get('participants');
    if (in_array($user->id(), array_column($participants->getValue(), 'target_id'))) {
      $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--warning">You are already registered for this event.</div>'));
      return $response;
    }

    $max_participants = $event_entity->get('max_participants')->value ?? PHP_INT_MAX;
    if (count($participants->getValue()) >= $max_participants) {
      $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--error">Registration is closed as the event is full.</div>'));
      return $response;
    }

    $event_entity->get('participants')->appendItem($user->id());
    $event_entity->save();

    $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--status">You have successfully registered for the event.</div>'));
    return $response;
  }

  /**
   * Provides the event view page.
   *
   * @param int $event
   *   The ID of the event.
   *
   * @return array
   *   A render array.
   */
  public function view($event) {
    $event_entity = $this->entityTypeManager->getStorage('event')->load($event);
    if (!$event_entity) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $build = [
      '#type' => 'markup',
      '#markup' => $this->t('<h2>@title</h2>', ['@title' => $event_entity->label()]),
    ];

    if ($event_entity->hasField('register')) {
      $build['register_button'] = [
        '#type' => 'link',
        '#weight' => 100,
        '#title' => $this->t('Register'),
        '#url' => $event_entity->toUrl('register'),
        '#attributes' => [
          'class' => ['button', 'button--primary'],
        ],
      ];
    }

    return $build;
  }

}
