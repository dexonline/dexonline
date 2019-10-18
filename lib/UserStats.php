<?php

class UserStats {
  static function getTopArtists() {
    $today = date('Y-m-d');
    $topArtists = Model::factory('User')
      ->table_alias('u')
      ->select('u.id')
      ->select_expr('count(*)', 'c')
      ->join('WotdArtist', ['a.userId', '=', 'u.id'], 'a')
      ->join('WotdAssignment', ['s.artistId', '=', 'a.id'], 's')
      ->where_lte('s.date', $today)
      ->group_by('u.id')
      ->find_array();

    return $topArtists;
  }

  static function getEmailContribution() {
    $query = 'select users.login, count(*) as count ' .
      'from otrs.users ' .
      'join otrs.article on users.id = article.change_by ' .
      'where users.id != 1 ' .
      'group by users.id';
    $dbResult = DB::execute($query, PDO::FETCH_ASSOC);

    return $dbResult;
  }

  static function getDonors($minDonation) {
    $donors = Model::factory('User')
      ->table_alias('u')
      ->select('u.*')
      ->select_expr('sum(d.amount)', 'total')
      ->distinct()
      ->join('Donation', ['d.email', '=', 'u.email'], 'd')
      ->where('anonymousDonor', 0)
      ->where_raw('!(medalMask & ?)', Medal::MEDAL_SPONSOR)
      ->group_by('u.id')
      ->having_raw('total >= ?', $minDonation)
      ->find_many();

    return $donors;
  }

  static function getBannerFreeAccounts($minDonation, $mustSeeBanners) {
    $noBanners = Model::factory('User')
      ->table_alias('u')
      ->select('u.*')
      ->select_expr('sum(d.amount)', 'total')
      ->distinct()
      ->join('Donation', ['d.email', '=', 'u.email'], 'd')
      ->where_not_in('u.id', $mustSeeBanners)
      ->where_raw('d.createDate >= u.noAdsUntil')
      ->group_by('u.id')
      ->having_raw('total >= ?', $minDonation)
      ->find_many();

    return $noBanners;
  }
}
