<?php
namespace Drupal\monitor;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Site\Settings;

/**
 * Class CoralogixApiConsumer
 *
 * Handles sending logs to the Coralogix service.
 */
class CoralogixApiConsumer implements ApiConsumerInterface {

  /**
   * The API key for authentication with Coralogix.
   *
   * @var string
   */
  private string $apiKey;

  /**
   * The API URL of the Coralogix.
   * @var string
   */
  private string $apiUrl;

  /**
   * Constructs a CoralogixApiConsumer object.
   */
  public function __construct() {
    $this->prepareApiCredentials();
  }

  function getApiUrl(): string {
    return $this->apiUrl;
  }

  /**
   * Prepares the API credentials required for Coralogix.
   *
   * @throws \Exception
   *   Throws exception if the API credentials are missing.
   */
  function prepareApiCredentials(): void {
    $coralogixSettings = Settings::get('coralogix');

    if (empty($coralogixSettings)) {
      throw new \Exception('Please add coralogix API credentials in settings.php');
    }

    $this->apiUrl = $coralogixSettings['api_url'];
    $this->apiKey = $coralogixSettings['api_key'];
  }

  /**
   * Constructs the request options for sending logs.
   *
   * @param array $transformedLogData
   *   The transformed log data to include in the request body.
   *
   * @return array
   *   The request options including headers and body.
   */
  public function getRequestOptions(array $transformedLogData): array {
    return [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ],
        'body' => json_encode($transformedLogData),
    ];
  }

  /**
   * Transforms the log data to the format required by Coralogix.
   *
   * @param array $logData
   *   The original log data.
   *
   * @return array
   *   The transformed log data.
   */
  public function transformLogData(array $logData): array {
    $level = $this->transformLoggingSeverityLevel($logData['data']['level']);

    return [
        'applicationName' => $logData['project'] ?? 'UnknownProject',
        'subsystemName' => $logData['environment'] ?? 'UnknownEnvironment',
        'timestamp' => $logData['data']['timestamp'] ?? time(),
        'severity' => $level,
        'category' => $logData['data']['channel'] ?? null,
        'text' => $logData['data']['message'] ?? 'No message provided',
    ];
  }

  /**
   * Transforms a Drupal log level to Coralogix log level because their order is reversed.
   *
   * @param int $drupalLogLevel
   *   The Drupal log level using RfcLogLevel constants.
   *
   * @return int
   *   The corresponding Coralogix log level.
   */
  private function transformLoggingSeverityLevel(int $drupalLogLevel): int {
    $mapping = [
        RfcLogLevel::DEBUG => 1,   // Debug
        RfcLogLevel::INFO => 3,    // Info
        RfcLogLevel::NOTICE => 3,  // Info (nearest match)
        RfcLogLevel::WARNING => 4, // Warn
        RfcLogLevel::ERROR => 5,   // Error
        RfcLogLevel::CRITICAL => 6,// Critical
        RfcLogLevel::ALERT => 6,   // Critical (nearest match)
        RfcLogLevel::EMERGENCY => 6  // Critical (nearest match)
    ];

    return $mapping[$drupalLogLevel] ?? 3; // Default to Info if undefined
  }
}
