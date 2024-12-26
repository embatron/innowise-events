<?php

namespace Drupal\innowise_events\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\MessengerCommand;
use Drupal\Core\Session\AccountProxyInterface;

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
   * Constructs a new EventController object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user.
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

public function registerAjax($event) {
  $response = new AjaxResponse();

  // Загружаем сущность события.
  $event_entity = $this->entityTypeManager()->getStorage('event')->load($event);

  if (!$event_entity) {
    $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--error">The event does not exist.</div>'));
    return $response;
  }

  $user = $this->currentUser();

  // Проверяем, авторизован ли пользователь.
  if ($user->isAnonymous()) {
    $login_url = \Drupal\Core\Url::fromRoute('user.login')->toString();
    $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--error">You must <a href="' . $login_url . '">log in</a> to register for the event.</div>'));
    return $response;
  }

  // Проверяем, активно ли событие.
  $status = $event_entity->get('status')->value;
  if (!$status) {
    $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--error">This event is no longer active and registrations are not allowed.</div>'));
    return $response;
  }

  // Проверяем, зарегистрирован ли пользователь.
  $is_registered = false;
  $participants = $event_entity->get('participants');
  foreach ($participants as $participant) {
    if ($participant->target_id == $user->id()) {
      $is_registered = true;
      break;
    }
  }

  if ($is_registered) {
    $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--warning">You are already registered for this event.</div>'));
    return $response;
  }

  // Проверяем лимит участников.
  $max_participants = $event_entity->get('max_participants')->value;
  if (count($participants) >= $max_participants) {
    $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--error">Registration is closed as the event is full.</div>'));
    return $response;
  }

  // Добавляем пользователя в список участников.
  $event_entity->get('participants')->appendItem($user->id());
  $event_entity->save();

  $response->addCommand(new HtmlCommand('#register-message', '<div class="messages messages--status">You have successfully registered for the event.</div>'));
  return $response;
}



  /**
   * Builds the event view page.
   *
   * @param int $event
   *   The ID of the event.
   *
   * @return array
   *   A render array.
   */
  public function view($event) {
    $event_entity = $this->entityTypeManager()->getStorage('event')->load($event);

    if (!$event_entity) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $build = [
      '#type' => 'markup',
      '#markup' => $this->t('<h2>@title</h2>', ['@title' => $event_entity->label()]),
    ];

    // Добавляем кнопку "Register".
    $build['register_button'] = [
      '#type' => 'link',
      '#weight' => 100,
      '#title' => $this->t('Register'),
      '#url' => $event_entity->toUrl('register'),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
      ],
    ];

    return $build;
  }

}
