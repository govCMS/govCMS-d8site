<?php

namespace Drupal\Tests\govcms_password_policy\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Class PasswordReuseTest.
 *
 * @coversDefaultClass Drupal\govcms_password_policy\Plugin\PasswordConstraint\PasswordReuse
 * @group govcms
 */
class PasswordReuseTest extends UnitTestCase {

  public $passwordReuseMock;

  /**
   * Set up the test mock.
   */
  public function setup() {
    $password_reuse = $this->getMockBuilder('\Drupal\govcms_password_policy\Plugin\PasswordConstraint\PasswordReuse')
      ->setMethods(['getHashes', 'getPasswordService', 't'])
      ->disableOriginalConstructor()
      ->getMock();

    $password_reuse
      ->expects($this->once())
      ->method('getHashes')
      ->willReturn([(object) ['pass_hash' => 'fake_password']]);

    $this->passwordReuseMock = $password_reuse;
  }

  /**
   * Ensure that a password check success results in the correct output.
   *
   * @dataProvider userContextProvider
   */
  public function testPasswordReuseValid($password, $user_context) {
    $passwordService = $this->getPasswordService(FALSE);

    $this->passwordReuseMock->expects($this->once())
      ->method('getPasswordService')
      ->willReturn($passwordService);

    $this->assertEquals($this->passwordReuseMock->validate($password, $user_context)->isValid(), TRUE);
  }

  /**
   * Ensure that a password check failure results in the correct output.
   *
   * @dataProvider userContextProvider
   */
  public function testPasswordReuseInvalid($password, $user_context) {
    $passwordService = $this->getPasswordService(TRUE);

    $this->passwordReuseMock->expects($this->once())
      ->method('getPasswordService')
      ->willReturn($passwordService);

    $this->passwordReuseMock->expects($this->once())
      ->method('t')
      ->willReturn('Invalid password');

    $this->assertEquals($this->passwordReuseMock->validate($password, $user_context)->isValid(), FALSE);
  }

  /**
   * Return a password interface mock object.
   *
   * @return \Drupal\Core\Password\PasswordInterface
   *   The password interface mock.
   */
  public function getPasswordService($return) {
    $password_service = $this->getMockBuilder('Drupal\Core\Password\PasswordInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $password_service->method('check')->willReturn($return);

    return $password_service;
  }

  /**
   * Data provider for the user context.
   *
   * @return array
   *   The user context array.
   */
  public function userContextProvider() {
    $user_context = [
      'mail' => 'test@example.com',
      'name' => 'username',
      'uid' => 10,
    ];

    return [['password', $user_context]];
  }

}
