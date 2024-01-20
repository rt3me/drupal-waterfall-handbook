<?php

/** 
 * @file
 * Contains the settings for administering the RSVP Form
 */

namespace Drupal\rsvplist\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class RSVPSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rsvplist_admin_settings';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'rsvplist.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // return an array of strings of the labels of node content types currently existing on the site
    $types = node_type_get_names();
    // a method of the ConfigFormBase class that retrieves the configuration object
    $config = $this->config('rsvplist.settings');
    // allowed_types requires its own file
    // do not need to declare a submit button in the form render array like RSVPForm
    $form['rsvplist_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('The content types to enable RSVP collection for'),
      '#default_value' => $config->get('allowed_types'),
      '#options' => $types,
      '#description' => $this->t('On the specified node types, an RSVP option
        will be available and can be enabled while the node is being edited.'),
    ];

    // no need for a submit button in the form render array because of this
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // remove empty strings
    $selected_allowed_types = array_filter($form_state->getValue(
      'rsvplist_types'));
    sort($selected_allowed_types);

    $this->config('rsvplist.settings')
      ->set('allowed_types', $selected_allowed_types)
      ->save();
    
    parent::submitForm($form, $form_state);
  }
}
