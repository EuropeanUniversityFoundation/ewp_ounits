<?php

namespace Drupal\ewp_ounits;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Organizational Unit entity.
 *
 * @see \Drupal\ewp_ounits\Entity\Ounit.
 */
class OunitAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ewp_ounits\Entity\OunitInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished organizational unit entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published organizational unit entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit organizational unit entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete organizational unit entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add organizational unit entities');
  }


}
