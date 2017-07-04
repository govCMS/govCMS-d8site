<?php

namespace Drupal\govcms_uikit\Service;

use Drupal\Core\Template\Attribute;
use Drupal\govcms_uikit\Error\UikitInvalidRenderArray;

/**
 * Table service for UI Kit.
 *
 * @package Drupal\govcms_uikit\Service
 */
class Table implements AlterServiceInterface {

  /**
   * {@inheritdoc}
   *
   * Loop through a given table render array and add data attributes for uikit.
   */
  public static function alter(array &$alterable = []) {
    $headers = isset($alterable['headers']) ? $alterable['headers'] : (isset($alterable['header']) ? $alterable['header'] : FALSE);
    $rows = isset($alterable['rows']) ? $alterable['rows'] : FALSE;

    if (!$headers) {
      throw new UikitInvalidRenderArray('Table render array requires \'headers\'');
    }

    if (!$rows) {
      throw new UikitInvalidRenderArray('Table render array requires \'rows\'');
    }

    $header_values = array_keys($headers);

    foreach ($rows as &$row) {
      $index = 0;
      // Unfortunately views table render uses columns instead of cells.
      $ci = isset($row['columns']) ? 'columns' : (isset($row['cells']) ? 'cells' : FALSE);

      if (!$ci) {
        throw new UikitInvalidRenderArray('Table row render array requires \'cells\'');
      }

      foreach ($row[$ci] as &$cell) {
        if (!$header = @$headers[$header_values[$index]]) {
          continue;
        }

        /** @var \Drupal\Core\Template\Attribute $attribute */
        $attribute = &$cell['attributes'];
        if (!is_a($attribute, Attribute::class)) {
          continue;
        }

        $attribute->setAttribute('data-label', $header['content']);
        $index++;
      }
    }
  }

}
