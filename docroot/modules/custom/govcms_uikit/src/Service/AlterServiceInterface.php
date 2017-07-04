<?php

namespace Drupal\govcms_uikit\Service;

/**
 * Interface for the UI Kit alter services.
 *
 * @package Drupal\govcms_uikit\Service
 */
interface AlterServiceInterface {

  /**
   * Alter the given render array based on UI Kit requirements.
   *
   * @param array $alterable
   *   A render array that is to be altered.
   *
   * @throws \Drupal\govcms_uikit\Error\UiKitInvalidRenderArray
   */
  public static function alter(array &$alterable = []);

}
