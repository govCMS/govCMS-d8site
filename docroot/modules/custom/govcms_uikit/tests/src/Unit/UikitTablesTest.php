<?php

namespace Drupal\Tests\govcms_uikit\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\govcms_uikit\Plugin\Filter\UikitTables;

/**
 * Class UikitTablesTest
 *
 * @coversDefaultClass Drupal\govcms_uikit\Plugin\Filter\UikitTables
 * @group govcms
 */
class UikitTablesTest extends UnitTestCase {

  /**
   * Ensure that markup is replace as expected.
   *
   * @dataProvider markupProvider
   */
  public function testReplaceMarkup($string, $expected, $langcode = 'en') {
    $filter = $this->getMockBuilder(UikitTables::class)
      ->disableOriginalConstructor()
      ->setMethods(null)
      ->getMock();

    $processResult = $filter->process($string, $langcode);

    foreach ($expected as $partial) {
      $this->assertContains($partial, $processResult->getProcessedText());
    }
  }

  /**
   * Data provider for the HTML values.
   *
   * @return array
   */
  public function markupProvider() {
    $simple_table = <<<EOD
<table>
<thead><tr><th>Test</th></tr></thead>
<tbody><tr><td>Value</td></tr></tbody>
</table>
EOD;

    $multiple_cols = <<<EOD
<table>
<thead><tr>
<th>Col 1</th>
<th>Col 2</th>
</tr></thead>
<tbody><tr>
<td>Value</td>
<td>Value 2</td>
</tr></tbody>
</table>
EOD;

    $mismatch_cells = <<<EOD
<table>
<thead><tr>
<th>Col 1</th>
</tr></thead>
<tbody><tr>
<td>Value</td>
<td class="test">Value 2</td>
</tr></tbody>
</table>
EOD;

    $content_before = <<<EOD
<p>Content before the table.</p>
<table>
<thead><tr><th>Test</th></tr></thead>
<tbody><tr><td>Value</td></tr></tbody>
</table>
EOD;

    $content_after = <<<EOD
<table>
<thead><tr><th>Test</th></tr></thead>
<tbody><tr><td>Value</td></tr></tbody>
</table>
<p>Content after the table.</p>
EOD;

    $correct_markup = <<<EOD
<table>
<thead><tr><th>Test</th></tr></thead>
<tbody><tr><td data-label="Test">Value</td></tr></tbody>
</table>
EOD;

    return [
      [$simple_table, ['<td data-label="Test">']],
      [$multiple_cols, ['<td data-label="Col 1">', '<td data-label="Col 2">']],
      [$mismatch_cells, ['<td data-label="Col 1">', '<td class="test">']],
      [$content_before, ['<td data-label="Test">', '<p>Content before the table.</p>']],
      [$content_after, ['<td data-label="Test">', '<p>Content after the table.</p>']],
      [$correct_markup, ['<td data-label="Test">']],
    ];
  }
}
