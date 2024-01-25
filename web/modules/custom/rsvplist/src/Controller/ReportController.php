<?php

namespace Drupal\rsvplist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @file
 * Provide a list of all RSVP List signups.
 *
 * Provide site administrators with a list of all the RSVP List signups
 * so they know who is attending their events.
 */

/**
 * {@inheritdoc}
 */
class ReportController extends ControllerBase {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

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
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(Connection $connection, MessengerInterface $messenger) {
    $this->connection = $connection;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('messenger')
    );
  }

  /**
   * Gets and returns all RSVPs for all nodes.
   *
   * These are returned as an associative array, with each row
   * containing the username, the node title, and email of RSVP.
   *
   * @return array|null
   *   An array of all RSVPs for all nodes.
   */
  protected function load() {
    try {
      // https://www.drupal.org/docs/8/api/database-api/dynamic-queries
      // introduction-to-dynamic-queries
      $select_query = $this->connection->select('rsvplist', 'r');

      // Join the user table, so we can get the entry creator's username.
      $select_query->join('users_field_data', 'u', 'r.uid = u.uid');
      // Join the node table, so we can the the event's name.
      $select_query->join('node_field_data', 'n', 'r.nid = n.nid');

      // Select these specific fields for the output.
      $select_query->addField('u', 'name', 'username');
      $select_query->addField('n', 'title');
      $select_query->addField('r', 'email');

      // Note that fetchAll() and fetchAllAssoc() will, by default, fetch using
      // whatever fetch mode was set on the query
      // (i.e. numeric array, associative array, or object).
      // Fetches can be modified by passing in a new fetch mode constant.
      // For fetchAll(), it is the first parameter.
      // https://www.drupal.org/docs/8/api/database-api/result-sets
      // https://www.php.net/manual/en/pdostatement.fetch.php
      $entries = $select_query->execute()->fetchAll(\PDO::FETCH_ASSOC);

      return $entries;
    }
    catch (\Exception $e) {
      // Display a user-friendly error.
      $this->messenger()->addStatus(
        $this->t('Unable to access the database at this time. Please try again later.')
      );
      return NULL;
    }
  }

}
