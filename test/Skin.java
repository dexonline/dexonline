public enum Skin {
  OLIMP("olimp"),
  POLAR("polar"),
  SIMPLE("simple"),
  SLICK("slick");

  private String displayName;

  Skin(String displayName) {
    this.displayName = displayName;
  }

  public String getDisplayName() {
    return displayName;
  }
}
