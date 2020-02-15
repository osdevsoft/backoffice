#!/bin/bash

composer create-project symfony/website-skeleton backoffice-web
./vendor/osds/backoffice/bin/post-install.sh
