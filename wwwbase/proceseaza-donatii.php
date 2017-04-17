<?php
require_once '../phplib/util.php';
require_once '../phplib/third-party/PHPMailer/PHPMailerAutoload.php';

User::require(User::PRIV_ADMIN);

define('OTRS_DONATION_EMAIL_REGEX',
       '/^Mesaj raspuns: Approved.*' .
       '^3. PRET: (?<amount>[0-9.]+) RON.*' .
       '^   EMAIL: (?<email>[^\n]+)$/ms');

$previewButton = Request::has('previewButton');
$processButton = Request::has('processButton');
$backButton = Request::has('backButton');
$includeOtrs = Request::has('includeOtrs');

if ($processButton) {
  if ($includeOtrs) {
    $odp = new OtrsDonationProvider();
    $odp->prepareDonors();

    $processTicketIds = Request::get('processTicketId', []);
    $messageTicketIds = Request::get('messageTicketId', []);
    foreach ($odp->getDonors() as $d) {
      if (in_array($d->ticketId, $processTicketIds)) {
        $d->sendEmail = in_array($d->ticketId, $messageTicketIds);
        $d->process();
      }
    }
  }

  $mdp = readManualDonorData();
  $mdp->prepareDonors();
  $mdp->processDonors();
  FlashMessage::add('Am procesat donațiile. Dacă au existat utilizatori care au primit ' .
                    'medalii și/sau scutiri de bannere, nu uitați să goliți parțial cache-ul ' .
                    'lui Varnish: sudo varnishadm ban.url ^/utilizator',
                    'success');
  util_redirect('proceseaza-donatii.php');

} else if ($previewButton) {
  if ($includeOtrs) {
    $odp = new OtrsDonationProvider();
    $odp->prepareDonors();
    SmartyWrap::assign('otrsDonors', $odp->getDonors());
  }

  $mdp = readManualDonorData();
  $mdp->prepareDonors();
  SmartyWrap::assign('manualDonors', $mdp->getDonors());
  SmartyWrap::assign('includeOtrs', $includeOtrs);

  if (FlashMessage::hasErrors()) {
    SmartyWrap::display('proceseaza-donatii.tpl');
  } else {
    SmartyWrap::display('proceseaza-donatii2.tpl');
  }

} else if ($backButton) {

  $mdp = readManualDonorData();
  SmartyWrap::assign('manualDonors', $mdp->getDonors());
  SmartyWrap::assign('includeOtrs', $includeOtrs);
  SmartyWrap::display('proceseaza-donatii.tpl');

} else {

  SmartyWrap::display('proceseaza-donatii.tpl');

}

/*************************************************************************/

function readManualDonorData() {
  $emails = Request::get('email', []);
  $amounts = Request::get('amount', []);
  $dates = Request::get('date', []);

  $sendEmail = [];
  foreach ($emails as $i => $e) {
    if (Request::has("manualSendMessage_{$i}")) {
      $sendEmail[] = $i;
    }
  }

  return new ManualDonationProvider($emails, $amounts, $dates, $sendEmail);
}

class Donor {
  const AMOUNT_MEDAL = 20;
  const AMOUNT_NO_BANNERS = 50;
  const AMOUNT_STICKER = 100;
  const AMOUNT_TEE = 200;

  public $email;
  public $amount;
  public $date;
  public $source;
  public $sendEmail;
  public $description;
  public $user;
  public $textMessage;
  public $htmlMessage;
  public $valid;

  function __construct($email, $amount, $date, $source, $sendEmail, $description) {
    $this->email = $email;
    $this->amount = $amount;
    $this->date = $date;
    $this->source = $source;
    $this->sendEmail = $sendEmail;
    $this->description = $description;
  }

  function needsEmail() {
    return $this->amount >= self::AMOUNT_MEDAL;
  }

  function validate() {
    $this->valid = $this->email && $this->amount && $this->date;

    if (!$this->valid) {
      FlashMessage::add("Donatorul {$this} nu poate fi procesat pentru că are câmpuri vide.");
    }

    return $this->valid;
  }

  function prepare() {
    $this->user = User::get_by_email($this->email);

    SmartyWrap::assign('donor', $this);

    if ($this->amount >= self::AMOUNT_MEDAL) {
      $this->textMessage = SmartyWrap::fetch('email/donationThankYouTxt.tpl');
      $this->htmlMessage = SmartyWrap::fetch('email/donationThankYouHtml.tpl');
    }
  }

