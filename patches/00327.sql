alter table WordOfTheMonth add column sponsor varchar(255) COLLATE utf8mb4_romanian_ci NOT NULL DEFAULT '' after description;
