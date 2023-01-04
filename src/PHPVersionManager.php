<?php

namespace Drupal\monitor;

/**
 * Services related to PHP Version
 */
class PHPVersionManager {

  //TODO the version should be dynamic
  const PHP_MAJOR_VERSION = '8';

  private string $currentVersion;

  public function __construct() {
    try {
      $this->currentVersion = $this->fetchCurrentVersion();
    } catch (\Exception $e) {
      \Drupal::logger('monitor')->error($e->getMessage());
    }
  }

  /**
   * Get the current php version
   *
   * @return string
   */
  public function getCurrentVersion(): string {
    return $this->currentVersion;
  }

  /**
   * Get release notes of current php version
   *
   * @return string
   */
  public function getCurrentReleaseNotes(): string {
    return 'https://www.php.net/ChangeLog-' . PHPVersionManager::PHP_MAJOR_VERSION . '.php#' . $this->getCurrentVersion();
  }

  /**
   * Calculates the risk of your version by comparing it to the current version
   *
   * @param $version
   * @return string
   * - 'low' if low risk
   * - 'no' if no risk at all
   *
   * @todo
   * - add some more in depth comparison for php version
   */
  public function calculateRisk($version): string {
    return version_compare($this->currentVersion, $version, '>') ? 'low' : 'no';
  }

  /**
   * Fetches the current php version from
   * https://www.php.net/releases/ API
   *
   * It considers only the defined major versions.
   *
   * @throws \Exception
   */
  private function fetchCurrentVersion(): string {
    $url = 'https://www.php.net/releases/?json&version=' . self::PHP_MAJOR_VERSION . '&max=1';
    $data = json_decode(file_get_contents($url), true);
    if(!$data) throw new \Exception('PHP version could not get fetched from ' . $url);
    return array_key_first($data);
  }

}
