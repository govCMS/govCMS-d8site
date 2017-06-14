<?php

namespace Drupal\govcms_draft_content_access\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provide govCMS Draft Content Access Block.
 *
 * @Block(
 *   id = "govcms_draft_content_access_block",
 *   admin_label = @Translation("govCMS Draft Content Access Block"),
 *   category = @Translation("govCMS"),
 * )
 */
class GovcmsDraftContentAccessBlock extends BlockBase {

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
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node) {
      $moderation_value = $node->get('moderation_state')->getValue();
      $moderation_state = reset($moderation_value);
      if (isset($moderation_state['target_id']) && $moderation_state['target_id'] == 'draft') {
        $system_roles = user_role_names(TRUE);
        // Make sure role already created, no check on module permission.
        if (array_key_exists('draft_content_access', $system_roles)) {
          $autologin_config = \Drupal::config('auto_login_url.settings');
          $autologin_config_expired = $autologin_config->get('expiration');

          $user = \Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['name' => 'autologin']);

          if (!empty($user)) {
            $uid_data = array_keys($user);
            $uid = reset($uid_data);
            $destination = \Drupal::service('path.current')->getPath();

            // Check existing data or create new autologin link.
            $connection = \Drupal::database();
            $query = $connection->select('auto_login_url_govcms', 'a');
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
                $date_format_interval = ($date_interval < 0) ? $this->t('Expired') : \Drupal::service('date.formatter')->formatInterval($date_interval);

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

                $rows[$row->id] = [
                  'link' => \Drupal::l($this->t('Copy'), $link_html),
                  'plain_link' => \Drupal::l($this->t('Right click and copy link'), $link_plain),
                  'expired' => $date_format_interval,
                  'operation' => \Drupal::l($this->t('Revoke link'), $revoke_url),
                ];
              }

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
                '#prefix' => $this->t('<p>List of existing access link to this content. Click revoke to remove existing/expired link.</p>'),
                '#type' => 'table',
                '#header' => $header,
                '#rows' => $rows,
                '#empty' => $this->t('No autologin link found for this node.'),
                '#suffix' => \Drupal::l($this->t('Generate link'), $generate_url),
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
                '#prefix' => $this->t('<p>Click generate button to create access link to this content.</p>'),
                '#type' => 'markup',
                '#markup' => \Drupal::l($this->t('Generate link'), $generate_url),
                '#cache' => [
                  'max-age' => 0,
                ],
              ];
            }
          }
        }
      }
    }

    return $build;
  }

}
