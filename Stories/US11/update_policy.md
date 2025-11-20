# Politique de mise à jour ProjectHub

## Processus et fréquence
- **Fréquence** : contrôle hebdomadaire chaque lundi matin.
- **Sources de vérification** :
  - Tableau de bord AlwaysData (versions PHP, MySQL/MariaDB, Debian Bullseye, nginx, certificats SSL, tâches planifiées).
  - Bulletins de sécurité PHP : https://www.php.net/security
  - Notes de version Debian : https://www.debian.org/security/
  - Newsletter AlwaysData / flux RSS incidents.
- **Méthode** :
  1. Se connecter à l’espace AlwaysData ProjectHub.
  2. Relever la version courante de chaque composant listé ci-dessous et comparer à la dernière version stable supportée.
  3. Documenter les résultats dans le tableau hebdomadaire (voir section suivante).
  4. Si une mise à jour est recommandée, appliquer l’action via le manager AlwaysData (changement de version PHP, redéploiement, etc.) puis tester l’application.
  5. Créer un ticket interne “Patch cycle semaine XX – AlwaysData” récapitulant les vérifications et actions.

---

## Tableau de suivi hebdomadaire
| Semaine | Composant | Version courante | Dernière version dispo / source | Action |
|---------|-----------|------------------|---------------------------------|--------|
|  | PHP runtime (AlwaysData) |  |  |  |
|  | Extension `openssl` |  |  |  |
|  | Extension `curl` |  |  |  |
|  | Extension `pdo_mysql` |  |  |  |
|  | Extension `gd` |  |  |  |
|  | Extension `mbstring` |  |  |  |
|  | Extension `intl` |  |  |  |
|  | Extension `zip` |  |  |  |
|  | Extension `imagick` |  |  |  |
|  | Extension `redis` |  |  |  |
|  | Serveur web nginx (AlwaysData) |  |  |  |
|  | OS hôte Debian Bullseye |  |  |  |
|  | Base MySQL/MariaDB (AlwaysData) |  |  |  |
|  | Certificats SSL Let’s Encrypt |  |  |  |
|  | Accès SSH / OpenSSH |  |  |  |

Notes :
- Même si certaines extensions ou composants ne sont pas utilisés actuellement, une vérification “N/A – non utilisé” doit être consignée pour confirmer la revue hebdo.
- Ajouter toute nouvelle dépendance logicielle dans ce tableau dès son introduction.

---

## Gestion des patchs critiques
1. Surveiller les alertes AlwaysData et les CVE PHP/Debian.
2. En cas de patch critique :
   - Ouvrir immédiatement un ticket “Patch critique – [Composant] – CVE-XXXX”.
   - Décrire la vulnérabilité, la version impactée, l’action corrective (ex : passage à PHP 8.2.20) et les tests réalisés.
   - Conserver les logs ou captures AlwaysData prouvant l’application du patch.
3. Reporter dans le tableau hebdo l’action réalisée et clôturer le ticket après validation fonctionnelle.