  function process() {
    if ($this->sendEmail) {
      $mail = new PHPMailer();

      $mail->setFrom(Config::get('global.contact'), 'dexonline');
      $mail->addAddress($this->email);
      $mail->isHTML(true);
      $mail->CharSet = 'utf-8';

      $mail->Subject = 'Mulțumiri';
      $mail->Body = $this->htmlMessage;
      $mail->AltBody = $this->textMessage;

      if (!$mail->send()) {
        FlashMessage::add(sprintf('Emailul către %s a eșuat: %s',
                                  $this->email, $mail->ErrorInfo));
      }
    }

    if ($this->user) {
      if ($this->amount >= self::AMOUNT_MEDAL) {
        $this->user->medalMask |= Medal::MEDAL_SPONSOR;
        $this->user->save();
      }
      if ($this->amount >= self::AMOUNT_NO_BANNERS) {
        $this->user->noAdsUntil = strtotime('+1 year');
        $this->user->save();
      }
    }

    $donation = Model::factory('Donation')->create();
    $donation->email = $this->email;
    $donation->amount = $this->amount;
    $donation->date = $this->date;
    $donation->userId = session_getUserId();
    $donation->source = $this->source;
    $donation->emailSent = $this->sendEmail;
    $donation->save();
  }

  function __toString() {
    return (string)$this->description;
  }
}

class OtrsDonor extends Donor {
  public $ticketId;

  function __construct($email, $amount, $date, $source, $sendEmail, $ticketId) {
    parent::__construct($email, $amount, $date, $source, $sendEmail, $ticketId);
    $this->ticketId = $ticketId;
  }

  function process() {
    parent::process();
    OtrsApiClient::closeTicket($this->ticketId);
  }

  function __toString() {
    return "tichet ID={$this->ticketId}";
  }
}

abstract class DonationProvider {
  protected $donors;

  function getDonors() {
    return $this->donors;
  }

  function prepareDonors() {
    foreach ($this->donors as $d) {
      $d->prepare();
    }
  }

  function processDonors() {
    foreach ($this->donors as $d) {
      $d->process();
    }
  }
}

class OtrsDonationProvider extends DonationProvider {

  function __construct() {

    // get ticket IDs from the donation queue and save them for postprocessing
    $response = OtrsApiClient::searchTickets([
      'Queues' => 'ONG',
      'States' => 'new',
      'From' => 'office@euplatesc.ro',
    ]);

    $ticketIds = isset($response->TicketID) ? $response->TicketID : [];
    $this->donors = [];

    // get the body for each ticket and, if it matches a donation email, build a new donor
    foreach ($ticketIds as $tid) {
      $ticket = OtrsApiClient::getTicket($tid);
      if (!$ticket ||
          !property_exists($ticket, 'Ticket') ||
          empty($ticket->Ticket) ||
          !property_exists($ticket->Ticket[0], 'Article') ||
          empty($ticket->Ticket[0]->Article)) {
        throw new Exception('Răspuns incorect de la OTRS');
      }
      $article = $ticket->Ticket[0]->Article[0];
      $created = $ticket->Ticket[0]->Created;
      $from = $article->From;
      $body = $article->Body;

      if (preg_match(OTRS_DONATION_EMAIL_REGEX, $body, $match)) {

        $d = new OtrsDonor($match['email'],
                           $match['amount'],
                           explode(' ', $created)[0], // just the date part
                           Donation::SOURCE_OTRS,
                           true,
                           $tid);
        $d->validate();
        $this->donors[] = $d;
      }
    }
  }

}

class ManualDonationProvider extends DonationProvider {

  function __construct($emails, $amounts, $dates, $sendEmail) {
    $this->donors = [];
    foreach ($emails as $i => $e) {
      if ($e || $amounts[$i] || $dates[$i]) {
        $s = in_array($i, $sendEmail);
        $d = new Donor($e, $amounts[$i], $dates[$i], Donation::SOURCE_MANUAL, $s, $i + 1);
        $d->validate();
        $this->donors[] = $d;
      }
    }
  }

  function getDonors() {
    return $this->donors;
  }
}

class OtrsApiClient {
  const METHOD_GET = 'GET';
  const METHOD_POST = 'POST';
  const METHOD_PATCH = 'PATCH';

  static function restQuery($page, $params, $method = self::METHOD_GET) {
    // add the login credentials
    $params['UserLogin'] = Config::get('otrs.login');
    $params['Password'] = Config::get('otrs.password');

    $url = sprintf('%s/%s', Config::get('otrs.restUrl'), $page);

    if ($method == SELF::METHOD_GET) {
      // URL-encode the params
      $getArgs = [];
      foreach ($params as $key => $value) {
        $getArgs[] = "{$key}=" . urlencode($value);
      }

      $url .= '?' . implode('&', $getArgs);
      list($response, $httpCode) = util_fetchUrl($url);

    } else {
      $jsonData = json_encode($params);
      list($response, $httpCode) = util_makeRequest($url, $jsonData, $method);
    }

    if ($httpCode != 200) {
      throw new Exception('Eroare la comunicarea cu OTRS');
    }

    return json_decode($response);
  }

  static function getTicket($ticketId) {
    return self::restQuery('TicketGet', [
      'TicketID' => $ticketId,
      'AllArticles' => '1',
    ]);
  }
  
  static function closeTicket($ticketId) {
    return self::restQuery('TicketUpdate', [
      'TicketID' => $ticketId,
      'Ticket' => [
        'State' => 'closed successful',
      ],
    ], self::METHOD_PATCH);
  }

  static function searchTickets($params) {
    return self::restQuery('TicketSearch', $params);
  }

}
