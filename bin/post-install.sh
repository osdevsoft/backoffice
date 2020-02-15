#!/bin/bash

#osds config
cp ./vendor/osds/backoffice/src/Infrastructure/Symfony/config/packages/osds_backoffice.yaml ./config/packages/osds_backoffice.yaml
cat ./vendor/osds/backoffice/src/Infrastructure/Symfony/config/routes/osds_backoffice.yaml >> ./config/routes/routes.yaml

ln -s ../sites_configurations sites_configurations

#nothing to copy
#cat ./vendor/osds/backoffice/example/.env >> .env
