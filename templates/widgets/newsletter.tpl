<div class="widget newsletter d-flex flex-md-column flex-xl-row">
  <div class="flex-grow-1">
    <h4>{t}Newsletter{/t}</h4>
    <p style="text-align: left;margin-bottom: 8px;">{t}Do you want to subscribe? (see <a href="/newsletter">archive</a>){/t}</p>

    <form target="_self" method="post" action="https://blog.dexonline.ro/wp-admin/admin-post.php?action=mailpoet_subscription_form">
      <input type="hidden" name="data[form_id]" value="2">
      <input type="hidden" name="token" value="c1e6b7c524">
      <input type="hidden" name="api_version" value="v1">
      <input type="hidden" name="endpoint" value="subscribers">
      <input type="hidden" name="mailpoet_method" value="subscribe">

      <label style="display: none !important;">{t}Please leave this field empty{/t}<input type="email" name="data[email]"></label>

      <div class="input-group">
        <input
          class="input-group-text"
          style="padding: 6px;"
          type="email"
          autocomplete="email"
          id="form_email_2"
          name="data[form_field_YWY1NmM5NmJjMDQyX2VtYWls]"
          title="{t}Email address{/t}"
          value=""
          placeholder="{t}email address{/t}">
        <input class="btn btn-primary" type="submit" value="{t}Yes!{/t}">
      </div>
    </form>

  </div>
</div>
