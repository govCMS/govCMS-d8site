<?php

namespace Drupal\govcms_uikit\Service;

/**
 * Table service for UI Kit.
 *
 * @package Drupal\govcms_uikit\Service
 */
class Table {

  /**
   * Attempt to add data-label attributes to the table render array.
   *
   * @param array $rows
   *   A render array for table rows.
   * @param array $headers
   *   A render array for table headers.
   * @param string $cell_index
   *   The index that the cell will be at in the row.
   *
   * @throws \Exception
   */
  public function alter(array &$rows = [], array $headers = [], $cell_index = 'cells') {
    $header_values = array_keys($headers);

    foreach ($rows as &$row) {
      $index = 0;

      if (empty($row[$cell_index])) {
        throw new \Exception('Invalid table row.');
      }

      foreach ($row[$cell_index] as &$cell) {
        if (!$header = @$headers[$header_values[$index]]) {
          continue;
        }
        /** @var \Drupal\Core\Template\Attribute $attribute */
        $attribute = &$cell['attributes'];
        $attribute->setAttribute('data-label', $header['content']);
        $index++;
      }
    }
  }

}
