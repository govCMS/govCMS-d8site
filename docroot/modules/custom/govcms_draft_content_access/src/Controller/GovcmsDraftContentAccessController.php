<?php

namespace Drupal\govcms_draft_content_access\Controller;

use Drupal\auto_login_url\AutoLoginUrlCreate;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * {@inheritdoc}
 */
class GovcmsDraftContentAccessController extends ControllerBase {

  /**
   * The Auto Login URL Create service.
   *
   * @var \Drupal\auto_login_url\AutoLoginUrlCreate
   */
  protected $aluCreate;

  /**
   * The Database Connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public function __construct(AutoLoginUrlCreate $alu_create, Connection $connection) {
    $this->aluCreate = $alu_create;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('auto_login_url.create'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function generatelink($uid, $destination) {

    $destination = str_replace("-", "/", $destination);
    $alu_destination = ltrim($destination, "/");
    $auto_login_url = $this->aluCreate->create($uid, $alu_destination, TRUE);

    // Add generated hash to ea alu table.
    $alu_ea_hash = explode("/", $auto_login_url);

    $this->connection->insert('auto_login_url_govcms')
      ->fields(['uid', 'hash', 'destination', 'timestamp'])
      ->values([
        'uid' => $uid,
        'hash' => end($alu_ea_hash),
        'destination' => $alu_destination,
        'timestamp' => time(),
      ])
      ->execute();

    drupal_set_message($this->t('Your tokenised link: <strong>@link</strong>', ['@link' => $auto_login_url]), 'status');

    return new RedirectResponse($destination);
  }

  /**
   * {@inheritdoc}
   */
  public function revokelink($hash, $destination) {

    $destination = str_replace("-", "/", $destination);
    $config = \Drupal::config('auto_login_url.settings');

    // Delete record based on hash url table.
    $this->connection->delete('auto_login_url_govcms')
      ->condition('hash', [$hash])
      ->execute();

    // Delete record based on alu hash table.
    $hash_db = hash('sha256', $hash . $config->get('secret'));
    $this->connection->delete('auto_login_url')
      ->condition('hash', [$hash_db])
      ->execute();

    return new RedirectResponse($destination);
  }

}
