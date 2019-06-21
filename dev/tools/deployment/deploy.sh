#!/usr/bin/env bash
# Prepend deployment script with directory
echo -e "cd $DEPLOYMENT_DIR\n" | cat - dev/tools/deployment/promote-candidate.sh > dev/tools/deployment/promote-candidate-cd.sh
ssh -A -ttv -o StrictHostKeyChecking=no $PROXY_SERVER -p $PROXY_PORT ssh -A -ttv -o StrictHostKeyChecking=no $DEPLOYMENT_SERVER -p $DEPLOYMENT_PORT < dev/tools/deployment/promote-candidate-cd.sh
