# OAuth Playground - PHP + Docker

A demonstration application showcasing OAuth 2.0 authentication flows with multiple providers (Google, GitHub, and Microsoft) in a PHP + Docker environment.

## Features

- 🔐 **Multiple OAuth Providers**: Google, GitHub, and Microsoft
- 🎨 **Consistent UI**: Beautiful, responsive interface for all providers
- 🔒 **Secure Sessions**: HTTP-only cookies, CSRF protection, and secure token handling
- 💾 **User Persistence**: MySQL database storing user data and OAuth claims
- 🐳 **Docker Ready**: Complete Docker setup with docker-compose
- 📝 **Clear Documentation**: Step-by-step setup instructions

## Architecture

### Project Structure

```
.
├── docker-compose.yml          # Docker orchestration
├── Dockerfile                  # PHP + Apache container
├── .env.example               # Environment variables template
├── database/
│   └── init.sql               # Database schema
├── public/                    # Web root
│   ├── index.php             # Landing page / Dashboard
│   ├── login.php             # OAuth initiation
│   ├── callback.php          # OAuth callback handler
│   ├── logout.php            # Logout handler
│   └── .htaccess             # Apache rewrite rules
└── src/
    ├── config.php            # Configuration & utilities
    ├── OAuth/
    │   ├── OAuthProvider.php     # Base OAuth class
    │   ├── GoogleProvider.php    # Google OAuth implementation
    │   ├── GitHubProvider.php    # GitHub OAuth implementation
    │   └── MicrosoftProvider.php # Microsoft OAuth implementation
    └── Views/
        ├── layout.php        # Base HTML layout
        ├── login.php         # Login page template
        └── dashboard.php     # Dashboard template
```

### Database Schema

The application uses a single `users` table:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider VARCHAR(50) NOT NULL,           -- 'google', 'github', or 'microsoft'
    provider_user_id VARCHAR(255) NOT NULL,  -- Provider's unique user ID
    email VARCHAR(255),                      -- User's email
    name VARCHAR(255),                       -- User's display name
    avatar TEXT,                             -- Avatar URL or data URI
    token_claims JSON,                       -- Raw OAuth token claims
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_provider_user (provider, provider_user_id),
    INDEX idx_email (email)
);
```

## Setup Instructions

### Prerequisites

- Docker and Docker Compose installed
- OAuth application credentials from Google, GitHub, and/or Microsoft

### 1. Clone the Repository

```bash
git clone <repository-url>
cd new_thing_docker
```

### 2. Configure Environment Variables

Copy the example environment file:

```bash
cp .env.example .env
```

Edit `.env` and configure your OAuth credentials:

```env
# Application Configuration
APP_URL=http://localhost:8080
SESSION_SECRET=<generate-a-random-string>

# Database Configuration (defaults are fine for local development)
DB_HOST=db
DB_PORT=3306
DB_NAME=oauth_playground
DB_USER=root
DB_PASSWORD=rootpass

# OAuth Provider Credentials (see setup instructions below)
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret

GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret

MICROSOFT_CLIENT_ID=your_microsoft_client_id
MICROSOFT_CLIENT_SECRET=your_microsoft_client_secret
```

### 3. OAuth Provider Setup

#### Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the "Google+ API"
4. Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client ID"
5. Configure the OAuth consent screen
6. Set application type to "Web application"
7. Add authorized redirect URI: `http://localhost:8080/callback.php?provider=google`
8. Copy the Client ID and Client Secret to your `.env` file

#### GitHub OAuth Setup

1. Go to [GitHub Developer Settings](https://github.com/settings/developers)
2. Click "New OAuth App"
3. Fill in the application details:
   - Application name: OAuth Playground
   - Homepage URL: `http://localhost:8080`
   - Authorization callback URL: `http://localhost:8080/callback.php?provider=github`
4. Copy the Client ID and Client Secret to your `.env` file

#### Microsoft OAuth Setup

1. Go to [Azure Portal](https://portal.azure.com/)
2. Navigate to "Azure Active Directory" → "App registrations" → "New registration"
3. Fill in the application details:
   - Name: OAuth Playground
   - Supported account types: Accounts in any organizational directory and personal Microsoft accounts
   - Redirect URI: Web - `http://localhost:8080/callback.php?provider=microsoft`
4. After creation, go to "Certificates & secrets" → "New client secret"
5. Copy the Application (client) ID and Client Secret to your `.env` file

### 4. Build and Run

Build and start the containers:

```bash
docker-compose up -d --build
```

Wait for the containers to start (about 30 seconds). You can check the status:

```bash
docker-compose ps
```

### 5. Access the Application

Open your browser and navigate to:

```
http://localhost:8080
```

You should see the OAuth Playground login page with buttons for each configured provider.

## Usage

1. **Login**: Click on any provider button (Google, GitHub, or Microsoft)
2. **Authorize**: You'll be redirected to the provider's login page. Log in and authorize the application
3. **Dashboard**: After successful authentication, you'll be redirected back to the dashboard
4. **View Profile**: The dashboard displays your profile information and raw OAuth claims
5. **Logout**: Click the logout button to end your session

## Security Features

- **CSRF Protection**: State parameter validation on OAuth callbacks
- **Secure Sessions**: HTTP-only cookies with secure configuration
- **Environment Variables**: Sensitive credentials stored in environment variables
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Prevention**: HTML escaping for all user-generated content
- **Security Headers**: X-Content-Type-Options, X-Frame-Options, X-XSS-Protection

## Development

### View Logs

```bash
docker-compose logs -f web
docker-compose logs -f db
```

### Access the Database

```bash
docker-compose exec db mysql -u root -p oauth_playground
```

### Restart Services

```bash
docker-compose restart
```

### Stop Services

```bash
docker-compose down
```

### Rebuild After Code Changes

```bash
docker-compose down
docker-compose up -d --build
```

## Troubleshooting

### "Database connection failed"

- Make sure the database container is running: `docker-compose ps`
- Wait a few seconds for MySQL to initialize
- Check database logs: `docker-compose logs db`

### "OAuth is not configured"

- Verify that your `.env` file exists and contains valid OAuth credentials
- Make sure to restart containers after changing `.env`: `docker-compose restart`

### OAuth Provider Errors

- Verify that redirect URIs in your OAuth app configuration match exactly
- For local development, use `http://localhost:8080` (not `127.0.0.1`)
- Check that your OAuth app is not restricted by domain or IP

### Port Already in Use

If port 8080 is already in use, edit `docker-compose.yml` and change:

```yaml
ports:
  - "8080:80"  # Change 8080 to another port
```

## Technology Stack

- **PHP 8.2**: Modern PHP with improved performance and features
- **Apache 2.4**: Web server with mod_rewrite
- **MySQL 8.0**: Database for user persistence
- **Docker & Docker Compose**: Containerization and orchestration
- **OAuth 2.0**: Industry-standard authorization framework

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
