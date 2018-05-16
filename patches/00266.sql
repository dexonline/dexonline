update Definition set abbrevReview = 0;

alter table Definition
  change abbrevReview hasAmbiguousAbbreviations int not null default 0;
