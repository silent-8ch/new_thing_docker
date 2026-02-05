<?php
session_start();
if (!empty($_SESSION['user'])) {
  header('Location: /landing.php');
  exit;
}
// Public index page (serves the sign-in button and JS)
$clientId = getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_CLIENT_ID';
$appleClientId = getenv('APPLE_CLIENT_ID') ?: '';
$appleRedirectUri = getenv('APPLE_REDIRECT_URI') ?: '';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <script src="https://accounts.google.com/gsi/client" async></script>
    <script src="https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js" async></script>
    <style>
      body {
        font-family: Arial, sans-serif;
        padding: 24px;
      }
      .auth-buttons {
        display: flex;
        flex-direction: column;
        gap: 12px;
        max-width: 320px;
      }
      #appleid-signin {
        width: 100%;
      }
    </style>

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

      async function handleAppleSignIn() {
        try {
          if (!window.AppleID) {
            renderMessage("Apple JS not loaded yet.");
            return;
          }
          const appleResponse = await AppleID.auth.signIn();
          const idToken = appleResponse?.authorization?.id_token;
          if (!idToken) {
            renderMessage("Apple sign-in failed: missing id_token");
            return;
          }

          const res = await fetch("/apple_verify.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: "id_token=" + encodeURIComponent(idToken),
          });

          if (!res.ok) {
            renderMessage(`Apple verify failed (${res.status})`);
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
            "No Apple account found. Create one now?"
          );
          if (!confirmCreate) {
            renderMessage("Account creation cancelled.");
            return;
          }

          const createRes = await fetch("/apple_create_account.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: "id_token=" + encodeURIComponent(idToken),
          });

          if (!createRes.ok) {
            renderMessage(`Apple create failed (${createRes.status})`);
            return;
          }

          const createData = await createRes.json().catch(() => null);
          if (createData?.ok) {
            window.location.href = "/landing.php";
            return;
          }

          renderMessage(createData?.error || "Create failed");
        } catch (err) {
          renderMessage("Apple sign-in error");
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
    <div class="auth-buttons">
      <div class="g_id_signin" data-width="240"></div>
      <div
        id="appleid-signin"
        data-type="sign in"
        data-color="black"
        data-border="true"
        data-border-radius="8"
        data-width="240"
        data-height="40"
      ></div>
    </div>
    <pre id="verify-output" style="white-space: pre-wrap;"></pre>
    <script>
      (function initApple() {
        const clientId = "<?php echo htmlspecialchars($appleClientId); ?>";
        const redirectUri = "<?php echo htmlspecialchars($appleRedirectUri); ?>";
        if (!clientId || !redirectUri) {
          return;
        }
        if (!window.AppleID) {
          setTimeout(initApple, 300);
          return;
        }
        AppleID.auth.init({
          clientId: clientId,
          scope: "name email",
          redirectURI: redirectUri,
          usePopup: true,
        });
        const btn = document.getElementById("appleid-signin");
        if (btn) {
          btn.addEventListener("click", handleAppleSignIn);
        }
      })();
    </script>
  </body>
</html>
