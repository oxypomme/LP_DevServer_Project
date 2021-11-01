# LP_DevServer_Project

SUBLET - TOUBON

## Production

```sh
docker-compose up --build
```

Note : Pour une raison obscure, le JWT n'est pas reconnu par le middleware. Nous conseillons de passer par la version de développement.

## Developpement

```sh
npm i
composer install
npm run docker
# Si le conteneur docker ne se lance pas automatiquement :
docker-compose -f "docker-compose.dev.yml" up --build
```
