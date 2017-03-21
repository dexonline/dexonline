<?php
require_once('../phplib/util.php');
util_assertModerator(PRIV_ADMIN);

define('OTRS_DONATION_EMAIL_REGEX',
       '/^Mesaj raspuns: Approved.*' .
       '^3. PRET: (?<amount>[0-9.]+) RON.*' .
       '^   EMAIL: (?<email>[^\n]+)$/ms');

$odp = new OtrsDonationProvider();
$donors = $odp->getDonors();
var_dump($donors);

//SmartyWrap::display('proceseaza-donatii.tpl');

abstract class DonationProvider {
  // Must return a list of tuples [ 'email' => <email address>, 'amount' => <donation amount ]
  abstract function getDonors();
}

class OtrsDonationProvider extends DonationProvider {
  private $ticketIds = null;

  function restQuery($page, $params) {
    $getArgs = [];
    foreach ($params as $key => $value) {
      $getArgs[] = "{$key}=" . urlencode($value);
    }

    $url = sprintf('%s/%s?%s',
                   Config::get('otrs.restUrl'),
                   $page,
                   implode('&', $getArgs));

    list($response, $httpCode) = util_fetchUrl($url);

    if ($httpCode != 200) {
      throw new Exception('Eroare la comunicarea cu OTRS');
    }

    return json_decode($response);
  }

  function getDonors() {
    // get tickets ID from the donation queue and save them for postprocessing
    $response = $this->restQuery('TicketSearch', [
      'UserLogin' => Config::get('otrs.login'),
      'Password' => Config::get('otrs.password'),
      'Queues' => 'ONG',
      'States' => 'new',
    ]);
    $this->ticketIds = $response->TicketID;

    $results = [];

    // get the body for each ticket and, if it matches a donation email, extract the email addresss
    // and amount
    foreach ($this->ticketIds as $tid) {
      $ticket = $this->getTicket($tid);
      if (!$ticket ||
          !property_exists($ticket, 'Ticket') ||
          empty($ticket->Ticket) ||
          !property_exists($ticket->Ticket[0], 'Article') ||
          empty($ticket->Ticket[0]->Article)) {
        throw new Exception('Răspuns incorect de la OTRS');
      }
      $article = $ticket->Ticket[0]->Article[0];
      $from = $article->From;
      $body = $article->Body;

      if (preg_match('/euplatesc/', $from) &&
          preg_match(OTRS_DONATION_EMAIL_REGEX, $body, $match)) {

        $results[] = [
          'email' => $match['email'],
          'amount' => $match['amount'],
        ];
      }
    }

    return $results;
  }

  function getTicket($ticketId) {
    return $this->restQuery('TicketGet', [
      'UserLogin' => Config::get('otrs.login'),
      'Password' => Config::get('otrs.password'),
      'TicketID' => $ticketId,
      'AllArticles' => '1',
    ]);
  }
}
