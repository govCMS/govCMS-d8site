<?php

namespace Drupal\govcms_user_actions\Event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\log_entity\Event\LogEvent;

/**
 * Class NodeEvent.
 *
 * @package Drupal\govcms_user_actions\Event
 */
class EntityEvent extends LogEvent {

  use StringTranslationTrait;

  /**
   * The operation.
   *
   * @var string
   */
  protected $op;

  /**
   * The user object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * The node object.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * NodeEvent constructor.
   *
   * @param string $event_type
   *   The event type.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   A user object.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   * @param string $op
   *   The operation being undertaken.
   */
  public function __construct($event_type, AccountInterface $user, EntityInterface $entity, $op = 'insert') {
    parent::__construct($event_type);
    $this->user = $user;
    $this->entity = $entity;
    $this->op = $op;
  }

  /**
   * Build an entity log string.
   *
   * @return string
   *   String representation of the entity.
   */
  public function getEntityString() {
    return $this->t('on @label (@id) @state @bundle', [
      '@label' => $this->entity->label(),
      '@id' => $this->entity->id(),
      '@state' => $this->op == 'insert' ? 'into' : 'from',
      '@bundle' => $this->entity->bundle(),
    ])->render();
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('User @user performed [@op] @entity', [
      '@user' => $this->user->getAccountName(),
      '@entity' => $this->getEntityString(),
      '@op' => $this->op,
    ])->render();
  }

}
