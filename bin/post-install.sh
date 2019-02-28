#!/bin/bash

#osds config
cp ./vendor/osds/backoffice/src/Infrastructure/Symfony/config/packages/osds_backoffice.yaml ./config/backoffice/osds_backoffice.yaml
cp ./vendor/osds/backoffice/src/Infrastructure/Symfony/config/routes/osds_backoffice.yaml ./config/routes/osds_backoffice.yaml

cat ./vendor/osds/backoffice/src/Infrastructure/Symfony/.env >> .env
