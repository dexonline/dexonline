alter table Definition add abbrevReview int after status;
update Definition set abbrevReview = 0;
