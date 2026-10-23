<?php

namespace Drupal\monitor;

use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;

/**
 * Class MonitorStorage.
 *
 * This class is used to save all monitor data.
 *
 * The data is kept in a non-expiring key/value store, so the last known
 * values of an instance stay available even if it doesn't report for a while
 * (e.g. stages which are only visited once a month).
 */
class MonitorStorage {

  const STORE_NAME = 'monitor';
  const PROJECT_NAME = 'projects';
  const ENVIRONMENT_NAME = 'environments';

  protected KeyValueStoreInterface $monitorDataStore;

  /**
   * @param KeyValueFactoryInterface $keyValueFactory
   */
  public function __construct(KeyValueFactoryInterface $keyValueFactory) {
    $this->monitorDataStore = $keyValueFactory->get(self::STORE_NAME);
  }

  /**
   * Set Data of an instance, which is defined by a project identifier and the environment
   *
   * @param string $project
   * @param string $environment
   * @param array $data
   * @return void
   */
  public function setInstanceData(string $project, string $environment, array $data): void {
    $this->addProject($project);
    $this->addEnvironment($project, $environment);
    //add environment to $data
    $data['identifier'] = $environment;
    $this->monitorDataStore->set($project . '_' . $environment, $data);
  }

  /**
   * Delete data of instance
   *
   * @param string $project
   * @param string $environment
   * @return void
   */
  public function deleteInstanceData(string $project, string $environment): void {
    $this->deleteEnvironment($project, $environment);
    $this->monitorDataStore->delete($project . '_' .$environment);
  }

  /**
   * Get the data of a specific environment of a project
   *
   * @param string $project
   * @param string $environment
   * @return mixed
   */
  private function getInstanceData(string $project, string $environment): mixed {
    return $this->monitorDataStore->get($project . '_' . $environment);
  }

  /**
   * Adds a project and makes sure it also prevents duplicates.
   *
   * @param string $project
   * @return void
   */
  private function addProject(string $project): void {
    $projects = $this->getProjects();
    $projects[] = $project;
    $this->monitorDataStore->set(self::PROJECT_NAME, array_unique($projects));
  }

  /**
   * Delete the whole project from storage
   *
   * @param string $project
   * @return void
   */
  public function deleteProject(string $project): void {
    foreach ($this->getEnvironments($project) as $environment) {
      $this->deleteInstanceData($project, $environment);
    }
    $projects = $this->getProjects();
    if (($key = array_search($project, $projects)) !== false) {
      unset($projects[$key]);
    }
    $this->monitorDataStore->set(self::PROJECT_NAME, array_unique($projects));
  }

  /**
   * Adds an environment to a project
   *
   * @param string $project
   * @param string $environment
   * @return void
   */
  private function addEnvironment(string $project, string $environment): void {
    $environments = $this->getEnvironments($project);
    $environments[] = $environment;
    $this->monitorDataStore->set($project . '_' . self::ENVIRONMENT_NAME, array_unique($environments));
  }

  /**
   * Delete environment of a project
   *
   * @param string $project
   * @param string $environment
   * @return void
   */
  private function deleteEnvironment(string $project, string $environment): void {
    $environments = $this->getEnvironments($project);
    if (($key = array_search($environment, $environments)) !== false) {
      unset($environments[$key]);
    }
    $this->monitorDataStore->set($project . '_' . self::ENVIRONMENT_NAME, array_unique($environments));
  }

  /**
   * Get all projects alphabetically
   *
   * @return array
   */
  public function getProjects(): array {
    $projects = $this->monitorDataStore->get(self::PROJECT_NAME);

    //if for any reason no projects saved, return empty array.
    if(!$projects) return [];

    //sort them by project name
    asort($projects);

    //all done
    return $projects;
  }

  /**
   * Get all environments of a specific project
   * Place "live" in first place, followed by "integration" and then the rest
   *
   * @param string $project
   *
   * @return array
   */
  public function getEnvironments(string $project): array {
    $environments = $this->monitorDataStore->get($project . '_' . self::ENVIRONMENT_NAME);

    //if for any reason no environments saved, return empty array.
    if(!$environments) return [];

    //else return sorted environment list.
    usort($environments, function($a, $b) {
      return match (true) {
        strtolower($a) == 'live' => -1,
        strtolower($b) == 'live' => 1,
        strtolower($a) == 'integration' => -1,
        strtolower($b) == 'integration' => 1,
        default => 0
      };
    });

    //return em
    return $environments;
  }

  /**
   * Get data (incl. environments) of a specific project
   *
   * @param string $project
   *
   * @return array
   */
  public function getProjectData(string $project): array {
    $data = [];
    $data['identifier'] = $project;
    $data['environments'] = array_map(function($environment) use ($project) {
      return $this->getInstanceData($project, $environment);
    }, $this->getEnvironments($project));
    return $data;
  }
}
