<?php
// Public index page (serves the sign-in button and JS)
$clientId = getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_CLIENT_ID';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <script src="https://accounts.google.com/gsi/client" async></script>

    <script>
      function decodeJWT(token) {

        let base64Url = token.split(".")[1];
        let base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
        let jsonPayload = decodeURIComponent(
          atob(base64)
            .split("")
            .map(function (c) {
              return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
            })
            .join("")
        );
        return JSON.parse(jsonPayload);
      }

      function renderMessage(message) {
        const output = document.getElementById("verify-output");
        if (!output) return;
        output.textContent = message || "";
      }

      async function handleCredentialResponse(response) {
        try {
          const res = await fetch("/verify.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: "id_token=" + encodeURIComponent(response.credential),
          });

          if (!res.ok) {
            renderMessage(`Verify failed (${res.status})`);
            return;
          }

          const data = await res.json().catch(() => null);
          if (!data || !data.ok) {
            renderMessage(data?.error || "Invalid response");
            return;
          }

          if (data.exists) {
            window.location.href = "/landing.php";
            return;
          }

          const confirmCreate = window.confirm(
            "No account found. Create one now?"
          );
          if (!confirmCreate) {
            renderMessage("Account creation cancelled.");
            return;
          }

          const createRes = await fetch("/create_account.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: "id_token=" + encodeURIComponent(response.credential),
          });

          if (!createRes.ok) {
            renderMessage(`Create failed (${createRes.status})`);
            return;
          }

          const createData = await createRes.json().catch(() => null);
          if (createData?.ok) {
            window.location.href = "/landing.php";
            return;
          }

          renderMessage(createData?.error || "Create failed");
        } catch (err) {
          renderMessage("Verify request error");
        }
      }
    </script>
  </head>
  <body>
    <!-- g_id_onload contains Google Identity Services settings -->
    <div
      id="g_id_onload"
      data-auto_prompt="false"
      data-callback="handleCredentialResponse"
      data-client_id="<?php echo htmlspecialchars($clientId); ?>"
    ></div>
    <!-- g_id_signin places the button on a page and supports customization -->
    <div class="g_id_signin"></div>
    <pre id="verify-output" style="white-space: pre-wrap;"></pre>
  </body>
</html>
