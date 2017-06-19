<?php

/**
 * @file
 * Update hook for to configure the panels.
 */

use Drupal\block_content\Entity\BlockContent;

/**
 * Creates blocks for homepage usage statistics.
 */
function govcms_base_update_8001(&$sandbox) {
  _usage_statistics_top_bottom_block();
}

/**
 * Create blocks for homepage usage statistics.
 */
function _usage_statistics_top_bottom_block() {

  // Check for existing blocks.
  $blockIds = \Drupal::entityQuery('block_content')
    ->condition('info', ['Usage statistics', 'Usage statistics - bottom'], 'IN')
    ->execute();

  // Blocks are only created if they do not currently exist.
  if (count($blockIds) === 0) {
    $uuid_service = \Drupal::service('uuid');

    // Top block - Title.
    $top_block = BlockContent::create([
      'type' => 'basic',
      'uuid' => $uuid_service->generate(),
      'info' => 'Usage statistics',
    ]);
    $top_block->body->value = '';
    $top_block->body->format = '';
    $top_block->save();

    // Bottom block - CTA.
    $bottom_block = BlockContent::create([
      'type' => 'basic',
      'uuid' => $uuid_service->generate(),
      'info' => 'Usage statistics - bottom',
    ]);
    $bottom_block->body->value = '<p><a class="uikit-btn hide-external" href="/dashboard">Statistics dashboard</a></p>';
    $bottom_block->body->format = 'rich_text';
    $bottom_block->save();
  }

}
