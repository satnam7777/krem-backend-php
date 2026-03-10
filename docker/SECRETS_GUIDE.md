# Secrets guide (production)

For production, do NOT keep passwords in `.env` committed to git.

Options:
1) Docker Swarm / Kubernetes secrets
2) Vault (best)
3) At minimum, `.env` outside repo + CI injects secrets

Variables to treat as secrets:
- DB_PASSWORD, TENANT_DB_PASSWORD
- APP_KEY
- Any SMTP/API keys

Minimum safe pattern:
- `.env` is ignored by git
- Use per-environment `.env.production` stored securely
