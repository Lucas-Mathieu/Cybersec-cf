# Rapport d'audit sécurité – ProjectHub

## 3. Checklist OWASP Top 10 (2021)
| ID | Libellé | État | Commentaire |
|----|---------|------|-------------|
| A01 | Broken Access Control | Conforme (scope public) | Routes critiques protégées par middleware `AuthController`, pas de bypass détecté côté scan public. |
| A02 | Cryptographic Failures | Conforme | HSTS/CSP Présent. |
| A03 | Injection | Conforme | Pas d’input injectable détecté par ZAP (SQL/command). |
| A04 | Insecure Design | JSP |  |
| A05 | Security Misconfiguration | Conforme | Présent. |
| A06 | Vulnerable & Outdated Components | Conforme | Processus de patch documenté (cf. `update_policy.md`). |
| A07 | Identification & Authentication Failures | Conforme | Auth endpoints présents, pas de fuite d’identifiants. |
| A08 | Software and Data Integrity Failures | JSP |  |
| A09 | Security Logging & Monitoring Failures | Conforme | Logger PHP actif (`core/Logger.php`). |
| A10 | Server-Side Request Forgery | Conforme | Aucun proxy côté serveur, endpoints ne relaient pas d’URL externes. |
