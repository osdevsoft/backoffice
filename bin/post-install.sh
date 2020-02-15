#!/bin/bash

#osds config
cp ./vendor/osds/backoffice/src/Infrastructure/Symfony/config/packages/osds_backoffice.yaml ./config/packages/osds_backoffice.yaml
cat ./vendor/osds/backoffice/src/Infrastructure/Symfony/config/routes/osds_backoffice.yaml >> ./config/routes/routes.yaml

#maybe just comment it...
rm ./config/packages/twig.yaml

cat ./vendor/osds/backoffice/src/Infrastructure/Symfony/.env >> .env

ln -s ../sites_configurations sites_configurations

#copy public files
cp -r ./vendor/osds/backoffice/data/public/* ./public/*

