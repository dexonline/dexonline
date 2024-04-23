<div class="widget newsletter d-flex flex-md-column flex-xl-row">
  <div class="flex-grow-1">
    <h4>Newsletter</h4><br>
    <form target="_self" method="post" action="https://blog.dexonline.ro/wp-admin/admin-post.php?action=mailpoet_subscription_form">
      <input type="hidden" name="data[form_id]" value="2">
      <input type="hidden" name="token" value="c1e6b7c524">
      <input type="hidden" name="api_version" value="v1">
      <input type="hidden" name="endpoint" value="subscribers">
      <input type="hidden" name="mailpoet_method" value="subscribe">

      <label style="display: none !important;">Te rog lasă gol acest câmp<input type="email" name="data[email]"></label>

      <div class="input-group">
        <span class="input-group-text">
          {include "bits/icon.tpl" i=email}
        </span>
        <input
          type="email"
          autocomplete="email"
          id="form_email_2"
          name="data[form_field_YWY1NmM5NmJjMDQyX2VtYWls]"
          title="Adresă de email"
          value=""
          placeholder="adresa de email">
      </div>
      <input class="btn btn-primary" type="submit" value="Abonează-te!">
    </form>

  </div>
</div>
