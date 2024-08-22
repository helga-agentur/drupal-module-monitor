<?php

namespace Drupal\monitor\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\monitor\DrupalVersionManager;
use Drupal\monitor\Form\SettingsForm;
use Drupal\monitor\MonitorStorage;
use Drupal\monitor\PHPVersionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for monitor routes.
 */
class MonitorController extends ControllerBase {

  protected MonitorStorage $monitorStorage;

  protected DrupalVersionManager $drupalVersionManager;

  protected PHPVersionManager $phpVersionManager;

  public function __construct(MonitorStorage $monitorStorage, DrupalVersionManager $drupalVersionManager, PHPVersionManager $phpVersionManager, ) {
    $this->monitorStorage = $monitorStorage;
    $this->drupalVersionManager = $drupalVersionManager;
    $this->phpVersionManager = $phpVersionManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('monitor.storage'),
      $container->get('monitor.drupal.version.manager'),
      $container->get('monitor.php.version.manager'),
    );
  }

  /**
   * Renders the monitor
   */
  public function build() {
    //check where we should read the data from
    $projects = match(boolval($this->config(SettingsForm::CONFIG)->get(SettingsForm::CONFIG_DUMMYDATA))) {
      //read mock data from data.json
      true => json_decode(file_get_contents(\Drupal::service('extension.list.module')->getPath('monitor') . '/data/data.json'), true),
      // read from internal storage
      default => array_map(function ($project) {
        return $this->monitorStorage->getProjectData($project);
      }, $this->monitorStorage->getProjects())
    };

    $globals['drupal'] = [
      'version' => $this->drupalVersionManager->getCurrentVersion(),
      'changelogUrl' => $this->drupalVersionManager->getCurrentReleaseNotes(),
    ];
    $globals['php'] = [
      'version' => $this->phpVersionManager->getCurrentVersion(),
      'changelogUrl' => $this->phpVersionManager->getCurrentReleaseNotes()
    ];

    //compare global versions to environment versions
    //by basically just looping the projects
    array_walk($projects, function(&$project) use ($globals) {
      //then their environments, remove empty ones.
      $environments = array_filter($project['environments']);
      array_walk($environments, function(&$environment) use ($globals) {
        //and compare
        $environment['php']['risk'] = $this->phpVersionManager->calculateRisk($environment['php']['version']);
        $environment['drupal']['risk'] = $this->drupalVersionManager->calculateRisk($environment['drupal']['version']);
      });
    });

    //TODO add proper cache tags
    return [
      '#theme' => 'monitor',
      'projects' => $projects,
      'globals' => $globals,
      '#cache' => [
        'max-age' => 0
      ],
    ];
  }

}
