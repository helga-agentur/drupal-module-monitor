<?php

namespace Drupal\monitor;

use Drupal\monitor\Plugin\rest\resource\MonitorResource;
use GuzzleHttp\Client;

class LogManager {

  public function __construct(
    private readonly Client $httpClient,
    private readonly ApiConsumerInterface $apiConsumer
  ) {
  }

  /**
   * Processes a log by deciding whether to send it to a SaaS or via email.
   * Currently, it only sends logs to the SaaS.
   *
   * @param array $logData
   */
  public function processLog(array $logData): void {
    if ($this->filter($logData)) {
      $this->sendLogToSaas($logData);
    } else {
      \Drupal::logger('monitor')->info('Log filtered out and not sent: {logData}', ['logData' => json_encode($logData)]);
    }
  }

  /**
   * @TODO - Implement this method with email alert functionality later.
   *
   * Sends an alert via email.
   */
  private function alertByMail(array $logData): void {}

  /**
   * Sends the log to an external SaaS (Coralogix).
   *
   * @param array $logData
   */
  private function sendLogToSaas(array $logData): void {
    $transformedLogData = $this->apiConsumer->transformLogData($logData);

    try {
      $response = $this->httpClient->post(
        $this->apiConsumer->getApiUrl(),
        $this->apiConsumer->getRequestOptions($transformedLogData),
      );

      if ($response->getStatusCode() !== 200) {
        \Drupal::logger('monitor')->error('Failed to send log: {message}', ['message' => $response->getBody()->getContents()]);
      }
    } catch (\Exception $e) {
      \Drupal::logger('monitor')->error('Error sending log: {message}', ['message' => $e->getMessage()]);
    }
  }

  /**
   *
   * Filters logs to determine if they should be sent.
   *
   * @param array $logData
   *
   * @return bool - Returns true if the log should be sent, false otherwise.
   */
  private function filter(array $logData): bool {
    if (
      strtolower($logData['severity']) === 'error'
      && in_array(
        strtolower($logData[MonitorResource::ENVIRONMENT]),
        MonitorResource::ALLOWED_ENVIRONMENTS
      )
    ) {
      return TRUE;
    }

    return FALSE;
  }
}
