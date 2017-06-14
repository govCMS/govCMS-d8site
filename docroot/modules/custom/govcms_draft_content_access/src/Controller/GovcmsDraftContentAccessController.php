<?php

namespace Drupal\govcms_draft_content_access\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * {@inheritdoc}
 */
class GovcmsDraftContentAccessController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function generatelink($uid, $destination) {

    $destination = str_replace("-", "/", $destination);
    /** @var \Drupal\auto_login_url\AutoLoginUrlCreate $alu_service */
    $alu_service = \Drupal::service('auto_login_url.create');
    $alu_destination = ltrim($destination, "/");
    $auto_login_url = $alu_service->create($uid, $alu_destination, TRUE);

    // Add generated hash to ea alu table.
    $alu_ea_hash = explode("/", $auto_login_url);

    $connection = \Drupal::database();

    $alu_ea_insert = $connection->insert('auto_login_url_govcms')
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
    $connection = \Drupal::database();
    $alu_ea_delete_record = $connection->delete('auto_login_url_govcms')
      ->condition('hash', [$hash])
      ->execute();

    // Delete record based on alu hash table.
    $hash_db = hash('sha256', $hash . $config->get('secret'));
    $alu_delete_record = $connection->delete('auto_login_url')
      ->condition('hash', [$hash_db])
      ->execute();

    return new RedirectResponse($destination);
  }

}
