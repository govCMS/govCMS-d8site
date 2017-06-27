<?php

namespace Drupal\govcms_uikit\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Prepares table markup for responsive tables.
 *
 * @Filter(
 *   id = "filter_uikit_tables",
 *   title = @Translation("UIKit Tables Filter"),
 *   description = @Translation("Ensure tables are correctly formatted for UI Kit 2.0"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class UikitTables extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    /** @var \DOMDocument $html */
    $html = Html::load($text);
    $tables = $html->getElementsByTagName('table');

    /** @var \DOMDocument $table */
    foreach ($tables as $table) {
      /** @var \DOMNodeList $headers */
      $headers = $table->getElementsByTagName('th');
      /** @var \DOMNodeList $cells */
      $body = $table->getElementsByTagName('tbody');

      if (empty($body)) {
        // Ensure that we have a tbody for the table. If we don't have a tbody
        // we assume that the table is marked up manually and doesn't require
        // intervention from the filter.
        continue;
      }

      /** @var \DOMNodeList $rows */
      $rows = $body->item(0)->getElementsByTagName('tr');

      /** @var \DOMElement $row */
      foreach ($rows as $row) {
        $cells = $row->getElementsByTagName('td');

        /*
         * @var int $index
         *   Used to match the cell to a header.
         * @var \DOMElement $cell
         *   The table cell DOM element.
         */
        foreach ($cells as $index => $cell) {
          // Suppress the warning if the header doesn't exist.
          $header = @$headers->item($index);

          if (!empty($cell->getAttribute('data-label')) || is_null($header)) {
            continue;
          }

          $dataLabel = $html->createAttribute('data-label');
          $dataLabel->value = $header->nodeValue;
          $cell->appendChild($dataLabel);
        }
      }
    }

    // The Html::load method creates a new DOM object complete with HEAD and
    // BODY tags. To preserve the $text string we need to retrieve the inner
    // HTML of the body element.
    // @see Html::load
    $xml = new \DOMXPath($html);
    $updated_text = '';
    foreach ($xml->query('body') as $body) {
      $updated_text .= $body->C14N();
    }

    return new FilterProcessResult($updated_text);
  }

}
