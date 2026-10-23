<?php

namespace Drupal\monitor;

use Drupal\monitor\Form\SettingsForm;

/**
 * Services related to Drupal Versions
 */
class DrupalVersionManager {

  private string $currentVersion;

  private int $currentMajorVersion;

  /**
   * Months before the end of life, in which a release cycle is marked as risky.
   */
  const EOL_WARNING_PERIOD = '+6 months';

  /**
   * End of life dates keyed by release cycle (e.g. "11.4" or "7").
   */
  private array $endOfLifeDates = [];

  public function __construct() {
    try {
      $this->currentMajorVersion = \Drupal::config(SettingsForm::CONFIG)->get(SettingsForm::CONFIG_DRUPALVERSION);
      $this->currentVersion = $this->fetchCurrentVersion();
      $this->endOfLifeDates = $this->fetchEndOfLifeDates();
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
   * Get release notes of current version
   *
   * @return string
   */
  public function getCurrentReleaseNotes(): string {
    return 'https://www.drupal.org/project/drupal/releases/' . $this->getCurrentVersion();
  }

  /**
   * Calculates the risk of your version by the end of life of its release cycle.
   *
   * @param $version
   * @return string
   * - 'high' if the release cycle doesn't get security updates anymore
   * - 'low' if the release cycle reaches its end of life within the warning period
   * - 'no' if the release cycle is supported beyond the warning period
   * - '' if the release cycle is unknown (e.g. unreleased minor versions)
   */
  public function calculateRisk($version): string {
    $versionParts = explode('.', $version);
    // Drupal 8+ cycles are minor versions, older ones only major versions.
    $endOfLife = $this->endOfLifeDates[$versionParts[0] . '.' . ($versionParts[1] ?? '')]
      ?? $this->endOfLifeDates[$versionParts[0]]
      ?? NULL;

    if ($endOfLife === NULL) {
      return '';
    }

    // endoflife.date uses booleans if no date is known.
    if (is_bool($endOfLife)) {
      return $endOfLife ? 'high' : 'no';
    }

    $endOfLifeDate = new \DateTimeImmutable($endOfLife);
    $today = new \DateTimeImmutable('today');
    return match (TRUE) {
      $endOfLifeDate <= $today => 'high',
      $endOfLifeDate <= $today->modify(self::EOL_WARNING_PERIOD) => 'low',
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
      // skip unstable releases like alpha, beta, rc or dev versions
      if(str_starts_with($version, $this->getCurrentMajorVersion() . '.') && preg_match('/^\d+\.\d+\.\d+$/', $version)) {
        return $version;
      }
    }

    return 'unknown';
  }

  /**
   * Fetch the end of life dates of all drupal release cycles from https://endoflife.date/api/drupal.json.
   *
   * @return array
   * @throws \Exception
   */
  private function fetchEndOfLifeDates(): array {
    $url = 'https://endoflife.date/api/drupal.json';
    $data = json_decode(file_get_contents($url), true);
    if(!$data) throw new \Exception('Drupal end of life dates could not get fetched from ' . $url);

    return array_column($data, 'eol', 'cycle');
  }
}
