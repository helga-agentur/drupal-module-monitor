<?php

namespace Drupal\monitor;

class LogManager {

  private ApiConsumer $apiConsumer;

  public function __construct(ApiConsumer $apiConsumer) {
    $this->apiConsumer = $apiConsumer;
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
  public function alertByMail(array $logData): void {}

  /**
   * Sends the log to an external SaaS (Coralogix).
   *
   * @param array $logData
   */
  public function sendLogToSaas(array $logData): void {
    $this->apiConsumer->sendLog($logData);
  }

  /**
   * @TODO - Implement filtering logic for logs later.
   *
   * Filters logs to determine if they should be sent.
   *
   * @param array $logData
   *
   * @return bool - Returns true if the log should be sent, false otherwise.
   */
  private function filter(array $logData): bool {
    return true;
  }
}
