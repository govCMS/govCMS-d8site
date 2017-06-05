<?php

namespace Drupal\govcms_user_actions\EventSubscriber;

use Drupal\Core\Entity\EntityTypeEvents;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\govcms_user_actions\Event\EntityTypeEventLog;
use Drupal\log_entity\LogEntityEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EventSubscriber.
 *
 * @package Drupal\govcms_user_actions\EventSubscriber
 */
class EventSubscriber implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $user;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcher
   */
  private $eventDispatcher;

  /**
   * EventSubscriber constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   The current user object.
   * @param \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher
   *   The event dispatcher.
   */
  public function __construct(AccountProxyInterface $user, EventDispatcher $eventDispatcher) {
    $this->user = $user;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[EntityTypeEvents::UPDATE][] = ['onUpdate'];
    $events[EntityTypeEvents::CREATE][] = ['onCreate'];
    $events[EntityTypeEvents::DELETE][] = ['onDelete'];
    return $events;
  }

  /**
   * Entity type update listener.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type object.
   */
  public function onUpdate(EntityTypeInterface $entity_type) {
    $this->logEvent($entity_type, 'update');
  }

  /**
   * Entity type create listener.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type object.
   */
  public function onCreate(EntityTypeInterface $entity_type) {
    $this->logEvent($entity_type, 'create');
  }

  /**
   * Entity type delete event listener.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type object.
   */
  public function onDelete(EntityTypeInterface $entity_type) {
    $this->logEvent($entity_type, 'delete');
  }

  /**
   * Create the log event in reaction to an entity type event.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type that is being operated on.
   * @param string $op
   *   The string operation.
   */
  public function logEvent(EntityTypeInterface $entity_type, $op = 'insert') {
    $event = new EntityTypeEventLog('entity_type', $this->user, $entity_type, $op);
    $event->stopPropagation();
    $this->eventDispatcher->dispatch(LogEntityEvents::LOG_EVENT, $event);
  }

}
