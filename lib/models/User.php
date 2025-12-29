<?php

class User extends BaseObject {
  public static $_table = 'User';

  public const PRIV_ADMIN = 0x01;
  public const PRIV_VIEW_HIDDEN = 0x02;
  public const PRIV_EDIT = 0x04;
  public const PRIV_WOTD = 0x08;
  public const PRIV_STRUCT = 0x10;
  public const PRIV_VISUAL = 0x20;
  public const PRIV_DONATION = 0x40;
  public const PRIV_TRAINEE = 0x80;
  public const PRIV_ORIGINAL = 0x100;
  public const PRIV_PLUGIN = 0x200;
  public const NUM_PRIVILEGES = 10;

  public const PRIV_NAMES = [
    self::PRIV_ADMIN => 'administrator',
    self::PRIV_VIEW_HIDDEN => 'definiții și surse ascunse',
    self::PRIV_EDIT => 'moderator',
    self::PRIV_WOTD => 'cuvântul zilei',
    self::PRIV_STRUCT => 'structurist',
    self::PRIV_VISUAL => 'dicționarul vizual',
    self::PRIV_DONATION => 'procesare donații',
    self::PRIV_TRAINEE => 'stagiar',
    self::PRIV_ORIGINAL => 'originale scanate',
    self::PRIV_PLUGIN => 'testare pluginuri',
  ];

  public const PRIV_ANY = (1 << self::NUM_PRIVILEGES) - 1;

  private static ?User $active = null; // user currently logged in

  public function __toString() {
    return $this->nick ?? 'Anonymous';
  }

  public static function getStructurists($includeUserId = 0): array
  {
    if (!$includeUserId) {
      $includeUserId = null; // prevent loading the Anonymous user (id = 0)
    }
    return Model::factory('User')
      ->where_raw('(moderator & ?) or (id = ?)', [self::PRIV_STRUCT, $includeUserId])
      ->order_by_asc('nick')
      ->find_many();
  }

  // If the user does not have at least one privilege from the mask, redirect to the home page.
  public static function mustHave($priv): void
  {
    if (!self::can($priv)) {
      FlashMessage::add('Nu aveți privilegii suficiente pentru a accesa această pagină.');
      Util::redirectToHome();
    }
  }

  // Check if the user has at least one privilege from the mask.
  public static function can($priv) {
    return self::$active
      ? (self::$active->moderator & $priv)
      : false;
  }

  public static function getActive(): ?User
  {
    return self::$active;
  }

  public static function getActiveId() {
    return self::$active ? self::$active->id : 0;
  }

  public static function setActive($userId): void
  {
    self::$active = User::get_by_id($userId);
  }

  public static function isTrainee() {
    return self::can(self::PRIV_TRAINEE);
  }

  // Checks if the user can claim this email when registering or editing their profile.
  // Returns null on success or an error message on errors.
  public static function canChooseEmail($email): ?string
  {
    if (!$email) {
      return null; // it's optional
    }

    $u = User::get_by_email($email);
    if ($u && $u->id != self::getActiveId()) {
      return 'Această adresă de e-mail este deja folosită.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return 'Adresa de e-mail pare incorectă.';
    }

    return null;
  }

  public static function validateNewPassword($password, $password2, &$errors, $field): void
  {
    if (!$password) {
      $errors[$field][] = 'Parola nu poate fi vidă.';
    } else if (!$password2) {
      $errors[$field][] = 'Introdu parola de două ori pentru verificare.';
    } else if ($password != $password2) {
      $errors[$field][] = 'Parolele nu coincid.';
    } else if (strlen($password) < 8) {
      $errors[$field][] = 'Parola trebuie să aibă minimum 8 caractere.';
    }
  }
}
