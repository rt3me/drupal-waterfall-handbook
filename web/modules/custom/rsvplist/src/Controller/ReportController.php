<?php

namespace Drupal\rsvplist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
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
      $select_query->addField('r', 'mail');

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

  /**
   * Creates the RSVPList report page.
   *
   * @return array
   *   Render array for the RSVPList report output.
   */
  public function report() {
    $content = [];

    $content['message'] = [
      '#markup' => $this->t(
        'Below is a list of all Event RSVPs including username,
        email address and the name of the event they will be attending.'
      ),
    ];

    $headers = [
      $this->t('Username'),
      $this->t('Event'),
      $this->t('Email'),
    ];

    // Because load() returns an associative array with each table row
    // as its own array, we can simply define the HTML table rows like this:
    $table_rows = $this->load();

    // However, as an example, if load() did not return the results in
    // a structure compatible with what we need, we could populate the
    // $table_rows variable like so:
    /*
     * $table_rows = [];
     * // Load the entries from database.
     * $rsvp_entries = $this->load();
     *
     * // Go through each entry and add it to $rows.
     * // Ultimately each array will be rendered as a row in an HTML table.
     * foreach ($rsvp_entries as $entry) {
     *   $table_rows[] = $entry;
     * }
     */

    // Create the render array for rendering an HTML table.
    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $table_rows,
      '#empty' => $this->t('No entries available.'),
    ];

    // Do not cache this page by setting the max-age to 0.
    $content['#cache']['max-age'] = 0;

    return $content;
  }

}
