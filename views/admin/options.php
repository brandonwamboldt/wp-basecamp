<div class="wrap">
  <div id="icon-options-general" class="icon32"><br></div>
  <h2>Basecamp Integration Settings</h2>

  <h3 class="title">37signals API settings</h3>

  <p>
    <a href="http://integrate.37signals.com/apps/new">Register a new application</a> for integration with a 37signals product,
    or <a href="http://integrate.37signals.com/">update your already registered application</a>.
  </p>

  <form method="post">
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row"><label for="api_client_id">Client ID</label></th>
          <td>
            <input name="api_client_id" type="text" id="api_client_id" value="<?= $client_id ?>" class="regular-text code">
          </td>
        </tr>

        <tr valign="top">
          <th scope="row"><label for="api_client_secret">Client Secret</label></th>
          <td>
            <input name="api_client_secret" type="text" id="api_client_secret" value="<?= $client_secret ?>" class="regular-text code">
          </td>
        </tr>

        <tr valign="top">
          <th scope="row"><label for="api_redirect_uri">Redirect URI</label></th>
          <td>
            <input name="api_redirect_uri" type="text" id="api_redirect_uri" value="<?= $redirect_uri ?>" class="regular-text" disabled>
          </td>
        </tr>

        <tr valign="top">
          <th scope="row"><label for="api_auth_endpoint">Auth Endpoint</label></th>
          <td>
            <input name="api_auth_endpoint" type="text" id="api_auth_endpoint" value="<?= $auth_endpoint ?>" class="regular-text">
          </td>
        </tr>

        <tr valign="top">
          <th scope="row"><label for="api_token_endpoint">Token Endpoint</label></th>
          <td>
            <input name="api_token_endpoint" type="text" id="api_token_endpoint" value="<?= $token_endpoint ?>" class="regular-text">
          </td>
        </tr>
      </tbody>
    </table>

    <h3>Basecamp Settings</h3>

    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row"><label for="organization_id">Organization ID</label></th>
          <td>
            <input name="organization_id" type="text" id="organization_id" value="<?= $organization_id ?>" class="regular-text code">
          </td>
        </tr>
      </tbody>
    </table>

    <p class="submit">
      <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
    </p>
  </form>
</div>
