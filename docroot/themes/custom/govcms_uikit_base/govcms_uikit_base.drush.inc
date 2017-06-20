<?php

/**
 * @file
 * Contains functions only needed for drush integration.
 *
 * Based on zen.drush.inc with modifications.
 */

/**
 * Implements hook_drush_command().
 */
function govcms_uikit_base_drush_command() {
  $items = [];

  $items['govcms-uikit'] = [
    'description' => 'Create a theme using govCMS UI Kit.',
    'arguments' => [
      'machine_name' => '[optional] A machine-readable name for your theme.',
      'name'         => 'A name for your theme.',
    ],
    'options' => [
      'name'         => 'A name for your theme.',
      'machine-name' => '[a-z, 0-9, _] A machine-readable name for your theme.',
      'path'         => 'The path where your theme will be created. Defaults to: themes/custom',
      'description'  => 'A description of your theme.',
    ],
    'examples' => [
      'drush govcms-uikit "Theme name"' => 'Create a sub-theme, using the default options.',
      'drush govcms-uikit some_theme "Theme name"' => 'Create a sub-theme with a specific machine name.',
      'drush govcms-uikit "Theme name" --path=themes --description="This is a theme."' => 'Create a sub-theme in the specified directory with a custom description.',
    ],
  ];

  return $items;
}

/**
 * Create a govCMS UI Kit sub-theme using the starter kit.
 */
