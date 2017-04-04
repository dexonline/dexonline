<?php
require_once('../phplib/util.php');
util_assertModerator(PRIV_ADMIN);

define('OTRS_DONATION_EMAIL_REGEX',
       '/^Mesaj raspuns: Approved.*' .
       '^3. PRET: (?<amount>[0-9.]+) RON.*' .
       '^   EMAIL: (?<email>[^\n]+)$/ms');

$previewButton = Request::has('previewButton');
$saveButton = Request::has('saveButton');

if ($previewButton) {
  $odp = new OtrsDonationProvider();
  // $otrsDonors = $odp->getDonors();
  $otrsDonors = [];

  $emails = Request::get('email');
  $amounts = Request::get('amount');
  $dates = Request::get('date');
  $mdp = new ManualDonationProvider($emails, $amounts, $dates);

  $mdp->processDonors();

  SmartyWrap::assign('manualDonors', $mdp->getDonors());
}

SmartyWrap::display('proceseaza-donatii.tpl');

class Donor {
  public $email;
  public $amount;
  public $date;
  public $description;

  function __construct($email, $amount, $date, $description) {
    $this->email = $email;
    $this->amount = $amount;
    $this->date = $date;
    $this->description = $description;
  }

  function validate() {
    if ($this->email && $this->amount && $this->date) {
      return true;
    } else {
      FlashMessage::add("Donatorul {$this} nu poate fi procesat pentru că are câmpuri vide.",
                        'warning');
      return false;
    }
  }

  function process() {
    if (!$this->validate()) {
      return;
    }
    $u = User::get_by_email($this->email);
    if ($u) {
      var_dump("{$this}: {$u->nick}");
    }
  }

  function __toString() {
    return (string)$this->description;
  }
}

abstract class DonationProvider {
  protected $donors;

  // must return an array of Donor objects
  abstract function getDonors();

  function processDonors() {
    foreach ($this->donors as $d) {
      $d->process();
    }
  }
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
      'From' => 'office@euplatesc.ro', // TODO: test if it needs '%' regex
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

      if (preg_match(OTRS_DONATION_EMAIL_REGEX, $body, $match)) {

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

class ManualDonationProvider extends DonationProvider {

  function __construct($emails, $amounts, $dates) {
    $this->donors = [];
    foreach ($emails as $i => $e) {
      if ($e || $amounts[$i] || $dates[$i]) {
        $this->donors[] = new Donor($e, $amounts[$i], $dates[$i], $i + 1);
      }
    }
  }

  function getDonors() {
    return $this->donors;
  }
}
