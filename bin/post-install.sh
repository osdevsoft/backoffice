#!/bin/bash

#osds config
cp ./vendor/osds/backoffice/data/example/config/packages/osds_backoffice.yaml ./config/backoffice/osds_backoffice.yaml
cp ./vendor/osds/backoffice/data/example/config/routes/osds_backoffice.yaml ./config/routes/osds_backoffice.yaml

cat ./vendor/osds/backoffice/data/example/.env >> .env
