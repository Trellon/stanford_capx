<?php
/**
 * @file
 * CAP HTTPClient extending Guzzle :)
 * This client is used for communicating with the various endpoints of the
 * CAP API. The base of this class is the Guzzle HTTP client but contains a few
 * helpers and a lightweight lazy loading API library.
 *
 * Some API functions require an authentication token. This can be obtained
 * through the AuthLib and the authenticate() method.
 *
 * EXAMPLES:
 *
 * $client = new HTTPClient();
 *
 * $auth = $client->api('auth');
 * $auth->authenticate('username', 'password');
 * $token = $auth->getAuthToken();
 *
 * $client->setAPIToken($token);
 *
 * $schema = $client->api('schema')->profile();
 */

namespace CAPx\APILib;
use \Guzzle\Http\Client as GuzzleClient;

class HTTPClient {

  // Storage for the Guzzle http client object.
  protected $httpClient = null;
  // Default CAP Endpoint url.
  protected $httpEndpoint = 'https://cap.stanford.edu/cap-api';
  // Auth Token is a very long string that is obtained from the CAP API after
  // successfully authenticating a username and password. See AuthLib.
  protected $httpAuthToken;
  // HTTP Options is an array of extra options to pass into the HTTP Client.
  protected $httpOptions;

  /**
   * Build with a Guzzle Client. Live... live!
   */
  public function __construct() {
    $client = new GuzzleClient();
    $this->setHttpClient($client);
  }

  /**
   * Getter for $httpEndpoint.
   * @return string A fully qualified url without the last slash.
   */
  public function getEndpoint() {
    return $this->httpEndpoint;
  }

  /**
   * Setter for $httpEndpoint. When this changes we also need to create a new
   * Guzzle client.
   * @param string $end A fully qualified URL without the last slash
   */
  public function setEndpoint($end) {
    // When the endpoint changes create a new client.
    $client = new GuzzleClient($end);
    $this->setHttpClient($client);

    $this->httpEndpoint = $end;
  }

  /**
   * Getter for $httpClient
   * @return GuzzleClient a Guzzle HTTP client.
   */
  public function getHttpClient() {

    // If we have a set client just return it.
    if (!is_null($this->httpClient)) {
      return $this->httpClient;
    }

    // If we do not have a client we need to create one.
    $client = new GuzzleClient($this->getHttpEndpoint());
    $this->setHttpClient($client);

    return $client;
  }

  /**
   * Setter for $httpClient
   * @param GuzzleClient $client a Guzzle client object.
   */
  public function setHttpClient($client) {
    $this->httpClient = $client;
  }

  /**
   * Setter for $httpAuthToken
   * @param string $token a very long string to use with authenticated requests.
   */
  public function setApiToken($token) {
    $this->httpAuthToken = $token;
  }

  /**
   * Getter for $httpAuthToken
   * @return string the authenticated token or null.
   */
  protected function getApiToken() {
    if (empty($this->httpAuthToken)) {
      return null;
    }
    return $this->httpAuthToken;
  }

  /**
   * Getter for $httpOptions
   * @return array An associative array of options to pass to the HTTP client.
   */
  public function gethttpOptions() {
    return $this->httpOptions;
  }

  /**
   * Setter for $httpOptions
   * @param array An associative array of options to pass to the HTTP client.
   */
  public function sethttpOptions($opts) {
    $this->httpOptions = $opts;
  }

  //
  // ---------------------------------------------------------------------------
  //

  /**
   * This API function acts as a gateway for the various parts of this Library.
   * By default it handles the passing of the http client and httpAuth token
   * into the HTTP client.
   * @param  string $name the name of the library part to use. eg: auth, org,
   *                      profile, schema, layout, or search.
   * @return object       An API Lib object for a specific part of the CAP API.
   */
  public function api($name) {

    $client = $this->getHttpClient();
    $options = $this->gethttpOptions();

    // Add access token or we wont be able to communicate.
    $options['query']['access_token'] = $this->getApiToken();

    switch ($name) {
      case "auth":
        $api = new \CAPx\APILib\AuthLib\AuthLib($client);
        break;
      case "org":
      case "orgs":
        $api = new \CAPx\APILib\OrgLib\OrgLib($client, $options);
        break;
      case "profile":
      case "profiles":
        $api = new \CAPx\APILib\ProfileLib\ProfileLib($client, $options);
        break;
      case "schema":
        $api = new \CAPx\APILib\SchemaLib\SchemaLib($client, $options);
        break;
      case "search":
        $api = new \CAPx\APILib\SearchLib\SearchLib($client, $options);
        break;
      case "layout":
      case "layouts":
        $api = new \CAPx\APILib\LayoutsLib\LayoutsLib($client, $options);
        break;
      default:
        throw new \Exception(sprintf('Undefined api instance called: "%s"', $name));
    }

  return $api;
  }

}
