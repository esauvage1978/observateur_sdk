# Observateur PHP SDK

Client PHP **fail-open** pour l’API d’ingestion [Observateur](https://api.observateurcentral.fr).

Une panne de monitoring ne doit jamais casser l’application cliente : timeout court (2 s), aucune exception métier si `failOpen` est activé.

## Installation

```bash
composer require observateur/php-sdk
```

Dépôt : [github.com/esauvage1978/observateur_sdk](https://github.com/esauvage1978/observateur_sdk)

## Configuration

```env
OBSERVATEUR_API_KEY=obs_production_xxxxxxxx
OBSERVATEUR_URL=https://api.observateurcentral.fr
```

La clé API s’obtient dans le dashboard Observateur (**Applications** → créer / générer une clé). Elle n’est affichée qu’une fois. Une clé = un couple application + environnement.

Ne jamais envoyer `organizationId` ni `applicationId` : ils sont déduits de la clé.

## Usage

```php
use Observateur\Client;
use Observateur\MonologHandler;

$client = new Client(
    apiKey: $_ENV['OBSERVATEUR_API_KEY'],
    baseUrl: $_ENV['OBSERVATEUR_URL'] ?? 'https://api.observateurcentral.fr',
    failOpen: true,
);

$client->info('Application démarrée', ['service' => 'checkout']);
$client->error('Paiement refusé', ['quoteId' => $id]);
$client->metrics([['name' => 'checkout.duration_ms', 'type' => 'histogram', 'value' => 245, 'unit' => 'ms']]);
$client->heartbeat('cron-facturation');
$client->deployment('1.4.2', 'abc123');
```

### Monolog (Symfony)

```php
$logger->pushHandler(new MonologHandler($client));
```

## Prérequis

- PHP 8.3+
- Clé API Observateur (`X-Api-Key`)

## Licence

Propriétaire. Voir `LICENSE`.
