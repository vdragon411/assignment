<?php

namespace Drupal\assignment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Assignment Form (Vinay Patil).
 *
 * A form where user enters name and submits. The name is then
 * shown through Drupal's alert and logged in watchdog.
 */
class AssignmentForm extends FormBase {

  /**
   * Messenger object to show the alert to the user.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Logger object to log the name in watchdog.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * Adding our dependencies needed to show and log the name.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   To show status messages.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   To log info or error to watchdog.
   */
  public function __construct(MessengerInterface $messenger, LoggerChannelFactoryInterface $logger) {
    $this->messenger = $messenger;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('messenger'),
      $container->get('logger.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'assignment_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // The field where the user will enter name.
    // This value will be used in the Drupal Alert and logged into watchdog.
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Getting the name value from form state.
    $name = $form_state->getValue('name');

    // Only allow alphabet and space.
    if (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
      $form_state->setErrorByName('name', 'Please enter a proper name.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Getting the name value from form state.
    $name = $form_state->getValue('name');

    // Showing the name through Drupal's Alert.
    $this->messenger()->addStatus("Name: $name");

    // Logging the name to watchdog.
    $this->logger('assignment')->info("Name: $name");
  }

}
