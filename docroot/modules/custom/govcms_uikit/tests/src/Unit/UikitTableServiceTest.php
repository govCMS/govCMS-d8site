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
  public function testAlter($rows, $headers, $index) {
    $this->service->alter($rows, $headers, $index);
    foreach ($rows as $row) {
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
   * @expectedException \Exception
   * @dataProvider renderArrayProvider
   */
  public function testInvalidColumnError($rows, $headers, $index) {
    $this->service->alter($rows, $headers, 'failure');
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
          ['cells' => [['attributes' => new Attribute()], ['attributes' => new Attribute()]]],
          ['cells' => [['attributes' => new Attribute()], ['attributes' => new Attribute()]]],
        ],
        [
          ['content' => 'Test'],
          ['content' => 'Test 2'],
        ],
        'cells',
      ],
      [
        [
          ['columns' => [['attributes' => new Attribute()], ['attributes' => new Attribute()]]],
          ['columns' => [['attributes' => new Attribute()], ['attributes' => new Attribute()]]],
        ],
        [
          ['content' => 'Test'],
          ['content' => 'Test 2'],
        ],
        'columns',
      ],
    ];
  }

}
