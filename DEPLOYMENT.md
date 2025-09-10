# Deployment

The application requires Google reCAPTCHA credentials.

## Configure reCAPTCHA keys

Set the following environment variables on the production server or provide them in a `.env` file located at the project root:

- `RECAPTCHA_SITE_KEY` – public site key used by the client.
- `RECAPTCHA_SECRET_KEY` – secret key used for server-side verification.

### Using environment variables

```bash
export RECAPTCHA_SITE_KEY="<your site key>"
export RECAPTCHA_SECRET_KEY="<your secret key>"
```
Ensure these are loaded for the web server process (e.g. via the service configuration).

### Using a `.env` file

Create a file named `.env` with the keys:

```env
RECAPTCHA_SITE_KEY=<your site key>
RECAPTCHA_SECRET_KEY=<your secret key>
```

The `.env` file is ignored by Git and should never be committed. Keep it secure.
