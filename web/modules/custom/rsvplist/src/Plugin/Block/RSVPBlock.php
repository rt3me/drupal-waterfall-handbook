<?php

namespace Drupal\rsvplist\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;

/**
 * @file
 * Creates a block which displays the RSVPForm contained in RSVPForm.php.
 */

/**
 * Provide the RSVP main block.
 *
 * @Block(
 *   id = "rsvp_block",
 *   admin_label = @Translation("The RSVP Block")
 * )
 */
class RSVPBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    return \Drupal::formBuilder()->getForm('Drupal\rsvplist\Form\RSVPForm');
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    // If viewing a node, get the fully loaded node object.
    $node = \Drupal::routeMatch()->getParameter('node');

    // Determine if the currently viewed page is a node.
    if (!(is_null($node))) {
      // Determine if RSVP collections are enabled on the node.
      $enabler = \Drupal::service('rsvplist.enabler');
      if ($enabler->isEnabled($node)) {
        // Determine if user has permission to view the block.
        return AccessResult::allowedIfHasPermission($account, 'view rsvplist');
      }
    }

    return AccessResult::forbidden();
  }

}
