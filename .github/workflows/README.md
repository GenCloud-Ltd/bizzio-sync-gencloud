# GitHub Actions Workflows

Два workflow-а: **QA** проверява всяка промяна, **Complete Release Pipeline** пуска версия само ако QA мине.

## `ci.yml` — QA

**Кога:** push в `main`/`dev`, PR към `main`/`dev`, ръчно, или извикан от `release.yml`.

| Job | Какво проверява |
|---|---|
| Version consistency | `Version:` в header-а = `Stable tag` в readme = `BIZZIO_SYNC_GENCLOUD_VERSION` = fallback в core класа; има changelog запис; при таг — тагът съвпада (`bin/check-versions.php`) |
| PHP 7.4 … 8.5 syntax | `php -l` на всеки PHP файл за всяка поддържана версия |
| PHPCS + PHPStan | WordPress Coding Standards + PHPCompatibilityWP (`phpcs.xml.dist`), PHPStan level 5 с WP/WC stubs (`phpstan.neon.dist`); грешките излизат като анотации в PR-а |
| Plugin Check | Официалният WP.org Plugin Check (strict + experimental) върху **билднатия ZIP**, т.е. точно това, което ще се качи |

Версиите на инструментите са заключени в `composer.lock`, затова локално и в CI резултатът е еднакъв.

## `release.yml` — Complete Release Pipeline

```
QA (ci.yml) ──► Build ZIP ──┬─► GitHub Release
                            └─► WordPress.org SVN (trunk + tags/<version>)
```

**Как се пуска версия:**
```bash
# 1. Вдигни версията: header + константа + fallback в core класа + readme (Stable tag + changelog)
composer qa                 # локално: PHPCS + PHPStan + Plugin Check (LocalWP)
php bin/check-versions.php  # трябва да отпечата новата версия
# 2. merge в main, после:
git tag v1.2.3 && git push origin v1.2.3
```

- При **таг `v*`**: QA → build → GitHub Release → deploy в WordPress.org.
- **Ръчно** (Actions → Complete Release Pipeline → Run workflow): QA → build → GitHub Release; deploy в WP.org само ако `deploy-to-wporg = true`.
- Всичко се билдва от тагнатия комит, а не от `main`.
- ZIP-ът се прави с `bin/build-zip.php` по `.distignore` (в корена на репото). В WP.org се качва съдържанието на същия ZIP.
- Банери/иконки за WP.org: сложи ги в `.wordpress-org/` в корена — 10up action-ът ги качва в `assets/`.

**Тайни:** `WP_SVN_USERNAME`, `WP_SVN_PASSWORD`.
**Environment:** deploy job-ът ползва environment `wordpress.org`. В Settings → Environments може да се добави задължително одобрение преди качване.

## Локално (същите проверки)

| Команда | Какво прави |
|---|---|
| `composer lint` / `composer lint:fix` | PHPCS / PHPCBF |
| `composer analyse` | PHPStan |
| `composer plugin-check` | Plugin Check в LocalWP сайта от `wp-cli.local.yml` |
| `composer build` | `build/bizzio-sync-for-woocommerce.zip` |
| `composer qa` | версии + lint + analyse + plugin-check |

Тестовият сайт се настройва с `bin/setup-local-site.sh` (виж коментара в скрипта).
