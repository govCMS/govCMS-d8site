<?php

namespace Drupal\govcms_user_actions\EventSubscriber;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Entity\EntityTypeEvent;
use Drupal\Core\Entity\EntityTypeEvents;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\govcms_user_actions\Event\EntityTypeEventLog;
use Drupal\log_entity\LogEntityEvents;
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
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $eventDispatcher
   *   The event dispatcher.
   */
  public function __construct(AccountProxyInterface $user, ContainerAwareEventDispatcher $eventDispatcher) {
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
   * @param \Drupal\Core\Entity\EntityTypeEvent $entity_type
   *   The entity type object.
   */
  public function onUpdate(EntityTypeEvent $entity_type) {
    $this->logEvent($entity_type, 'update');
  }

  /**
   * Entity type create listener.
   *
   * @param \Drupal\Core\Entity\EntityTypeEvent $entity_type
   *   The entity type object.
   */
  public function onCreate(EntityTypeEvent $entity_type) {
    $this->logEvent($entity_type, 'create');
  }

  /**
   * Entity type delete event listener.
   *
   * @param \Drupal\Core\Entity\EntityTypeEvent $entity_type
   *   The entity type object.
   */
  public function onDelete(EntityTypeEvent $entity_type) {
    $this->logEvent($entity_type, 'delete');
  }

  /**
   * Create the log event in reaction to an entity type event.
   *
   * @param \Drupal\Core\Entity\EntityTypeEvent $entity_type
   *   The entity type that is being operated on.
   * @param string $op
   *   The string operation.
   */
  public function logEvent(EntityTypeEvent $entity_type, $op = 'insert') {
    $event = new EntityTypeEventLog('entity_type', $this->user, $entity_type->getEntityType(), $op);
    $event->stopPropagation();
    $this->eventDispatcher->dispatch(LogEntityEvents::LOG_EVENT, $event);
  }

}
