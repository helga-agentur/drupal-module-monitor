<?php

namespace Drupal\monitor;

use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\SharedTempStoreFactory;

/**
 * Class MonitorStorage.
 *
 * This class is used to save all monitor data.
 */
class MonitorStorage {

  const TEMP_STORE_NAME = 'monitor';
  const PROJECT_NAME = 'projects';
  const ENVIRONMENT_NAME = 'environments';

  protected $monitorDataStore;

  /**
   * @param PrivateTempStoreFactory $sharedTempStore
   */
  public function __construct(SharedTempStoreFactory $sharedTempStore) {
    $this->monitorDataStore = $sharedTempStore->get(self::TEMP_STORE_NAME);
  }

  /**
   * Set Data of an instance, which is defined by a project identifier and the environment
   *
   * @param string $project
   * @param string $environment
   * @param array $data
   * @return void
   * @throws \Drupal\Core\TempStore\TempStoreException
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
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function deleteInstanceData(string $project, string $environment) {
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
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  private function addProject(string $project): void {
    $projects = $this->getProjects();
    $projects[] = $project;
    $this->monitorDataStore->set(self::PROJECT_NAME, array_unique($projects));
  }

  /**
   * Adds an environment to a project
   *
   * @param string $project
   * @param string $environment
   * @return void
   * @throws \Drupal\Core\TempStore\TempStoreException
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
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  private function deleteEnvironment(string $project, string $environment): void {
    $environments = $this->getEnvironments($project);
    if (($key = array_search($environment, $environments)) !== false) {
      unset($environments[$key]);
    }
    $this->monitorDataStore->set($project . '_' . self::ENVIRONMENT_NAME, array_unique($environments));
  }

  /**
   * Get all projects
   *
   * @return array
   */
  public function getProjects(): array {
    $projects = $this->monitorDataStore->get(self::PROJECT_NAME);
    return $projects ?? [];
  }

  /**
   * Get all environments of a specific project
   *
   * @param string $project
   *
   * @return array
   */
  public function getEnvironments(string $project): array {
    $environments = $this->monitorDataStore->get($project . '_' . self::ENVIRONMENT_NAME);
    return $environments ?? [];
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
