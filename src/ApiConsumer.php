<?php
namespace Drupal\monitor;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Client;

class ApiConsumer {

  private Client $httpClient;

  public function __construct(Client $httpClient) {
    $this->httpClient = $httpClient;
  }

  public function sendLog(array $logData): void {
    $coralogixSettings = Settings::get('coralogix');

    if (empty($coralogixSettings)) {
      throw new \Exception('Please add coralogix API credentials in settings.php');
    }

    $apiUrl = $coralogixSettings['api_url'];
    $apiKey = $coralogixSettings['api_key'];

    $transformedData = $this->transformLogDataForCoralogix($logData);

    try {
        $response = $this->httpClient->post($apiUrl, [
          'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
          ],
        'body' => json_encode($transformedData),
        ]);

        if ($response->getStatusCode() !== 200) {
          \Drupal::logger('monitor')->error('Failed to send log to Coralogix: {message}', ['message' => $response->getBody()->getContents()]);
        }
    } catch (\Exception $e) {
      \Drupal::logger('monitor')->error('Error sending log to Coralogix: {message}', ['message' => $e->getMessage()]);
    }
  }

  /**
   * @param array $logData
   *
   * @return array
   */
  private function transformLogDataForCoralogix(array $logData): array {
    $level = $this->transformLoggingSeverityLevelForCoralogix($logData['data']['level']);

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
  private function transformLoggingSeverityLevelForCoralogix(int $drupalLogLevel): int {
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