function drush_govcms_uikit_base_govcms_uikit($machine_name = NULL, $name = NULL) {

  // ***************************************************
  // Determine all the command line options given.
  // ***************************************************.
  // Determine the theme name and machine name.
  if (!isset($name)) {
    // If only given one argument, it is the $name.
    if (isset($machine_name)) {
      $name = $machine_name;
      unset($machine_name);
    }
    else {
      $name = drush_get_option('name');
    }

    if (!isset($machine_name)) {
      $machine_name = drush_get_option('machine-name');
    }
  }

  if (!$name) {
    if ($machine_name) {
      $name = $machine_name;
    }
    else {
      return drush_set_error('GOVCMS_UI_KIT_SUBTHEME_NAME_NOT_SPECIFIED', dt('The name of the theme was not specified.'));
    }
  }

  if (!$machine_name) {
    $machine_name = $name;
  }

  // Clean up the machine name.
  $machine_name = str_replace(' ', '_', strtolower($machine_name));
  $search = [
    '/[^a-z0-9_]/',
  // Remove characters not valid in function names.
    '/^[^a-z]+/',
  // Functions must begin with an alpha character.
  ];
  $machine_name = preg_replace($search, '', $machine_name);

  // Determine the path to the new sub-theme.
  $sub_theme_path = 'themes/custom';
  if ($path = drush_get_option('path')) {
    $sub_theme_path = drush_trim_path($path);
  }
  $sub_theme_path = drush_normalize_path(drush_get_context('DRUSH_DRUPAL_ROOT') . '/' . $sub_theme_path . '/' . $machine_name);

  // ***************************************************
  // Error check directories, then copy STARTERKIT.
  // ***************************************************.
  // Ensure the destination directory (not the sub-theme folder) exists.
  if (!is_dir(dirname($sub_theme_path))) {
    return drush_set_error('GOVCMS_UI_KIT_DESTINATION_NOT_FOUND', dt('The directory "!directory" was not found.', ['!directory' => dirname($sub_theme_path)]));
  }

  // Ensure the STARTERKIT directory exists.
  $starterkit_path = drush_normalize_path(drush_get_context('DRUSH_DRUPAL_ROOT') . '/' . drupal_get_path('theme', 'govcms_uikit_base') . '/STARTERKIT');
  if (!is_dir($starterkit_path)) {
    return drush_set_error('GOVCMS_UI_KIT_STARTERKIT_NOT_FOUND', dt('The STARTERKIT directory was not found in "!directory"', ['!directory' => dirname($starterkit_path)]));
  }

  drush_print(dt('Copying files from STARTERKIT...'));

  // Make a fresh copy of the original starter kit.
  if (!drush_op('drush_copy_dir', $starterkit_path, $sub_theme_path)) {
    // drush_copy_dir errors are fatal errors for
    // our govCMS UI Kit drush command.
    return FALSE;
  }

  // ***************************************************
  // Alter the contents of the .info.yml file.
  // ***************************************************.
  drush_print(dt('Updating .info.yml file…'));

  $info_strings = [
    ': govcms_uikit_starterkit' => ': ' . $name,
    '# core: 8.x' => 'core: 8.x',
    "core: '8.x'\n" => '',
    "project: 'govcms_uikit_base'\n" => '',
    '- govcms_uikit_starterkit/' => '- ' . $machine_name . '/',
  ];

  // Replace the description if provided.
  if ($description = drush_get_option('description')) {
    $info_strings['Starter Kit for govCMS UI Kit Base'] = $description;
  }

  // Remove unwanted theme info.
  $info_regexs = [
    ['pattern' => "/hidden: true\n/", 'replacement' => ''],
    ['pattern' => '/\# Information added by Drupal\.org packaging script on [\d-]+\n/', 'replacement' => ''],
    ['pattern' => "/version: '[^']+'\n/", 'replacement' => ''],
    ['pattern' => '/datestamp: \d+\n/', 'replacement' => ''],
  ];

  drush_op('govcms_uikit_base_file_replace', $sub_theme_path . '/govcms_uikit_starterkit.info.yml', $info_strings, $info_regexs);

  // ***************************************************
  // Replace STARTERKIT in file names and contents.
  // ***************************************************.
  drush_print(dt('Replacing "govcms_uikit_starterkit" in all files…'));

  // Iterate through the sub-theme directory finding files to filter.
  $directory_iterator = new \RecursiveDirectoryIterator($sub_theme_path);
  $starter_kit_filter = new \RecursiveCallbackFilterIterator($directory_iterator, function ($current, $key, $iterator) {
    // Skip hidden files and directories.
    if ($current->getFilename()[0] === '.') {
      return FALSE;
    }
    // Skip node_modules and the asset-builds folder.
    elseif ($current->getFilename() === 'node_modules' || $current->getFilename() === 'asset-builds') {
      return FALSE;
    }
    // Recursively go through all folders.
    if ($current->isDir()) {
      return TRUE;
    }
    else {
      // Only return Twig templates or files with
      // "govcms_uikit_starterkit" in their name.
      return strpos($current->getFilename(), '.twig') !== FALSE || strpos($current->getFilename(), 'govcms_uikit_starterkit') !== FALSE;
    }
  });
  $iterator = new \RecursiveIteratorIterator($starter_kit_filter);
  $sub_theme_files = [];
  foreach ($iterator as $path => $info) {
    $sub_theme_files[$info->getFilename()] = $path;
  }

  // Add theme-settings.php to the list of files to filter.
  $sub_theme_files['package.json'] = $sub_theme_path . '/package.json';
  $sub_theme_files['theme-settings.php'] = $sub_theme_path . '/theme-settings.php';

  foreach ($sub_theme_files as $filename) {
    // Replace occurrences of 'govcms_uikit_starterkit'
    // with machine name of our sub theme.
    drush_op('govcms_uikit_base_file_replace', $filename, ['govcms_uikit_starterkit' => $machine_name]);

    // Rename all files with govcms_uikit_starterkit in their name.
    if (strpos($filename, 'govcms_uikit_starterkit') !== FALSE) {
      drush_op('rename', $filename, str_replace('govcms_uikit_starterkit', $machine_name, $filename));
    }
  }

  // ***************************************************
  // Notify user of the newly created theme.
  // ***************************************************.
  drush_print(dt('Starter kit for "!name" created in: !path', [
    '!name' => $name,
    '!path' => $sub_theme_path,
  ]));
}

/**
 * Replace strings in a file.
 */
function govcms_uikit_base_file_replace($file_path, $strings, $regexs = []) {
  $file_path = drush_normalize_path($file_path);
  $file_contents = file_get_contents($file_path);

  if ($file_contents !== FALSE) {
    // Find text with strings.
    $find = array_keys($strings);
    $replace = $strings;
    $file_contents = str_replace($find, $replace, $file_contents);

    // Find text with regex.
    foreach ($regexs as $regex) {
      $file_contents = preg_replace($regex['pattern'], $regex['replacement'], $file_contents);
    }

    // Write to file.
    file_put_contents($file_path, $file_contents);
  }
}
