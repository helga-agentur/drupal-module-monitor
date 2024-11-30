<?php

namespace Drupal\monitor;

/**
 * Interface for an API consumer that sends log data.
 */
interface ApiConsumerInterface {

  /**
   * @return string
   */
  function getApiUrl(): string;

  /**
   * Prepares the API credentials required for authentication.
   *
   * @return array
   *   An associative array containing API credential details such as
   *   API keys or tokens.
   */
  function prepareApiCredentials(): void;

  /**
   * Constructs the request options for an HTTP client.
   *
   * @return array
   *   An associative array of request options. This typically includes
   *   headers, authentication, timeouts, or any other configuration
   *   necessary for making HTTP requests.
   */
   function getRequestOptions(array $transformedLogData): array;

  /**
   * Transforms log data into the format required by the API.
   *
   * @param array $logData
   *   The original log data to be transformed.
   *
   * @return array
   *   The transformed log data arranged to match the API's expected
   *   format. This includes adjusting structure, adding required fields,
   *   or changing data representations.
   */
  function transformLogData(array $logData): array;
}
