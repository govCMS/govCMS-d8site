<?php

namespace Drupal\govcms_draft_content_access\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide govCMS Draft Content Access Block.
 *
 * @Block(
 *   id = "govcms_draft_content_access_block",
 *   admin_label = @Translation("govCMS Draft Content Access Block"),
 *   category = @Translation("govCMS"),
 * )
 */
class GovcmsDraftContentAccessBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * User entity storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  /**
   * Current route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $user_storage, RouteMatchInterface $current_route_match, ConfigFactoryInterface $config_factory, CurrentPathStack $current_path, Connection $connection, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->userStorage = $user_storage;
    $this->currentRouteMatch = $current_route_match;
    $this->configFactory = $config_factory;
    $this->currentPath = $current_path;
    $this->connection = $connection;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_manager->getStorage('user'),
      $container->get('current_route_match'),
      $container->get('config.factory'),
      $container->get('path.current'),
      $container->get('database'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Provide default build markup to resolve cache issue.
    $build = [
      '#type' => 'markup',
      '#markup' => '',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    // Get node and it's moderation state.
    $node = $this->currentRouteMatch->getParameter('node');
    if (!$node) {
      return $build;
    }

    $moderation_value = $node->get('moderation_state')->getValue();
    $moderation_state = reset($moderation_value);
    if (!isset($moderation_state['target_id']) || $moderation_state['target_id'] != 'draft') {
      return $build;
    }

    $system_roles = user_role_names(TRUE);
    // Make sure role already created, no check on module permission.
    if (!array_key_exists('draft_content_access', $system_roles)) {
      return $build;
    }

    $autologin_config = $this->configFactory->get('auto_login_url.settings');
    $autologin_config_expired = $autologin_config->get('expiration');

    $user = $this->userStorage->loadByProperties(['name' => 'autologin']);
    if (empty($user)) {
      return $build;
    }

    $uid_data = array_keys($user);
    $uid = reset($uid_data);
    $destination = $this->currentPath->getPath();

    // Check existing data or create new autologin link.
    $query = $this->connection->select('auto_login_url_govcms', 'a');
    $query->fields('a');
    $query->condition('uid', $uid, '=');
    $query->condition('destination', ltrim($destination, "/"), '=');
    $query->orderBy('timestamp', 'DESC');

    $result = $query->execute()->fetchAll();
    if ($result) {

      // Table header.
      $header = [
        'link' => $this->t('Copy to clipboard'),
        'plain_link' => $this->t('Manual link copy'),
        'expired' => $this->t('Link expires'),
        'operation' => $this->t('Operation'),
      ];

      $rows = [];

      foreach ($result as $row) {
        $date_interval = ($row->timestamp + $autologin_config_expired) - time();
        $date_format_interval = ($date_interval < 0) ? $this->t('Expired') : $this->dateFormatter->formatInterval($date_interval);

        $autologin_link = Url::fromRoute('auto_login_url.login', [
          'uid' => $uid,
          'hash' => $row->hash,
        ], [
          'absolute' => TRUE,
        ]);

        $revoke_url = Url::fromRoute('govcms_draft_content_access.revokelink', [
          'hash' => $row->hash,
          'destination' => str_replace("/", "-", $destination),
        ], [
          'attributes' => [
            'class' => [
              'btn',
              'btn-danger',
            ],
          ],
        ]);

        $link_plain = $autologin_link;

        $link_html = Url::fromUri('internal:#', [
          'attributes' => [
            'class' => [
              'btn',
              'btn-primary',
              'btn-clipboard',
            ],
            'data-clipboard-text' => [
              $autologin_link->toString(),
            ],
          ],
        ]);

        $rows[$row->id]['data'] = [
          'link' => [
            'data' => [
              '#type' => 'link',
              '#title' => $this->t('Copy'),
              '#url' => $link_html,
            ],
          ],
          'plain_link' => [
            'data' => [
              '#type' => 'link',
              '#url' => $link_plain,
              '#title' => $this->t('Right click and copy link'),
            ],
          ],
          'expired' => $date_format_interval,
          'operation' => [
            'data' => [
              '#type' => 'link',
              '#url' => $revoke_url,
              '#title' => $this->t('Revoke link'),
            ],
          ],
        ];
      }

      $generate_url = [
        '#type' => 'link',
        '#url' => Url::fromRoute('govcms_draft_content_access.generatelink', [
          'uid' => $uid,
          'destination' => str_replace("/", "-", $destination),
        ], [
          'attributes' => [
            'class' => [
              'btn',
              'btn-success',
            ],
          ],
        ]),
        '#title' => $this->t('Generate link'),
      ];

      $build = [
        '#prefix' => '<p>' . $this->t('List of existing access link to this content. Click revoke to remove existing/expired link.') . '</p>',
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No autologin link found for this node.'),
        '#suffix' => render($generate_url),
        '#attached' => [
          'library' => ['govcms_draft_content_access/ea-dca'],
        ],
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }
    else {
      $generate_url = Url::fromRoute('govcms_draft_content_access.generatelink', [
        'uid' => $uid,
        'destination' => str_replace("/", "-", $destination),
      ], [
        'attributes' => [
          'class' => [
            'btn',
            'btn-success',
          ],
        ],
      ]);

      $build = [
        '#prefix' => '<p>' . $this->t('Click generate button to create access link to this content.') . '</p>',
        '#type' => 'link',
        '#url' => $generate_url,
        '#title' => $this->t('Generate link'),
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }

    return $build;
  }

}
