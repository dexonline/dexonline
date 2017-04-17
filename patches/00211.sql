update User
  set moderator = ((moderator >> 6) << 4) | ((moderator & 0x10) >> 1) | (moderator & 0x7);
