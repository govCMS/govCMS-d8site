<?php

namespace Drupal\govcms_password_policy\Plugin\PasswordConstraint;

use Drupal\password_policy\PasswordConstraintBase;
use Drupal\password_policy\PasswordPolicyValidation;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Password\PasswordInterface;

/**
 * Enforces repeat passwords cannot be used.
 *
 * @PasswordConstraint(
 *   id = "password_policy_reuse_constraint",
 *   title = @Translation("Password Reuse"),
 *   description = @Translation("Provide restrictions preventing the same password used repeatedly."),
 *   error_message = @Translation("You have used a previously used password and cannot.")
 * )
 */
class PasswordReuse extends PasswordConstraintBase implements ContainerFactoryPluginInterface {

  /**
   * The password service.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $passwordService;

  /**
   * A database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('password'),
      \Drupal::database()
    );
  }

  /**
   * Constructs a new PasswordRepeat constraint.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Password\PasswordInterface $password_service
   *   The password service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The Connection service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PasswordInterface $password_service, Connection $connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->passwordService = $password_service;
    $this->db = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'password_match' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['password_match'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number between password reuse'),
      '#description' => $this->t('Define how many different passwords need to be used before a password can be reused'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('password_match');
    if (!is_numeric($value) || $value < 1) {
      $form_state->setErrorByName('password_match', $this->t('The number between passwords must be greater than zero.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['password_match'] = $form_state->getValue('password_match');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return $this->t('Number of passwords between reuse: @number-passwords', [
      '@number-passwords' => $this->configuration['password_match'],
    ]);
  }

  /**
   * Get the recent passwords for a given user.
   *
   * Attempt to get the latest password that a user has changed limited by the
   * number of reuses that are configured for the policy.
   *
   * @return array
   *   A result matching all password history hashes for the user.
   */
  protected function getHashes($uid) {
    $configuration = $this->getConfiguration();

    return $this->db->select('password_policy_history', 'pph')
      ->fields('pph', ['pass_hash'])
      ->condition('uid', $uid)
      ->range(0, $configuration['password_match'])
      ->orderBy('timestamp', 'desc')
      ->execute()
      ->fetchAll();
  }

  /**
   * Private accessor for the password service.
   *
   * @return \Drupal\Core\Password\PasswordInterface
   *   The password interface service.
   */
  protected function getPasswordService() {
    return $this->passwordService;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($password, $user_context) {
    $validation = new PasswordPolicyValidation();

    if (empty($user_context['uid'])) {
      return $validation;
    }

    foreach ($this->getHashes($user_context['uid']) as $hash) {
      if ($this->getPasswordService()->check($password, $hash->pass_hash)) {
        $validation->setErrorMessage($this->t('Password has been used previously. Choose a different password'));
      }
    }

    return $validation;
  }

}
