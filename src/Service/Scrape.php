<?php

namespace App\Service;

use App\Entity\FetchResponse;
use App\Entity\ScrapeResponse;

class Scrape
{

  /**
   * @var \App\Service\Kubota
   */
  private Kubota $api;

  public function __construct(Kubota $api)
  {
    $this->api = $api;
  }

  public function scrapeAllBooks()
  {

  }

  public function scrapeBooksFor($text){
    $book_ids = $this->api->getBookIdsFor($text);
    print_r($book_ids);
  }

  /**
   * Get JSON from Kubota's API
   * @param  string  $url
   * @param  bool  $allowReAuthentication  No need to set. Prevents recursion.
   * @return \Exception|\App\Entity\FetchResponse
   */
  protected function scrape(string $url, bool $allowReAuthentication = true): FetchResponse|\Exception
  {
    try {
      return $this->api->fetch($url, $allowReAuthentication);
    } catch (\Exception $e) {
      return $e;
    }
  }



}
