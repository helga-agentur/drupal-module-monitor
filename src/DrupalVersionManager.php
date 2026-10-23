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
   * Seconds the fetched release data is cached.
   */
  const CACHE_LIFETIME = 3600;

  /**
   * End of life dates keyed by release cycle (e.g. "11.4" or "7").
   */
  private array $endOfLifeDates = [];

  /**
   * Versions marked as insecure by drupal.org, because a security release superseded them.
   */
  private array $insecureVersions = [];

  public function __construct() {
    try {
      $this->currentMajorVersion = \Drupal::config(SettingsForm::CONFIG)->get(SettingsForm::CONFIG_DRUPALVERSION);
      $this->currentVersion = $this->fetchCurrentVersion();
    } catch (\Exception $e) {
      \Drupal::logger('monitor')->error($e->getMessage());
    }

    try {
      $this->endOfLifeDates = $this->getCachedData('monitor:drupal:end_of_life_dates', fn() => $this->fetchEndOfLifeDates());
      $this->insecureVersions = $this->getCachedData('monitor:drupal:insecure_versions', fn() => $this->fetchInsecureVersions());
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
   * Calculates the risk of your version by the end of life of its release cycle
   * and by missing security updates.
   *
   * @param $version
   * @return string
   * - 'high' if the release cycle doesn't get security updates anymore
   * - 'moderate' if a security update is available for this version
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
      $endOfLife = $endOfLife ? 'today' : '+100 years';
    }

    $endOfLifeDate = new \DateTimeImmutable($endOfLife);
    $today = new \DateTimeImmutable('today');
    return match (TRUE) {
      $endOfLifeDate <= $today => 'high',
      in_array($version, $this->insecureVersions, TRUE) => 'moderate',
      $endOfLifeDate <= $today->modify(self::EOL_WARNING_PERIOD) => 'low',
      default => 'no'
    };
  }

  /**
   * Get the meaning of the risks returned by calculateRisk().
   *
   * @return array
   *   Labels keyed by risk, ordered from the highest to the lowest risk.
   */
  public function getRiskLegend(): array {
    return [
      'high' => 'No security updates anymore',
      'moderate' => 'Security update available',
      'low' => 'End of life within ' . trim(self::EOL_WARNING_PERIOD, '+'),
      'no' => 'Supported',
    ];
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

  /**
   * Fetch all versions marked as insecure from https://updates.drupal.org/release-history/drupal/current.
   *
   * @return array
   * @throws \Exception
   */
  private function fetchInsecureVersions(): array {
    $url = 'https://updates.drupal.org/release-history/drupal/current';
    $data = simplexml_load_string(file_get_contents($url));
    if(!$data) throw new \Exception('Drupal release history could not get fetched from ' . $url);

    $insecureVersions = [];
    foreach ($data->releases->release as $release) {
      foreach ($release->terms->term ?? [] as $term) {
        if ((string) $term->name === 'Release type' && (string) $term->value === 'Insecure') {
          $insecureVersions[] = (string) $release->version;
        }
      }
    }
    return $insecureVersions;
  }

  /**
   * Get data from the cache or fetch and cache it, if it isn't cached yet.
   *
   * @param string $cid
   * @param callable $fetch
   * @return array
   * @throws \Exception
   */
  private function getCachedData(string $cid, callable $fetch): array {
    if ($cache = \Drupal::cache()->get($cid)) {
      return $cache->data;
    }
    $data = $fetch();
    \Drupal::cache()->set($cid, $data, \Drupal::time()->getRequestTime() + self::CACHE_LIFETIME);
    return $data;
  }
}
