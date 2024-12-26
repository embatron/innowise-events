<?php

namespace Drupal\innowise_events;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Event entity.
 */
class EventAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        // Доступ на просмотр для всех авторизованных пользователей.
        return AccessResult::allowed();

      case 'update':
        // Доступ только для администраторов.
        return AccessResult::allowedIfHasPermission($account, 'administer event entities');

      case 'delete':
        // Доступ только для администраторов.
        return AccessResult::allowedIfHasPermission($account, 'administer event entities');
    }

    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    // Доступ к созданию событий только для администраторов.
    return AccessResult::allowedIfHasPermission($account, 'administer event entities');
  }
}
