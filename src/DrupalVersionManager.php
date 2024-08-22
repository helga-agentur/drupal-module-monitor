<?php

namespace Drupal\monitor;

use Drupal\monitor\Form\SettingsForm;

/**
 * Services related to Drupal Versions
 */
class DrupalVersionManager {

  private string $currentVersion;

  private int $currentMajorVersion;

  private string $currentSecurityPatch;

  public function __construct() {
    try {
      $this->currentMajorVersion = \Drupal::config(SettingsForm::CONFIG)->get(SettingsForm::CONFIG_DRUPALVERSION);
      $this->currentVersion = $this->fetchCurrentVersion();
      $this->currentSecurityPatch = $this->fetchCurrentSecurityPatch();
    } catch (\Exception $e) {
      \Drupal::logger('monitor')->error($e->getMessage());
    }
  }

  /**
   * Get current Drupal version
   *
   * @return string
   */
  public function getCurrentVersion(): string {
    return $this->currentVersion;
  }

  /**
   * Get current major Drupal Version i.e. 10 or 11.
   *
   * @return int
   */
  private function getCurrentMajorVersion(): int {
    return $this->currentMajorVersion;
  }

  /**
   * Get the latest security patch
   *
   * @todo actually get the current security patch - we still need to discuss how this should be done
   *
   * @return string
   */
  private function fetchCurrentSecurityPatch(): string {
    return $this->currentVersion;
  }

  /**
   * Get release notes of current version
   *
   * @return string
   */
  public function getCurrentReleaseNotes(): string {
    return 'https://www.drupal.org/project/drupal/releases/' . $this->getCurrentVersion();
  }

  /**
   * Calculates the risk of your version by comparing it to the current version
   * Also security patches are considered being necessary.
   *
   * @param $version
   * @return string
   * - 'high' if security patch available
   * - 'low' if version is outdated, but patches not necessary
   * - 'no' if no risk at all
   */
  public function calculateRisk($version): string {
    return match (TRUE) {
      version_compare($this->currentSecurityPatch, $version, '>') => 'high',
      version_compare($this->currentVersion, $version, '>') => 'low',
      default => 'no'
    };
  }

  /**
   * Fetch the current drupal version from https://repo.packagist.org/p2/drupal/core.json.
   * It considers only the defined major versions.
   *
   * @return string
   * @throws \Exception
   */
  private function fetchCurrentVersion(): string {
    $url = 'https://repo.packagist.org/p2/drupal/core.json';
    $data = json_decode(file_get_contents($url), true);
    if(!$data) throw new \Exception('Drupal version could not get fetched from ' . $url);

    foreach ($data['packages']['drupal/core'] as $package) {
      $version = $package['version'];
      if(str_starts_with($version, $this->getCurrentMajorVersion()) && !str_contains($version, 'rc')) {
        return $version;
      }
    }

    return 'unknown';
  }
}
