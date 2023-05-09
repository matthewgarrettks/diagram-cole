<?php
namespace App\Service;

use App\Entity\FetchResponse;

class Kubota
{

  private ?string $authValue = null;

  private int $roleId = 23;

  private string $lang = 'en';

  private string $origin = 'https://kpad.kubota.com';

  private int $consecutiveErrorCount;

  private \DateTimeImmutable $lastAuth;

  private string $contentType = 'application/json';

  private string $consumerCode = '100faiXie8u';

  private array $errors;

  /**
   * @var \App\Service\Log
   */
  private Log $log;

  public function __construct(Log $log)
  {
    $this->log = $log;
  }

  /**
   * Get JSON from Kubota's API
   * @param  string  $url
   * @param  bool  $allowReAuthentication  No need to set. Prevents recursion.
   * @return Exception|ScrapeResponse
   */
  public function fetch(string $url, bool $allowReAuthentication = true): FetchResponse|\Exception
  {
    try {
      echo "Start";
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-type: application/json"]);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
      curl_setopt($curl, CURLOPT_TIMEOUT, 15);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $this->authValue,
        'kubotapad-role-id: ' . $this->roleId,
        'KubotaPAD-Language-Code: ' . $this->lang,
      ]);
      $json_response = curl_exec($curl);
      $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);
      if (200 !== $status) {
        $this->consecutiveErrorCount++;
      }
      else {
        $this->consecutiveErrorCount = 0;
      }
      if ($this->consecutiveErrorCount > 9) {
        $this->log->log('Too many bad responses. Stopping.');
        $this->log->end();
      }
      if ((true === $allowReAuthentication) && (200 !== $status) && (true === $this->authenticate())) {
        return $this->scrape($url, false);
      }
      return new FetchResponse($status, $json_response);

    } catch (\Exception $e) {
      echo "FAIL";
      return $e;
    }
  }

  /**
   * Authenticates against the Kubota server
   * @return true|void
   */
  private function authenticate()
  {
    if (time() - $this->lastAuth->getTimestamp() < 300) {
      return true;
    }
    $result = $this->authRaw();
    if (true === $result) {
      return true;
    }
    $this->log->log('!!! Auth error, trying again !!!');
    sleep(5);
    $result = $this->authRaw();
    if (true === $result) {
      return true;
    }
    $this->log('!!! Second attempt auth error: ' . $result->getMessage() . ' !!!');
    $this->log->end();

  }

  /**
   * Uses curl to perform the authentication
   * @return bool|\Exception
   */
  private function authRaw(): bool|\Exception
  {
    try {
      $curl = curl_init('https://kpad.kubota.com/api/token/consumer');
      curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
      curl_setopt($curl, CURLOPT_TIMEOUT, 15);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-type: ' . $this->contentType,
        'Origin: ' . $this->origin,
        'KubotaPAD-Language-Code: ' . $this->lang,
      ]);
      curl_setopt($curl, CURLOPT_POSTFIELDS, '{"consumer_code":"' . $this->consumerCode . '"}');
      $json_response = curl_exec($curl);
      $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);
      if (200 !== $status) {
        throw new \Exception('Response code other than 200: ' . var_export($status, true));
      }
      if (empty($json_response)) {
        throw new \Exception('Empty response body');
      }
      $data = json_decode($json_response);
      if (!property_exists($data, 'access_token')) {
        throw new \Exception('No access token');
      }
      $this->authValue = 'Bearer ' . $data->access_token;
      $this->lastAuth = time();
      $this->log->log('*** Authorized ***');
      return true;
    } catch (\Exception $e) {
      return $e;
    }

  }

}
