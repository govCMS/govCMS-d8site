<?php

namespace Drupal\govcms_user_actions\Event;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\log_entity\Event\LogEvent;

/**
 * Class EntityTypeEventLog.
 *
 * @package Drupal\govcms_user_actions\Event
 */
class EntityTypeEventLog extends LogEvent {

  use StringTranslationTrait;

  /**
   * The entity type the event is being triggered for.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  private $entityType;

  /**
   * The current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $user;

  /**
   * The operation.
   *
   * @var string
   */
  private $op;

  /**
   * EntityTypeEvent constructor.
   *
   * @param string $event_type
   *   The event type that is being triggered.
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type that is being operated on.
   * @param string $op
   *   The operation bering performed.
   */
  public function __construct($event_type, AccountProxyInterface $user, EntityTypeInterface $entity_type, $op = 'insert') {
    parent::__construct($event_type);
    $this->user = $user;
    $this->entityType = $entity_type;
    $this->op = $op;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('@user performed [@op] on @type', [
      '@user' => $this->user->getAccountName(),
      '@op' => $this->op,
      '@type' => $this->entityType->getLabel(),
    ])->render();
  }

}
