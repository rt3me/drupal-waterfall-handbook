<?php

namespace Drupal\rsvplist;

use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;

/**
 * @file
 * Contains the RSVP Enabler service.
 */

/**
 * RSVP Enabler service.
 */
class EnablerService {

  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs an object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Checks if individual node is RSVP enabled.
   *
   * @param \Drupal\node\Entity\Node $node
   *   A node object.
   *
   * @return bool
   *   Whether or not the node is enabled for RSVP.
   */
  public function isEnabled(Node &$node) {
    if ($node->isNew()) {
      return FALSE;
    }
    try {
      $select = $this->connection->select('rsvplist_enabled', 're');
      $select->fields('re', ['nid']);
      $select->condition('nid', $node->id());
      $results = $select->execute();

      return !(empty($results->fetchCol()));
    }
    catch (\Exception $e) {
      $this->messenger()->addError(
        $this->t('Unable to determine RSVP settings at this time. Please try again.')
      );
      return NULL;
    }
  }

  /**
   * Sets an individual node to be RSVP enabled.
   *
   * @param \Drupal\node\Entity\Node $node
   *   A node object.
   *
   * @throws Exception
   */
  public function setEnabled(Node $node) {
    try {
      if (!($this->isEnabled($node))) {
        $insert = $this->connection->insert('rsvplist_enabled');
        $insert->fields(['nid']);
        $insert->values([$node->id()]);
        $insert->execute();
      }
    }
    catch (\Exception $e) {
      $this->messenger()->addError(
        $this->t('Unable to save RSVP settings at this time. Please try again.')
      );
    }
  }

  /**
   * Deletes RSVP enabled setting for a single node.
   *
   * @param \Drupal\node\Entity\Node $node
   *   A node object.
   */
  public function delEnabled(Node $node) {
    try {
      $delete = $this->connection->delete('rsvplist_enabled');
      $delete->condition('nid', $node->id());
      $delete->execute();
    }
    catch (\Exception $e) {
      $this->messenger()->addError(
        $this->t('Unable to save RSVP settings at this time. Please try again.')
      );
    }
  }

}
