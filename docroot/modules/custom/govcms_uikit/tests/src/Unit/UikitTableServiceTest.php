<?php

namespace Drupa\Tests\govcms_uikit\Unit;

use Drupal\Core\Template\Attribute;
use Drupal\Tests\UnitTestCase;
use Drupal\govcms_uikit\Service\Table;

/**
 * Class UikitTableServiceTest.
 *
 * @coversDefaultClass Drupal\govcms_uikit\Service\Table
 * @group govcms_uikit
 */
class UikitTableServiceTest extends UnitTestCase {

  /**
   * A table service mock.
   *
   * @var \Drupal\govcms_uikit\Service\Table
   */
  private $service;

  /**
   * Set up the test runner.
   */
  public function setup() {
    $this->service = $this->getMockBuilder(Table::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();
  }

  /**
   * Ensure that the given render arrays are altered correctly.
   *
   * @dataProvider renderArrayProvider
   * @group govcms_uikit
   */
  public function testAlter($alterable, $index) {
    $this->service->alter($alterable);
    foreach ($alterable['rows'] as $row) {
      foreach ($row[$index] as $cell) {
        /** @var \Drupal\Core\Template\Attribute $attribute */
        $attribute = $cell['attributes'];
        $dataLabel = $attribute->offsetGet('data-label');
        $this->assertNotNull($dataLabel);
      }
    }
  }

  /**
   * Ensure that the service throws a catchable.
   *
   * @expectedException \Drupal\govcms_uikit\Error\UikitInvalidRenderArray
   * @dataProvider invalidRenderArray
   */
  public function testInvalidColumnError($alterable) {
    $this->service->alter($alterable);
  }

  /**
   * Provide partial render arrays for testing.
   *
   * @return array
   *   A collection of render arrays for test functions.
   */
  public function renderArrayProvider() {
    return [
      [
        [
          'rows' => [
            ['cells' => [['attributes' => new Attribute()], ['attributes' => new Attribute()]]],
            ['cells' => [['attributes' => new Attribute()], ['attributes' => new Attribute()]]],
          ],
          'headers' => [
            ['content' => 'Test'],
            ['content' => 'Test 2'],
          ],
        ],
        'cells',
      ],
      [
        [
          'rows' => [
            ['columns' => [['attributes' => new Attribute()], ['attributes' => new Attribute()]]],
            ['columns' => [['attributes' => new Attribute()], ['attributes' => new Attribute()]]],
          ],
          'headers' => [
            ['content' => 'Test'],
            ['content' => 'Test 2'],
          ],
        ],
        'columns',
      ],
    ];
  }

  /**
   * Provide unexpected render arrays to the alter method.
   *
   * @return array
   *   Arrays that don't match tables.
   */
  public function invalidRenderArray() {
    return [
      [['no_rows' => [], 'no_headers' => []]],
      [['rows' => [], 'no_headers' => []]],
      [['no_rows' => [], 'headers' => []]],
    ];
  }

}
